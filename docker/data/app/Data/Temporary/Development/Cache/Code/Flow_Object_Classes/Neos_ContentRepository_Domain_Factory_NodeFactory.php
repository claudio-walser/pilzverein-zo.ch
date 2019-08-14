<?php 
namespace Neos\ContentRepository\Domain\Factory;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Reflection\ReflectionService;
use Neos\Flow\Security\Context as SecurityContext;
use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\Context;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\ContentRepository\Exception\NodeConfigurationException;

/**
 * This factory creates nodes based on node data. Its main purpose is to
 * assure that nodes created for a certain node data container and context
 * are unique in memory.
 *
 * @Flow\Scope("singleton")
 */
class NodeFactory_Original
{
    /**
     * @var array<\Neos\ContentRepository\Domain\Model\Node>
     */
    protected $nodes = [];

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\Inject
     * @var SecurityContext
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;


    /**
     * Creates a node from the given NodeData container.
     *
     * If this factory has previously created a Node for the given $node and it's dimensions,
     * it will return the same node again.
     *
     * @param NodeData $nodeData
     * @param Context $context
     * @return NodeInterface
     * @throws NodeConfigurationException if a configured 'class' for a Node does not exist or does not inherit NodeInterface
     */
    public function createFromNodeData(NodeData $nodeData, Context $context)
    {
        if ($nodeData->isInternal()) {
            return null;
        }

        $internalNodeIdentifier = $nodeData->getIdentifier() . spl_object_hash($context);

        // In case there is a Node with an internal NodeData (because the NodeData was changed in the meantime) we need to flush it.
        if (isset($this->nodes[$internalNodeIdentifier]) && $this->nodes[$internalNodeIdentifier]->getNodeData()->isInternal()) {
            unset($this->nodes[$internalNodeIdentifier]);
        }

        if (!isset($this->nodes[$internalNodeIdentifier])) {
            // Warning: Alternative node implementations are considered internal for now, feature can change or be removed anytime. We want to be sure it works well and makes sense before declaring it public.
            $class = $nodeData->getNodeType()->getConfiguration('class') ?: $this->objectManager->getClassNameByObjectName(NodeInterface::class);
            if (!in_array($class, static::getNodeInterfaceImplementations($this->objectManager))) {
                throw new NodeConfigurationException('The configured implementation class name "' . $class . '" for NodeType "' . $nodeData->getNodeType() . '" does not inherit from ' .NodeInterface::class.'.', 1406884014);
            }
            $this->nodes[$internalNodeIdentifier] = new $class($nodeData, $context);
        }
        $node = $this->nodes[$internalNodeIdentifier];

        return $this->filterNodeByContext($node, $context);
    }

    /**
     * Get all NodeInterface implementations to check if a configured node class is in there.
     *
     * @param ObjectManagerInterface $objectManager
     * @return array
     * @Flow\CompileStatic
     */
    public static function getNodeInterfaceImplementations($objectManager)
    {
        $reflectionService = $objectManager->get(ReflectionService::class);
        $nodeImplementations = $reflectionService->getAllImplementationClassNamesForInterface(NodeInterface::class);
        return $nodeImplementations;
    }

    /**
     * Filter a node by the current context.
     * Will either return the node or NULL if it is not permitted in current context.
     *
     * @param NodeInterface $node
     * @param Context $context
     * @return NodeInterface|NULL
     */
    protected function filterNodeByContext(NodeInterface $node, Context $context)
    {
        $this->securityContext->withoutAuthorizationChecks(function () use (&$node, $context) {
            if (!$context->isRemovedContentShown() && $node->isRemoved()) {
                $node = null;
                return;
            }
            if (!$context->isInvisibleContentShown() && !$node->isVisible()) {
                $node = null;
                return;
            }
            if (!$context->isInaccessibleContentShown() && !$node->isAccessible()) {
                $node = null;
            }
        });
        return $node;
    }

    /**
     * Generates a Context that exactly fits the given NodeData Workspace and Dimensions.
     *
     * TODO: We could get more specific about removed and invisible content by adding some more logic here that generates fitting values.
     *
     * @param NodeData $nodeData
     * @return Context
     */
    public function createContextMatchingNodeData(NodeData $nodeData)
    {
        return $this->contextFactory->create([
            'workspaceName' => $nodeData->getWorkspace()->getName(),
            'invisibleContentShown' => true,
            'inaccessibleContentShown' => true,
            'removedContentShown' => true,
            'dimensions' => $nodeData->getDimensionValues()
        ]);
    }

    /**
     * Reset the node instances (for testing)
     *
     * @return void
     */
    public function reset()
    {
        $this->nodes = [];
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\ContentRepository\Domain\Factory;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * This factory creates nodes based on node data. Its main purpose is to
 * assure that nodes created for a certain node data container and context
 * are unique in memory.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class NodeFactory extends NodeFactory_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\ContentRepository\Domain\Factory\NodeFactory') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\ContentRepository\Domain\Factory\NodeFactory', $this);
        if ('Neos\ContentRepository\Domain\Factory\NodeFactory' === get_class($this)) {
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
  'nodes' => 'array<\\Neos\\ContentRepository\\Domain\\Model\\Node>',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'securityContext' => 'Neos\\Flow\\Security\\Context',
  'contextFactory' => 'Neos\\ContentRepository\\Domain\\Service\\ContextFactoryInterface',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\ContentRepository\Domain\Factory\NodeFactory') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\ContentRepository\Domain\Factory\NodeFactory', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Context', 'Neos\Flow\Security\Context', 'securityContext', 'f7e2ddeaebd191e228b8c2e4dc7f1f83', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Context'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\ContextFactoryInterface', 'Neos\Neos\Domain\Service\ContentContextFactory', 'contextFactory', '98dca7b1f95a25ec173662fc4e785341', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\ContextFactoryInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'objectManager',
  1 => 'securityContext',
  2 => 'contextFactory',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.ContentRepository/Classes/Domain/Factory/NodeFactory.php
#