<?php 
namespace Neos\Neos\Controller\Service;

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
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Property\TypeConverter\PersistentObjectConverter;
use Neos\FluidAdaptor\View\TemplateView;
use Neos\Neos\View\Service\WorkspaceJsonView;
use Neos\ContentRepository\Domain\Model\Workspace;
use Neos\ContentRepository\Domain\Repository\WorkspaceRepository;

/**
 * REST service for workspaces
 *
 * @Flow\Scope("singleton")
 */
class WorkspacesController_Original extends ActionController
{

    /**
     * @Flow\Inject
     * @var WorkspaceRepository
     */
    protected $workspaceRepository;

    /**
     * @Flow\Inject
     * @var \Neos\Neos\Domain\Service\UserService
     */
    protected $userService;

    /**
     * @var array
     */
    protected $viewFormatToObjectNameMap = [
        'html' => TemplateView::class,
        'json' => WorkspaceJsonView::class
    ];

    /**
     * A list of IANA media types which are supported by this controller
     *
     * @var array
     * @see http://www.iana.org/assignments/media-types/index.html
     */
    protected $supportedMediaTypes = [
        'text/html',
        'application/json'
    ];

    /**
     * Shows a list of existing workspaces
     *
     * @return string
     */
    public function indexAction()
    {
        $user = $this->userService->getCurrentUser();
        $workspacesArray = [];
        /** @var Workspace $workspace */
        foreach ($this->workspaceRepository->findAll() as $workspace) {

            // FIXME: This check should be implemented through a specialized Workspace Privilege or something similar
            if ($workspace->getOwner() !== null && $workspace->getOwner() !== $user) {
                continue;
            }

            $workspaceArray = [
                'name' => $workspace->getName(),
                'title' => $workspace->getTitle(),
                'description' => $workspace->getDescription(),
                'baseWorkspace' => $workspace->getBaseWorkspace()
            ];
            if ($user !== null) {
                $workspaceArray['readonly'] = !$this->userService->currentUserCanPublishToWorkspace($workspace);
            }
            $workspacesArray[] = $workspaceArray;
        }

        $this->view->assign('workspaces', $workspacesArray);
    }

    /**
     * Shows details of the given workspace
     *
     * @param Workspace $workspace
     * @return string
     */
    public function showAction(Workspace $workspace)
    {
        $this->view->assign('workspace', $workspace);
    }

    /**
     * Create a workspace
     *
     * @param string $workspaceName
     * @param Workspace $baseWorkspace
     * @param string $ownerAccountIdentifier
     * @return string
     */
    public function createAction($workspaceName, Workspace $baseWorkspace, $ownerAccountIdentifier = null)
    {
        $existingWorkspace = $this->workspaceRepository->findByIdentifier($workspaceName);
        if ($existingWorkspace !== null) {
            $this->throwStatus(409, 'Workspace already exists', '');
        }

        if ($ownerAccountIdentifier !== null) {
            $owner = $this->userService->getUser($ownerAccountIdentifier);
            if ($owner === null) {
                $this->throwStatus(422, 'Requested owner account does not exist', '');
            }
        } else {
            $owner = null;
        }

        $workspace = new Workspace($workspaceName, $baseWorkspace, $owner);
        $this->workspaceRepository->add($workspace);
        $this->throwStatus(201, 'Workspace created', '');
    }

