<?php 
namespace Neos\Flow\Security\Authentication;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Authentication\Token\SessionlessTokenInterface;
use Neos\Flow\Security\Context;
use Neos\Flow\Security\Exception\NoTokensAuthenticatedException;
use Neos\Flow\Security\Exception\AuthenticationRequiredException;
use Neos\Flow\Security\Exception;
use Neos\Flow\Session\SessionManagerInterface;

/**
 * The default authentication manager, which relies on Authentication Providers
 * to authenticate the tokens stored in the security context.
 *
 * @Flow\Scope("singleton")
 */
class AuthenticationProviderManager_Original implements AuthenticationManagerInterface
{
    /**
     * @var SessionManagerInterface
     * @Flow\Inject
     */
    protected $sessionManager;

    /**
     * @var TokenAndProviderFactoryInterface
     */
    protected $tokenAndProviderFactory;

    /**
     * The security context of the current request
     *
     * @Flow\Inject
     * @var Context
     */
    protected $securityContext;

    /**
     * Injected configuration for providers.
     * Will be null'd again after building the object instances.
     *
     * @var array|null
     */
    protected $providerConfigurations;

    /**
     * @var boolean
     */
    protected $isAuthenticated = null;

    /**
     * @var bool
     */
    protected $isInitialized = false;

    /**
     * @var string
     */
    protected $authenticationStrategy;

    /**
     * @param TokenAndProviderFactoryInterface $tokenAndProviderFacory
     */
    public function __construct(TokenAndProviderFactoryInterface $tokenAndProviderFacory)
    {
        $this->tokenAndProviderFactory = $tokenAndProviderFacory;
    }

    /**
     * Inject the settings and does a fresh build of tokens based on the injected settings
     *
     * @param array $settings The settings
     * @return void
     * @throws Exception
     */
    public function injectSettings(array $settings): void
    {
        if (isset($settings['security']['authentication']['authenticationStrategy'])) {
            $authenticationStrategyName = $settings['security']['authentication']['authenticationStrategy'];
            switch ($authenticationStrategyName) {
                case 'allTokens':
                    $this->authenticationStrategy = Context::AUTHENTICATE_ALL_TOKENS;
                    break;
                case 'oneToken':
                    $this->authenticationStrategy = Context::AUTHENTICATE_ONE_TOKEN;
                    break;
                case 'atLeastOneToken':
                    $this->authenticationStrategy = Context::AUTHENTICATE_AT_LEAST_ONE_TOKEN;
                    break;
                case 'anyToken':
                    $this->authenticationStrategy = Context::AUTHENTICATE_ANY_TOKEN;
                    break;
                default:
                    throw new Exception('Invalid setting "' . $authenticationStrategyName . '" for security.authentication.authenticationStrategy', 1291043022);
            }
        }
    }

