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
use Neos\Flow\Property\PropertyMapper;
use Neos\Neos\Service\PublishingService;
use Neos\Neos\Service\View\NodeView;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\Workspace;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\ContentRepository\Domain\Repository\WorkspaceRepository;
use Neos\ContentRepository\TypeConverter\NodeConverter;

/**
 * Service Controller for managing Workspaces
 */
class WorkspaceController_Original extends AbstractServiceController
{
    /**
     * @var string
     */
    protected $defaultViewObjectName = NodeView::class;

    /**
     * @var NodeView
     */
    protected $view;

    /**
     * @Flow\Inject
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;

    /**
     * @Flow\Inject
     * @var PublishingService
     */
    protected $publishingService;

    /**
     * @Flow\Inject
     * @var WorkspaceRepository
     */
    protected $workspaceRepository;

    /**
     * @Flow\Inject
     * @var PropertyMapper
     */
    protected $propertyMapper;

    /**
     * @return void
     */
    protected function initializeAction()
    {
        if ($this->arguments->hasArgument('node')) {
            $this
                ->arguments
                ->getArgument('node')
                ->getPropertyMappingConfiguration()
                ->setTypeConverterOption(NodeConverter::class, NodeConverter::REMOVED_CONTENT_SHOWN, true);
        }

        if ($this->arguments->hasArgument('nodes')) {
            $this
                ->arguments
                ->getArgument('nodes')
                ->getPropertyMappingConfiguration()
                ->forProperty('*')
                ->setTypeConverterOption(NodeConverter::class, NodeConverter::REMOVED_CONTENT_SHOWN, true);
        }
    }

    /**
     * Publishes the given node to the specified targetWorkspace
     *
     * @param NodeInterface $node
     * @param string $targetWorkspaceName
     * @return void
     */
    public function publishNodeAction(NodeInterface $node, $targetWorkspaceName = null)
    {
        $targetWorkspace = ($targetWorkspaceName !== null) ? $this->workspaceRepository->findOneByName($targetWorkspaceName) : null;
        $this->publishingService->publishNode($node, $targetWorkspace);

        $this->throwStatus(204, 'Node published', '');
    }

    /**
     * Publishes the given nodes to the specified targetWorkspace
     *
     * @param array<\Neos\ContentRepository\Domain\Model\NodeInterface> $nodes
     * @param string $targetWorkspaceName
     * @return void
     */
    public function publishNodesAction(array $nodes, $targetWorkspaceName  = null)
    {
        $targetWorkspace = ($targetWorkspaceName !== null) ? $this->workspaceRepository->findOneByName($targetWorkspaceName) : null;
        $this->publishingService->publishNodes($nodes, $targetWorkspace);

        $this->throwStatus(204, 'Nodes published', '');
    }

    /**
     * Discards the given node
     *
     * @param NodeInterface $node
     * @return void
     */
    public function discardNodeAction(NodeInterface $node)
    {
        $this->publishingService->discardNode($node);

        $this->throwStatus(204, 'Node changes have been discarded', '');
    }

    /**
     * Discards the given nodes
     *
     * @param array<\Neos\ContentRepository\Domain\Model\NodeInterface> $nodes
     * @return void
     */
    public function discardNodesAction(array $nodes)
    {
        $this->publishingService->discardNodes($nodes);

        $this->throwStatus(204, 'Node changes have been discarded', '');
    }

    /**
     * Publish everything in the workspace with the given workspace name
     *
     * @param string $sourceWorkspaceName Name of the source workspace containing the content to publish
     * @param string $targetWorkspaceName Name of the target workspace the content should be published to
     * @return void
     */
    public function publishAllAction($sourceWorkspaceName, $targetWorkspaceName)
    {
        $sourceWorkspace = $this->workspaceRepository->findOneByName($sourceWorkspaceName);
        $targetWorkspace = $this->workspaceRepository->findOneByName($targetWorkspaceName);
        if ($sourceWorkspace === null) {
            $this->throwStatus(400, 'Invalid source workspace');
        }
        if ($targetWorkspace === null) {
            $this->throwStatus(400, 'Invalid target workspace');
        }
        $this->publishingService->publishNodes($this->publishingService->getUnpublishedNodes($sourceWorkspace), $targetWorkspace);

        $this->throwStatus(204, sprintf('All changes in workspace %s have been published to %s', $sourceWorkspaceName, $targetWorkspaceName), '');
    }

    /**
     * Get every unpublished node in the workspace with the given workspace name
     *
     * @param Workspace $workspace
     * @return void
     */
    public function getWorkspaceWideUnpublishedNodesAction($workspace)
    {
        $this->view->assignNodes($this->publishingService->getUnpublishedNodes($workspace));
    }

