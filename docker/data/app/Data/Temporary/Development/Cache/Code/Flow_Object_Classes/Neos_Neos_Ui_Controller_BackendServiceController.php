<?php 
namespace Neos\Neos\Ui\Controller;

/*
 * This file is part of the Neos.Neos.Ui package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Repository\WorkspaceRepository;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\RequestInterface;
use Neos\Flow\Mvc\ResponseInterface;
use Neos\Flow\Mvc\View\JsonView;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Neos\Domain\Service\ContentContextFactory;
use Neos\Neos\Domain\Service\ContentDimensionPresetSourceInterface;
use Neos\Neos\Service\PublishingService;
use Neos\Neos\Service\UserService;
use Neos\Neos\Ui\ContentRepository\Service\NodeService;
use Neos\Neos\Ui\ContentRepository\Service\WorkspaceService;
use Neos\Neos\Ui\Domain\Model\ChangeCollection;
use Neos\Neos\Ui\Domain\Model\Feedback\Messages\Error;
use Neos\Neos\Ui\Domain\Model\Feedback\Messages\Info;
use Neos\Neos\Ui\Domain\Model\Feedback\Messages\Success;
use Neos\Neos\Ui\Domain\Model\Feedback\Operations\Redirect;
use Neos\Neos\Ui\Domain\Model\Feedback\Operations\ReloadDocument;
use Neos\Neos\Ui\Domain\Model\Feedback\Operations\RemoveNode;
use Neos\Neos\Ui\Domain\Model\Feedback\Operations\UpdateNodeInfo;
use Neos\Neos\Ui\Domain\Model\Feedback\Operations\UpdateWorkspaceInfo;
use Neos\Neos\Ui\Domain\Model\FeedbackCollection;
use Neos\Neos\Ui\Service\NodeClipboard;
use Neos\Neos\Ui\Service\NodePolicyService;
use Neos\Neos\Ui\Domain\Service\NodeTreeBuilder;
use Neos\Neos\Ui\Fusion\Helper\NodeInfoHelper;
use Neos\Neos\Ui\Fusion\Helper\WorkspaceHelper;

class BackendServiceController_Original extends ActionController
{
    /**
     * @Flow\Inject
     * @var ContentContextFactory
     */
    protected $contextFactory;

    /**
     * @var array
     */
    protected $supportedMediaTypes = ['application/json'];

    /**
     * @var string
     */
    protected $defaultViewObjectName = JsonView::class;

    /**
     * @Flow\Inject
     * @var FeedbackCollection
     */
    protected $feedbackCollection;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var PublishingService
     */
    protected $publishingService;

    /**
     * @Flow\Inject
     * @var NodeService
     */
    protected $nodeService;

    /**
     * @Flow\Inject
     * @var WorkspaceRepository
     */
    protected $workspaceRepository;

    /**
     * @Flow\Inject
     * @var WorkspaceService
     */
    protected $workspaceService;

    /**
     * @Flow\Inject
     * @var UserService
     */
    protected $userService;

    /**
     * @Flow\Inject
     * @var NodePolicyService
     */
    protected $nodePolicyService;

    /**
     * @Flow\Inject
     * @var NodeClipboard
     */
    protected $clipboard;

    /**
     * @Flow\Inject
     * @var ContentDimensionPresetSourceInterface
     */
    protected $contentDimensionsPresetSource;

    /**
     * Set the controller context on the feedback collection after the controller
     * has been initialized
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return void
     */
    public function initializeController(RequestInterface $request, ResponseInterface $response)
    {
        parent::initializeController($request, $response);
        $this->feedbackCollection->setControllerContext($this->getControllerContext());
    }

    /**
     * Helper method to inform the client, that new workspace information is available
     *
     * @param string $documentNodeContextPath
     * @return void
     */
    protected function updateWorkspaceInfo(string $documentNodeContextPath)
    {
        $updateWorkspaceInfo = new UpdateWorkspaceInfo();
        $documentNode = $this->nodeService->getNodeFromContextPath($documentNodeContextPath, null, null, true);
        $updateWorkspaceInfo->setWorkspace(
            $documentNode->getContext()->getWorkspace()
        );

        $this->feedbackCollection->add($updateWorkspaceInfo);
    }

    /**
     * Apply a set of changes to the system
     *
     * @param ChangeCollection $changes
     * @return void
     */
    public function changeAction(ChangeCollection $changes)
    {
        try {
            $count = $changes->count();
            $changes->apply();

            $success = new Info();
            $success->setMessage(sprintf('%d change(s) successfully applied.', $count));

            $this->feedbackCollection->add($success);
            $this->persistenceManager->persistAll();
        } catch (\Exception $e) {
            $error = new Error();
            $error->setMessage($e->getMessage());

            $this->feedbackCollection->add($error);
        }

        $this->view->assign('value', $this->feedbackCollection);
    }

    /**
     * Publish nodes
     *
     * @param array $nodeContextPaths
     * @param string $targetWorkspaceName
     * @return void
     */
    public function publishAction(array $nodeContextPaths, string $targetWorkspaceName)
    {
        try {
            $targetWorkspace = $this->workspaceRepository->findOneByName($targetWorkspaceName);

            foreach ($nodeContextPaths as $contextPath) {
                $node = $this->nodeService->getNodeFromContextPath($contextPath, null, null, true);
                $this->publishingService->publishNode($node, $targetWorkspace);
            }

            $success = new Success();
            $success->setMessage(sprintf('Published %d change(s) to %s.', count($nodeContextPaths), $targetWorkspaceName));

            $this->updateWorkspaceInfo($nodeContextPaths[0]);
            $this->feedbackCollection->add($success);

            $this->persistenceManager->persistAll();
        } catch (\Exception $e) {
            $error = new Error();
            $error->setMessage($e->getMessage());

            $this->feedbackCollection->add($error);
        }

        $this->view->assign('value', $this->feedbackCollection);
    }

    /**
     * Discard nodes
     *
     * @param array $nodeContextPaths
     * @return void
     */
    public function discardAction(array $nodeContextPaths)
    {
        try {
            foreach ($nodeContextPaths as $contextPath) {
                $node = $this->nodeService->getNodeFromContextPath($contextPath, null, null, true);
                if ($node->isRemoved() === true) {
                    // When discarding node removal we should re-create it
                    $updateNodeInfo = new UpdateNodeInfo();
                    $updateNodeInfo->setNode($node);
                    $updateNodeInfo->recursive();

                    $updateParentNodeInfo = new UpdateNodeInfo();
                    $updateParentNodeInfo->setNode($node->getParent());

                    $this->feedbackCollection->add($updateNodeInfo);
                    $this->feedbackCollection->add($updateParentNodeInfo);

                    // Reload document for content node changes
                    // (as we can't RenderContentOutOfBand from here, we don't know dom addresses)
                    if (!$this->nodeService->isDocument($node)) {
                        $reloadDocument = new ReloadDocument();
                        $this->feedbackCollection->add($reloadDocument);
                    }
                } elseif (!$this->nodeService->nodeExistsInWorkspace($node, $node->getWorkSpace()->getBaseWorkspace())) {
                    // If the node doesn't exist in the target workspace, tell the UI to remove it
                    $removeNode = new RemoveNode();
                    $removeNode->setNode($node);
                    $this->feedbackCollection->add($removeNode);
                }

                $this->publishingService->discardNode($node);
            }

            $success = new Success();
            $success->setMessage(sprintf('Discarded %d node(s).', count($nodeContextPaths)));

            $this->updateWorkspaceInfo($nodeContextPaths[0]);
            $this->feedbackCollection->add($success);

            $this->persistenceManager->persistAll();
        } catch (\Exception $e) {
            $error = new Error();
            $error->setMessage($e->getMessage());

            $this->feedbackCollection->add($error);
        }

        $this->view->assign('value', $this->feedbackCollection);
    }

    /**
     * Change base workspace of current user workspace
     *
     * @param string $targetWorkspaceName ,
     * @param NodeInterface $documentNode
     * @return void
     * @throws \Exception
     */
    public function changeBaseWorkspaceAction(string $targetWorkspaceName, NodeInterface $documentNode)
    {
        try {
            $targetWorkspace = $this->workspaceRepository->findOneByName($targetWorkspaceName);
            $userWorkspace = $this->userService->getPersonalWorkspace();

            if (count($this->workspaceService->getPublishableNodeInfo($userWorkspace)) > 0) {
                // TODO: proper error dialog
                throw new \Exception('Your personal workspace currently contains unpublished changes. In order to switch to a different target workspace you need to either publish or discard pending changes first.');
            }

            $userWorkspace->setBaseWorkspace($targetWorkspace);
            $this->workspaceRepository->update($userWorkspace);

            $success = new Success();
            $success->setMessage(sprintf('Switched base workspace to %s.', $targetWorkspaceName));
            $this->feedbackCollection->add($success);

            $updateWorkspaceInfo = new UpdateWorkspaceInfo();
            $updateWorkspaceInfo->setWorkspace($userWorkspace);
            $this->feedbackCollection->add($updateWorkspaceInfo);

            // Construct base workspace context
            $originalContext = $documentNode->getContext();
            $contextProperties = $documentNode->getContext()->getProperties();
            $contextProperties['workspaceName'] = $targetWorkspaceName;
            $contentContext = $this->contextFactory->create($contextProperties);

            // If current document node doesn't exist in the base workspace, traverse its parents to find the one that exists
            $redirectNode = $documentNode;
            while (true) {
                $redirectNodeInBaseWorkspace = $contentContext->getNodeByIdentifier($redirectNode->getIdentifier());
                if ($redirectNodeInBaseWorkspace) {
                    break;
                } else {
                    $redirectNode = $redirectNode->getParent();
                    if (!$redirectNode) {
                        throw new \Exception(sprintf('Wasn\'t able to locate any valid node in rootline of node %s in the workspace %s.', $documentNode->getContextPath(), $targetWorkspaceName), 1458814469);
                    }
                }
            }

            // If current document node exists in the base workspace, then reload, else redirect
            if ($redirectNode === $documentNode) {
                $reloadDocument = new ReloadDocument();
                $reloadDocument->setNode($documentNode);
                $this->feedbackCollection->add($reloadDocument);
            } else {
                $redirect = new Redirect();
                $redirect->setNode($redirectNode);
                $this->feedbackCollection->add($redirect);
            }

            $this->persistenceManager->persistAll();
        } catch (\Exception $e) {
            $error = new Error();
            $error->setMessage($e->getMessage());

            $this->feedbackCollection->add($error);
        }

        $this->view->assign('value', $this->feedbackCollection);
    }

    /**
     * Persists the clipboard node on copy
     *
     * @param NodeInterface $node
     * @return void
     */
    public function copyNodeAction(NodeInterface $node)
    {
        $this->clipboard->copyNode($node);
    }

    /**
     * Clears the clipboard state
     *
     * @return void
     */
    public function clearClipboardAction()
    {
        $this->clipboard->clear();
    }

    /**
     * Persists the clipboard node on cut
     *
     * @param NodeInterface $node
     * @return void
     */
    public function cutNodeAction(NodeInterface $node)
    {
        $this->clipboard->cutNode($node);
    }

    public function getWorkspaceInfoAction()
    {
        $workspaceHelper = new WorkspaceHelper();
        $personalWorkspaceInfo = $workspaceHelper->getPersonalWorkspace();
        $this->view->assign('value', $personalWorkspaceInfo);
    }

    public function initializeLoadTreeAction()
    {
        $this->arguments['nodeTreeArguments']->getPropertyMappingConfiguration()->allowAllProperties();
    }

    /**
     * Load the nodetree
     *
     * @param NodeTreeBuilder $nodeTreeArguments
     * @param boolean $includeRoot
     * @return void
     */
    public function loadTreeAction(NodeTreeBuilder $nodeTreeArguments, $includeRoot = false)
    {
        $nodeTreeArguments->setControllerContext($this->controllerContext);
        $this->view->assign('value', $nodeTreeArguments->build($includeRoot));
    }

    /**
     * @throws \Neos\Flow\Mvc\Exception\NoSuchArgumentException
     */
    public function initializeGetAdditionalNodeMetadataAction()
    {
        $this->arguments->getArgument('nodes')->getPropertyMappingConfiguration()->allowAllProperties();
    }

    /**
     * Fetches all the node information that can be lazy-loaded
     *
     * @param array<NodeInterface> $nodes
     */
    public function getAdditionalNodeMetadataAction(array $nodes)
    {
        $result = [];
        /** @var NodeInterface $node */
        foreach ($nodes as $node) {
            $otherNodeVariants = array_values(array_filter(array_map(function ($node) {
                return $this->getCurrentDimensionPresetIdentifiersForNode($node);
            }, $node->getOtherNodeVariants())));
            $result[$node->getContextPath()] = [
                'policy' => $this->nodePolicyService->getNodePolicyInformation($node),
                'dimensions' => $this->getCurrentDimensionPresetIdentifiersForNode($node),
                'otherNodeVariants' => $otherNodeVariants
            ];
        }

        $this->view->assign('value', $result);
    }

    /**
     * Gets an array of current preset identifiers for each dimension of the give node
     *
     * @param NodeInterface $node
     * @return array
     */
    protected function getCurrentDimensionPresetIdentifiersForNode($node)
    {
        $targetPresets = $this->contentDimensionsPresetSource->findPresetsByTargetValues($node->getDimensions());
        $presetCombo = [];
        foreach ($targetPresets as $dimensionName => $presetConfig) {
            $fullPresetConfig = $this->contentDimensionsPresetSource->findPresetByDimensionValues($dimensionName, $presetConfig['values']);
            $presetCombo[$dimensionName] = $fullPresetConfig['identifier'];
        }
        return $presetCombo;
    }

    /**
     * Build and execute a flow query chain
     *
     * @param array $chain
     * @return string
     */
    public function flowQueryAction(array $chain)
    {
        $createContext = array_shift($chain);
        $finisher = array_pop($chain);

        $flowQuery = new FlowQuery(array_map(
            function ($envelope) {
                return $this->nodeService->getNodeFromContextPath($envelope['$node']);
            },
            $createContext['payload']
        ));

        foreach ($chain as $operation) {
            $flowQuery = call_user_func_array([$flowQuery, $operation['type']], $operation['payload']);
        }

        $nodeInfoHelper = new NodeInfoHelper();
        $result = [];
        switch ($finisher['type']) {
            case 'get':
                $result = $nodeInfoHelper->renderNodes($flowQuery->get(), $this->getControllerContext());
            break;
            case 'getForTree':
                $result = $nodeInfoHelper->renderNodes($flowQuery->get(), $this->getControllerContext(), true);
            break;
            case 'getForTreeWithParents':
                $result = $nodeInfoHelper->renderNodesWithParents($flowQuery->get(), $this->getControllerContext());
            break;
        }

        return json_encode($result);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Ui\Controller;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 */
class BackendServiceController extends BackendServiceController_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'])) {

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('__construct');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', '__construct', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
            return;
        }
        if ('Neos\Neos\Ui\Controller\BackendServiceController' === get_class($this)) {
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
            '__construct' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            '__clone' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'initializeController' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'updateWorkspaceInfo' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'changeAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'publishAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'discardAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'changeBaseWorkspaceAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'copyNodeAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'clearClipboardAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'cutNodeAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'getWorkspaceInfoAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'initializeLoadTreeAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'loadTreeAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'initializeGetAdditionalNodeMetadataAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'getAdditionalNodeMetadataAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'getCurrentDimensionPresetIdentifiersForNode' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'flowQueryAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'injectSettings' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'injectLogger' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'processRequest' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'resolveActionMethodName' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'initializeActionMethodArguments' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'getInformationNeededForInitializeActionMethodValidators' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'initializeActionMethodValidators' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'initializeAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'callActionMethod' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'resolveView' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'resolveViewObjectName' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'initializeView' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'errorAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'handleTargetNotFoundError' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'addErrorFlashMessage' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'forwardToReferringRequest' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'getFlattenedValidationErrorMessage' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'getErrorFlashMessage' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'getControllerContext' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'addFlashMessage' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'forward' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'forwardToRequest' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'redirect' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'redirectToRequest' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'redirectToUri' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'throwStatus' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'mapRequestArgumentsToControllerArguments' => array(
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
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __clone()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone'])) {
            $result = NULL;

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('__clone');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', '__clone', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return void
     */
    public function initializeController(\Neos\Flow\Mvc\RequestInterface $request, \Neos\Flow\Mvc\ResponseInterface $response)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeController'])) {
            $result = parent::initializeController($request, $response);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeController'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['request'] = $request;
                $methodArguments['response'] = $response;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('initializeController');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'initializeController', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeController']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeController']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param string $documentNodeContextPath
     * @return void
     */
    protected function updateWorkspaceInfo(string $documentNodeContextPath)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateWorkspaceInfo'])) {
            $result = parent::updateWorkspaceInfo($documentNodeContextPath);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['updateWorkspaceInfo'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['documentNodeContextPath'] = $documentNodeContextPath;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('updateWorkspaceInfo');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'updateWorkspaceInfo', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateWorkspaceInfo']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateWorkspaceInfo']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param ChangeCollection $changes
     * @return void
     */
    public function changeAction(\Neos\Neos\Ui\Domain\Model\ChangeCollection $changes)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['changeAction'])) {
            $result = parent::changeAction($changes);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['changeAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['changes'] = $changes;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('changeAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'changeAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['changeAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['changeAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param array $nodeContextPaths
     * @param string $targetWorkspaceName
     * @return void
     */
    public function publishAction(array $nodeContextPaths, string $targetWorkspaceName)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishAction'])) {
            $result = parent::publishAction($nodeContextPaths, $targetWorkspaceName);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['publishAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['nodeContextPaths'] = $nodeContextPaths;
                $methodArguments['targetWorkspaceName'] = $targetWorkspaceName;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('publishAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'publishAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param array $nodeContextPaths
     * @return void
     */
    public function discardAction(array $nodeContextPaths)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['discardAction'])) {
            $result = parent::discardAction($nodeContextPaths);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['discardAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['nodeContextPaths'] = $nodeContextPaths;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('discardAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'discardAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['discardAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['discardAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param string $targetWorkspaceName ,
     * @param NodeInterface $documentNode
     * @return void
     * @throws \Exception
     */
    public function changeBaseWorkspaceAction(string $targetWorkspaceName, \Neos\ContentRepository\Domain\Model\NodeInterface $documentNode)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['changeBaseWorkspaceAction'])) {
            $result = parent::changeBaseWorkspaceAction($targetWorkspaceName, $documentNode);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['changeBaseWorkspaceAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['targetWorkspaceName'] = $targetWorkspaceName;
                $methodArguments['documentNode'] = $documentNode;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('changeBaseWorkspaceAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'changeBaseWorkspaceAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['changeBaseWorkspaceAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['changeBaseWorkspaceAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param NodeInterface $node
     * @return void
     */
    public function copyNodeAction(\Neos\ContentRepository\Domain\Model\NodeInterface $node)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['copyNodeAction'])) {
            $result = parent::copyNodeAction($node);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['copyNodeAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('copyNodeAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'copyNodeAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['copyNodeAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['copyNodeAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     */
    public function clearClipboardAction()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['clearClipboardAction'])) {
            $result = parent::clearClipboardAction();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['clearClipboardAction'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('clearClipboardAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'clearClipboardAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['clearClipboardAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['clearClipboardAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param NodeInterface $node
     * @return void
     */
    public function cutNodeAction(\Neos\ContentRepository\Domain\Model\NodeInterface $node)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['cutNodeAction'])) {
            $result = parent::cutNodeAction($node);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['cutNodeAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('cutNodeAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'cutNodeAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['cutNodeAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['cutNodeAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function getWorkspaceInfoAction()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getWorkspaceInfoAction'])) {
            $result = parent::getWorkspaceInfoAction();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getWorkspaceInfoAction'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getWorkspaceInfoAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'getWorkspaceInfoAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getWorkspaceInfoAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getWorkspaceInfoAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function initializeLoadTreeAction()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeLoadTreeAction'])) {
            $result = parent::initializeLoadTreeAction();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeLoadTreeAction'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('initializeLoadTreeAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'initializeLoadTreeAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeLoadTreeAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeLoadTreeAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param NodeTreeBuilder $nodeTreeArguments
     * @param boolean $includeRoot
     * @return void
     */
    public function loadTreeAction(\Neos\Neos\Ui\Domain\Service\NodeTreeBuilder $nodeTreeArguments, $includeRoot = false)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['loadTreeAction'])) {
            $result = parent::loadTreeAction($nodeTreeArguments, $includeRoot);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['loadTreeAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['nodeTreeArguments'] = $nodeTreeArguments;
                $methodArguments['includeRoot'] = $includeRoot;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('loadTreeAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'loadTreeAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['loadTreeAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['loadTreeAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @throws \Neos\Flow\Mvc\Exception\NoSuchArgumentException
     */
    public function initializeGetAdditionalNodeMetadataAction()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeGetAdditionalNodeMetadataAction'])) {
            $result = parent::initializeGetAdditionalNodeMetadataAction();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeGetAdditionalNodeMetadataAction'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('initializeGetAdditionalNodeMetadataAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'initializeGetAdditionalNodeMetadataAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeGetAdditionalNodeMetadataAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeGetAdditionalNodeMetadataAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param array<NodeInterface> $nodes
     */
    public function getAdditionalNodeMetadataAction(array $nodes)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getAdditionalNodeMetadataAction'])) {
            $result = parent::getAdditionalNodeMetadataAction($nodes);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getAdditionalNodeMetadataAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['nodes'] = $nodes;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getAdditionalNodeMetadataAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'getAdditionalNodeMetadataAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getAdditionalNodeMetadataAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getAdditionalNodeMetadataAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param NodeInterface $node
     * @return array
     */
    protected function getCurrentDimensionPresetIdentifiersForNode($node)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getCurrentDimensionPresetIdentifiersForNode'])) {
            $result = parent::getCurrentDimensionPresetIdentifiersForNode($node);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getCurrentDimensionPresetIdentifiersForNode'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getCurrentDimensionPresetIdentifiersForNode');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'getCurrentDimensionPresetIdentifiersForNode', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getCurrentDimensionPresetIdentifiersForNode']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getCurrentDimensionPresetIdentifiersForNode']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param array $chain
     * @return string
     */
    public function flowQueryAction(array $chain)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['flowQueryAction'])) {
            $result = parent::flowQueryAction($chain);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['flowQueryAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['chain'] = $chain;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('flowQueryAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'flowQueryAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['flowQueryAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['flowQueryAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['injectSettings'])) {
            $result = parent::injectSettings($settings);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['injectSettings'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['settings'] = $settings;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('injectSettings');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'injectSettings', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['injectSettings']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['injectSettings']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param LoggerInterface $logger
     * @return void
     */
    public function injectLogger(\Psr\Log\LoggerInterface $logger)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['injectLogger'])) {
            $result = parent::injectLogger($logger);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['injectLogger'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['logger'] = $logger;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('injectLogger');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'injectLogger', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['injectLogger']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['injectLogger']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param RequestInterface $request The request object
     * @param ResponseInterface|ActionResponse $response The response, modified by this handler
     * @return void
     * @throws UnsupportedRequestTypeException
     */
    public function processRequest(\Neos\Flow\Mvc\RequestInterface $request, \Neos\Flow\Mvc\ResponseInterface $response)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['processRequest'])) {
            $result = parent::processRequest($request, $response);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['processRequest'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['request'] = $request;
                $methodArguments['response'] = $response;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('processRequest');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'processRequest', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['processRequest']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['processRequest']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return string Method name of the current action
     * @throws NoSuchActionException
     * @throws InvalidActionVisibilityException
     */
    protected function resolveActionMethodName()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['resolveActionMethodName'])) {
            $result = parent::resolveActionMethodName();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['resolveActionMethodName'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('resolveActionMethodName');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'resolveActionMethodName', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['resolveActionMethodName']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['resolveActionMethodName']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     * @throws InvalidArgumentTypeException
     */
    protected function initializeActionMethodArguments()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeActionMethodArguments'])) {
            $result = parent::initializeActionMethodArguments();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeActionMethodArguments'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('initializeActionMethodArguments');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'initializeActionMethodArguments', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeActionMethodArguments']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeActionMethodArguments']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return array
     */
    protected function getInformationNeededForInitializeActionMethodValidators()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getInformationNeededForInitializeActionMethodValidators'])) {
            $result = parent::getInformationNeededForInitializeActionMethodValidators();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getInformationNeededForInitializeActionMethodValidators'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getInformationNeededForInitializeActionMethodValidators');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'getInformationNeededForInitializeActionMethodValidators', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getInformationNeededForInitializeActionMethodValidators']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getInformationNeededForInitializeActionMethodValidators']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     */
    protected function initializeActionMethodValidators()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeActionMethodValidators'])) {
            $result = parent::initializeActionMethodValidators();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeActionMethodValidators'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('initializeActionMethodValidators');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'initializeActionMethodValidators', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeActionMethodValidators']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeActionMethodValidators']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     */
    protected function initializeAction()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeAction'])) {
            $result = parent::initializeAction();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeAction'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('initializeAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'initializeAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     */
    protected function callActionMethod()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['callActionMethod'])) {
            $result = parent::callActionMethod();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['callActionMethod'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('callActionMethod');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'callActionMethod', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['callActionMethod']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['callActionMethod']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return ViewInterface the resolved view
     * @throws ViewNotFoundException if no view can be resolved
     */
    protected function resolveView()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['resolveView'])) {
            $result = parent::resolveView();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['resolveView'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('resolveView');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'resolveView', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['resolveView']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['resolveView']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return mixed The fully qualified view object name or false if no matching view could be found.
     */
    protected function resolveViewObjectName()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['resolveViewObjectName'])) {
            $result = parent::resolveViewObjectName();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['resolveViewObjectName'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('resolveViewObjectName');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'resolveViewObjectName', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['resolveViewObjectName']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['resolveViewObjectName']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param ViewInterface $view The view to be initialized
     * @return void
     */
    protected function initializeView(\Neos\Flow\Mvc\View\ViewInterface $view)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeView'])) {
            $result = parent::initializeView($view);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeView'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['view'] = $view;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('initializeView');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'initializeView', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeView']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeView']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return string
     */
    protected function errorAction()
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'errorAction', $methodArguments, $adviceChain);
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
     * @return void
     * @throws TargetNotFoundException
     */
    protected function handleTargetNotFoundError()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['handleTargetNotFoundError'])) {
            $result = parent::handleTargetNotFoundError();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['handleTargetNotFoundError'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('handleTargetNotFoundError');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'handleTargetNotFoundError', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['handleTargetNotFoundError']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['handleTargetNotFoundError']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     */
    protected function addErrorFlashMessage()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['addErrorFlashMessage'])) {
            $result = parent::addErrorFlashMessage();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['addErrorFlashMessage'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('addErrorFlashMessage');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'addErrorFlashMessage', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['addErrorFlashMessage']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['addErrorFlashMessage']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     * @throws ForwardException
     */
    protected function forwardToReferringRequest()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['forwardToReferringRequest'])) {
            $result = parent::forwardToReferringRequest();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['forwardToReferringRequest'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('forwardToReferringRequest');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'forwardToReferringRequest', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['forwardToReferringRequest']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['forwardToReferringRequest']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return string
     */
    protected function getFlattenedValidationErrorMessage()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getFlattenedValidationErrorMessage'])) {
            $result = parent::getFlattenedValidationErrorMessage();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getFlattenedValidationErrorMessage'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getFlattenedValidationErrorMessage');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'getFlattenedValidationErrorMessage', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getFlattenedValidationErrorMessage']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getFlattenedValidationErrorMessage']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return \Neos\Error\Messages\Message The flash message or false if no flash message should be set
     */
    protected function getErrorFlashMessage()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getErrorFlashMessage'])) {
            $result = parent::getErrorFlashMessage();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getErrorFlashMessage'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getErrorFlashMessage');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'getErrorFlashMessage', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getErrorFlashMessage']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getErrorFlashMessage']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return ControllerContext The current controller context
     */
    public function getControllerContext()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getControllerContext'])) {
            $result = parent::getControllerContext();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getControllerContext'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getControllerContext');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'getControllerContext', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getControllerContext']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getControllerContext']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param string $messageBody text of the FlashMessage
     * @param string $messageTitle optional header of the FlashMessage
     * @param string $severity severity of the FlashMessage (one of the Message::SEVERITY_* constants)
     * @param array $messageArguments arguments to be passed to the FlashMessage
     * @param integer $messageCode
     * @return void
     * @throws \InvalidArgumentException if the message body is no string
     */
    public function addFlashMessage($messageBody, $messageTitle = '', $severity = 'OK', array $messageArguments = array(), $messageCode = NULL)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['addFlashMessage'])) {
            $result = parent::addFlashMessage($messageBody, $messageTitle, $severity, $messageArguments, $messageCode);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['addFlashMessage'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['messageBody'] = $messageBody;
                $methodArguments['messageTitle'] = $messageTitle;
                $methodArguments['severity'] = $severity;
                $methodArguments['messageArguments'] = $messageArguments;
                $methodArguments['messageCode'] = $messageCode;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('addFlashMessage');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'addFlashMessage', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['addFlashMessage']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['addFlashMessage']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param string $actionName Name of the action to forward to
     * @param string $controllerName Unqualified object name of the controller to forward to. If not specified, the current controller is used.
     * @param string $packageKey Key of the package containing the controller to forward to. May also contain the sub package, concatenated with backslash (Vendor.Foo\Bar\Baz). If not specified, the current package is assumed.
     * @param array $arguments Arguments to pass to the target action
     * @return void
     * @throws ForwardException
     */
    protected function forward($actionName, $controllerName = NULL, $packageKey = NULL, array $arguments = array())
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['forward'])) {
            $result = parent::forward($actionName, $controllerName, $packageKey, $arguments);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['forward'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['actionName'] = $actionName;
                $methodArguments['controllerName'] = $controllerName;
                $methodArguments['packageKey'] = $packageKey;
                $methodArguments['arguments'] = $arguments;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('forward');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'forward', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['forward']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['forward']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param ActionRequest $request The request to redirect to
     * @return void
     * @throws ForwardException
     */
    protected function forwardToRequest(\Neos\Flow\Mvc\ActionRequest $request)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['forwardToRequest'])) {
            $result = parent::forwardToRequest($request);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['forwardToRequest'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['request'] = $request;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('forwardToRequest');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'forwardToRequest', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['forwardToRequest']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['forwardToRequest']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param string $actionName Name of the action to forward to
     * @param string $controllerName Unqualified object name of the controller to forward to. If not specified, the current controller is used.
     * @param string $packageKey Key of the package containing the controller to forward to. If not specified, the current package is assumed.
     * @param array $arguments Array of arguments for the target action
     * @param integer $delay (optional) The delay in seconds. Default is no delay.
     * @param integer $statusCode (optional) The HTTP status code for the redirect. Default is "303 See Other
     * @param string $format The format to use for the redirect URI
     * @return void
     * @throws StopActionException
     */
    protected function redirect($actionName, $controllerName = NULL, $packageKey = NULL, ?array $arguments = NULL, $delay = 0, $statusCode = 303, $format = NULL)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['redirect'])) {
            $result = parent::redirect($actionName, $controllerName, $packageKey, $arguments, $delay, $statusCode, $format);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['redirect'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['actionName'] = $actionName;
                $methodArguments['controllerName'] = $controllerName;
                $methodArguments['packageKey'] = $packageKey;
                $methodArguments['arguments'] = $arguments;
                $methodArguments['delay'] = $delay;
                $methodArguments['statusCode'] = $statusCode;
                $methodArguments['format'] = $format;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('redirect');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'redirect', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['redirect']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['redirect']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param ActionRequest $request The request to redirect to
     * @param integer $delay (optional) The delay in seconds. Default is no delay.
     * @param integer $statusCode (optional) The HTTP status code for the redirect. Default is "303 See Other
     * @return void
     * @throws StopActionException
     */
    protected function redirectToRequest(\Neos\Flow\Mvc\ActionRequest $request, $delay = 0, $statusCode = 303)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['redirectToRequest'])) {
            $result = parent::redirectToRequest($request, $delay, $statusCode);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['redirectToRequest'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['request'] = $request;
                $methodArguments['delay'] = $delay;
                $methodArguments['statusCode'] = $statusCode;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('redirectToRequest');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'redirectToRequest', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['redirectToRequest']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['redirectToRequest']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param mixed $uri Either a string representation of a URI or a \Neos\Flow\Http\Uri object
     * @param integer $delay (optional) The delay in seconds. Default is no delay.
     * @param integer $statusCode (optional) The HTTP status code for the redirect. Default is "303 See Other
     * @throws UnsupportedRequestTypeException If the request is not a web request
     * @throws StopActionException
     */
    protected function redirectToUri($uri, $delay = 0, $statusCode = 303)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['redirectToUri'])) {
            $result = parent::redirectToUri($uri, $delay, $statusCode);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['redirectToUri'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['uri'] = $uri;
                $methodArguments['delay'] = $delay;
                $methodArguments['statusCode'] = $statusCode;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('redirectToUri');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'redirectToUri', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['redirectToUri']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['redirectToUri']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param integer $statusCode The HTTP status code
     * @param string $statusMessage A custom HTTP status message
     * @param string $content Body content which further explains the status
     * @throws UnsupportedRequestTypeException If the request is not a web request
     * @throws StopActionException
     */
    protected function throwStatus($statusCode, $statusMessage = NULL, $content = NULL)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['throwStatus'])) {
            $result = parent::throwStatus($statusCode, $statusMessage, $content);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['throwStatus'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['statusCode'] = $statusCode;
                $methodArguments['statusMessage'] = $statusMessage;
                $methodArguments['content'] = $content;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('throwStatus');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'throwStatus', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['throwStatus']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['throwStatus']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     * @throws RequiredArgumentMissingException
     */
    protected function mapRequestArgumentsToControllerArguments()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['mapRequestArgumentsToControllerArguments'])) {
            $result = parent::mapRequestArgumentsToControllerArguments();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['mapRequestArgumentsToControllerArguments'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('mapRequestArgumentsToControllerArguments');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendServiceController', 'mapRequestArgumentsToControllerArguments', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['mapRequestArgumentsToControllerArguments']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['mapRequestArgumentsToControllerArguments']);
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
  'contextFactory' => 'Neos\\Neos\\Domain\\Service\\ContentContextFactory',
  'supportedMediaTypes' => 'array',
  'defaultViewObjectName' => 'string',
  'feedbackCollection' => 'Neos\\Neos\\Ui\\Domain\\Model\\FeedbackCollection',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'publishingService' => 'Neos\\Neos\\Service\\PublishingService',
  'nodeService' => 'Neos\\Neos\\Ui\\ContentRepository\\Service\\NodeService',
  'workspaceRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\WorkspaceRepository',
  'workspaceService' => 'Neos\\Neos\\Ui\\ContentRepository\\Service\\WorkspaceService',
  'userService' => 'Neos\\Neos\\Service\\UserService',
  'nodePolicyService' => 'Neos\\Neos\\Ui\\Service\\NodePolicyService',
  'clipboard' => 'Neos\\Neos\\Ui\\Service\\NodeClipboard',
  'contentDimensionsPresetSource' => 'Neos\\Neos\\Domain\\Service\\ContentDimensionPresetSourceInterface',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'reflectionService' => 'Neos\\Flow\\Reflection\\ReflectionService',
  'mvcPropertyMappingConfigurationService' => 'Neos\\Flow\\Mvc\\Controller\\MvcPropertyMappingConfigurationService',
  'viewConfigurationManager' => 'Neos\\Flow\\Mvc\\ViewConfigurationManager',
  'view' => 'Neos\\Flow\\Mvc\\View\\ViewInterface',
  'viewObjectNamePattern' => 'string',
  'viewFormatToObjectNameMap' => 'array',
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
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Neos.Ui'));
        $this->injectLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Psr\Log\LoggerInterface'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Service\ContentContextFactory', 'Neos\Neos\Domain\Service\ContentContextFactory', 'contextFactory', 'bf6447fb48e80589ca3a024bc3882005', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Service\ContentContextFactory'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Ui\Domain\Model\FeedbackCollection', 'Neos\Neos\Ui\Domain\Model\FeedbackCollection', 'feedbackCollection', '159b5a4040d2f09b39bc0359dd53a19b', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Ui\Domain\Model\FeedbackCollection'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\PublishingService', 'Neos\Neos\Service\PublishingService', 'publishingService', '790a6e9f9a23baf9242545af9512e2e0', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\PublishingService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Ui\ContentRepository\Service\NodeService', 'Neos\Neos\Ui\ContentRepository\Service\NodeService', 'nodeService', 'c1132e56328e2286433a0639d659934e', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Ui\ContentRepository\Service\NodeService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'workspaceRepository', '9cacb5dd2ad57e06d6f8c82dd5707855', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\WorkspaceRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Ui\ContentRepository\Service\WorkspaceService', 'Neos\Neos\Ui\ContentRepository\Service\WorkspaceService', 'workspaceService', '1414206d023b0ded5d402759d5c9dadd', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Ui\ContentRepository\Service\WorkspaceService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\UserService', 'Neos\Neos\Service\UserService', 'userService', '3af75a289d0337400c3d43d557f82c49', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\UserService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Ui\Service\NodePolicyService', 'Neos\Neos\Ui\Service\NodePolicyService', 'nodePolicyService', 'bc6df74a6a2a1f2dbcfee3c657d1fa48', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Ui\Service\NodePolicyService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Ui\Service\NodeClipboard', 'Neos\Neos\Ui\Service\NodeClipboard', 'clipboard', 'b7dd4cefe121ffb6816fd968fbe9d17d', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Ui\Service\NodeClipboard'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Service\ContentDimensionPresetSourceInterface', 'Neos\Neos\Domain\Service\ConfigurationContentDimensionPresetSource', 'contentDimensionsPresetSource', '96bb2f02eb23939468e8a031d3fe4c1a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Service\ContentDimensionPresetSourceInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Reflection\ReflectionService', 'Neos\Flow\Reflection\ReflectionService', 'reflectionService', '464c26aa94c66579c050985566cbfc1f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Reflection\ReflectionService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService', 'Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService', 'mvcPropertyMappingConfigurationService', '245f31ad31ca22b8c2b2255e0f65f847', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\ViewConfigurationManager', 'Neos\Flow\Mvc\ViewConfigurationManager', 'viewConfigurationManager', '40e27e95b530777b9b476762cf735a69', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\ViewConfigurationManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\SystemLoggerInterface', 'Neos\Flow\Log\Logger', 'systemLogger', '717e9de4d0309f4f47c821b9257eb5c2', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Validation\ValidatorResolver', 'Neos\Flow\Validation\ValidatorResolver', 'validatorResolver', 'e992f50de62d81bfe770d5c5f1242621', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Validation\ValidatorResolver'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\FlashMessageContainer', 'Neos\Flow\Mvc\FlashMessageContainer', 'flashMessageContainer', 'a5f5265657df54eb081324fb2ff5b8e1', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\FlashMessageContainer'); });
        $this->defaultViewImplementation = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow.mvc.view.defaultImplementation');
        $this->Flow_Injected_Properties = array (
  0 => 'settings',
  1 => 'logger',
  2 => 'contextFactory',
  3 => 'feedbackCollection',
  4 => 'persistenceManager',
  5 => 'publishingService',
  6 => 'nodeService',
  7 => 'workspaceRepository',
  8 => 'workspaceService',
  9 => 'userService',
  10 => 'nodePolicyService',
  11 => 'clipboard',
  12 => 'contentDimensionsPresetSource',
  13 => 'objectManager',
  14 => 'reflectionService',
  15 => 'mvcPropertyMappingConfigurationService',
  16 => 'viewConfigurationManager',
  17 => 'systemLogger',
  18 => 'validatorResolver',
  19 => 'flashMessageContainer',
  20 => 'defaultViewImplementation',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos.Ui/Classes/Controller/BackendServiceController.php
#