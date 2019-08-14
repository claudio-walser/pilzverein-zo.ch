<?php 
namespace Neos\Setup\Controller;

/*
 * This file is part of the Neos.Setup package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Error\Messages\Message;
use Neos\Utility\Files;

/**
 * @Flow\Scope("singleton")
 */
class LoginController_Original extends \Neos\Flow\Mvc\Controller\ActionController
{
    /**
     * @var string
     */
    protected $keyName;

    /**
     * The authentication manager
     *
     * @var \Neos\Flow\Security\Authentication\AuthenticationManagerInterface
     * @Flow\Inject
     */
    protected $authenticationManager;

    /**
     * @var \Neos\Flow\Security\Cryptography\FileBasedSimpleKeyService
     * @Flow\Inject
     */
    protected $fileBasedSimpleKeyService;

    /**
     * @Flow\Inject
     * @var \Neos\Flow\Configuration\ConfigurationManager
     */
    protected $configurationManager;

    /**
     * Gets the authentication provider configuration needed
     *
     * @return void
     */
    public function initializeObject()
    {
        $settings = $this->configurationManager->getConfiguration(\Neos\Flow\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'Neos.Flow');
        if (isset($settings['security']['authentication']['providers']['Neos.Setup:Login']['providerOptions']['keyName'])) {
            $this->keyName = $settings['security']['authentication']['providers']['Neos.Setup:Login']['providerOptions']['keyName'];
        }
    }

    /**
     * @param integer $step The requested setup step
     * @return void
     */
    public function loginAction($step = 0)
    {
        if ($this->fileBasedSimpleKeyService->keyExists($this->keyName) === false || file_exists($this->settings['initialPasswordFile'])) {
            $setupPassword = $this->fileBasedSimpleKeyService->generateKey($this->keyName);

            $initialPasswordFileContents = 'The setup password is:' . PHP_EOL;
            $initialPasswordFileContents .= PHP_EOL;
            $initialPasswordFileContents .= $setupPassword . PHP_EOL;
            $initialPasswordFileContents .= PHP_EOL;
            $initialPasswordFileContents .= 'After you successfully logged in, this file is automatically deleted for security reasons.' . PHP_EOL;
            $initialPasswordFileContents .= 'Make sure to save the setup password for later use.' . PHP_EOL;

            $result = file_put_contents($this->settings['initialPasswordFile'], $initialPasswordFileContents);
            if ($result === false) {
                $this->addFlashMessage('It was not possible to save the initial setup password to file "%s". Check file permissions and retry.', 'Password Generation Failure', Message::SEVERITY_ERROR, [$this->settings['initialPasswordFile']]);
            } else {
                $this->view->assign('initialPasswordFile', $this->settings['initialPasswordFile']);
            }
        }
        $this->view->assign('step', $step);
    }

    /**
     * @param integer $step The requested setup step
     * @return void
     * @Flow\SkipCsrfProtection
     */
    public function authenticateAction($step)
    {
        try {
            $this->authenticationManager->authenticate();

            if (file_exists($this->settings['initialPasswordFile'])) {
                unlink($this->settings['initialPasswordFile']);
            }
            $this->redirect('index', 'Setup', null, ['step' => $step]);
        } catch (\Neos\Flow\Security\Exception\AuthenticationRequiredException $exception) {
            $this->addFlashMessage('Sorry, you were not able to authenticate.', 'Authentication error', Message::SEVERITY_ERROR);
            $this->redirect('login', null, null, ['step' => $step]);
        }
    }

    /**
     * Removes the existing password and starts over by generating a new one.
     *
     * @param integer $step The requested setup step
     * @return void
     * @Flow\SkipCsrfProtection
     */
    public function generateNewPasswordAction($step = 0)
    {
        $existingPasswordFile = Files::concatenatePaths([FLOW_PATH_DATA, 'Persistent', 'FileBasedSimpleKeyService', $this->keyName]);
        if (file_exists($existingPasswordFile)) {
            unlink($existingPasswordFile);
            $this->addFlashMessage('A new password has been generated.', 'Password reset');
        }
        $this->redirect('login', null, null, ['step' => $step]);
    }

