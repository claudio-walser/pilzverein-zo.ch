<?php 
namespace Neos\Neos\Controller;

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
use Neos\Cache\Frontend\StringFrontend;
use Neos\Error\Messages\Message;
use Neos\Flow\Http\Cookie;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\View\JsonView;
use Neos\Flow\Security\Authentication\Controller\AbstractAuthenticationController;
use Neos\Flow\Security\Exception\AuthenticationRequiredException;
use Neos\Flow\Session\SessionInterface;
use Neos\Flow\Session\SessionManagerInterface;
use Neos\FluidAdaptor\View\TemplateView;
use Neos\Neos\Domain\Repository\DomainRepository;
use Neos\Neos\Domain\Repository\SiteRepository;
use Neos\Neos\Service\BackendRedirectionService;

/**
 * A controller which allows for logging into the backend
 */
class LoginController_Original extends AbstractAuthenticationController
{

    /**
     * @Flow\Inject
     * @var SessionInterface
     */
    protected $session;

    /**
     * @Flow\Inject
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @Flow\Inject
     * @var BackendRedirectionService
     */
    protected $backendRedirectionService;

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
     * @var StringFrontend
     */
    protected $loginTokenCache;

    /**
     * @Flow\InjectConfiguration(package="Neos.Flow", path="session.name")
     * @var string
     */
    protected $sessionName;

    /**
     * @var array
     */
    protected $viewFormatToObjectNameMap = [
        'html' => TemplateView::class,
        'json' => JsonView::class
    ];

    /**
     * @var array
     */
    protected $supportedMediaTypes = [
        'text/html',
        'application/json'
    ];

    /**
     * @return void
     */
    public function initializeIndexAction()
    {
        if (is_array($this->request->getInternalArgument('__authentication'))) {
            $authentication = $this->request->getInternalArgument('__authentication');
            if (isset($authentication['Neos']['Flow']['Security']['Authentication']['Token']['UsernamePassword']['username'])) {
                $this->request->setArgument('username', $authentication['Neos']['Flow']['Security']['Authentication']['Token']['UsernamePassword']['username']);
            }
        }
    }

    /**
     * Default action, displays the login screen
     *
     * @param string $username Optional: A username to pre-fill into the username field
     * @param boolean $unauthorized
     * @return void
     */
    public function indexAction($username = null, $unauthorized = false)
    {
        if ($this->securityContext->getInterceptedRequest() || $unauthorized) {
            $this->response->setStatus(401);
        }
        if ($this->authenticationManager->isAuthenticated()) {
            $this->redirect('index', 'Backend\Backend');
        }
        $currentDomain = $this->domainRepository->findOneByActiveRequest();
        $currentSite = $currentDomain !== null ? $currentDomain->getSite() : $this->siteRepository->findDefault();
        $this->view->assignMultiple([
            'styles' => array_filter($this->settings['userInterface']['backendLoginForm']['stylesheets']),
            'username' => $username,
            'site' => $currentSite
        ]);
    }

    /**
     * Logs a user in if a session identifier is available under the given token in the token cache.
     *
     * @param string $token
     * @return void
     */
    public function tokenLoginAction($token)
    {
        $newSessionId = $this->loginTokenCache->get($token);
        $this->loginTokenCache->remove($token);

        if ($newSessionId === false) {
            $this->systemLogger->log(sprintf('Token-based login failed, non-existing or expired token %s', $token), LOG_WARNING);
            $this->redirect('index');
        }

        $this->systemLogger->log(sprintf('Token-based login succeeded, token %s', $token), LOG_DEBUG);

        $newSession = $this->sessionManager->getSession($newSessionId);
        if ($newSession->canBeResumed()) {
            $newSession->resume();
        }
        if ($newSession->isStarted()) {
            $newSession->putData('lastVisitedNode', null);
        } else {
            $this->systemLogger->log(sprintf('Failed resuming or starting session %s which was referred to in the login token %s.', $newSessionId, $token), LOG_ERR);
        }

        $this->replaceSessionCookie($newSessionId);
        $this->redirect('index', 'Backend\Backend');
    }

