<?php 
namespace Neos\ContentRepository\Command;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Exception\NodeTypeNotFoundException;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Cli\DescriptionAwareCommandControllerInterface;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Reflection\ReflectionService;
use Neos\ContentRepository\Domain\Repository\WorkspaceRepository;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;

/**
 * Node command controller for the Neos.ContentRepository package
 *
 * @Flow\Scope("singleton")
 */
class NodeCommandController_Original extends CommandController implements DescriptionAwareCommandControllerInterface
{
    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @Flow\Inject
     * @var WorkspaceRepository
     */
    protected $workspaceRepository;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $pluginConfigurations = [];

    /**
     * Repair inconsistent nodes
     *
     * This command analyzes and repairs the node tree structure and individual nodes
     * based on the current node type configuration.
     *
     * It is possible to execute only one or more specific checks by providing the <b>--skip</b>
     * or <b>--only</b> option. See the full description of checks further below for possible check
     * identifiers.
     *
     * The following checks will be performed:
     *
     * {pluginDescriptions}
     * <b>Examples:</b>
     *
     * ./flow node:repair
     *
     * ./flow node:repair --node-type Neos.NodeTypes:Page
     *
     * ./flow node:repair --workspace user-robert --only removeOrphanNodes,removeNodesWithInvalidDimensions
     *
     * ./flow node:repair --skip removeUndefinedProperties
     *
     * @param string $nodeType Node type name, if empty update all declared node types
     * @param string $workspace Workspace name, default is 'live'
     * @param boolean $dryRun Don't do anything, but report actions
     * @param boolean $cleanup If false, cleanup tasks are skipped
     * @param string $skip Skip the given check or checks (comma separated)
     * @param string $only Only execute the given check or checks (comma separated)
     * @return void
     * @throws StopActionException
     */
    public function repairCommand($nodeType = null, $workspace = 'live', $dryRun = false, $cleanup = true, $skip = null, $only = null)
    {
        $this->pluginConfigurations = self::detectPlugins($this->objectManager);

        /** @noinspection PhpUndefinedMethodInspection */
        if ($this->workspaceRepository->countByName($workspace) === 0) {
            $this->outputLine('Workspace "%s" does not exist', [$workspace]);
            exit(1);
        }

        if ($nodeType !== null) {
            try {
                $nodeType = $this->nodeTypeManager->getNodeType($nodeType);
            } catch (NodeTypeNotFoundException $e) {
                $this->outputLine('<error>Node type "%s" does not exist</error>', [$nodeType]);
                $this->quit(1);
                return;
            }
        }

        if ($dryRun) {
            $this->outputLine('Dry run, not committing any changes.');
        }

        if (!$cleanup) {
            $this->outputLine('Omitting cleanup tasks.');
        }

        foreach ($this->pluginConfigurations as $pluginConfiguration) {
            /** @var NodeCommandControllerPluginInterface $plugin */
            $plugin = $pluginConfiguration['object'];
            $this->outputLine('<b>' . $plugin->getSubCommandShortDescription('repair') . '</b>');
            $this->outputLine();
            if ($plugin instanceof EventDispatchingNodeCommandControllerPluginInterface) {
                $this->attachPluginEventHandlers($plugin, $dryRun);
            }
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $plugin->invokeSubCommand('repair', $this->output, $nodeType, $workspace, $dryRun, $cleanup, $skip, $only);
            $this->outputLine();
        }

        if ($dryRun) {
            $this->outputLine('Node repair dry run finished.');
        } else {
            $this->outputLine('<success>Node repair finished.</success>');
        }
    }

    /**
     * @param EventDispatchingNodeCommandControllerPluginInterface $plugin
     * @param bool $dryRun
     * @return void
     */
    private function attachPluginEventHandlers(EventDispatchingNodeCommandControllerPluginInterface $plugin, bool $dryRun): void
    {
        $plugin->on(EventDispatchingNodeCommandControllerPluginInterface::EVENT_NOTICE, function (string $text) {
            $this->outputLine($text);
        });
        $plugin->on(EventDispatchingNodeCommandControllerPluginInterface::EVENT_TASK, function (string $description, \Closure $task, bool $requiresConfirmation = false) use ($dryRun) {
            $text = sprintf(' <b>❱ %s</b> ', $description);

            if (!$dryRun && $requiresConfirmation) {
                $proceed = $this->output->askConfirmation($text . '- <comment>Proceed? (y/n)</comment>', false);
                if (!$proceed) {
                    $this->outputLine('    <comment>skipped ✖</comment>');
                    return;
                }
            } else {
                $this->outputLine($text);
            }
            if ($dryRun) {
                $this->outputLine('    skipped (dry run)');
            } else {
                $task();
                $this->outputLine('    <success>applied ✔</success>');
            }
        });
        $plugin->on(EventDispatchingNodeCommandControllerPluginInterface::EVENT_WARNING, function (string $text) {
            $this->outputLine('<comment>WARNING: %s</comment>', [$text]);
        });
        $plugin->on(EventDispatchingNodeCommandControllerPluginInterface::EVENT_ERROR, function (string $text) {
            $this->outputLine('<error>%s</error>', [$text]);
            $this->quit(1);
        });
    }