    /**
     * Logout all active authentication tokens.
     *
     * @return void
     */
    public function logoutAction()
    {
        $this->authenticationManager->logout();
        $this->addFlashMessage('Successfully logged out.', 'Logged out');
        $this->redirect('login');
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Setup\Controller;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 * @\Neos\Flow\Annotations\Scope("singleton")
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
        if (get_class($this) === 'Neos\Setup\Controller\LoginController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Setup\Controller\LoginController', $this);
        if ('Neos\Setup\Controller\LoginController' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Setup\Controller\LoginController';
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
            'generateNewPasswordAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'logoutAction' => array(
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
        if (get_class($this) === 'Neos\Setup\Controller\LoginController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Setup\Controller\LoginController', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();

        $isSameClass = get_class($this) === 'Neos\Setup\Controller\LoginController';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Setup\Controller\LoginController', $classParents) !== false && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== false;

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
     * @param integer $step The requested setup step
     * @return void
     */
    public function loginAction($step = 0)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['loginAction'])) {
            $result = parent::loginAction($step);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['loginAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['step'] = $step;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('loginAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Setup\Controller\LoginController', 'loginAction', $methodArguments, $adviceChain);
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
     * @param integer $step The requested setup step
     * @return void
     * @\Neos\Flow\Annotations\SkipCsrfProtection
     */
    public function authenticateAction($step)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['authenticateAction'])) {
            $result = parent::authenticateAction($step);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['authenticateAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['step'] = $step;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('authenticateAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Setup\Controller\LoginController', 'authenticateAction', $methodArguments, $adviceChain);
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
     * @param integer $step The requested setup step
     * @return void
     * @\Neos\Flow\Annotations\SkipCsrfProtection
     */
    public function generateNewPasswordAction($step = 0)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['generateNewPasswordAction'])) {
            $result = parent::generateNewPasswordAction($step);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['generateNewPasswordAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['step'] = $step;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('generateNewPasswordAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Setup\Controller\LoginController', 'generateNewPasswordAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['generateNewPasswordAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['generateNewPasswordAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Setup\Controller\LoginController', 'logoutAction', $methodArguments, $adviceChain);
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
     */
    public function __sleep()
    {
            $result = NULL;
        $this->Flow_Object_PropertiesToSerialize = array();

        $transientProperties = array (
);
        $propertyVarTags = array (
  'keyName' => 'string',
  'authenticationManager' => '\\Neos\\Flow\\Security\\Authentication\\AuthenticationManagerInterface',
  'fileBasedSimpleKeyService' => '\\Neos\\Flow\\Security\\Cryptography\\FileBasedSimpleKeyService',
  'configurationManager' => '\\Neos\\Flow\\Configuration\\ConfigurationManager',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'reflectionService' => 'Neos\\Flow\\Reflection\\ReflectionService',
  'mvcPropertyMappingConfigurationService' => 'Neos\\Flow\\Mvc\\Controller\\MvcPropertyMappingConfigurationService',
  'viewConfigurationManager' => 'Neos\\Flow\\Mvc\\ViewConfigurationManager',
  'view' => 'Neos\\Flow\\Mvc\\View\\ViewInterface',
  'viewObjectNamePattern' => 'string',
  'viewFormatToObjectNameMap' => 'array',
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
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Setup'));
        $this->injectLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Psr\Log\LoggerInterface'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Authentication\AuthenticationManagerInterface', 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'authenticationManager', '120656e0bf02d1651faed5ff8e217e9d', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Authentication\AuthenticationManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Cryptography\FileBasedSimpleKeyService', 'Neos\Flow\Security\Cryptography\FileBasedSimpleKeyService', 'fileBasedSimpleKeyService', '5f2b7ef4473302756999cf3134280575', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Cryptography\FileBasedSimpleKeyService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Configuration\ConfigurationManager', 'Neos\Flow\Configuration\ConfigurationManager', 'configurationManager', 'f559bc775c41b957515dc1c69b91d8b1', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Configuration\ConfigurationManager'); });
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
  2 => 'authenticationManager',
  3 => 'fileBasedSimpleKeyService',
  4 => 'configurationManager',
  5 => 'objectManager',
  6 => 'reflectionService',
  7 => 'mvcPropertyMappingConfigurationService',
  8 => 'viewConfigurationManager',
  9 => 'systemLogger',
  10 => 'validatorResolver',
  11 => 'flashMessageContainer',
  12 => 'persistenceManager',
  13 => 'defaultViewImplementation',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Setup/Classes/Controller/LoginController.php
#