    /**
     * Sets the security context
     *
     * @param Context $securityContext The security context of the current request
     * @return void
     * @deprecated Just get it injected
     */
    public function setSecurityContext(Context $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * Returns the security context
     *
     * @return Context $securityContext The security context of the current request
     */
    public function getSecurityContext()
    {
        return $this->securityContext;
    }

    /**
     * Returns clean tokens this manager is responsible for.
     * Note: The order of the tokens in the array is important, as the tokens will be authenticated in the given order.
     *
     * @return array Array of TokenInterface this manager is responsible for
     * @deprecated Use TokenAndProviderFactory
     * @see TokenAndProviderFactoryInterface::getTokens()
     */
    public function getTokens()
    {
        return $this->tokenAndProviderFactory->getTokens();
    }

    /**
     * Returns all configured authentication providers
     *
     * @return array Array of \Neos\Flow\Security\Authentication\AuthenticationProviderInterface
     * @deprecated Use TokenAndProviderFactory
     * @see TokenAndProviderFactoryInterface::getProviders()
     */
    public function getProviders()
    {
        return $this->tokenAndProviderFactory->getProviders();
    }

    /**
     * Tries to authenticate the tokens in the security context (in the given order)
     * with the available authentication providers, if needed.
     * If the authentication strategy is set to "allTokens", all tokens have to be authenticated.
     * If the strategy is set to "oneToken", only one token needs to be authenticated, but the
     * authentication will stop after the first authenticated token. The strategy
     * "atLeastOne" will try to authenticate at least one and as many tokens as possible.
     *
     * @return void
     * @throws Exception
     * @throws AuthenticationRequiredException
     * @throws NoTokensAuthenticatedException
     */
    public function authenticate()
    {
        $this->isAuthenticated = false;
        $anyTokenAuthenticated = false;

        if ($this->securityContext === null) {
            throw new Exception('Cannot authenticate because no security context has been set.', 1232978667);
        }

        $tokens = $this->securityContext->getAuthenticationTokens();
        if (count($tokens) === 0) {
            throw new NoTokensAuthenticatedException('The security context contained no tokens which could be authenticated.', 1258721059);
        }

        $session = $this->sessionManager->getCurrentSession();

        /** @var $token TokenInterface */
        foreach ($tokens as $token) {
            /** @var $provider AuthenticationProviderInterface */
            foreach ($this->tokenAndProviderFactory->getProviders() as $provider) {
                if ($provider->canAuthenticate($token) && $token->getAuthenticationStatus() === TokenInterface::AUTHENTICATION_NEEDED) {
                    $provider->authenticate($token);
                    if ($token->isAuthenticated()) {
                        $this->emitAuthenticatedToken($token);
                    }
                    break;
                }
            }
            if ($token->isAuthenticated()) {
                if (!$token instanceof SessionlessTokenInterface) {
                    if (!$session->isStarted()) {
                        $session->start();
                    }
                    $account = $token->getAccount();
                    if ($account !== null) {
                        $this->securityContext->withoutAuthorizationChecks(function () use ($account, $session) {
                            $session->addTag($this->securityContext->getSessionTagForAccount($account));
                        });
                    }
                }
                if ($this->authenticationStrategy === Context::AUTHENTICATE_ONE_TOKEN) {
                    $this->isAuthenticated = true;
                    $this->emitSuccessfullyAuthenticated();
                    return;
                }
                $anyTokenAuthenticated = true;
            } else {
                if ($this->authenticationStrategy === Context::AUTHENTICATE_ALL_TOKENS) {
                    throw new AuthenticationRequiredException('Could not authenticate all tokens, but authenticationStrategy was set to "all".', 1222203912);
                }
            }
        }

        if (!$anyTokenAuthenticated && $this->authenticationStrategy !== Context::AUTHENTICATE_ANY_TOKEN) {
            throw new NoTokensAuthenticatedException('Could not authenticate any token. Might be missing or wrong credentials or no authentication provider matched.', 1222204027);
        }

        $this->isAuthenticated = $anyTokenAuthenticated;
        $this->emitSuccessfullyAuthenticated();
    }

    /**
     * Checks if one or all tokens are authenticated (depending on the authentication strategy).
     *
     * Will call authenticate() if not done before.
     *
     * @return boolean
     * @throws Exception
     */
    public function isAuthenticated()
    {
        if ($this->isAuthenticated === null) {
            try {
                $this->authenticate();
            } catch (AuthenticationRequiredException $exception) {
            }
        }
        return $this->isAuthenticated;
    }

    /**
     * Logout all active authentication tokens
     *
     * @return void
     * @throws Exception
     * @throws \Neos\Flow\Session\Exception\SessionNotStartedException
     */
    public function logout()
    {
        if ($this->isAuthenticated() !== true) {
            return;
        }
        $this->isAuthenticated = null;
        $session = $this->sessionManager->getCurrentSession();

        /** @var $token TokenInterface */
        foreach ($this->securityContext->getAuthenticationTokens() as $token) {
            $token->setAuthenticationStatus(TokenInterface::NO_CREDENTIALS_GIVEN);
        }
        $this->emitLoggedOut();
        if ($session->isStarted()) {
            $session->destroy('Logout through AuthenticationProviderManager');
        }
    }

    /**
     * Signals that the specified token has been successfully authenticated.
     *
     * @param TokenInterface $token The token which has been authenticated
     * @return void
     * @Flow\Signal
     */
    protected function emitAuthenticatedToken(TokenInterface $token): void
    {
    }

    /**
     * Signals that all active authentication tokens have been invalidated.
     * Note: the session will be destroyed after this signal has been emitted.
     *
     * @return void
     * @Flow\Signal
     */
    protected function emitLoggedOut(): void
    {
    }

    /**
     * Signals that authentication commenced and at least one token was authenticated.
     *
     * @return void
     * @Flow\Signal
     */
    protected function emitSuccessfullyAuthenticated(): void
    {
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Security\Authentication;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * The default authentication manager, which relies on Authentication Providers
 * to authenticate the tokens stored in the security context.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class AuthenticationProviderManager extends AuthenticationProviderManager_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     * @param TokenAndProviderFactoryInterface $tokenAndProviderFacory
     */
    public function __construct()
    {
        $arguments = func_get_args();

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        if (get_class($this) === 'Neos\Flow\Security\Authentication\AuthenticationProviderManager') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authentication\AuthenticationProviderManager', $this);
        if (get_class($this) === 'Neos\Flow\Security\Authentication\AuthenticationProviderManager') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authentication\AuthenticationManagerInterface', $this);

        if (!array_key_exists(0, $arguments)) $arguments[0] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Authentication\TokenAndProviderFactoryInterface');
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $tokenAndProviderFacory in class ' . __CLASS__ . '. Please check your calling code and Dependency Injection configuration.', 1296143787);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Flow\Security\Authentication\AuthenticationProviderManager' === get_class($this)) {
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
            'authenticate' => array(
                'Neos\Flow\Aop\Advice\AfterAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterAdvice('Neos\Flow\Security\Aspect\LoggingAspect', 'logManagerAuthenticate', $objectManager, NULL),
                ),
            ),
            'logout' => array(
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\Security\Aspect\LoggingAspect', 'logManagerLogout', $objectManager, NULL),
                ),
            ),
            'emitAuthenticatedToken' => array(
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\SignalSlot\SignalAspect', 'forwardSignalToDispatcher', $objectManager, NULL),
                ),
            ),
            'emitLoggedOut' => array(
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\SignalSlot\SignalAspect', 'forwardSignalToDispatcher', $objectManager, NULL),
                ),
            ),
            'emitSuccessfullyAuthenticated' => array(
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
        if (get_class($this) === 'Neos\Flow\Security\Authentication\AuthenticationProviderManager') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authentication\AuthenticationProviderManager', $this);
        if (get_class($this) === 'Neos\Flow\Security\Authentication\AuthenticationProviderManager') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authentication\AuthenticationManagerInterface', $this);

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
     * @return void
     * @throws Exception
     * @throws AuthenticationRequiredException
     * @throws NoTokensAuthenticatedException
     */
    public function authenticate()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['authenticate'])) {
            $result = parent::authenticate();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['authenticate'] = true;
            try {
            
                $methodArguments = [];

        $result = NULL;
        $afterAdviceInvoked = false;
        try {

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'authenticate', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['authenticate']['Neos\Flow\Aop\Advice\AfterAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['authenticate']['Neos\Flow\Aop\Advice\AfterAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'authenticate', $methodArguments, NULL, $result);
                    $afterAdviceInvoked = true;
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {

                if (!$afterAdviceInvoked && isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['authenticate']['Neos\Flow\Aop\Advice\AfterAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['authenticate']['Neos\Flow\Aop\Advice\AfterAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'authenticate', $methodArguments, NULL, NULL, $exception);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }
                }

                throw $exception;
        }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['authenticate']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['authenticate']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     * @throws Exception
     * @throws \Neos\Flow\Session\Exception\SessionNotStartedException
     */
    public function logout()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['logout'])) {
            $result = parent::logout();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['logout'] = true;
            try {
            
                $methodArguments = [];

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'logout', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['logout']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['logout']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'logout', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['logout']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['logout']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param TokenInterface $token The token which has been authenticated
     * @return void
     * @\Neos\Flow\Annotations\Signal
     */
    protected function emitAuthenticatedToken(\Neos\Flow\Security\Authentication\TokenInterface $token) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAuthenticatedToken'])) {
            parent::emitAuthenticatedToken($token);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAuthenticatedToken'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['token'] = $token;
            
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'emitAuthenticatedToken', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAuthenticatedToken']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAuthenticatedToken']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'emitAuthenticatedToken', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAuthenticatedToken']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAuthenticatedToken']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     * @\Neos\Flow\Annotations\Signal
     */
    protected function emitLoggedOut() : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitLoggedOut'])) {
            parent::emitLoggedOut();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitLoggedOut'] = true;
            try {
            
                $methodArguments = [];

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'emitLoggedOut', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitLoggedOut']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitLoggedOut']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'emitLoggedOut', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitLoggedOut']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitLoggedOut']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     * @\Neos\Flow\Annotations\Signal
     */
    protected function emitSuccessfullyAuthenticated() : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitSuccessfullyAuthenticated'])) {
            parent::emitSuccessfullyAuthenticated();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitSuccessfullyAuthenticated'] = true;
            try {
            
                $methodArguments = [];

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'emitSuccessfullyAuthenticated', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitSuccessfullyAuthenticated']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitSuccessfullyAuthenticated']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'emitSuccessfullyAuthenticated', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitSuccessfullyAuthenticated']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitSuccessfullyAuthenticated']);
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
  'sessionManager' => 'Neos\\Flow\\Session\\SessionManagerInterface',
  'tokenAndProviderFactory' => 'Neos\\Flow\\Security\\Authentication\\TokenAndProviderFactoryInterface',
  'securityContext' => 'Neos\\Flow\\Security\\Context',
  'providerConfigurations' => 'array|null',
  'isAuthenticated' => 'boolean',
  'isInitialized' => 'boolean',
  'authenticationStrategy' => 'string',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Session\SessionManagerInterface', 'Neos\Flow\Session\SessionManager', 'sessionManager', '76e58e15e7015ece7292c22da877c6ac', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Session\SessionManagerInterface'); });
        $this->setSecurityContext(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Context'));
        $this->Flow_Injected_Properties = array (
  0 => 'settings',
  1 => 'sessionManager',
  2 => 'securityContext',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Framework/Neos.Flow/Classes/Security/Authentication/AuthenticationProviderManager.php
#