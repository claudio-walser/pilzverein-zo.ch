<?php 
namespace Neos\Neos\Fusion;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Http\Response;
use Neos\Flow\Mvc\ActionResponse;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Neos\Domain\Model\PluginViewDefinition;
use Neos\Neos\Service\PluginService;
use Neos\ContentRepository\Domain\Model\NodeInterface;

/**
 * A Fusion PluginView.
 */
class PluginViewImplementation_Original extends PluginImplementation
{
    /**
     * @var PluginService
     * @Flow\Inject
     */
    protected $pluginService;

    /**
     * @var NodeInterface
     */
    protected $pluginViewNode;

    /**
     * Build the proper pluginRequest to render the PluginView
     * of some configured Master Plugin
     *
     * @return ActionRequest
     */
    protected function buildPluginRequest()
    {
        /** @var $parentRequest ActionRequest */
        $parentRequest = $this->runtime->getControllerContext()->getRequest();
        $pluginRequest = new ActionRequest($parentRequest);

        if (!$this->pluginViewNode instanceof NodeInterface) {
            $pluginRequest->setArgumentNamespace('--' . $this->getPluginNamespace());
            $this->passArgumentsToPluginRequest($pluginRequest);
            $pluginRequest->setControllerPackageKey($this->getPackage());
            $pluginRequest->setControllerSubpackageKey($this->getSubpackage());
            $pluginRequest->setControllerName($this->getController());
            $pluginRequest->setControllerActionName($this->getAction());
            return $pluginRequest;
        }

        $pluginNodeIdentifier = $this->pluginViewNode->getProperty('plugin');
        if (strlen($pluginNodeIdentifier) === 0) {
            return $pluginRequest;
        }

        // Set the node to render this to the master plugin node
        $this->node = $this->pluginViewNode->getContext()->getNodeByIdentifier($pluginNodeIdentifier);
        if ($this->node === null) {
            return $pluginRequest;
        }

        $pluginRequest->setArgument('__node', $this->node);
        $pluginRequest->setArgumentNamespace('--' . $this->getPluginNamespace());
        $this->passArgumentsToPluginRequest($pluginRequest);

        if ($pluginRequest->getControllerObjectName() !== '') {
            return $pluginRequest;
        }

        $controllerObjectPairs = [];
        $pluginViewName = $this->pluginViewNode->getProperty('view');
        foreach ($this->pluginService->getPluginViewDefinitionsByPluginNodeType($this->node->getNodeType()) as $pluginViewDefinition) {
            /** @var PluginViewDefinition $pluginViewDefinition */
            if ($pluginViewDefinition->getName() !== $pluginViewName) {
                continue;
            }
            $controllerObjectPairs = $pluginViewDefinition->getControllerActionPairs();
            break;
        }

        if ($controllerObjectPairs === []) {
            return $pluginRequest;
        }

        $defaultControllerObjectName = key($controllerObjectPairs);
        $defaultActionName = current($controllerObjectPairs[$defaultControllerObjectName]);
        $pluginRequest->setControllerObjectName($defaultControllerObjectName);
        $pluginRequest->setControllerActionName($defaultActionName);

        return $pluginRequest;
    }

    /**
     * Returns the rendered content of this plugin
     *
     * @return string The rendered content as a string
     * @throws StopActionException
     */
    public function evaluate()
    {
        $currentContext = $this->runtime->getCurrentContext();
        $this->pluginViewNode = $currentContext['node'];
        /** @var $parentResponse Response */
        $parentResponse = $this->runtime->getControllerContext()->getResponse();
        $pluginResponse = new ActionResponse($parentResponse);

        $pluginRequest = $this->buildPluginRequest();
        if ($pluginRequest->getControllerObjectName() === '') {
            $message = 'Master View not selected';
            if ($this->pluginViewNode->getProperty('plugin')) {
                $message = 'Plugin View not selected';
            }
            if ($this->pluginViewNode->getProperty('view')) {
                $message ='Master View or Plugin View not found';
            }
            return $this->pluginViewNode->getContext()->getWorkspaceName() !== 'live' || $this->objectManager->getContext()->isDevelopment() ? '<p>' . $message . '</p>' : '<!-- ' . $message . '-->';
        }
        $this->dispatcher->dispatch($pluginRequest, $pluginResponse);
        return $pluginResponse->getContent();
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Fusion;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A Fusion PluginView.
 */
class PluginViewImplementation extends PluginViewImplementation_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param Runtime $runtime
     * @param string $path
     * @param string $fusionObjectName
     */
    public function __construct()
    {
        $arguments = func_get_args();

        if (!array_key_exists(0, $arguments)) $arguments[0] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Fusion\Core\Runtime');
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $runtime in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $path in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(2, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $fusionObjectName in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Neos\Fusion\PluginViewImplementation' === get_class($this)) {
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
  'pluginService' => 'Neos\\Neos\\Service\\PluginService',
  'pluginViewNode' => 'Neos\\ContentRepository\\Domain\\Model\\NodeInterface',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'dispatcher' => 'Neos\\Flow\\Mvc\\Dispatcher',
  'node' => 'Neos\\ContentRepository\\Domain\\Model\\NodeInterface',
  'documentNode' => 'Neos\\ContentRepository\\Domain\\Model\\NodeInterface',
  'properties' => 'array',
  'ignoreProperties' => 'array',
  'runtime' => 'Neos\\Fusion\\Core\\Runtime',
  'path' => 'string',
  'fusionObjectName' => 'string',
  'tsValueCache' => 'array',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\PluginService', 'Neos\Neos\Service\PluginService', 'pluginService', '9a5e6d9c8043f9403867eb9224792c01', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\PluginService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\Dispatcher', 'Neos\Flow\Mvc\Dispatcher', 'dispatcher', '8ddded8be27664ffb31ad6b8c6b2be64', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\Dispatcher'); });
        $this->Flow_Injected_Properties = array (
  0 => 'pluginService',
  1 => 'objectManager',
  2 => 'dispatcher',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Fusion/PluginViewImplementation.php
#