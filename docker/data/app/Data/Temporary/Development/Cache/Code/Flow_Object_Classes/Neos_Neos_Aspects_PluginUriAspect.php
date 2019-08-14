<?php 
namespace Neos\Neos\Aspects;

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
use Neos\Flow\Aop\JoinPointInterface;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Neos\Service\PluginService;
use Neos\ContentRepository\Domain\Model\Node;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Eel\FlowQuery\FlowQuery;

/**
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class PluginUriAspect_Original
{
    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * The pluginService
     *
     * @var PluginService
     * @Flow\Inject
     */
    protected $pluginService;

    /**
     * @Flow\Around("method(Neos\Flow\Mvc\Routing\UriBuilder->uriFor())")
     * @param \Neos\Flow\Aop\JoinPointInterface $joinPoint The current join point
     * @return string The result of the target method if it has not been intercepted
     */
    public function rewritePluginViewUris(JoinPointInterface $joinPoint)
    {
        /** @var ActionRequest $request */
        $request = $joinPoint->getProxy()->getRequest();
        $arguments = $joinPoint->getMethodArguments();

        $currentNode = $request->getInternalArgument('__node');
        if (!$request->getMainRequest()->hasArgument('node') || !$currentNode instanceof Node) {
            return $joinPoint->getAdviceChain()->proceed($joinPoint);
        }

        $currentNode = $request->getInternalArgument('__node');
        $controllerObjectName = $this->getControllerObjectName($request, $arguments);
        $actionName = $arguments['actionName'] !== null ? $arguments['actionName'] : $request->getControllerActionName();

        $targetNode = $this->pluginService->getPluginNodeByAction($currentNode, $controllerObjectName, $actionName);

        // TODO override namespace

        $q = new FlowQuery([$targetNode]);
        $pageNode = $q->closest('[instanceof Neos.Neos:Document]')->get(0);
        $result = $this->generateUriForNode($request, $joinPoint, $pageNode);

        return $result;
    }

    /**
     * Merge the default plugin arguments of the Plugin with the arguments in the request
     * and generate a controllerObjectName
     *
     * @param object $request
     * @param array $arguments
     * @return string $controllerObjectName
     */
    public function getControllerObjectName($request, array $arguments)
    {
        $controllerName = $arguments['controllerName'] !== null ? $arguments['controllerName'] : $request->getControllerName();
        $subPackageKey = $arguments['subPackageKey'] !== null ? $arguments['subPackageKey'] : $request->getControllerSubpackageKey();
        $packageKey = $arguments['packageKey'] !== null ? $arguments['packageKey'] : $request->getControllerPackageKey();

        $possibleObjectName = '@package\@subpackage\Controller\@controllerController';
        $possibleObjectName = str_replace('@package', str_replace('.', '\\', $packageKey), $possibleObjectName);
        $possibleObjectName = str_replace('@subpackage', $subPackageKey, $possibleObjectName);
        $possibleObjectName = str_replace('@controller', $controllerName, $possibleObjectName);
        $possibleObjectName = str_replace('\\\\', '\\', $possibleObjectName);

        $controllerObjectName = $this->objectManager->getCaseSensitiveObjectName($possibleObjectName);
        return ($controllerObjectName !== false) ? $controllerObjectName : '';
    }

    /**
     * This method generates the Uri through the joinPoint with
     * temporary overriding the used node
     *
     * @param ActionRequest $request
     * @param JoinPointInterface $joinPoint The current join point
     * @param NodeInterface $node
     * @return string $uri
     */
    public function generateUriForNode(ActionRequest $request, JoinPointInterface $joinPoint, NodeInterface $node)
    {
        // store original node path to restore it after generating the uri
        $originalNodePath = $request->getMainRequest()->getArgument('node');

        // generate the uri for the given node
        $request->getMainRequest()->setArgument('node', $node->getContextPath());
        $result = $joinPoint->getAdviceChain()->proceed($joinPoint);

        // restore the original node path
        $request->getMainRequest()->setArgument('node', $originalNodePath);

        return $result;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Aspects;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 * @\Neos\Flow\Annotations\Scope("singleton")
 * @\Neos\Flow\Annotations\Aspect
 */
class PluginUriAspect extends PluginUriAspect_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Neos\Aspects\PluginUriAspect') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Aspects\PluginUriAspect', $this);
        if ('Neos\Neos\Aspects\PluginUriAspect' === get_class($this)) {
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
  'pluginService' => 'Neos\\Neos\\Service\\PluginService',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Neos\Aspects\PluginUriAspect') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Aspects\PluginUriAspect', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\PluginService', 'Neos\Neos\Service\PluginService', 'pluginService', '9a5e6d9c8043f9403867eb9224792c01', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\PluginService'); });
        $this->Flow_Injected_Properties = array (
  0 => 'objectManager',
  1 => 'pluginService',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Aspects/PluginUriAspect.php
#