    /**
     * Processes the given short description of the specified command.
     *
     * @param string $controllerCommandName Name of the command the description is referring to, for example "flush"
     * @param string $shortDescription The short command description so far
     * @param ObjectManagerInterface $objectManager The object manager, can be used to access further information necessary for rendering the description
     * @return string the possibly modified short command description
     */
    public static function processShortDescription($controllerCommandName, $shortDescription, ObjectManagerInterface $objectManager)
    {
        return $shortDescription;
    }

    /**
     * Processes the given description of the specified command.
     *
     * @param string $controllerCommandName Name of the command the description is referring to, for example "flush"
     * @param string $description The command description so far
     * @param ObjectManagerInterface $objectManager The object manager, can be used to access further information necessary for rendering the description
     * @return string the possibly modified command description
     */
    public static function processDescription($controllerCommandName, $description, ObjectManagerInterface $objectManager)
    {
        $pluginConfigurations = self::detectPlugins($objectManager);
        $pluginDescriptions = '';
        foreach ($pluginConfigurations as $className => $configuration) {
            /** @noinspection PhpUndefinedMethodInspection */
            $pluginDescriptions .= $className::getSubCommandDescription($controllerCommandName) . PHP_EOL;
        }
        return str_replace('{pluginDescriptions}', $pluginDescriptions, $description);
    }

    /**
     * Detects plugins for this command controller
     *
     * @param ObjectManagerInterface $objectManager
     * @return array
     */
    protected static function detectPlugins(ObjectManagerInterface $objectManager)
    {
        $pluginConfigurations = [];
        $classNames = $objectManager->get(ReflectionService::class)->getAllImplementationClassNamesForInterface(NodeCommandControllerPluginInterface::class);
        foreach ($classNames as $className) {
            $pluginConfigurations[$className] = [
                'object' => $objectManager->get($objectManager->getObjectNameByClassName($className))
            ];
        }
        return $pluginConfigurations;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\ContentRepository\Command;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Node command controller for the Neos.ContentRepository package
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class NodeCommandController extends NodeCommandController_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\ContentRepository\Command\NodeCommandController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\ContentRepository\Command\NodeCommandController', $this);
        if (get_class($this) === 'Neos\ContentRepository\Command\NodeCommandController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Cli\DescriptionAwareCommandControllerInterface', $this);
        parent::__construct();
        if ('Neos\ContentRepository\Command\NodeCommandController' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __sleep()
    {
            $result = NULL;
        $this->Flow_Object_PropertiesToSerialize = array();

        $transientProperties = array (
);
        $propertyVarTags = array (
  'nodeTypeManager' => 'Neos\\ContentRepository\\Domain\\Service\\NodeTypeManager',
  'workspaceRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\WorkspaceRepository',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'pluginConfigurations' => 'array',
  'request' => 'Neos\\Flow\\Cli\\Request',
  'response' => 'Neos\\Flow\\Cli\\Response',
  'arguments' => 'Neos\\Flow\\Mvc\\Controller\\Arguments',
  'commandMethodName' => 'string',
  'commandManager' => 'Neos\\Flow\\Cli\\CommandManager',
  'output' => 'Neos\\Flow\\Cli\\ConsoleOutput',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\ContentRepository\Command\NodeCommandController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\ContentRepository\Command\NodeCommandController', $this);
        if (get_class($this) === 'Neos\ContentRepository\Command\NodeCommandController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Cli\DescriptionAwareCommandControllerInterface', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectCommandManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Cli\CommandManager'));
        $this->injectObjectManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\NodeTypeManager', 'Neos\ContentRepository\Domain\Service\NodeTypeManager', 'nodeTypeManager', 'e525e2ecb65f7f9731d6537ddecd16d4', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\NodeTypeManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'workspaceRepository', '9cacb5dd2ad57e06d6f8c82dd5707855', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\WorkspaceRepository'); });
        $this->Flow_Injected_Properties = array (
  0 => 'commandManager',
  1 => 'objectManager',
  2 => 'nodeTypeManager',
  3 => 'workspaceRepository',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.ContentRepository/Classes/Command/NodeCommandController.php
#