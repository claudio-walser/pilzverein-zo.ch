<?php 
namespace Neos\Neos\ViewHelpers\Backend;

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
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\I18n\Service;
use Neos\Flow\Log\ThrowableStorageInterface;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Utility\ObjectAccess;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Flow\Security\Context;
use Neos\Utility\Files;
use Neos\Utility\PositionalArraySorter;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\Neos\Domain\Repository\DomainRepository;
use Neos\Neos\Utility\BackendAssetsUtility;
use Psr\Log\LoggerInterface;

/**
 * ViewHelper for the backend JavaScript configuration. Renders the required JS snippet to configure
 * the Neos backend.
 */
class JavascriptConfigurationViewHelper_Original extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @Flow\Inject
     * @var Bootstrap
     */
    protected $bootstrap;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var Service
     */
    protected $i18nService;

    /**
     * @Flow\Inject
     * @var Context
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var BackendAssetsUtility
     */
    protected $backendAssetsUtility;

    /**
     * @Flow\Inject
     * @var DomainRepository
     */
    protected $domainRepository;

    /**
     * @var ThrowableStorageInterface
     */
    private $throwableStorage;

    /**
     * @param LoggerInterface $logger
     */
    public function injectLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ThrowableStorageInterface $throwableStorage
     */
    public function injectThrowableStorage(ThrowableStorageInterface $throwableStorage)
    {
        $this->throwableStorage = $throwableStorage;
    }

    /**
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return string
     */
    public function render()
    {
        $configuration = [
            'window.T3Configuration = {};',
            'window.T3Configuration.UserInterface = ' . json_encode($this->settings['userInterface']) . ';',
            'window.T3Configuration.nodeTypes = {};',
            'window.T3Configuration.nodeTypes.groups = ' . json_encode($this->getNodeTypeGroupsSettings()) . ';',
            'window.T3Configuration.requirejs = {};',
            'window.T3Configuration.neosStaticResourcesBaseUri = ' . json_encode($this->resourceManager->getPublicPackageResourceUri('Neos.Neos', '')) . ';',
            'window.T3Configuration.requirejs.paths = ' . json_encode($this->getRequireJsPathMapping()) . ';',
            'window.T3Configuration.maximumFileUploadSize = ' . $this->renderMaximumFileUploadSize()
        ];

        $neosJavaScriptBasePath = $this->getStaticResourceWebBaseUri('resource://Neos.Neos/Public/JavaScript');

        $configuration[] = 'window.T3Configuration.neosJavascriptBasePath = ' . json_encode($neosJavaScriptBasePath) . ';';
        if ($this->backendAssetsUtility->shouldLoadMinifiedJavascript()) {
            $configuration[] = 'window.T3Configuration.neosJavascriptVersion = ' . json_encode($this->backendAssetsUtility->getJavascriptBuiltVersion()) . ';';
        }

        if ($this->bootstrap->getContext()->isDevelopment()) {
            $configuration[] = 'window.T3Configuration.DevelopmentMode = true;';
        }

        if ($activeDomain = $this->domainRepository->findOneByActiveRequest()) {
            $configuration[] = 'window.T3Configuration.site = "' . $activeDomain->getSite()->getNodeName() . '";';
        }

        return implode("\n", $configuration);
    }

    /**
     * @param string $resourcePath
     * @return string
     */
    protected function getStaticResourceWebBaseUri($resourcePath)
    {
        $localizedResourcePathData = $this->i18nService->getLocalizedFilename($resourcePath);

        $matches = [];
        try {
            if (preg_match('#resource://([^/]+)/Public/(.*)#', current($localizedResourcePathData), $matches) === 1) {
                $packageKey = $matches[1];
                $path = $matches[2];
                return $this->resourceManager->getPublicPackageResourceUri($packageKey, $path);
            }
        } catch (\Exception $exception) {
            $logMessage = $this->throwableStorage->logThrowable($exception);
            $this->logger->error($logMessage, LogEnvironment::fromMethodName(__METHOD__));
        }
        return '';
    }

    /**
     * @return array
     */
    protected function getRequireJsPathMapping()
    {
        $pathMappings = [];

        $validatorSettings = ObjectAccess::getPropertyPath($this->settings, 'userInterface.validators');
        if (is_array($validatorSettings)) {
            foreach ($validatorSettings as $validatorName => $validatorConfiguration) {
                if (isset($validatorConfiguration['path'])) {
                    $pathMappings[$validatorName] = $this->getStaticResourceWebBaseUri($validatorConfiguration['path']);
                }
            }
        }

        $editorSettings = ObjectAccess::getPropertyPath($this->settings, 'userInterface.inspector.editors');
        if (is_array($editorSettings)) {
            foreach ($editorSettings as $editorName => $editorConfiguration) {
                if (isset($editorConfiguration['path'])) {
                    $pathMappings[$editorName] = $this->getStaticResourceWebBaseUri($editorConfiguration['path']);
                }
            }
        }

        $requireJsPathMappingSettings = ObjectAccess::getPropertyPath($this->settings, 'userInterface.requireJsPathMapping');
        if (is_array($requireJsPathMappingSettings)) {
            foreach ($requireJsPathMappingSettings as $namespace => $path) {
                $pathMappings[$namespace] = $this->getStaticResourceWebBaseUri($path);
            }
        }

        return $pathMappings;
    }

    /**
     * @return array
     */
    protected function getNodeTypeGroupsSettings()
    {
        $settings = [];
        $nodeTypeGroupsSettings = new PositionalArraySorter($this->settings['nodeTypes']['groups']);
        foreach ($nodeTypeGroupsSettings->toArray() as $nodeTypeGroupName => $nodeTypeGroupSettings) {
            if (!isset($nodeTypeGroupSettings['label'])) {
                continue;
            }
            $settings[] = [
                'name' => $nodeTypeGroupName,
                'label' => $nodeTypeGroupSettings['label'],
                'collapsed' => isset($nodeTypeGroupSettings['collapsed']) ? $nodeTypeGroupSettings['collapsed'] : true
            ];
        }

        return $settings;
    }

    /**
     * Returns the lowest configured maximum upload file size
     *
     * @return string
     */
    protected function renderMaximumFileUploadSize()
    {
        $maximumFileUploadSizeInBytes = min(Files::sizeStringToBytes(ini_get('post_max_size')), Files::sizeStringToBytes(ini_get('upload_max_filesize')));
        return sprintf('"%d"; // %s, as configured in php.ini', $maximumFileUploadSizeInBytes, Files::bytesToSizeString($maximumFileUploadSizeInBytes));
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\ViewHelpers\Backend;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * ViewHelper for the backend JavaScript configuration. Renders the required JS snippet to configure
 * the Neos backend.
 */
class JavascriptConfigurationViewHelper extends JavascriptConfigurationViewHelper_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\Neos\ViewHelpers\Backend\JavascriptConfigurationViewHelper' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
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
  'escapeOutput' => 'boolean',
  'settings' => 'array',
  'bootstrap' => 'Neos\\Flow\\Core\\Bootstrap',
  'resourceManager' => 'Neos\\Flow\\ResourceManagement\\ResourceManager',
  'i18nService' => 'Neos\\Flow\\I18n\\Service',
  'securityContext' => 'Neos\\Flow\\Security\\Context',
  'backendAssetsUtility' => 'Neos\\Neos\\Utility\\BackendAssetsUtility',
  'domainRepository' => 'Neos\\Neos\\Domain\\Repository\\DomainRepository',
  'throwableStorage' => 'Neos\\Flow\\Log\\ThrowableStorageInterface',
  'controllerContext' => 'Neos\\Flow\\Mvc\\Controller\\ControllerContext',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'systemLogger' => 'Neos\\Flow\\Log\\SystemLoggerInterface',
  'logger' => 'Psr\\Log\\LoggerInterface',
  'argumentDefinitions' => 'array<TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ArgumentDefinition>',
  'viewHelperNode' => 'TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\ViewHelperNode',
  'arguments' => 'array',
  'childNodes' => 'NodeInterface[] array',
  'templateVariableContainer' => 'TYPO3Fluid\\Fluid\\Core\\Variables\\VariableProviderInterface',
  'renderingContext' => 'TYPO3Fluid\\Fluid\\Core\\Rendering\\RenderingContextInterface',
  'renderChildrenClosure' => '\\Closure',
  'viewHelperVariableContainer' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperVariableContainer',
  'escapeChildren' => 'boolean',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Psr\Log\LoggerInterface'));
        $this->injectThrowableStorage(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\ThrowableStorageInterface'));
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Neos'));
        $this->injectObjectManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'));
        $this->injectSystemLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Core\Bootstrap', 'Neos\Flow\Core\Bootstrap', 'bootstrap', 'aed14e789673142988a77dfdf496f415', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Core\Bootstrap'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ResourceManagement\ResourceManager', 'Neos\Flow\ResourceManagement\ResourceManager', 'resourceManager', '5c4c2fb284addde18c78849a54b02875', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ResourceManagement\ResourceManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\I18n\Service', 'Neos\Flow\I18n\Service', 'i18nService', 'bdcad09aa1b6973b35f2987887987892', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\Service'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Context', 'Neos\Flow\Security\Context', 'securityContext', 'f7e2ddeaebd191e228b8c2e4dc7f1f83', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Context'); });
        $this->backendAssetsUtility = new \Neos\Neos\Utility\BackendAssetsUtility();
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\DomainRepository', 'Neos\Neos\Domain\Repository\DomainRepository', 'domainRepository', '37b1b7f7b2d5d92dae299591af3b7e10', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\DomainRepository'); });
        $this->Flow_Injected_Properties = array (
  0 => 'logger',
  1 => 'throwableStorage',
  2 => 'settings',
  3 => 'objectManager',
  4 => 'systemLogger',
  5 => 'bootstrap',
  6 => 'resourceManager',
  7 => 'i18nService',
  8 => 'securityContext',
  9 => 'backendAssetsUtility',
  10 => 'domainRepository',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/ViewHelpers/Backend/JavascriptConfigurationViewHelper.php
#