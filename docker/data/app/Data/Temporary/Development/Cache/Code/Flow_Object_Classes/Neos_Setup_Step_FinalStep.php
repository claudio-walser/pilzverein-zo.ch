<?php 
namespace Neos\Setup\Step;

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
class FinalStep_Original extends \Neos\Setup\Step\AbstractStep
{
    /**
     * Returns the form definitions for the step
     *
     * @param \Neos\Form\Core\Model\FormDefinition $formDefinition
     * @return void
     */
    protected function buildForm(\Neos\Form\Core\Model\FormDefinition $formDefinition)
    {
        $page1 = $formDefinition->createPage('page1');
        $page1->setRenderingOption('header', 'Setup complete');

        $title = $page1->createElement('connectionSection', 'Neos.Form:Section');
        $title->setLabel('Congratulations');

        $success = $title->createElement('success', 'Neos.Form:StaticText');
        $success->setProperty('text', 'You successfully completed the setup');
        $success->setProperty('elementClassAttribute', 'alert alert-success');

        $link = $title->createElement('link', 'Neos.Setup:LinkElement');
        $link->setLabel('Go to the homepage');
        $link->setProperty('href', '/');
        $link->setProperty('elementClassAttribute', 'btn btn-large btn-primary');

        $info = $title->createElement('info', 'Neos.Form:StaticText');
        $info->setProperty('text', 'If the homepage doesn\'t work, you might need configure routing in Configuration/Routes.yaml');
        $info->setProperty('elementClassAttribute', 'alert alert-info');

        $loggedOut = $page1->createElement('loggedOut', 'Neos.Form:StaticText');
        $loggedOut->setProperty('text', 'You have automatically been logged out for security reasons since this is the final step. Refresh the page to log in again if you missed something.');
        $loggedOut->setProperty('elementClassAttribute', 'alert alert-info');
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Setup\Step;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class FinalStep extends FinalStep_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Setup\Step\FinalStep') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Setup\Step\FinalStep', $this);
        if ('Neos\Setup\Step\FinalStep' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Setup\Step\FinalStep';
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
        if (get_class($this) === 'Neos\Setup\Step\FinalStep') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Setup\Step\FinalStep', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;

        $isSameClass = get_class($this) === 'Neos\Setup\Step\FinalStep';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Setup\Step\FinalStep', $classParents) !== false && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== false;

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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Configuration\ConfigurationManager', 'Neos\Flow\Configuration\ConfigurationManager', 'configurationManager', 'f559bc775c41b957515dc1c69b91d8b1', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Configuration\ConfigurationManager'); });
        $this->Flow_Injected_Properties = array (
  0 => 'configurationManager',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Setup/Classes/Step/FinalStep.php
#