<?php 
namespace Neos\Neos\Service;

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
use Neos\Flow\Security\Context;
use Neos\Neos;
use Neos\Neos\Domain\Model\PluginViewDefinition;
use Neos\Neos\Domain\Service\ContentContext;
use Neos\Neos\Domain\Service\ContentContextFactory;
use Neos\ContentRepository\Domain\Factory\NodeFactory;
use Neos\ContentRepository\Domain\Model\Node;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;

/**
 * Central authority for interactions with plugins.
 * Whenever details about Plugins or PluginViews are needed this service should be used.
 *
 * For some methods the ContentContext has to be specified. This is required in order for the ContentRepository to fetch nodes
 * of the current workspace. The context can be retrieved from any node of the correct workspace & tree. If no node
 * is available (e.g. for CLI requests) the ContentContextFactory can be used to create a context instance.
 *
 * @Flow\Scope("singleton")
 */
class PluginService_Original
{
    /**
     * @var NodeTypeManager
     * @Flow\Inject
     */
    protected $nodeTypeManager;

    /**
     * @Flow\Inject
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;

    /**
     * @Flow\Inject
     * @var Context
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var ContentContextFactory
     */
    protected $contentContextFactory;

    /**
     * @Flow\Inject
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * Returns an array of all available plugin nodes
     *
     * @param ContentContext $context current content context, see class doc comment for details
     * @return array<NodeInterface> all plugin nodes in the current $context
     */
    public function getPluginNodes(ContentContext $context)
    {
        $pluginNodeTypes = $this->nodeTypeManager->getSubNodeTypes('Neos.Neos:Plugin', false);
        return $this->getNodes(array_keys($pluginNodeTypes), $context);
    }

    /**
     * Returns an array of all plugin nodes with View Definitions
     *
     * @param ContentContext $context
     * @return array<NodeInterface> all plugin nodes with View Definitions in the current $context
     */
    public function getPluginNodesWithViewDefinitions(ContentContext $context)
    {
        $pluginNodes = [];
        foreach ($this->getPluginNodes($context) as $pluginNode) {
            /** @var NodeInterface $pluginNode */
            if ($this->getPluginViewDefinitionsByPluginNodeType($pluginNode->getNodeType()) !== []) {
                $pluginNodes[] = $pluginNode;
            }
        }
        return $pluginNodes;
    }

    /**
     * Find all nodes of a specific node type
     *
     * @param array $nodeTypes
     * @param ContentContext $context current content context, see class doc comment for details
     * @return array<NodeInterface> all nodes of type $nodeType in the current $context
     */
    protected function getNodes(array $nodeTypes, ContentContext $context)
    {
        $nodes = [];
        $siteNode = $context->getCurrentSiteNode();
        foreach ($this->nodeDataRepository->findByParentAndNodeTypeRecursively($siteNode->getPath(), implode(',', $nodeTypes), $context->getWorkspace()) as $nodeData) {
            $nodes[] = $this->nodeFactory->createFromNodeData($nodeData, $context);
        }
        return $nodes;
    }

    /**
     * Get all configured PluginView definitions for a specific $pluginNodeType
     *
     * @param NodeType $pluginNodeType node type name of the master plugin
     * @return array<PluginViewDefinition> list of PluginViewDefinition instances for the given $pluginNodeName
     */
    public function getPluginViewDefinitionsByPluginNodeType(NodeType $pluginNodeType)
    {
        $viewDefinitions = [];
        foreach ($this->getPluginViewConfigurationsByPluginNodeType($pluginNodeType) as $pluginViewName => $pluginViewConfiguration) {
            $viewDefinitions[] = new PluginViewDefinition($pluginNodeType, $pluginViewName, $pluginViewConfiguration);
        }
        return $viewDefinitions;
    }

    /**
     * @param NodeType $pluginNodeType
     * @return array
     */
    protected function getPluginViewConfigurationsByPluginNodeType(NodeType $pluginNodeType)
    {
        $pluginNodeTypeOptions = $pluginNodeType->getOptions();
        return isset($pluginNodeTypeOptions['pluginViews']) ? $pluginNodeTypeOptions['pluginViews'] : [];
    }

    /**
     * returns a plugin node or one of it's view nodes
     * if an view has been configured for that specific
     * controller and action combination
     *
     * @param NodeInterface $currentNode
     * @param string $controllerObjectName
     * @param string $actionName
     * @return NodeInterface
     */
    public function getPluginNodeByAction(NodeInterface $currentNode, $controllerObjectName, $actionName)
    {
        $viewDefinition = $this->getPluginViewDefinitionByAction($controllerObjectName, $actionName);

        if ($currentNode->getNodeType()->isOfType('Neos.Neos:PluginView')) {
            $masterPluginNode = $this->getPluginViewNodeByMasterPlugin($currentNode, $viewDefinition->getName());
        } else {
            $masterPluginNode = $currentNode;
        }

        if ($viewDefinition !== null) {
            $viewNode = $this->getPluginViewNodeByMasterPlugin($currentNode, $viewDefinition->getName());
            if ($viewNode instanceof Node) {
                return $viewNode;
            }
        }

        return $masterPluginNode;
    }

