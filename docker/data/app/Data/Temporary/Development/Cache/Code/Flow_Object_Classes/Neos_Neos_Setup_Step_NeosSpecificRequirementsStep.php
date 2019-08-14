<?php 
namespace Neos\Neos\Setup\Step;

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
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Configuration\Source\YamlSource;
use Neos\Flow\Package\PackageManagerInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Utility\Arrays;
use Neos\Utility\Files;
use Neos\Form\Core\Model\FormDefinition;
use Neos\Imagine\ImagineFactory;
use Neos\Setup\Step\AbstractStep;

/**
 * @Flow\Scope("singleton")
 */
class NeosSpecificRequirementsStep_Original extends AbstractStep
{
    /**
     * @Flow\Inject
     * @var YamlSource
     */
    protected $configurationSource;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var ImagineFactory
     */
    protected $imagineFactory;

    /**
     * @Flow\Inject
     * @var PackageManagerInterface
     */
    protected $packageManager;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * {@inheritdoc}
     */
    protected function buildForm(FormDefinition $formDefinition)
    {
        $page1 = $formDefinition->createPage('page1');
        $page1->setRenderingOption('header', 'Neos requirements check');

        $imageSection = $page1->createElement('connectionSection', 'Neos.Form:Section');
        $imageSection->setLabel('Image Manipulation');

        $formElement = $imageSection->createElement('imageLibrariesInfo', 'Neos.Form:StaticText');
        $formElement->setProperty('text', 'We checked for supported image manipulation libraries on your server.
		Only one is needed and we select the best one available for you.
		Using GD in production environment is not recommended as it has some issues and can easily lead to blank pages due to memory exhaustion.');
        $formElement->setProperty('elementClassAttribute', 'alert alert-primary');

        $foundImageHandler = false;
        foreach (['gd', 'gmagick', 'imagick'] as $extensionName) {
            $formElement = $imageSection->createElement($extensionName, 'Neos.Form:StaticText');

            if (extension_loaded($extensionName)) {
                $unsupportedFormats = $this->findUnsupportedImageFormats($extensionName);
                if (count($unsupportedFormats) === 0) {
                    $formElement->setProperty('text', 'PHP extension "' . $extensionName .'" is installed');
                    $formElement->setProperty('elementClassAttribute', 'alert alert-info');
                    $foundImageHandler = $extensionName;
                } else {
                    $formElement->setProperty('text', 'PHP extension "' . $extensionName . '" is installed but lacks support for ' . implode(', ', $unsupportedFormats));
                    $formElement->setProperty('elementClassAttribute', 'alert alert-default');
                }
            } else {
                $formElement->setProperty('text', 'PHP extension "' . $extensionName . '" is not installed');
                $formElement->setProperty('elementClassAttribute', 'alert alert-default');
            }
        }

        if ($foundImageHandler === false) {
            $formElement = $imageSection->createElement('noImageLibrary', 'Neos.Form:StaticText');
            $formElement->setProperty('text', 'No suitable PHP extension for image manipulation was found. Please install one of the required PHP extensions and restart the php process. Then proceed with the setup.');
            $formElement->setProperty('elementClassAttribute', 'alert alert-error');
        } else {
            $formElement = $imageSection->createElement('configuredImageLibrary', 'Neos.Form:StaticText');
            $formElement->setProperty('text', 'Neos will be configured to use extension "' . $foundImageHandler . '"');
            $formElement->setProperty('elementClassAttribute', 'alert alert-success');
            $hiddenField = $imageSection->createElement('imagineDriver', 'Neos.Form:HiddenField');
            $hiddenField->setDefaultValue(ucfirst($foundImageHandler));
        }
    }

    /**
     * @param string $driver
     * @return array Not supported image format
     */
    protected function findUnsupportedImageFormats($driver)
    {
        $this->imagineFactory->injectSettings(['driver' => ucfirst($driver)]);
        $imagine = $this->imagineFactory->create();
        $unsupportedFormats = [];

        foreach (['jpg', 'gif', 'png'] as $imageFormat) {
            $imagePath = Files::concatenatePaths([$this->packageManager->getPackage('Neos.Neos')->getResourcesPath(), 'Private/Installer/TestImages/Test.' . $imageFormat]);

            try {
                $imagine->open($imagePath);
            } catch (\Exception $exception) {
                $unsupportedFormats[] = sprintf('"%s"', $imageFormat);
            }
        }

        return $unsupportedFormats;
    }

    /**
     * {@inheritdoc}
     */
    public function postProcessFormValues(array $formValues)
    {
        $this->distributionSettings = Arrays::setValueByPath($this->distributionSettings, 'Neos.Imagine.driver', $formValues['imagineDriver']);
        $this->configurationSource->save(FLOW_PATH_CONFIGURATION . ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, $this->distributionSettings);

        $this->configurationManager->refreshConfiguration();
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Setup\Step;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class NeosSpecificRequirementsStep extends NeosSpecificRequirementsStep_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Neos\Setup\Step\NeosSpecificRequirementsStep') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Setup\Step\NeosSpecificRequirementsStep', $this);
        if ('Neos\Neos\Setup\Step\NeosSpecificRequirementsStep' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Neos\Setup\Step\NeosSpecificRequirementsStep';
        if ($isSameClass) {
            $this->initializeObject(1);
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
  'configurationSource' => 'Neos\\Flow\\Configuration\\Source\\YamlSource',
  'resourceManager' => 'Neos\\Flow\\ResourceManagement\\ResourceManager',
  'imagineFactory' => 'Neos\\Imagine\\ImagineFactory',
  'packageManager' => 'Neos\\Flow\\Package\\PackageManagerInterface',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'optional' => 'boolean',
  'formSettings' => 'array',
  'configurationManager' => '\\Neos\\Flow\\Configuration\\ConfigurationManager',
  'options' => 'array',
  'distributionSettings' => 'array',
  'presetName' => 'string',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Neos\Setup\Step\NeosSpecificRequirementsStep') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Setup\Step\NeosSpecificRequirementsStep', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;

        $isSameClass = get_class($this) === 'Neos\Neos\Setup\Step\NeosSpecificRequirementsStep';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Neos\Setup\Step\NeosSpecificRequirementsStep', $classParents) !== false && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== false;

        if ($isSameClass || $isClassProxy) {
            $this->initializeObject(2);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Configuration\Source\YamlSource', 'Neos\Flow\Configuration\Source\YamlSource', 'configurationSource', '4e81d02eaab2f307379618613fe4e33a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Configuration\Source\YamlSource'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ResourceManagement\ResourceManager', 'Neos\Flow\ResourceManagement\ResourceManager', 'resourceManager', '5c4c2fb284addde18c78849a54b02875', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ResourceManagement\ResourceManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Imagine\ImagineFactory', 'Neos\Imagine\ImagineFactory', 'imagineFactory', '8a13d91cac6919550522e4667a038097', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Imagine\ImagineFactory'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Package\PackageManagerInterface', 'Neos\Flow\Package\PackageManager', 'packageManager', 'b44be8eaae4695ec4f42edfbf6f8880a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Package\PackageManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Configuration\ConfigurationManager', 'Neos\Flow\Configuration\ConfigurationManager', 'configurationManager', 'f559bc775c41b957515dc1c69b91d8b1', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Configuration\ConfigurationManager'); });
        $this->Flow_Injected_Properties = array (
  0 => 'configurationSource',
  1 => 'resourceManager',
  2 => 'imagineFactory',
  3 => 'packageManager',
  4 => 'persistenceManager',
  5 => 'configurationManager',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Setup/Step/NeosSpecificRequirementsStep.php
#