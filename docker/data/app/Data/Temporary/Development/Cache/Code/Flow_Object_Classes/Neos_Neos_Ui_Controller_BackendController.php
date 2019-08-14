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
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Flow\Mvc\Exception\UnsupportedRequestTypeException;
use Neos\Flow\Mvc\Routing\Exception\MissingActionNameException;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Flow\Session\Exception\SessionNotStartedException;
use Neos\Flow\Session\SessionInterface;
use Neos\Fusion\View\FusionView;
use Neos\Neos\Controller\Backend\MenuHelper;
use Neos\Neos\Domain\Repository\DomainRepository;
use Neos\Neos\Domain\Repository\SiteRepository;
use Neos\Neos\Domain\Service\ContentContext;
use Neos\Neos\Service\BackendRedirectionService;
use Neos\Neos\Service\UserService;
use Neos\Neos\Ui\Domain\Service\StyleAndJavascriptInclusionService;
use Neos\Neos\Ui\Service\NodeClipboard;

class BackendController_Original extends ActionController
{

    /**
     * @var string
     */
    protected $defaultViewObjectName = 'Neos\Neos\Ui\View\BackendFusionView';

    /**
     * @var FusionView
     */
    protected $view;

    /**
     * @Flow\Inject
     * @var UserService
     */
    protected $userService;

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var DomainRepository
     */
    protected $domainRepository;

    /**
     * @Flow\Inject
     * @var SiteRepository
     */
    protected $siteRepository;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var SessionInterface
     */
    protected $session;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var MenuHelper
     */
    protected $menuHelper;

    /**
     * @Flow\Inject(lazy=false)
     * @var BackendRedirectionService
     */
    protected $backendRedirectionService;

    /**
     * @Flow\Inject
     * @var StyleAndJavascriptInclusionService
     */
    protected $styleAndJavascriptInclusionService;

    /**
     * @Flow\Inject
     * @var NodeClipboard
     */
    protected $clipboard;

    public function initializeView(ViewInterface $view)
    {
        $view->setFusionPath('backend');
    }

    /**
     * Displays the backend interface
     *
     * @Flow\IgnoreValidation("$node")
     * @param NodeInterface $node The node that will be displayed on the first tab
     * @return void
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws MissingActionNameException
     * @throws \ReflectionException
     */
    public function indexAction(NodeInterface $node = null)
    {
        $user = $this->userService->getBackendUser();

        if ($user === null) {
            $this->redirectToUri($this->uriBuilder->uriFor('index', [], 'Login', 'Neos.Neos'));
        }

        if ($node === null) {
            $node = $this->findNodeToEdit();
        }

        $siteNode = $node->getContext()->getCurrentSiteNode();

        $this->view->assign('user', $user);
        $this->view->assign('documentNode', $node);
        $this->view->assign('site', $siteNode);
        $this->view->assign('clipboardNode', $this->clipboard->getNodeContextPath());
        $this->view->assign('clipboardMode', $this->clipboard->getMode());
        $this->view->assign('headScripts', $this->styleAndJavascriptInclusionService->getHeadScripts());
        $this->view->assign('headStylesheets', $this->styleAndJavascriptInclusionService->getHeadStylesheets());
        $this->view->assign('splashScreenPartial', $this->settings['splashScreen']['partial']);
        $this->view->assign('sitesForMenu', $this->menuHelper->buildSiteList($this->getControllerContext()));

        $this->view->assign('interfaceLanguage', $this->userService->getInterfaceLanguage());
    }

    /**
     * @param NodeInterface $node
     * @throws StopActionException
     */
    public function redirectToAction(NodeInterface $node)
    {
        $this->response->getHeaders()->setCacheControlDirective('no-cache');
        $this->response->getHeaders()->setCacheControlDirective('no-store');
        $this->redirect('show', 'Frontend\Node', 'Neos.Neos', ['node' => $node]);
    }

    /**
     * @return NodeInterface|null
     */
    protected function getSiteNodeForLoggedInUser()
    {
        $user = $this->userService->getBackendUser();
        if ($user === null) {
            return null;
        }

        $workspaceName = $this->userService->getPersonalWorkspaceName();
        $contentContext = $this->createContext($workspaceName);

        return $contentContext->getCurrentSiteNode();
    }