    /**
     * Is called if authentication failed.
     *
     * @param AuthenticationRequiredException $exception The exception thrown while the authentication process
     * @return void
     */
    protected function onAuthenticationFailure(AuthenticationRequiredException $exception = null)
    {
        if ($this->view instanceof JsonView) {
            $this->view->assign('value', ['success' => false]);
        } else {
            $this->addFlashMessage('The entered username or password was wrong', 'Wrong credentials', Message::SEVERITY_ERROR, [], ($exception === null ? 1347016771 : $exception->getCode()));
        }
    }

    /**
     * Is called if authentication was successful.
     *
     * @param ActionRequest $originalRequest The request that was intercepted by the security framework, NULL if there was none
     * @return void
     */
    protected function onAuthenticationSuccess(ActionRequest $originalRequest = null)
    {
        if ($this->view instanceof JsonView) {
            $this->view->assign('value', ['success' => $this->authenticationManager->isAuthenticated(), 'csrfToken' => $this->securityContext->getCsrfProtectionToken()]);
        } else {
            if ($this->request->hasArgument('lastVisitedNode') && strlen($this->request->getArgument('lastVisitedNode')) > 0) {
                $this->session->putData('lastVisitedNode', $this->request->getArgument('lastVisitedNode'));
            }
            if ($originalRequest !== null) {
                // Redirect to the location that redirected to the login form because the user was nog logged in
                $this->redirectToRequest($originalRequest);
            }

            $this->redirect('index', 'Backend\Backend');
        }
    }

    /**
     * Logs out a - possibly - currently logged in account.
     * The possible redirection URI is queried from the redirection service
     * at first, before the actual logout takes place, and the session gets destroyed.
     *
     * @Flow\SkipCsrfProtection
     *
     * @return void
     */
    public function logoutAction()
    {
        $possibleRedirectionUri = $this->backendRedirectionService->getAfterLogoutRedirectionUri($this->request);
        parent::logoutAction();
        switch ($this->request->getFormat()) {
            case 'json':
                $this->view->assign('value', ['success' => true]);
            break;
            default:
                if ($possibleRedirectionUri !== null) {
                    $this->redirectToUri($possibleRedirectionUri);
                }
                $this->addFlashMessage('Successfully logged out', 'Logged out', Message::SEVERITY_NOTICE, [], 1318421560);
                $this->redirect('index');
        }
    }

    /**
     * Disable the default error flash message
     *
     * @return boolean
     */
    protected function getErrorFlashMessage()
    {
        return false;
    }

