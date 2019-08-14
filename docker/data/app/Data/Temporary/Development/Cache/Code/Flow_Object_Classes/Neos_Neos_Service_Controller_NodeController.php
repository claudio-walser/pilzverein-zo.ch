<?php 
namespace Neos\Neos\Service\Controller;

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
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Property\TypeConverter\PersistentObjectConverter;
use Neos\Neos\Domain\Repository\DomainRepository;
use Neos\Neos\Domain\Service\NodeSearchServiceInterface;
use Neos\Neos\Service\NodeOperations;
use Neos\Neos\Service\View\NodeView;
use Neos\ContentRepository\Domain\Factory\NodeFactory;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\Node;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\ContentRepository\Exception\NodeException;
use Neos\ContentRepository\TypeConverter\NodeConverter;

/**
 * Service Controller for managing Nodes
 *
 * Note: This controller should be, step-by-step, transformed into a clean REST controller (see NEOS-190 and NEOS-199).
 *       Since this is a rather big endeavor, we slice the elephant and move methods in a clean way from here to the
 *       new NodesController (\Neos\Neos\Controller\Service\NodesController)
 */
class NodeController_Original extends AbstractServiceController
{
    /**
     * @var NodeView
     */
    protected $view;

    /**
     * @var array
     */
    protected $viewFormatToObjectNameMap = [
        'html' => NodeView::class,
        'json' => NodeView::class
    ];

    /**
     * @var array
     */
    protected $supportedMediaTypes = [
        'text/html',
        'application/json'
    ];

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @Flow\Inject
     * @var NodeSearchServiceInterface
     */
    protected $nodeSearchService;

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
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;

    /**
     * @Flow\Inject
     * @var NodeOperations
     */
    protected $nodeOperations;

    /**
     * @Flow\Inject
     * @var DomainRepository
     */
    protected $domainRepository;