    /**
     * Configure property mapping for the updateAction
     *
     * @return void
     */
    public function initializeUpdateAction()
    {
        $propertyMappingConfiguration = $this->arguments->getArgument('workspace')->getPropertyMappingConfiguration();
        $propertyMappingConfiguration->allowProperties('name', 'baseWorkspace');
        $propertyMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, true);
    }

    /**
     * Updates a workspace
     *
     * @param Workspace $workspace The updated workspace
     * @return void
     */
    public function updateAction(Workspace $workspace)
    {
        $this->workspaceRepository->update($workspace);
        $this->throwStatus(200, 'Workspace updated', '');
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Controller\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * REST service for workspaces
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class WorkspacesController extends WorkspacesController_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
        if (get_class($this) === 'Neos\Neos\Controller\Service\WorkspacesController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Controller\Service\WorkspacesController', $this);
        if ('Neos\Neos\Controller\Service\WorkspacesController' === get_class($this)) {
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
            'indexAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'showAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'createAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'updateAction' => array(
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
        if (get_class($this) === 'Neos\Neos\Controller\Service\WorkspacesController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Controller\Service\WorkspacesController', $this);

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
     * @return string
     */
    public function indexAction()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction'])) {
            $result = parent::indexAction();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('indexAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Service\WorkspacesController', 'indexAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Workspace $workspace
     * @return string
     */
    public function showAction(\Neos\ContentRepository\Domain\Model\Workspace $workspace)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['showAction'])) {
            $result = parent::showAction($workspace);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['showAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['workspace'] = $workspace;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('showAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Service\WorkspacesController', 'showAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['showAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['showAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param string $workspaceName
     * @param Workspace $baseWorkspace
     * @param string $ownerAccountIdentifier
     * @return string
     */
    public function createAction($workspaceName, \Neos\ContentRepository\Domain\Model\Workspace $baseWorkspace, $ownerAccountIdentifier = NULL)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createAction'])) {
            $result = parent::createAction($workspaceName, $baseWorkspace, $ownerAccountIdentifier);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['createAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['workspaceName'] = $workspaceName;
                $methodArguments['baseWorkspace'] = $baseWorkspace;
                $methodArguments['ownerAccountIdentifier'] = $ownerAccountIdentifier;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('createAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Service\WorkspacesController', 'createAction', $methodArguments, $adviceChain);
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
     * @param Workspace $workspace The updated workspace
     * @return void
     */
    public function updateAction(\Neos\ContentRepository\Domain\Model\Workspace $workspace)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAction'])) {
            $result = parent::updateAction($workspace);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['workspace'] = $workspace;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('updateAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Service\WorkspacesController', 'updateAction', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Service\WorkspacesController', 'errorAction', $methodArguments, $adviceChain);
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
  'workspaceRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\WorkspaceRepository',
  'userService' => '\\Neos\\Neos\\Domain\\Service\\UserService',
  'viewFormatToObjectNameMap' => 'array',
  'supportedMediaTypes' => 'array',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'reflectionService' => 'Neos\\Flow\\Reflection\\ReflectionService',
  'mvcPropertyMappingConfigurationService' => 'Neos\\Flow\\Mvc\\Controller\\MvcPropertyMappingConfigurationService',
  'viewConfigurationManager' => 'Neos\\Flow\\Mvc\\ViewConfigurationManager',
  'view' => 'Neos\\Flow\\Mvc\\View\\ViewInterface',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'workspaceRepository', '9cacb5dd2ad57e06d6f8c82dd5707855', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\WorkspaceRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Service\UserService', 'Neos\Neos\Domain\Service\UserService', 'userService', '187743c7a02891374827e34e9a230cc7', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Service\UserService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Reflection\ReflectionService', 'Neos\Flow\Reflection\ReflectionService', 'reflectionService', '464c26aa94c66579c050985566cbfc1f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Reflection\ReflectionService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService', 'Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService', 'mvcPropertyMappingConfigurationService', '245f31ad31ca22b8c2b2255e0f65f847', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\ViewConfigurationManager', 'Neos\Flow\Mvc\ViewConfigurationManager', 'viewConfigurationManager', '40e27e95b530777b9b476762cf735a69', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\ViewConfigurationManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\SystemLoggerInterface', 'Neos\Flow\Log\Logger', 'systemLogger', '717e9de4d0309f4f47c821b9257eb5c2', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Validation\ValidatorResolver', 'Neos\Flow\Validation\ValidatorResolver', 'validatorResolver', 'e992f50de62d81bfe770d5c5f1242621', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Validation\ValidatorResolver'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\FlashMessageContainer', 'Neos\Flow\Mvc\FlashMessageContainer', 'flashMessageContainer', 'a5f5265657df54eb081324fb2ff5b8e1', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\FlashMessageContainer'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->defaultViewImplementation = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow.mvc.view.defaultImplementation');
        $this->Flow_Injected_Properties = array (
  0 => 'settings',
  1 => 'logger',
  2 => 'workspaceRepository',
  3 => 'userService',
  4 => 'objectManager',
  5 => 'reflectionService',
  6 => 'mvcPropertyMappingConfigurationService',
  7 => 'viewConfigurationManager',
  8 => 'systemLogger',
  9 => 'validatorResolver',
  10 => 'flashMessageContainer',
  11 => 'persistenceManager',
  12 => 'defaultViewImplementation',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Controller/Service/WorkspacesController.php
#