    /**
     * Discard everything in the workspace with the given workspace name
     *
     * @param Workspace $workspace
     * @return void
     */
    public function discardAllAction($workspace)
    {
        $this->publishingService->discardAllNodes($workspace);

        $this->throwStatus(204, 'Workspace changes have been discarded', '');
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Service\Controller;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Service Controller for managing Workspaces
 */
class WorkspaceController extends WorkspaceController_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
        if ('Neos\Neos\Service\Controller\WorkspaceController' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Neos\Service\Controller\WorkspaceController';
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
            'publishNodeAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'publishNodesAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'discardNodeAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'discardNodesAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'publishAllAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'getWorkspaceWideUnpublishedNodesAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'discardAllAction' => array(
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

        $isSameClass = get_class($this) === 'Neos\Neos\Service\Controller\WorkspaceController';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Neos\Service\Controller\WorkspaceController', $classParents) !== false && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== false;

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
     * @param NodeInterface $node
     * @param string $targetWorkspaceName
     * @return void
     */
    public function publishNodeAction(\Neos\ContentRepository\Domain\Model\NodeInterface $node, $targetWorkspaceName = NULL)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishNodeAction'])) {
            $result = parent::publishNodeAction($node, $targetWorkspaceName);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['publishNodeAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
                $methodArguments['targetWorkspaceName'] = $targetWorkspaceName;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('publishNodeAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\WorkspaceController', 'publishNodeAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishNodeAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishNodeAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param array<\Neos\ContentRepository\Domain\Model\NodeInterface> $nodes
     * @param string $targetWorkspaceName
     * @return void
     */
    public function publishNodesAction(array $nodes, $targetWorkspaceName = NULL)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishNodesAction'])) {
            $result = parent::publishNodesAction($nodes, $targetWorkspaceName);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['publishNodesAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['nodes'] = $nodes;
                $methodArguments['targetWorkspaceName'] = $targetWorkspaceName;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('publishNodesAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\WorkspaceController', 'publishNodesAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishNodesAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishNodesAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param NodeInterface $node
     * @return void
     */
    public function discardNodeAction(\Neos\ContentRepository\Domain\Model\NodeInterface $node)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['discardNodeAction'])) {
            $result = parent::discardNodeAction($node);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['discardNodeAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('discardNodeAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\WorkspaceController', 'discardNodeAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['discardNodeAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['discardNodeAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param array<\Neos\ContentRepository\Domain\Model\NodeInterface> $nodes
     * @return void
     */
    public function discardNodesAction(array $nodes)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['discardNodesAction'])) {
            $result = parent::discardNodesAction($nodes);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['discardNodesAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['nodes'] = $nodes;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('discardNodesAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\WorkspaceController', 'discardNodesAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['discardNodesAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['discardNodesAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param string $sourceWorkspaceName Name of the source workspace containing the content to publish
     * @param string $targetWorkspaceName Name of the target workspace the content should be published to
     * @return void
     */
    public function publishAllAction($sourceWorkspaceName, $targetWorkspaceName)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishAllAction'])) {
            $result = parent::publishAllAction($sourceWorkspaceName, $targetWorkspaceName);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['publishAllAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['sourceWorkspaceName'] = $sourceWorkspaceName;
                $methodArguments['targetWorkspaceName'] = $targetWorkspaceName;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('publishAllAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\WorkspaceController', 'publishAllAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishAllAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishAllAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Workspace $workspace
     * @return void
     */
    public function getWorkspaceWideUnpublishedNodesAction($workspace)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getWorkspaceWideUnpublishedNodesAction'])) {
            $result = parent::getWorkspaceWideUnpublishedNodesAction($workspace);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getWorkspaceWideUnpublishedNodesAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['workspace'] = $workspace;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getWorkspaceWideUnpublishedNodesAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\WorkspaceController', 'getWorkspaceWideUnpublishedNodesAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getWorkspaceWideUnpublishedNodesAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getWorkspaceWideUnpublishedNodesAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Workspace $workspace
     * @return void
     */
    public function discardAllAction($workspace)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['discardAllAction'])) {
            $result = parent::discardAllAction($workspace);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['discardAllAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['workspace'] = $workspace;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('discardAllAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\WorkspaceController', 'discardAllAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['discardAllAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['discardAllAction']);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\Controller\WorkspaceController', 'errorAction', $methodArguments, $adviceChain);
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
  'defaultViewObjectName' => 'string',
  'view' => 'Neos\\Neos\\Service\\View\\NodeView',
  'nodeDataRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\NodeDataRepository',
  'publishingService' => 'Neos\\Neos\\Service\\PublishingService',
  'workspaceRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\WorkspaceRepository',
  'propertyMapper' => 'Neos\\Flow\\Property\\PropertyMapper',
  'supportedMediaTypes' => 'array',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'reflectionService' => 'Neos\\Flow\\Reflection\\ReflectionService',
  'mvcPropertyMappingConfigurationService' => 'Neos\\Flow\\Mvc\\Controller\\MvcPropertyMappingConfigurationService',
  'viewConfigurationManager' => 'Neos\\Flow\\Mvc\\ViewConfigurationManager',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\NodeDataRepository', 'Neos\ContentRepository\Domain\Repository\NodeDataRepository', 'nodeDataRepository', '6d07985e92d364413ac81acd8f47b11b', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\NodeDataRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\PublishingService', 'Neos\Neos\Service\PublishingService', 'publishingService', '790a6e9f9a23baf9242545af9512e2e0', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\PublishingService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'workspaceRepository', '9cacb5dd2ad57e06d6f8c82dd5707855', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\WorkspaceRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Property\PropertyMapper', 'Neos\Flow\Property\PropertyMapper', 'propertyMapper', '2ab4a1ce2ee31715713d0f207f0ac637', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Property\PropertyMapper'); });
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
  2 => 'nodeDataRepository',
  3 => 'publishingService',
  4 => 'workspaceRepository',
  5 => 'propertyMapper',
  6 => 'objectManager',
  7 => 'reflectionService',
  8 => 'mvcPropertyMappingConfigurationService',
  9 => 'viewConfigurationManager',
  10 => 'systemLogger',
  11 => 'validatorResolver',
  12 => 'flashMessageContainer',
  13 => 'persistenceManager',
  14 => '_localizationService',
  15 => '_userService',
  16 => 'defaultViewImplementation',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Service/Controller/WorkspaceController.php
#