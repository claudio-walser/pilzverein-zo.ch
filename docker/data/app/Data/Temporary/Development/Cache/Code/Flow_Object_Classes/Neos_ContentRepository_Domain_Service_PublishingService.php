<?php 
namespace Neos\ContentRepository\Domain\Service;

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
use Neos\ContentRepository\Domain\Factory\NodeFactory;
use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\Workspace;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\ContentRepository\Domain\Repository\WorkspaceRepository;
use Neos\ContentRepository\Exception\WorkspaceException;
use Neos\ContentRepository\Service\Utility\NodePublishingDependencySolver;

/**
 * A generic ContentRepository Publishing Service
 *
 * @api
 * @Flow\Scope("singleton")
 */
class PublishingService_Original implements PublishingServiceInterface
{
    /**
     * @Flow\Inject
     * @var WorkspaceRepository
     */
    protected $workspaceRepository;

    /**
     * @Flow\Inject
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;

    /**
     * @Flow\Inject
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var ContentDimensionPresetSourceInterface
     */
    protected $contentDimensionPresetSource;

    /**
     * Returns a list of nodes contained in the given workspace which are not yet published
     *
     * @param Workspace $workspace
     * @return array<\Neos\ContentRepository\Domain\Model\NodeInterface>
     * @api
     */
    public function getUnpublishedNodes(Workspace $workspace)
    {
        if ($workspace->getBaseWorkspace() === null) {
            return [];
        }

        $nodeData = $this->nodeDataRepository->findByWorkspace($workspace);
        $unpublishedNodes = [];
        foreach ($nodeData as $singleNodeData) {
            /** @var NodeData $singleNodeData */
            // Skip the root entry from the workspace as it can't be published
            if ($singleNodeData->getPath() === '/') {
                continue;
            }
            $node = $this->nodeFactory->createFromNodeData($singleNodeData, $this->createContext($workspace, $singleNodeData->getDimensionValues()));
            if ($node !== null) {
                $unpublishedNodes[] = $node;
            }
        }

        $unpublishedNodes = $this->sortNodesForPublishing($unpublishedNodes);

        return $unpublishedNodes;
    }

    /**
     * Returns the number of unpublished nodes contained in the given workspace
     *
     * @param Workspace $workspace
     * @return integer
     * @api
     */
    public function getUnpublishedNodesCount(Workspace $workspace)
    {
        return $workspace->getNodeCount() - 1;
    }

    /**
     * Publishes the given node to the specified target workspace. If no workspace is specified, the source workspace's
     * base workspace is assumed.
     *
     * @param NodeInterface $node
     * @param Workspace $targetWorkspace If not set the base workspace is assumed to be the publishing target
     * @return void
     * @api
     */
    public function publishNode(NodeInterface $node, Workspace $targetWorkspace = null)
    {
        if ($targetWorkspace === null) {
            $targetWorkspace = $node->getWorkspace()->getBaseWorkspace();
        }
        if ($targetWorkspace instanceof Workspace) {
            $node->getWorkspace()->publishNode($node, $targetWorkspace);
            $this->emitNodePublished($node, $targetWorkspace);
        }
    }

    /**
     * Publishes the given nodes to the specified target workspace. If no workspace is specified, the source workspace's
     * base workspace is assumed.
     *
     * @param array<\Neos\ContentRepository\Domain\Model\NodeInterface> $nodes The nodes to publish
     * @param Workspace $targetWorkspace If not set the base workspace is assumed to be the publishing target
     * @return void
     * @api
     */
    public function publishNodes(array $nodes, Workspace $targetWorkspace = null)
    {
        $nodes = $this->sortNodesForPublishing($nodes);
        foreach ($nodes as $node) {
            $this->publishNode($node, $targetWorkspace);
        }
    }

    /**
     * Discards the given node.
     *
     * If the node has been moved, this method will also discard all changes of child nodes of the given node.
     *
     * @param NodeInterface $node The node to discard
     * @return void
     * @throws WorkspaceException
     * @api
     */
    public function discardNode(NodeInterface $node)
    {
        $this->doDiscardNode($node);
    }

