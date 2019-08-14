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

/**
 * @Flow\Scope("singleton")
 */
class SetupController_Original extends \Neos\Flow\Mvc\Controller\ActionController
{
    /**
     * The authentication manager
     *
     * @var \Neos\Flow\Security\Authentication\AuthenticationManagerInterface
     * @Flow\Inject
     */
    protected $authenticationManager;

    /**
     * @var \Neos\Flow\Configuration\Source\YamlSource
     * @Flow\Inject
     */
    protected $configurationSource;

    /**
     * The settings parsed from Settings.yaml
     *
     * @var array
     */
    protected $distributionSettings;

    /**
     * Contains the current step to be executed
     *
     * @see determineCurrentStepIndex()
     *
     * @var integer
     */
    protected $currentStepIndex = 0;

    /**
     * @return void
     */
    protected function initializeAction()
    {
        $this->distributionSettings = $this->configurationSource->load(FLOW_PATH_CONFIGURATION . \Neos\Flow\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS);
    }

    /**
     * @param integer $step
     * @return void
     * @Flow\SkipCsrfProtection
     */
    public function indexAction($step = 0)
    {
        $this->currentStepIndex = $step;
        $this->checkRequestedStepIndex();
        $currentStep = $this->instantiateCurrentStep();
        $controller = $this;
        $callback = function (\Neos\Form\Core\Model\FinisherContext $finisherContext) use ($controller, $currentStep) {
            $controller->postProcessStep($finisherContext->getFormValues(), $currentStep);
        };
        $formDefinition = $currentStep->getFormDefinition($callback);
        if ($this->currentStepIndex > 0) {
            $formDefinition->setRenderingOption('previousStepUri', $this->uriBuilder->uriFor('index', ['step' => $this->currentStepIndex - 1]));
        }
        if ($currentStep->isOptional()) {
            $formDefinition->setRenderingOption('nextStepUri', $this->uriBuilder->uriFor('index', ['step' => $this->currentStepIndex + 1]));
        }
        $totalAmountOfSteps = count($this->settings['steps']);
        if ($this->currentStepIndex === $totalAmountOfSteps - 1) {
            $formDefinition->setRenderingOption('finalStep', true);
            $this->authenticationManager->logout();
        }
        $response = new \Neos\Flow\Http\Response($this->response);
        $form = $formDefinition->bind($this->request, $response);

        try {
            $renderedForm = $form->render();
        } catch (\Neos\Setup\Exception $exception) {
            $this->addFlashMessage($exception->getMessage(), 'Exception while executing setup step', \Neos\Error\Messages\Message::SEVERITY_ERROR);
            $this->redirect('index', null, null, ['step' => $this->currentStepIndex]);
        }
        $this->view->assignMultiple([
            'form' => $renderedForm,
            'totalAmountOfSteps' => $totalAmountOfSteps,
            'currentStepNumber' => $this->currentStepIndex + 1
        ]);
    }

    /**
     * @return void
     * @throws \Neos\Setup\Exception
     */
    protected function checkRequestedStepIndex()
    {
        if (!isset($this->settings['stepOrder']) || !is_array($this->settings['stepOrder'])) {
            throw new \Neos\Setup\Exception('No "stepOrder" configured, setup can\'t be invoked', 1332167136);
        }
        $stepOrder = $this->settings['stepOrder'];
        if (!array_key_exists($this->currentStepIndex, $stepOrder)) {
            // TODO instead of throwing an exception we might also quietly jump to another step
            throw new \Neos\Setup\Exception(sprintf('No setup step #%d configured, setup can\'t be invoked', $this->currentStepIndex), 1332167418);
        }
        while ($this->checkRequiredConditions($stepOrder[$this->currentStepIndex]) !== true) {
            if ($this->currentStepIndex === 0) {
                throw new \Neos\Setup\Exception('Not all requirements are met for the first setup step, aborting setup', 1332169088);
            }
            $this->addFlashMessage('Not all requirements are met for step "%s"', '', \Neos\Error\Messages\Message::SEVERITY_ERROR, [$stepOrder[$this->currentStepIndex]]);
            $this->redirect('index', null, null, ['step' => $this->currentStepIndex - 1]);
        };
    }