    /**
     * @return NodeInterface|null
     * @throws \ReflectionException
     */
    protected function findNodeToEdit()
    {
        $siteNode = $this->getSiteNodeForLoggedInUser();
        $reflectionMethod = new \ReflectionMethod($this->backendRedirectionService, 'getLastVisitedNode');
        $reflectionMethod->setAccessible(true);
        $node = $reflectionMethod->invoke($this->backendRedirectionService, $siteNode->getContext()->getWorkspaceName());

        if ($node === null) {
            $node = $siteNode;
        }

        return $node;
    }

    /**
     * Create a ContentContext to be used for the backend redirects.
     *
     * @param string $workspaceName
     * @return ContentContext
     */
    protected function createContext($workspaceName)
    {
        $contextProperties = [
            'workspaceName' => $workspaceName,
            'invisibleContentShown' => true,
            'inaccessibleContentShown' => true
        ];

        $currentDomain = $this->domainRepository->findOneByActiveRequest();

        if ($currentDomain !== null) {
            $contextProperties['currentSite'] = $currentDomain->getSite();
            $contextProperties['currentDomain'] = $currentDomain;
        } else {
            $contextProperties['currentSite'] = $this->siteRepository->findFirstOnline();
        }

        return $this->contextFactory->create($contextProperties);
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
class BackendController extends BackendController_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', '__construct', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
            return;
        }
        if ('Neos\Neos\Ui\Controller\BackendController' === get_class($this)) {
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
            'initializeView' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'indexAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'redirectToAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'getSiteNodeForLoggedInUser' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'findNodeToEdit' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'createContext' => array(
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
            'initializeController' => array(
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', '__clone', $methodArguments, $adviceChain);
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
     */
    public function initializeView(\Neos\Flow\Mvc\View\ViewInterface $view)
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'initializeView', $methodArguments, $adviceChain);
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
     * @param NodeInterface $node The node that will be displayed on the first tab
     * @return void
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws MissingActionNameException
     * @throws \ReflectionException
     * @\Neos\Flow\Annotations\IgnoreValidation(argumentName="node")
     */
    public function indexAction(?\Neos\ContentRepository\Domain\Model\NodeInterface $node = NULL)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction'])) {
            $result = parent::indexAction($node);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('indexAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'indexAction', $methodArguments, $adviceChain);
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
     * @param NodeInterface $node
     * @throws StopActionException
     */
    public function redirectToAction(\Neos\ContentRepository\Domain\Model\NodeInterface $node)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['redirectToAction'])) {
            $result = parent::redirectToAction($node);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['redirectToAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('redirectToAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'redirectToAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['redirectToAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['redirectToAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return NodeInterface|null
     */
    protected function getSiteNodeForLoggedInUser()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getSiteNodeForLoggedInUser'])) {
            $result = parent::getSiteNodeForLoggedInUser();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getSiteNodeForLoggedInUser'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getSiteNodeForLoggedInUser');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'getSiteNodeForLoggedInUser', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getSiteNodeForLoggedInUser']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getSiteNodeForLoggedInUser']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return NodeInterface|null
     * @throws \ReflectionException
     */
    protected function findNodeToEdit()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['findNodeToEdit'])) {
            $result = parent::findNodeToEdit();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['findNodeToEdit'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('findNodeToEdit');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'findNodeToEdit', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['findNodeToEdit']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['findNodeToEdit']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param string $workspaceName
     * @return ContentContext
     */
    protected function createContext($workspaceName)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createContext'])) {
            $result = parent::createContext($workspaceName);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['createContext'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['workspaceName'] = $workspaceName;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('createContext');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'createContext', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createContext']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createContext']);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'injectSettings', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'injectLogger', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'processRequest', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'resolveActionMethodName', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'initializeActionMethodArguments', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'getInformationNeededForInitializeActionMethodValidators', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'initializeActionMethodValidators', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'initializeAction', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'callActionMethod', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'resolveView', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'resolveViewObjectName', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'errorAction', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'handleTargetNotFoundError', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'addErrorFlashMessage', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'forwardToReferringRequest', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'getFlattenedValidationErrorMessage', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'getErrorFlashMessage', $methodArguments, $adviceChain);
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
     * @param RequestInterface $request
     * @param ResponseInterface|ActionResponse $response
     * @throws UnsupportedRequestTypeException
     */
    protected function initializeController(\Neos\Flow\Mvc\RequestInterface $request, \Neos\Flow\Mvc\ResponseInterface $response)
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'initializeController', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'getControllerContext', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'addFlashMessage', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'forward', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'forwardToRequest', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'redirect', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'redirectToRequest', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'redirectToUri', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'throwStatus', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Controller\BackendController', 'mapRequestArgumentsToControllerArguments', $methodArguments, $adviceChain);
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
  'defaultViewObjectName' => 'string',
  'view' => 'Neos\\Fusion\\View\\FusionView',
  'userService' => 'Neos\\Neos\\Service\\UserService',
  'contextFactory' => 'Neos\\ContentRepository\\Domain\\Service\\ContextFactoryInterface',
  'domainRepository' => 'Neos\\Neos\\Domain\\Repository\\DomainRepository',
  'siteRepository' => 'Neos\\Neos\\Domain\\Repository\\SiteRepository',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'session' => 'Neos\\Flow\\Session\\SessionInterface',
  'resourceManager' => 'Neos\\Flow\\ResourceManagement\\ResourceManager',
  'menuHelper' => 'Neos\\Neos\\Controller\\Backend\\MenuHelper',
  'backendRedirectionService' => 'Neos\\Neos\\Service\\BackendRedirectionService',
  'styleAndJavascriptInclusionService' => 'Neos\\Neos\\Ui\\Domain\\Service\\StyleAndJavascriptInclusionService',
  'clipboard' => 'Neos\\Neos\\Ui\\Service\\NodeClipboard',
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
  'supportedMediaTypes' => 'array',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\UserService', 'Neos\Neos\Service\UserService', 'userService', '3af75a289d0337400c3d43d557f82c49', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\UserService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\ContextFactoryInterface', 'Neos\Neos\Domain\Service\ContentContextFactory', 'contextFactory', '98dca7b1f95a25ec173662fc4e785341', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\ContextFactoryInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\DomainRepository', 'Neos\Neos\Domain\Repository\DomainRepository', 'domainRepository', '37b1b7f7b2d5d92dae299591af3b7e10', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\DomainRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\SiteRepository', 'Neos\Neos\Domain\Repository\SiteRepository', 'siteRepository', '42785f5eca4dff104f1860b84f531a9f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\SiteRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->session = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Session\SessionInterface');
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ResourceManagement\ResourceManager', 'Neos\Flow\ResourceManagement\ResourceManager', 'resourceManager', '5c4c2fb284addde18c78849a54b02875', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ResourceManagement\ResourceManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Controller\Backend\MenuHelper', 'Neos\Neos\Controller\Backend\MenuHelper', 'menuHelper', '055da50bc2046ba640c541b85d4ab58f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Controller\Backend\MenuHelper'); });
        $this->backendRedirectionService = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\BackendRedirectionService');
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Ui\Domain\Service\StyleAndJavascriptInclusionService', 'Neos\Neos\Ui\Domain\Service\StyleAndJavascriptInclusionService', 'styleAndJavascriptInclusionService', '23e517db8242373f37e3f6bb8651a074', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Ui\Domain\Service\StyleAndJavascriptInclusionService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Ui\Service\NodeClipboard', 'Neos\Neos\Ui\Service\NodeClipboard', 'clipboard', 'b7dd4cefe121ffb6816fd968fbe9d17d', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Ui\Service\NodeClipboard'); });
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
  2 => 'userService',
  3 => 'contextFactory',
  4 => 'domainRepository',
  5 => 'siteRepository',
  6 => 'persistenceManager',
  7 => 'session',
  8 => 'resourceManager',
  9 => 'menuHelper',
  10 => 'backendRedirectionService',
  11 => 'styleAndJavascriptInclusionService',
  12 => 'clipboard',
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
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos.Ui/Classes/Controller/BackendController.php
#