    /**
     * Method which does the actual work of discarding, includes a protection against endless recursions and
     * multiple discarding of the same node.
     *
     * @param NodeInterface $node The node to discard
     * @param array &$alreadyDiscardedNodeIdentifiers List of node identifiers which already have been discarded during one discardNode() run
     * @return void
     * @throws \Neos\ContentRepository\Exception\WorkspaceException
     */
    protected function doDiscardNode(NodeInterface $node, array &$alreadyDiscardedNodeIdentifiers = [])
    {
        if ($node->getWorkspace()->getBaseWorkspace() === null) {
            throw new WorkspaceException('Nodes in a workspace without a base workspace cannot be discarded.', 1395841899);
        }
        if ($node->getPath() === '/') {
            return;
        }
        if (array_search($node->getIdentifier(), $alreadyDiscardedNodeIdentifiers) !== false) {
            return;
        }

        $alreadyDiscardedNodeIdentifiers[] = $node->getIdentifier();

        $possibleShadowNodeData = $this->nodeDataRepository->findOneByMovedTo($node->getNodeData());
        if ($possibleShadowNodeData instanceof NodeData) {
            if ($possibleShadowNodeData->getMovedTo() !== null) {
                $parentBasePath = $node->getPath();
                $affectedChildNodeDataInSameWorkspace = $this->nodeDataRepository->findByParentAndNodeType($parentBasePath, null, $node->getWorkspace(), null, false, true);
                foreach ($affectedChildNodeDataInSameWorkspace as $affectedChildNodeData) {
                    /** @var NodeData $affectedChildNodeData */
                    $affectedChildNode = $this->nodeFactory->createFromNodeData($affectedChildNodeData, $node->getContext());
                    $this->doDiscardNode($affectedChildNode, $alreadyDiscardedNodeIdentifiers);
                }
            }

            $this->nodeDataRepository->remove($possibleShadowNodeData);
        }

        $this->nodeDataRepository->remove($node);
        $this->emitNodeDiscarded($node);
    }

    /**
     * Discards the given nodes.
     *
     * @param array<\Neos\ContentRepository\Domain\Model\NodeInterface> $nodes The nodes to discard
     * @return void
     * @api
     */
    public function discardNodes(array $nodes)
    {
        $discardedNodeIdentifiers = [];
        foreach ($nodes as $node) {
            $this->doDiscardNode($node, $discardedNodeIdentifiers);
        }
    }

    /**
     * Discards all unpublished nodes of the given workspace.
     *
     * TODO: This method needs to be optimized / implemented in collaboration with a DQL-based method in NodeDataRepository
     *
     * @param Workspace $workspace The workspace to flush, can't be the live workspace
     * @return void
     * @throws WorkspaceException
     * @api
     */
    public function discardAllNodes(Workspace $workspace)
    {
        if ($workspace->getName() === 'live') {
            throw new WorkspaceException('Nodes in the live workspace cannot be discarded.', 1428937112);
        }

        foreach ($this->getUnpublishedNodes($workspace) as $node) {
            /** @var NodeInterface $node */
            if ($node->getPath() !== '/') {
                $this->discardNode($node);
            }
        }
    }

    /**
     * Sort an unsorted list of nodes in a publishable order
     *
     * @param array $nodes Unsorted list of nodes (unpublished nodes)
     * @return array Sorted list of nodes for publishing
     * @throws WorkspaceException
     */
    protected function sortNodesForPublishing(array $nodes)
    {
        $sorter = new NodePublishingDependencySolver();
        return $sorter->sort($nodes);
    }

    /**
     * Signals that a node has been published.
     *
     * The signal emits the source node and target workspace, i.e. the node contains its source
     * workspace.
     *
     * @param NodeInterface $node
     * @param Workspace $targetWorkspace
     * @return void
     * @Flow\Signal
     * @api
     */
    public function emitNodePublished(NodeInterface $node, Workspace $targetWorkspace = null)
    {
    }

    /**
     * Signals that a node has been discarded.
     *
     * The signal emits the node that has been discarded.
     *
     * @param NodeInterface $node
     * @return void
     * @Flow\Signal
     * @api
     */
    public function emitNodeDiscarded(NodeInterface $node)
    {
    }

    /**
     * Creates a new content context based on the given workspace and the NodeData object.
     *
     * @param Workspace $workspace Workspace for the new context
     * @param array $dimensionValues The dimension values for the new context
     * @param array $contextProperties Additional pre-defined context properties
     * @return Context
     */
    protected function createContext(Workspace $workspace, array $dimensionValues, array $contextProperties = [])
    {
        $presetsMatchingDimensionValues = $this->contentDimensionPresetSource->findPresetsByTargetValues($dimensionValues);
        $dimensions = array_map(function ($preset) {
            return $preset['values'];
        }, $presetsMatchingDimensionValues);

        $contextProperties += [
            'workspaceName' => $workspace->getName(),
            'inaccessibleContentShown' => true,
            'invisibleContentShown' => true,
            'removedContentShown' => true,
            'dimensions' => $dimensions
        ];

        return $this->contextFactory->create($contextProperties);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\ContentRepository\Domain\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A generic ContentRepository Publishing Service
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class PublishingService extends PublishingService_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        if (get_class($this) === 'Neos\ContentRepository\Domain\Service\PublishingService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\ContentRepository\Domain\Service\PublishingService', $this);
        if ('Neos\ContentRepository\Domain\Service\PublishingService' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }
    }