    /**
     * @return \Neos\Setup\Step\StepInterface
     * @throws \Neos\Setup\Exception
     */
    protected function instantiateCurrentStep()
    {
        $currentStepIdentifier = $this->settings['stepOrder'][$this->currentStepIndex];
        $currentStepConfiguration = $this->settings['steps'][$currentStepIdentifier];
        if (!isset($currentStepConfiguration['className'])) {
            throw new \Neos\Setup\Exception(sprintf('No className specified for setup step "%s", setup can\'t be invoked', $currentStepIdentifier), 1332169398);
        }
        $currentStep = new $currentStepConfiguration['className']();
        if (!$currentStep instanceof \Neos\Setup\Step\StepInterface) {
            throw new \Neos\Setup\Exception(sprintf('ClassName %s of setup step "%s" does not implement StepInterface, setup can\'t be invoked', $currentStepConfiguration['className'], $currentStepIdentifier), 1332169576);
        }
        if (isset($currentStepConfiguration['options'])) {
            $currentStep->setOptions($currentStepConfiguration['options']);
        }
        $currentStep->setDistributionSettings($this->distributionSettings);

        return $currentStep;
    }

    /**
     * @param string $stepIdentifier
     * @return boolean TRUE if all required conditions were met, otherwise FALSE
     * @throws \Neos\Setup\Exception
     */
    protected function checkRequiredConditions($stepIdentifier)
    {
        if (!isset($this->settings['steps'][$stepIdentifier]) || !is_array($this->settings['steps'][$stepIdentifier])) {
            throw new \Neos\Setup\Exception(sprintf('No configuration found for setup step "%s", setup can\'t be invoked', $stepIdentifier), 1332167685);
        }
        $stepConfiguration = $this->settings['steps'][$stepIdentifier];
        if (!isset($stepConfiguration['requiredConditions'])) {
            return true;
        }
        foreach ($stepConfiguration['requiredConditions'] as $index => $conditionConfiguration) {
            if (!isset($conditionConfiguration['className'])) {
                throw new \Neos\Setup\Exception(sprintf('No condition className specified for condition #%d in setup step "%s", setup can\'t be invoked', $index, $stepIdentifier), 1332168070);
            }
            $condition = new $conditionConfiguration['className']();
            if (!$condition instanceof \Neos\Setup\Condition\ConditionInterface) {
                throw new \Neos\Setup\Exception(sprintf('Condition #%d (%s) in setup step "%s" does not implement ConditionInterface, setup can\'t be invoked', $index, $conditionConfiguration['className'], $stepIdentifier), 1332168070);
            }
            if (isset($conditionConfiguration['options'])) {
                $condition->setOptions($conditionConfiguration['options']);
            }
            if ($condition->isMet() !== true) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $formValues
     * @param \Neos\Setup\Step\StepInterface $currentStep
     * @return void
     */
    public function postProcessStep(array $formValues, \Neos\Setup\Step\StepInterface $currentStep)
    {
        try {
            $currentStep->postProcessFormValues($formValues);
        } catch (\Neos\Setup\Exception $exception) {
            $this->addFlashMessage($exception->getMessage(), 'Exception while executing setup step', \Neos\Error\Messages\Message::SEVERITY_ERROR);
            $this->redirect('index', null, null, ['step' => $this->currentStepIndex]);
        }
        $this->redirect('index', null, null, ['step' => $this->currentStepIndex + 1]);
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
class SetupController extends SetupController_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
        if (get_class($this) === 'Neos\Setup\Controller\SetupController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Setup\Controller\SetupController', $this);
        if ('Neos\Setup\Controller\SetupController' === get_class($this)) {
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
        );
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        if (get_class($this) === 'Neos\Setup\Controller\SetupController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Setup\Controller\SetupController', $this);

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
     * @param integer $step
     * @return void
     * @\Neos\Flow\Annotations\SkipCsrfProtection
     */
    public function indexAction($step = 0)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction'])) {
            $result = parent::indexAction($step);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['step'] = $step;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('indexAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Setup\Controller\SetupController', 'indexAction', $methodArguments, $adviceChain);
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
     */
    public function __sleep()
    {
            $result = NULL;
        $this->Flow_Object_PropertiesToSerialize = array();

        $transientProperties = array (
);
        $propertyVarTags = array (
  'authenticationManager' => '\\Neos\\Flow\\Security\\Authentication\\AuthenticationManagerInterface',
  'configurationSource' => '\\Neos\\Flow\\Configuration\\Source\\YamlSource',
  'distributionSettings' => 'array',
  'currentStepIndex' => 'integer',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Configuration\Source\YamlSource', 'Neos\Flow\Configuration\Source\YamlSource', 'configurationSource', '4e81d02eaab2f307379618613fe4e33a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Configuration\Source\YamlSource'); });
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
  3 => 'configurationSource',
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
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Setup/Classes/Controller/SetupController.php
#