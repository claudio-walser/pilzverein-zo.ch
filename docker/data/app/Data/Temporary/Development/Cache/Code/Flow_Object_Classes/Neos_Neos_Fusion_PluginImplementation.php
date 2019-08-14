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
use Neos\Flow\Mvc\Dispatcher;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Fusion\FusionObjects\AbstractArrayFusionObject;

/**
 * A Fusion Plugin object.
 */
class PluginImplementation_Original extends AbstractArrayFusionObject
{
    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\Inject
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var NodeInterface
     */
    protected $node;

    /**
     * @var NodeInterface
     */
    protected $documentNode;

    /**
     * @return string
     */
    public function getPackage()
    {
        return $this->fusionValue('package');
    }

    /**
     * @return string
     */
    public function getSubpackage()
    {
        return $this->fusionValue('subpackage');
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->fusionValue('controller');
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->fusionValue('action');
    }

    /**
     * @return string
     */
    public function getArgumentNamespace()
    {
        return $this->fusionValue('argumentNamespace');
    }

    /**
     * Build the pluginRequest object
     *
     * @return ActionRequest
     */
    protected function buildPluginRequest()
    {
        /** @var $parentRequest ActionRequest */
        $parentRequest = $this->runtime->getControllerContext()->getRequest();
        $pluginRequest = new ActionRequest($parentRequest);
        $pluginRequest->setArgumentNamespace('--' . $this->getPluginNamespace());
        $this->passArgumentsToPluginRequest($pluginRequest);

        if ($this->node instanceof NodeInterface) {
            $pluginRequest->setArgument('__node', $this->node);
            if ($pluginRequest->getControllerPackageKey() === null) {
                $pluginRequest->setControllerPackageKey($this->node->getProperty('package') ?: $this->getPackage());
            }
            if ($pluginRequest->getControllerSubpackageKey() === null) {
                $pluginRequest->setControllerSubpackageKey($this->node->getProperty('subpackage') ?: $this->getSubpackage());
            }
            if ($pluginRequest->getControllerName() === null) {
                $pluginRequest->setControllerName($this->node->getProperty('controller') ?: $this->getController());
            }
            if ($pluginRequest->getControllerActionName() === null) {
                $actionName = $this->node->getProperty('action');
                if ($actionName === null || $actionName === '') {
                    $actionName = $this->getAction() !== null ? $this->getAction() : 'index';
                }
                $pluginRequest->setControllerActionName($actionName);
            }

            $pluginRequest->setArgument('__node', $this->node);
            $pluginRequest->setArgument('__documentNode', $this->documentNode);
        } else {
            $pluginRequest->setControllerPackageKey($this->getPackage());
            $pluginRequest->setControllerSubpackageKey($this->getSubpackage());
            $pluginRequest->setControllerName($this->getController());
            $pluginRequest->setControllerActionName($this->getAction());
        }

        foreach ($this->properties as $key => $value) {
            $pluginRequest->setArgument('__' . $key, $this->fusionValue($key));
        }
        return $pluginRequest;
    }

    /**
     * Returns the rendered content of this plugin
     *
     * @return string The rendered content as a string
     */
    public function evaluate()
    {
        $currentContext = $this->runtime->getCurrentContext();
        $this->node = $currentContext['node'];
        $this->documentNode = $currentContext['documentNode'];
        /** @var $parentResponse Response */
        $parentResponse = $this->runtime->getControllerContext()->getResponse();
        $pluginResponse = new ActionResponse($parentResponse);

        $this->dispatcher->dispatch($this->buildPluginRequest(), $pluginResponse);

        foreach ($pluginResponse->getHeaders()->getAll() as $key => $value) {
            $parentResponse->getHeaders()->set($key, $value, true);
        }

        return $pluginResponse->getContent();
    }

    /**
     * Returns the plugin namespace that will be prefixed to plugin parameters in URIs.
     * By default this is <plugin_class_name>
     *
     * @return string
     */
    protected function getPluginNamespace()
    {
        if ($this->getArgumentNamespace() !== null) {
            return $this->getArgumentNamespace();
        }

        if ($this->node instanceof NodeInterface) {
            $nodeArgumentNamespace = $this->node->getProperty('argumentNamespace');
            if ($nodeArgumentNamespace !== null) {
                return $nodeArgumentNamespace;
            }

            $nodeArgumentNamespace = $this->node->getNodeType()->getName();
            $nodeArgumentNamespace = str_replace(':', '-', $nodeArgumentNamespace);
            $nodeArgumentNamespace = str_replace('.', '_', $nodeArgumentNamespace);
            $nodeArgumentNamespace = strtolower($nodeArgumentNamespace);
            return $nodeArgumentNamespace;
        }

        $argumentNamespace = str_replace([':', '.', '\\'], ['_', '_', '_'], ($this->getPackage() . '_' . $this->getSubpackage() . '-' . $this->getController()));
        $argumentNamespace = strtolower($argumentNamespace);

        return $argumentNamespace;
    }

    /**
     * Pass the arguments which were addressed to the plugin to its own request
     *
     * @param ActionRequest $pluginRequest The plugin request
     * @return void
     */
    protected function passArgumentsToPluginRequest(ActionRequest $pluginRequest)
    {
        $arguments = $pluginRequest->getMainRequest()->getPluginArguments();
        $pluginNamespace = $this->getPluginNamespace();
        if (isset($arguments[$pluginNamespace])) {
            $pluginRequest->setArguments($arguments[$pluginNamespace]);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->evaluate();
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Fusion;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A Fusion Plugin object.
 */
class PluginImplementation extends PluginImplementation_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
        if ('Neos\Neos\Fusion\PluginImplementation' === get_class($this)) {
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\Dispatcher', 'Neos\Flow\Mvc\Dispatcher', 'dispatcher', '8ddded8be27664ffb31ad6b8c6b2be64', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\Dispatcher'); });
        $this->Flow_Injected_Properties = array (
  0 => 'objectManager',
  1 => 'dispatcher',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Fusion/PluginImplementation.php
#