    /**
     * Select special error action
     *
     * @return void
     */
    protected function initializeAction()
    {
        if ($this->arguments->hasArgument('referenceNode')) {
            $this->arguments->getArgument('referenceNode')->getPropertyMappingConfiguration()->setTypeConverterOption(NodeConverter::class, NodeConverter::REMOVED_CONTENT_SHOWN, true);
        }
        $this->uriBuilder->setRequest($this->request->getMainRequest());
        if (in_array($this->request->getControllerActionName(), ['update', 'updateAndRender'], true)) {
            // Set PropertyMappingConfiguration for updating the node (and attached objects)
            $propertyMappingConfiguration = $this->arguments->getArgument('node')->getPropertyMappingConfiguration();
            $propertyMappingConfiguration->allowOverrideTargetType();
            $propertyMappingConfiguration->allowAllProperties();
            $propertyMappingConfiguration->skipUnknownProperties();
            $propertyMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, true);
            $propertyMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, true);
        }
    }

    #
    # Actions which are not yet refactored to REST below (see NEOS-199):
    #

    /**
     * Return child nodes of specified node for usage in a TreeLoader
     *
     * @param Node $node The node to find child nodes for
     * @param string $nodeTypeFilter A node type filter
     * @param integer $depth levels of childNodes (0 = unlimited)
     * @param Node $untilNode expand the child nodes until $untilNode is reached, independent of $depth
     * @return void
     */
    public function getChildNodesForTreeAction(Node $node, $nodeTypeFilter, $depth, Node $untilNode)
    {
        $this->view->assignChildNodes($node, $nodeTypeFilter, NodeView::STYLE_TREE, $depth, $untilNode);
    }

    /**
     * Return child nodes of specified node for usage in a TreeLoader based on filter
     *
     * @param Node $node The node to find child nodes for
     * @param string $term
     * @param string $nodeType
     * @return void
     */
    public function filterChildNodesForTreeAction(Node $node, $term, $nodeType)
    {
        $nodeTypes = strlen($nodeType) > 0 ? [$nodeType] : array_keys($this->nodeTypeManager->getSubNodeTypes('Neos.Neos:Document', false));
        $context = $node->getContext();
        if ($term !== '') {
            $nodes = $this->nodeSearchService->findByProperties($term, $nodeTypes, $context, $node);
        } else {
            $nodes = [];
            $nodeDataRecords = $this->nodeDataRepository->findByParentAndNodeTypeRecursively($node->getPath(), implode(',', $nodeTypes), $context->getWorkspace(), $context->getDimensions());
            foreach ($nodeDataRecords as $nodeData) {
                $matchedNode = $this->nodeFactory->createFromNodeData($nodeData, $context);
                if ($matchedNode !== null) {
                    $nodes[$matchedNode->getPath()] = $matchedNode;
                }
            }
        }
        $this->view->assignFilteredChildNodes(
            $node,
            $nodes
        );
    }

    /**
     * Creates a new node
     *
     * We need to call persistAll() in order to return the nextUri. We can't persist only the nodes in NodeDataRepository
     * because they might be connected to images / resources which need to be updated at the same time.
     *
     * @param Node $referenceNode
     * @param array $nodeData
     * @param string $position where the node should be added (allowed: before, into, after)
     * @return void
     */
    public function createAction(Node $referenceNode, array $nodeData, $position)
    {
        $newNode = $this->nodeOperations->create($referenceNode, $nodeData, $position);

        if ($this->request->getHttpRequest()->isMethodSafe() === false) {
            $this->persistenceManager->persistAll();
        }

        $nextUri = $this->uriBuilder->reset()->setFormat('html')->setCreateAbsoluteUri(true)->uriFor('show', ['node' => $newNode], 'Frontend\Node', 'Neos.Neos');
        $this->view->assign('value', ['data' => ['nextUri' => $nextUri], 'success' => true]);
    }

    /**
     * Creates a new node and renders the node inside the containing content collection.
     *
     * @param Node $referenceNode
     * @param string $fusionPath The Fusion path of the collection
     * @param array $nodeData
     * @param string $position where the node should be added (allowed: before, into, after)
     * @return string
     */
    public function createAndRenderAction(Node $referenceNode, $fusionPath, array $nodeData, $position)
    {
        $newNode = $this->nodeOperations->create($referenceNode, $nodeData, $position);
        $this->redirectToRenderNode($newNode, $fusionPath);
    }

    /**
     * Creates a new node and returns tree structure
     *
     * @param Node $referenceNode
     * @param array $nodeData
     * @param string $position where the node should be added, -1 is before, 0 is in, 1 is after
     * @param string $nodeTypeFilter
     * @return void
     */
    public function createNodeForTheTreeAction(Node $referenceNode, array $nodeData, $position, $nodeTypeFilter = '')
    {
        $newNode = $this->nodeOperations->create($referenceNode, $nodeData, $position);
        $this->view->assignNodeAndChildNodes($newNode, $nodeTypeFilter);
    }

    /**
     * Move $node before, into or after $targetNode
     *
     * We need to call persistAll() in order to return the nextUri. We can't persist only the nodes in NodeDataRepository
     * because they might be connected to images / resources which need to be updated at the same time.
     *
     * @param Node $node The node to be moved
     * @param Node $targetNode The target node to be moved "to", see $position
     * @param string $position where the node should be added (allowed: before, into, after)
     * @return void
     */
    public function moveAction(Node $node, Node $targetNode, $position)
    {
        $node = $this->nodeOperations->move($node, $targetNode, $position);

        if ($this->request->getHttpRequest()->isMethodSafe() === false) {
            $this->persistenceManager->persistAll();
        }

        $data = ['newNodePath' => $node->getContextPath()];
        if ($node->getNodeType()->isOfType('Neos.Neos:Document')) {
            $data['nextUri'] = $this->uriBuilder->reset()->setFormat('html')->setCreateAbsoluteUri(true)->uriFor('show', ['node' => $node], 'Frontend\Node', 'Neos.Neos');
        }
        $this->view->assign('value', ['data' => $data, 'success' => true]);
    }

    /**
     * Move the given node before, into or after the target node depending on the given position and renders it's content collection.
     *
     * @param Node $node The node to be moved
     * @param Node $targetNode The target node to be moved "to", see $position
     * @param string $position Where the node should be added in relation to $targetNode (allowed: before, into, after)
     * @param string $fusionPath The Fusion path of the collection
     * @return void
     */
    public function moveAndRenderAction(Node $node, Node $targetNode, $position, $fusionPath)
    {
        $this->nodeOperations->move($node, $targetNode, $position);
        $this->redirectToRenderNode($node, $fusionPath);
    }

    /**
     * Copy $node before, into or after $targetNode
     *
     * We need to call persistAll() in order to return the nextUri. We can't persist only the nodes in NodeDataRepository
     * because they might be connected to images / resources which need to be updated at the same time.
     *
     * @param Node $node The node to be copied
     * @param Node $targetNode The target node to be copied "to", see $position
     * @param string $position Where the node should be added in relation to $targetNode (allowed: before, into, after)
     * @param string $nodeName Optional node name (if empty random node name will be generated)
     * @return void
     * @throws NodeException
     */
    public function copyAction(Node $node, Node $targetNode, $position, $nodeName = null)
    {
        $copiedNode = $this->nodeOperations->copy($node, $targetNode, $position, $nodeName);

        if ($this->request->getHttpRequest()->isMethodSafe() === false) {
            $this->persistenceManager->persistAll();
        }

        $q = new FlowQuery([$copiedNode]);
        $closestDocumentNode = $q->closest('[instanceof Neos.Neos:Document]')->get(0);

        $requestData = [
            'nextUri' => $this->uriBuilder->reset()->setFormat('html')->setCreateAbsoluteUri(true)->uriFor('show', ['node' => $closestDocumentNode], 'Frontend\Node', 'Neos.Neos'),
            'newNodePath' => $copiedNode->getContextPath()
        ];

        if ($node->getNodeType()->isOfType('Neos.Neos:Document')) {
            $requestData['nodeUri'] = $this->uriBuilder->reset()->setFormat('html')->setCreateAbsoluteUri(true)->uriFor('show', ['node' => $copiedNode], 'Frontend\Node', 'Neos.Neos');
        }

        $this->view->assign('value', ['data' => $requestData, 'success' => true]);
    }

    /**
     * Copies the given node before, into or after the target node depending on the given position and renders it's content collection.
     *
     * @param Node $node The node to be copied
     * @param Node $targetNode The target node to be copied "to", see $position
     * @param string $position Where the node should be added in relation to $targetNode (allowed: before, into, after)
     * @param string $nodeName Optional node name (if empty random node name will be generated)
     * @param string $fusionPath The Fusion path of the collection
     * @return void
     */
    public function copyAndRenderAction(Node $node, Node $targetNode, $position, $fusionPath, $nodeName = null)
    {
        $copiedNode = $this->nodeOperations->copy($node, $targetNode, $position, $nodeName);
        $this->redirectToRenderNode($copiedNode, $fusionPath);
    }

    /**
     * Updates the specified node.
     *
     * Returns the following data:
     *
     * - the (possibly changed) workspace name of the node
     * - the URI of the closest document node. If $node is a document node (f.e. a Page), the own URI is returned.
     *   This is important to handle renames of nodes correctly.
     *
     * Note: We do not call $nodeDataRepository->update() here, as ContentRepository has a stateful API for now.
     *       We need to call persistAll() in order to return the nextUri. We can't persist only the nodes in NodeDataRepository
     *       because they might be connected to images / resources which need to be updated at the same time.
     *
     * @param Node $node The node to be updated
     * @return void
     */
    public function updateAction(Node $node)
    {
        if ($this->request->getHttpRequest()->isMethodSafe() === false) {
            $this->persistenceManager->persistAll();
        }

        $q = new FlowQuery([$node]);
        $closestDocumentNode = $q->closest('[instanceof Neos.Neos:Document]')->get(0);
        $nextUri = $this->uriBuilder->reset()->setFormat('html')->setCreateAbsoluteUri(true)->uriFor('show', ['node' => $closestDocumentNode], 'Frontend\Node', 'Neos.Neos');
        $this->view->assign('value', [
            'data' => [
                'workspaceNameOfNode' => $node->getWorkspace()->getName(),
                'labelOfNode' => $node->getLabel(),
                'nextUri' => $nextUri
            ],
            'success' => true
        ]);
    }

    /**
     * Updates the specified node and renders it's content collection.
     *
     * @param Node $node The node to be updated
     * @param string $fusionPath The Fusion path of the collection
     * @return void
     */
    public function updateAndRenderAction(Node $node, $fusionPath)
    {
        $this->redirectToRenderNode($node, $fusionPath);
    }

    /**
     * Deletes the specified node and all of its sub nodes
     *
     * We need to call persistAll() in order to return the nextUri. We can't persist only the nodes in NodeDataRepository
     * because they might be connected to images / resources which need to be removed at the same time.
     *
     * @param Node $node
     * @return void
     */
    public function deleteAction(Node $node)
    {
        if ($this->request->getHttpRequest()->isMethodSafe() === false) {
            $this->persistenceManager->persistAll();
        }

        $q = new FlowQuery([$node]);
        $node->remove();
        $closestDocumentNode = $q->closest('[instanceof Neos.Neos:Document]')->get(0);
        $nextUri = $this->uriBuilder->reset()->setFormat('html')->setCreateAbsoluteUri(true)->uriFor('show', ['node' => $closestDocumentNode], 'Frontend\Node', 'Neos.Neos');

        $this->view->assign('value', ['data' => ['nextUri' => $nextUri], 'success' => true]);
    }

    /**
     * Takes care of creating a redirect to properly render the collection the given node is in.
     *
     * @param NodeInterface $node
     * @param string $fusionPath
     * @return string
     */
    protected function redirectToRenderNode(NodeInterface $node, $fusionPath)
    {
        $q = new FlowQuery([$node]);
        $closestContentCollection = $q->closest('[instanceof Neos.Neos:ContentCollection]')->get(0);
        $closestDocumentNode = $q->closest('[instanceof Neos.Neos:Document]')->get(0);

        $this->redirect('show', 'Frontend\\Node', 'Neos.Neos', [
            'node' => $closestDocumentNode,
            '__nodeContextPath' => $closestContentCollection->getContextPath(),
            '__affectedNodeContextPath' => $node->getContextPath(),
            '__fusionPath' => $fusionPath
        ], 0, 303, 'html');
    }

    /**
     * Returns an array with the data needed by for example the Hallo and Aloha
     * link plugins to represent the passed Node instance.
     *
     * @param NodeInterface $node
     * @return array
     */
    protected function processNodeForEditorPlugins(NodeInterface $node)
    {
        return [
            'id' => $node->getPath(),
            'name' => $node->getLabel(),
            'url' => $this->uriBuilder->uriFor('show', ['node' => $node], 'Frontend\Node', 'Neos.Neos'),
            'type' => 'neos/internal-link'
        ];
    }

    /**
     * Create a Context for a workspace given by name to be used in this controller.
     *
     * @param string $workspaceName Name of the current workspace
     * @return \Neos\ContentRepository\Domain\Service\Context
     */
    protected function createContext($workspaceName)
    {
        $contextProperties = [
            'workspaceName' => $workspaceName
        ];

        $currentDomain = $this->domainRepository->findOneByActiveRequest();
        if ($currentDomain !== null) {
            $contextProperties['currentSite'] = $currentDomain->getSite();
            $contextProperties['currentDomain'] = $currentDomain;
        }

        return $this->contextFactory->create($contextProperties);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Service\Controller;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Service Controller for managing Nodes
 * 
 * Note: This controller should be, step-by-step, transformed into a clean REST controller (see NEOS-190 and NEOS-199).
 *       Since this is a rather big endeavor, we slice the elephant and move methods in a clean way from here to the
 *       new NodesController (\Neos\Neos\Controller\Service\NodesController)
 */
class NodeController extends NodeController_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
        if ('Neos\Neos\Service\Controller\NodeController' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Neos\Service\Controller\NodeController';
        if ($isSameClass) {
            $this->initializeObject(1);
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
            'getChildNodesForTreeAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'filterChildNodesForTreeAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'createAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'createAndRenderAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'createNodeForTheTreeAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'moveAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'moveAndRenderAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'copyAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'copyAndRenderAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'updateAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'updateAndRenderAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'deleteAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'errorAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
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

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();

        $isSameClass = get_class($this) === 'Neos\Neos\Service\Controller\NodeController';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Neos\Service\Controller\NodeController', $classParents) !== false && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== false;

        if ($isSameClass || $isClassProxy) {
            $this->initializeObject(2);
        }
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
     * @param Node $node The node to find child nodes for
     * @param string $nodeTypeFilter A node type filter
     * @param integer $depth levels of childNodes (0 = unlimited)
     * @param Node $untilNode expand the child nodes until $untilNode is reached, independent of $depth
     * @return void
     */
    public function getChildNodesForTreeAction(\Neos\ContentRepository\Domain\Model\Node $node, $nodeTypeFilter, $depth, \Neos\ContentRepository\Domain\Model\Node $untilNode)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getChildNodesForTreeAction'])) {
            $result = parent::getChildNodesForTreeAction($node, $nodeTypeFilter, $depth, $untilNode);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getChildNodesForTreeAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
                $methodArguments['nodeTypeFilter'] = $nodeTypeFilter;
                $methodArguments['depth'] = $depth;
                $methodArguments['untilNode'] = $untilNode;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getChildNodesForTreeAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\NodeController', 'getChildNodesForTreeAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getChildNodesForTreeAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getChildNodesForTreeAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Node $node The node to find child nodes for
     * @param string $term
     * @param string $nodeType
     * @return void
     */
    public function filterChildNodesForTreeAction(\Neos\ContentRepository\Domain\Model\Node $node, $term, $nodeType)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['filterChildNodesForTreeAction'])) {
            $result = parent::filterChildNodesForTreeAction($node, $term, $nodeType);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['filterChildNodesForTreeAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
                $methodArguments['term'] = $term;
                $methodArguments['nodeType'] = $nodeType;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('filterChildNodesForTreeAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\NodeController', 'filterChildNodesForTreeAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['filterChildNodesForTreeAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['filterChildNodesForTreeAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Node $referenceNode
     * @param array $nodeData
     * @param string $position where the node should be added (allowed: before, into, after)
     * @return void
     */
    public function createAction(\Neos\ContentRepository\Domain\Model\Node $referenceNode, array $nodeData, $position)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createAction'])) {
            $result = parent::createAction($referenceNode, $nodeData, $position);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['createAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['referenceNode'] = $referenceNode;
                $methodArguments['nodeData'] = $nodeData;
                $methodArguments['position'] = $position;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('createAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\NodeController', 'createAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Node $referenceNode
     * @param string $fusionPath The Fusion path of the collection
     * @param array $nodeData
     * @param string $position where the node should be added (allowed: before, into, after)
     * @return string
     */
    public function createAndRenderAction(\Neos\ContentRepository\Domain\Model\Node $referenceNode, $fusionPath, array $nodeData, $position)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createAndRenderAction'])) {
            $result = parent::createAndRenderAction($referenceNode, $fusionPath, $nodeData, $position);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['createAndRenderAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['referenceNode'] = $referenceNode;
                $methodArguments['fusionPath'] = $fusionPath;
                $methodArguments['nodeData'] = $nodeData;
                $methodArguments['position'] = $position;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('createAndRenderAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\NodeController', 'createAndRenderAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createAndRenderAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createAndRenderAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Node $referenceNode
     * @param array $nodeData
     * @param string $position where the node should be added, -1 is before, 0 is in, 1 is after
     * @param string $nodeTypeFilter
     * @return void
     */
    public function createNodeForTheTreeAction(\Neos\ContentRepository\Domain\Model\Node $referenceNode, array $nodeData, $position, $nodeTypeFilter = '')
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createNodeForTheTreeAction'])) {
            $result = parent::createNodeForTheTreeAction($referenceNode, $nodeData, $position, $nodeTypeFilter);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['createNodeForTheTreeAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['referenceNode'] = $referenceNode;
                $methodArguments['nodeData'] = $nodeData;
                $methodArguments['position'] = $position;
                $methodArguments['nodeTypeFilter'] = $nodeTypeFilter;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('createNodeForTheTreeAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\NodeController', 'createNodeForTheTreeAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createNodeForTheTreeAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createNodeForTheTreeAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Node $node The node to be moved
     * @param Node $targetNode The target node to be moved "to", see $position
     * @param string $position where the node should be added (allowed: before, into, after)
     * @return void
     */
    public function moveAction(\Neos\ContentRepository\Domain\Model\Node $node, \Neos\ContentRepository\Domain\Model\Node $targetNode, $position)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['moveAction'])) {
            $result = parent::moveAction($node, $targetNode, $position);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['moveAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
                $methodArguments['targetNode'] = $targetNode;
                $methodArguments['position'] = $position;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('moveAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\NodeController', 'moveAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['moveAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['moveAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Node $node The node to be moved
     * @param Node $targetNode The target node to be moved "to", see $position
     * @param string $position Where the node should be added in relation to $targetNode (allowed: before, into, after)
     * @param string $fusionPath The Fusion path of the collection
     * @return void
     */
    public function moveAndRenderAction(\Neos\ContentRepository\Domain\Model\Node $node, \Neos\ContentRepository\Domain\Model\Node $targetNode, $position, $fusionPath)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['moveAndRenderAction'])) {
            $result = parent::moveAndRenderAction($node, $targetNode, $position, $fusionPath);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['moveAndRenderAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
                $methodArguments['targetNode'] = $targetNode;
                $methodArguments['position'] = $position;
                $methodArguments['fusionPath'] = $fusionPath;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('moveAndRenderAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\NodeController', 'moveAndRenderAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['moveAndRenderAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['moveAndRenderAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Node $node The node to be copied
     * @param Node $targetNode The target node to be copied "to", see $position
     * @param string $position Where the node should be added in relation to $targetNode (allowed: before, into, after)
     * @param string $nodeName Optional node name (if empty random node name will be generated)
     * @return void
     * @throws NodeException
     */
    public function copyAction(\Neos\ContentRepository\Domain\Model\Node $node, \Neos\ContentRepository\Domain\Model\Node $targetNode, $position, $nodeName = NULL)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['copyAction'])) {
            $result = parent::copyAction($node, $targetNode, $position, $nodeName);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['copyAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
                $methodArguments['targetNode'] = $targetNode;
                $methodArguments['position'] = $position;
                $methodArguments['nodeName'] = $nodeName;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('copyAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\NodeController', 'copyAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['copyAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['copyAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Node $node The node to be copied
     * @param Node $targetNode The target node to be copied "to", see $position
     * @param string $position Where the node should be added in relation to $targetNode (allowed: before, into, after)
     * @param string $nodeName Optional node name (if empty random node name will be generated)
     * @param string $fusionPath The Fusion path of the collection
     * @return void
     */
    public function copyAndRenderAction(\Neos\ContentRepository\Domain\Model\Node $node, \Neos\ContentRepository\Domain\Model\Node $targetNode, $position, $fusionPath, $nodeName = NULL)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['copyAndRenderAction'])) {
            $result = parent::copyAndRenderAction($node, $targetNode, $position, $fusionPath, $nodeName);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['copyAndRenderAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
                $methodArguments['targetNode'] = $targetNode;
                $methodArguments['position'] = $position;
                $methodArguments['fusionPath'] = $fusionPath;
                $methodArguments['nodeName'] = $nodeName;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('copyAndRenderAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\NodeController', 'copyAndRenderAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['copyAndRenderAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['copyAndRenderAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Node $node The node to be updated
     * @return void
     */
    public function updateAction(\Neos\ContentRepository\Domain\Model\Node $node)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAction'])) {
            $result = parent::updateAction($node);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('updateAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\NodeController', 'updateAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Node $node The node to be updated
     * @param string $fusionPath The Fusion path of the collection
     * @return void
     */
    public function updateAndRenderAction(\Neos\ContentRepository\Domain\Model\Node $node, $fusionPath)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAndRenderAction'])) {
            $result = parent::updateAndRenderAction($node, $fusionPath);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAndRenderAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
                $methodArguments['fusionPath'] = $fusionPath;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('updateAndRenderAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\NodeController', 'updateAndRenderAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAndRenderAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAndRenderAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Node $node
     * @return void
     */
    public function deleteAction(\Neos\ContentRepository\Domain\Model\Node $node)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['deleteAction'])) {
            $result = parent::deleteAction($node);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['deleteAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('deleteAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\NodeController', 'deleteAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['deleteAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['deleteAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     */
    public function errorAction()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['errorAction'])) {
            $result = parent::errorAction();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['errorAction'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('errorAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\NodeController', 'errorAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['errorAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['errorAction']);
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
  'view' => 'Neos\\Neos\\Service\\View\\NodeView',
  'viewFormatToObjectNameMap' => 'array',
  'supportedMediaTypes' => 'array',
  'nodeTypeManager' => 'Neos\\ContentRepository\\Domain\\Service\\NodeTypeManager',
  'nodeSearchService' => 'Neos\\Neos\\Domain\\Service\\NodeSearchServiceInterface',
  'nodeFactory' => 'Neos\\ContentRepository\\Domain\\Factory\\NodeFactory',
  'contextFactory' => 'Neos\\ContentRepository\\Domain\\Service\\ContextFactoryInterface',
  'nodeDataRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\NodeDataRepository',
  'nodeOperations' => 'Neos\\Neos\\Service\\NodeOperations',
  'domainRepository' => 'Neos\\Neos\\Domain\\Repository\\DomainRepository',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'reflectionService' => 'Neos\\Flow\\Reflection\\ReflectionService',
  'mvcPropertyMappingConfigurationService' => 'Neos\\Flow\\Mvc\\Controller\\MvcPropertyMappingConfigurationService',
  'viewConfigurationManager' => 'Neos\\Flow\\Mvc\\ViewConfigurationManager',
  'viewObjectNamePattern' => 'string',
  'defaultViewObjectName' => 'string',
  'defaultViewImplementation' => 'string',
  'actionMethodName' => 'string',
  'errorMethodName' => 'string',
  'settings' => 'array',
  'systemLogger' => 'Neos\\Flow\\Log\\SystemLoggerInterface',
  'logger' => 'Psr\\Log\\LoggerInterface',
  'uriBuilder' => 'Neos\\Flow\\Mvc\\Routing\\UriBuilder',
  'validatorResolver' => 'Neos\\Flow\\Validation\\ValidatorResolver',
  'request' => 'Neos\\Flow\\Mvc\\ActionRequest',
  'response' => 'Neos\\Flow\\Mvc\\ActionResponse',
  'arguments' => 'Neos\\Flow\\Mvc\\Controller\\Arguments',
  'controllerContext' => 'Neos\\Flow\\Mvc\\Controller\\ControllerContext',
  'flashMessageContainer' => 'Neos\\Flow\\Mvc\\FlashMessageContainer',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  '_localizationService' => '\\Neos\\Flow\\I18n\\Service',
  '_userService' => '\\Neos\\Neos\\Service\\UserService',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Neos'));
        $this->injectLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Psr\Log\LoggerInterface'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\NodeTypeManager', 'Neos\ContentRepository\Domain\Service\NodeTypeManager', 'nodeTypeManager', 'e525e2ecb65f7f9731d6537ddecd16d4', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\NodeTypeManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Service\NodeSearchServiceInterface', 'Neos\Neos\Domain\Service\NodeSearchService', 'nodeSearchService', 'e161d65eb2ddc7a4461eb3fc98cdaf5a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Service\NodeSearchServiceInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Factory\NodeFactory', 'Neos\ContentRepository\Domain\Factory\NodeFactory', 'nodeFactory', 'dd541629b8e42562866a1bf47375f14d', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Factory\NodeFactory'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\ContextFactoryInterface', 'Neos\Neos\Domain\Service\ContentContextFactory', 'contextFactory', '98dca7b1f95a25ec173662fc4e785341', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\ContextFactoryInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\NodeDataRepository', 'Neos\ContentRepository\Domain\Repository\NodeDataRepository', 'nodeDataRepository', '6d07985e92d364413ac81acd8f47b11b', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\NodeDataRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\NodeOperations', 'Neos\Neos\Service\NodeOperations', 'nodeOperations', '39984c4ae5535846f0a7b34b7560eb28', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\NodeOperations'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\DomainRepository', 'Neos\Neos\Domain\Repository\DomainRepository', 'domainRepository', '37b1b7f7b2d5d92dae299591af3b7e10', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\DomainRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Reflection\ReflectionService', 'Neos\Flow\Reflection\ReflectionService', 'reflectionService', '464c26aa94c66579c050985566cbfc1f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Reflection\ReflectionService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService', 'Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService', 'mvcPropertyMappingConfigurationService', '245f31ad31ca22b8c2b2255e0f65f847', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\ViewConfigurationManager', 'Neos\Flow\Mvc\ViewConfigurationManager', 'viewConfigurationManager', '40e27e95b530777b9b476762cf735a69', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\ViewConfigurationManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\SystemLoggerInterface', 'Neos\Flow\Log\Logger', 'systemLogger', '717e9de4d0309f4f47c821b9257eb5c2', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Validation\ValidatorResolver', 'Neos\Flow\Validation\ValidatorResolver', 'validatorResolver', 'e992f50de62d81bfe770d5c5f1242621', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Validation\ValidatorResolver'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\FlashMessageContainer', 'Neos\Flow\Mvc\FlashMessageContainer', 'flashMessageContainer', 'a5f5265657df54eb081324fb2ff5b8e1', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\FlashMessageContainer'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\I18n\Service', 'Neos\Flow\I18n\Service', '_localizationService', 'bdcad09aa1b6973b35f2987887987892', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\Service'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\UserService', 'Neos\Neos\Service\UserService', '_userService', '3af75a289d0337400c3d43d557f82c49', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\UserService'); });
        $this->defaultViewImplementation = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow.mvc.view.defaultImplementation');
        $this->Flow_Injected_Properties = array (
  0 => 'settings',
  1 => 'logger',
  2 => 'nodeTypeManager',
  3 => 'nodeSearchService',
  4 => 'nodeFactory',
  5 => 'contextFactory',
  6 => 'nodeDataRepository',
  7 => 'nodeOperations',
  8 => 'domainRepository',
  9 => 'objectManager',
  10 => 'reflectionService',
  11 => 'mvcPropertyMappingConfigurationService',
  12 => 'viewConfigurationManager',
  13 => 'systemLogger',
  14 => 'validatorResolver',
  15 => 'flashMessageContainer',
  16 => 'persistenceManager',
  17 => '_localizationService',
  18 => '_userService',
  19 => 'defaultViewImplementation',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Service/Controller/NodeController.php
#