    /**
     * Sets the session cookie to the given identifier, overriding an existing cookie.
     *
     * @param string $sessionIdentifier
     * @return void
     */
    protected function replaceSessionCookie($sessionIdentifier)
    {
        $sessionCookie = new Cookie($this->sessionName, $sessionIdentifier);
        $this->response->setCookie($sessionCookie);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Controller;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A controller which allows for logging into the backend
 */
class LoginController extends LoginController_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
        if ('Neos\Neos\Controller\LoginController' === get_class($this)) {
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
            'tokenLoginAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'logoutAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'loginAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'authenticateAction' => array(
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
    }

    /**
     * Autogenerated Proxy Method
     * @param string $username Optional: A username to pre-fill into the username field
     * @param boolean $unauthorized
     * @return void
     */
    public function indexAction($username = NULL, $unauthorized = false)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction'])) {
            $result = parent::indexAction($username, $unauthorized);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['username'] = $username;
                $methodArguments['unauthorized'] = $unauthorized;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('indexAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\LoginController', 'indexAction', $methodArguments, $adviceChain);
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
     * @param string $token
     * @return void
     */
    public function tokenLoginAction($token)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['tokenLoginAction'])) {
            $result = parent::tokenLoginAction($token);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['tokenLoginAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['token'] = $token;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('tokenLoginAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\LoginController', 'tokenLoginAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['tokenLoginAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['tokenLoginAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     * @\Neos\Flow\Annotations\SkipCsrfProtection
     */
    public function logoutAction()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['logoutAction'])) {
            $result = parent::logoutAction();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['logoutAction'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('logoutAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\LoginController', 'logoutAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['logoutAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['logoutAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     */
    public function loginAction()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['loginAction'])) {
            $result = parent::loginAction();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['loginAction'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('loginAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\LoginController', 'loginAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['loginAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['loginAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return string
     * @\Neos\Flow\Annotations\SkipCsrfProtection
     */
    public function authenticateAction()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['authenticateAction'])) {
            $result = parent::authenticateAction();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['authenticateAction'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('authenticateAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\LoginController', 'authenticateAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['authenticateAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['authenticateAction']);
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
  'session' => 'Neos\\Flow\\Session\\SessionInterface',
  'sessionManager' => 'Neos\\Flow\\Session\\SessionManagerInterface',
  'backendRedirectionService' => 'Neos\\Neos\\Service\\BackendRedirectionService',
  'domainRepository' => 'Neos\\Neos\\Domain\\Repository\\DomainRepository',
  'siteRepository' => 'Neos\\Neos\\Domain\\Repository\\SiteRepository',
  'loginTokenCache' => 'Neos\\Cache\\Frontend\\StringFrontend',
  'sessionName' => 'string',
  'viewFormatToObjectNameMap' => 'array',
  'supportedMediaTypes' => 'array',
  'authenticationManager' => 'Neos\\Flow\\Security\\Authentication\\AuthenticationManagerInterface',
  'securityContext' => 'Neos\\Flow\\Security\\Context',
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
        $this->Flow_Proxy_LazyPropertyInjection('', '', 'loginTokenCache', 'dcc634b9ed204338a954fc0f3a2532c2', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Cache\CacheManager')->getCache('Neos_Neos_LoginTokenCache'); });
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Neos'));
        $this->injectLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Psr\Log\LoggerInterface'));
        $this->session = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Session\SessionInterface');
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Session\SessionManagerInterface', 'Neos\Flow\Session\SessionManager', 'sessionManager', '76e58e15e7015ece7292c22da877c6ac', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Session\SessionManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\BackendRedirectionService', 'Neos\Neos\Service\BackendRedirectionService', 'backendRedirectionService', '208e7172fc51b86a745ac36d62b0e81d', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\BackendRedirectionService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\DomainRepository', 'Neos\Neos\Domain\Repository\DomainRepository', 'domainRepository', '37b1b7f7b2d5d92dae299591af3b7e10', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\DomainRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\SiteRepository', 'Neos\Neos\Domain\Repository\SiteRepository', 'siteRepository', '42785f5eca4dff104f1860b84f531a9f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\SiteRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Authentication\AuthenticationManagerInterface', 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'authenticationManager', '120656e0bf02d1651faed5ff8e217e9d', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Authentication\AuthenticationManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Context', 'Neos\Flow\Security\Context', 'securityContext', 'f7e2ddeaebd191e228b8c2e4dc7f1f83', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Context'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Reflection\ReflectionService', 'Neos\Flow\Reflection\ReflectionService', 'reflectionService', '464c26aa94c66579c050985566cbfc1f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Reflection\ReflectionService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService', 'Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService', 'mvcPropertyMappingConfigurationService', '245f31ad31ca22b8c2b2255e0f65f847', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\ViewConfigurationManager', 'Neos\Flow\Mvc\ViewConfigurationManager', 'viewConfigurationManager', '40e27e95b530777b9b476762cf735a69', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\ViewConfigurationManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\SystemLoggerInterface', 'Neos\Flow\Log\Logger', 'systemLogger', '717e9de4d0309f4f47c821b9257eb5c2', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Validation\ValidatorResolver', 'Neos\Flow\Validation\ValidatorResolver', 'validatorResolver', 'e992f50de62d81bfe770d5c5f1242621', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Validation\ValidatorResolver'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\FlashMessageContainer', 'Neos\Flow\Mvc\FlashMessageContainer', 'flashMessageContainer', 'a5f5265657df54eb081324fb2ff5b8e1', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\FlashMessageContainer'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->sessionName = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow.session.name');
        $this->defaultViewImplementation = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow.mvc.view.defaultImplementation');
        $this->Flow_Injected_Properties = array (
  0 => 'loginTokenCache',
  1 => 'settings',
  2 => 'logger',
  3 => 'session',
  4 => 'sessionManager',
  5 => 'backendRedirectionService',
  6 => 'domainRepository',
  7 => 'siteRepository',
  8 => 'authenticationManager',
  9 => 'securityContext',
  10 => 'objectManager',
  11 => 'reflectionService',
  12 => 'mvcPropertyMappingConfigurationService',
  13 => 'viewConfigurationManager',
  14 => 'systemLogger',
  15 => 'validatorResolver',
  16 => 'flashMessageContainer',
  17 => 'persistenceManager',
  18 => 'sessionName',
  19 => 'defaultViewImplementation',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Controller/LoginController.php
#