    /**
     * Fetch a PluginView definition that matches the specified controller and action combination
     *
     * @param string $controllerObjectName
     * @param string $actionName
     * @return PluginViewDefinition
     * @throws Neos\Exception if more than one PluginView matches the given controller/action pair
     */
    public function getPluginViewDefinitionByAction($controllerObjectName, $actionName)
    {
        $pluginNodeTypes = $this->nodeTypeManager->getSubNodeTypes('Neos.Neos:Plugin', false);

        $matchingPluginViewDefinitions = [];
        foreach ($pluginNodeTypes as $pluginNodeType) {
            /** @var $pluginViewDefinition PluginViewDefinition */
            foreach ($this->getPluginViewDefinitionsByPluginNodeType($pluginNodeType) as $pluginViewDefinition) {
                if ($pluginViewDefinition->matchesControllerActionPair($controllerObjectName, $actionName) !== true) {
                    continue;
                }
                $matchingPluginViewDefinitions[] = $pluginViewDefinition;
            }
        }
        if (count($matchingPluginViewDefinitions) > 1) {
            throw new Neos\Exception(sprintf('More than one PluginViewDefinition found for controller "%s", action "%s":%s', $controllerObjectName, $actionName, chr(10) . implode(chr(10), $matchingPluginViewDefinitions)), 1377597671);
        }

        return count($matchingPluginViewDefinitions) > 0 ? current($matchingPluginViewDefinitions) : null;
    }

    /**
     * returns a specific view node of an master plugin
     * or NULL if it does not exist
     *
     * @param NodeInterface $node
     * @param string $viewName
     * @return NodeInterface
     */
    public function getPluginViewNodeByMasterPlugin(NodeInterface $node, $viewName)
    {
        /** @var $context ContentContext */
        $context = $node->getContext();
        foreach ($this->getNodes(['Neos.Neos:PluginView'], $context) as $pluginViewNode) {
            /** @var NodeInterface $pluginViewNode */
            if ($pluginViewNode->isRemoved()) {
                continue;
            }
            if ($pluginViewNode->getProperty('plugin') === $node->getIdentifier()
                && $pluginViewNode->getProperty('view') === $viewName) {
                return $pluginViewNode;
            }
        }

        return null;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Central authority for interactions with plugins.
 * Whenever details about Plugins or PluginViews are needed this service should be used.
 * 
 * For some methods the ContentContext has to be specified. This is required in order for the ContentRepository to fetch nodes
 * of the current workspace. The context can be retrieved from any node of the correct workspace & tree. If no node
 * is available (e.g. for CLI requests) the ContentContextFactory can be used to create a context instance.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class PluginService extends PluginService_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Neos\Service\PluginService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Service\PluginService', $this);
        if ('Neos\Neos\Service\PluginService' === get_class($this)) {
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
  'nodeDataRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\NodeDataRepository',
  'securityContext' => 'Neos\\Flow\\Security\\Context',
  'contentContextFactory' => 'Neos\\Neos\\Domain\\Service\\ContentContextFactory',
  'nodeFactory' => 'Neos\\ContentRepository\\Domain\\Factory\\NodeFactory',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Neos\Service\PluginService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Service\PluginService', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\NodeTypeManager', 'Neos\ContentRepository\Domain\Service\NodeTypeManager', 'nodeTypeManager', 'e525e2ecb65f7f9731d6537ddecd16d4', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\NodeTypeManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\NodeDataRepository', 'Neos\ContentRepository\Domain\Repository\NodeDataRepository', 'nodeDataRepository', '6d07985e92d364413ac81acd8f47b11b', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\NodeDataRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Context', 'Neos\Flow\Security\Context', 'securityContext', 'f7e2ddeaebd191e228b8c2e4dc7f1f83', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Context'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Service\ContentContextFactory', 'Neos\Neos\Domain\Service\ContentContextFactory', 'contentContextFactory', 'bf6447fb48e80589ca3a024bc3882005', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Service\ContentContextFactory'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Factory\NodeFactory', 'Neos\ContentRepository\Domain\Factory\NodeFactory', 'nodeFactory', 'dd541629b8e42562866a1bf47375f14d', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Factory\NodeFactory'); });
        $this->Flow_Injected_Properties = array (
  0 => 'nodeTypeManager',
  1 => 'nodeDataRepository',
  2 => 'securityContext',
  3 => 'contentContextFactory',
  4 => 'nodeFactory',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Service/PluginService.php
#