    /**
     * Autogenerated Proxy Method
     */
    protected function Flow_Aop_Proxy_buildMethodsAndAdvicesArray()
    {
        if (method_exists(get_parent_class(), 'Flow_Aop_Proxy_buildMethodsAndAdvicesArray') && is_callable('parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray')) parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        $objectManager = \Neos\Flow\Core\Bootstrap::$staticObjectManager;
        $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array(
            'emitNodePublished' => array(
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\SignalSlot\SignalAspect', 'forwardSignalToDispatcher', $objectManager, NULL),
                ),
            ),
            'emitNodeDiscarded' => array(
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\SignalSlot\SignalAspect', 'forwardSignalToDispatcher', $objectManager, NULL),
                ),
            ),
        );
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        if (get_class($this) === 'Neos\ContentRepository\Domain\Service\PublishingService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\ContentRepository\Domain\Service\PublishingService', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __clone()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
    }

    /**
     * Autogenerated Proxy Method
     * @param NodeInterface $node
     * @param Workspace $targetWorkspace
     * @return void
     * @\Neos\Flow\Annotations\Signal
     */
    public function emitNodePublished(\Neos\ContentRepository\Domain\Model\NodeInterface $node, ?\Neos\ContentRepository\Domain\Model\Workspace $targetWorkspace = NULL)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitNodePublished'])) {
            $result = parent::emitNodePublished($node, $targetWorkspace);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitNodePublished'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
                $methodArguments['targetWorkspace'] = $targetWorkspace;
            
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\ContentRepository\Domain\Service\PublishingService', 'emitNodePublished', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitNodePublished']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitNodePublished']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\ContentRepository\Domain\Service\PublishingService', 'emitNodePublished', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitNodePublished']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitNodePublished']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param NodeInterface $node
     * @return void
     * @\Neos\Flow\Annotations\Signal
     */
    public function emitNodeDiscarded(\Neos\ContentRepository\Domain\Model\NodeInterface $node)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitNodeDiscarded'])) {
            $result = parent::emitNodeDiscarded($node);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitNodeDiscarded'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
            
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\ContentRepository\Domain\Service\PublishingService', 'emitNodeDiscarded', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitNodeDiscarded']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitNodeDiscarded']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\ContentRepository\Domain\Service\PublishingService', 'emitNodeDiscarded', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitNodeDiscarded']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitNodeDiscarded']);
        }
        return $result;
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
  'workspaceRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\WorkspaceRepository',
  'nodeDataRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\NodeDataRepository',
  'nodeFactory' => 'Neos\\ContentRepository\\Domain\\Factory\\NodeFactory',
  'contextFactory' => 'Neos\\ContentRepository\\Domain\\Service\\ContextFactoryInterface',
  'contentDimensionPresetSource' => 'Neos\\ContentRepository\\Domain\\Service\\ContentDimensionPresetSourceInterface',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'workspaceRepository', '9cacb5dd2ad57e06d6f8c82dd5707855', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\WorkspaceRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\NodeDataRepository', 'Neos\ContentRepository\Domain\Repository\NodeDataRepository', 'nodeDataRepository', '6d07985e92d364413ac81acd8f47b11b', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\NodeDataRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Factory\NodeFactory', 'Neos\ContentRepository\Domain\Factory\NodeFactory', 'nodeFactory', 'dd541629b8e42562866a1bf47375f14d', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Factory\NodeFactory'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\ContextFactoryInterface', 'Neos\Neos\Domain\Service\ContentContextFactory', 'contextFactory', '98dca7b1f95a25ec173662fc4e785341', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\ContextFactoryInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\ContentDimensionPresetSourceInterface', 'Neos\Neos\Domain\Service\ConfigurationContentDimensionPresetSource', 'contentDimensionPresetSource', '33404cce491062aa2636da962a6cf674', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\ContentDimensionPresetSourceInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'workspaceRepository',
  1 => 'nodeDataRepository',
  2 => 'nodeFactory',
  3 => 'contextFactory',
  4 => 'contentDimensionPresetSource',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.ContentRepository/Classes/Domain/Service/PublishingService.php
#