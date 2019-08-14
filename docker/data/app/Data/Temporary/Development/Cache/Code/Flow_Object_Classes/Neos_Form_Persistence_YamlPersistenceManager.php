<?php 
namespace Neos\Form\Persistence;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Form\Exception\PersistenceManagerException;
use Neos\Utility\Files;
use Symfony\Component\Yaml\Yaml;

/**
 * persistence identifier is some resource:// uri probably
 *
 * @Flow\Scope("singleton")
 */
class YamlPersistenceManager_Original implements FormPersistenceManagerInterface
{
    /**
     * @var string
     */
    protected $savePath;

    /**
     * @param array $settings
     */
    public function injectSettings(array $settings)
    {
        if (isset($settings['yamlPersistenceManager']['savePath'])) {
            $this->savePath = $settings['yamlPersistenceManager']['savePath'];
            if (!is_dir($this->savePath)) {
                Files::createDirectoryRecursively($this->savePath);
            }
        }
    }

    /**
     * Load the array form representation identified by $persistenceIdentifier, and return it
     *
     * @param string $persistenceIdentifier
     * @return array
     * @throws PersistenceManagerException
     */
    public function load($persistenceIdentifier)
    {
        if (!$this->exists($persistenceIdentifier)) {
            throw new PersistenceManagerException(sprintf('The form identified by "%s" could not be loaded in "%s".', $persistenceIdentifier, $this->getFormPathAndFilename($persistenceIdentifier)), 1329307034);
        }
        $formPathAndFilename = $this->getFormPathAndFilename($persistenceIdentifier);
        return Yaml::parse(file_get_contents($formPathAndFilename));
    }

    /**
     * Save the array form representation identified by $persistenceIdentifier
     *
     * @param string $persistenceIdentifier
     * @param array $formDefinition
     */
    public function save($persistenceIdentifier, array $formDefinition)
    {
        $formPathAndFilename = $this->getFormPathAndFilename($persistenceIdentifier);
        file_put_contents($formPathAndFilename, Yaml::dump($formDefinition, 99));
    }

    /**
     * Check whether a form with the specified $persistenceIdentifier exists
     *
     * @param string $persistenceIdentifier
     * @return boolean TRUE if a form with the given $persistenceIdentifier can be loaded, otherwise FALSE
     */
    public function exists($persistenceIdentifier)
    {
        return is_file($this->getFormPathAndFilename($persistenceIdentifier));
    }

    /**
     * List all form definitions which can be loaded through this form persistence
     * manager.
     *
     * Returns an associative array with each item containing the keys 'name' (the human-readable name of the form)
     * and 'persistenceIdentifier' (the unique identifier for the Form Persistence Manager e.g. the path to the saved form definition).
     *
     * @return array in the format array(array('name' => 'Form 01', 'persistenceIdentifier' => 'path1'), array( .... ))
     */
    public function listForms()
    {
        $this->assertSavePathIsValid();

        $forms = [];
        $directoryIterator = new \DirectoryIterator($this->savePath);

        foreach ($directoryIterator as $fileObject) {
            if (!$fileObject->isFile()) {
                continue;
            }
            $fileInfo = pathinfo($fileObject->getFilename());
            if (strtolower($fileInfo['extension']) !== 'yaml') {
                continue;
            }
            $persistenceIdentifier = $fileInfo['filename'];
            $form = $this->load($persistenceIdentifier);
            $forms[] = array(
                'identifier' => $form['identifier'],
                'name' => isset($form['label']) ? $form['label'] : $form['identifier'],
                'persistenceIdentifier' => $persistenceIdentifier
            );
        }
        return $forms;
    }

    /**
     * Returns the absolute path and filename of the form with the specified $persistenceIdentifier
     * Note: This (intentionally) does not check whether the file actually exists
     *
     * @param string $persistenceIdentifier
     * @return string the absolute path and filename of the form with the specified $persistenceIdentifier
     */
    protected function getFormPathAndFilename($persistenceIdentifier)
    {
        $this->assertSavePathIsValid();

        $formFileName = sprintf('%s.yaml', $persistenceIdentifier);
        return Files::concatenatePaths(array($this->savePath, $formFileName));
    }

    /**
     * Check if the save path is set and points to a directory.
     *
     * @return void
     * @throws PersistenceManagerException
     */
    protected function assertSavePathIsValid()
    {
        if ($this->savePath === null || !is_dir($this->savePath)) {
            throw new PersistenceManagerException(sprintf('The savePath "%s" is not usable.', $this->savePath), 1499347363);
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Form\Persistence;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * persistence identifier is some resource:// uri probably
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class YamlPersistenceManager extends YamlPersistenceManager_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Form\Persistence\YamlPersistenceManager') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Form\Persistence\YamlPersistenceManager', $this);
        if (get_class($this) === 'Neos\Form\Persistence\YamlPersistenceManager') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Form\Persistence\FormPersistenceManagerInterface', $this);
        if ('Neos\Form\Persistence\YamlPersistenceManager' === get_class($this)) {
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
  'savePath' => 'string',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Form\Persistence\YamlPersistenceManager') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Form\Persistence\YamlPersistenceManager', $this);
        if (get_class($this) === 'Neos\Form\Persistence\YamlPersistenceManager') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Form\Persistence\FormPersistenceManagerInterface', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Form'));
        $this->Flow_Injected_Properties = array (
  0 => 'settings',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Form/Classes/Persistence/YamlPersistenceManager.php
#