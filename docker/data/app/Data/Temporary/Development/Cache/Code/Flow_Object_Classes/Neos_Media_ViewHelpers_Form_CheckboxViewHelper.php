<?php 
namespace Neos\Media\ViewHelpers\Form;

/*
 * This file is part of the Neos.Media package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\FluidAdaptor\ViewHelpers\Form\AbstractFormFieldViewHelper;

/**
 * View Helper which creates a simple checkbox (<input type="checkbox">).
 *
 * = Examples =
 *
 * <code title="Example">
 * <neos.media:form.checkbox name="myCheckBox" value="someValue" />
 * </code>
 * <output>
 * <input type="checkbox" name="myCheckBox" value="someValue" />
 * </output>
 *
 * <code title="Preselect">
 * <neos.media:form.checkbox name="myCheckBox" value="someValue" checked="{object.value} == 5" />
 * </code>
 * <output>
 * <input type="checkbox" name="myCheckBox" value="someValue" checked="checked" />
 * (depending on $object)
 * </output>
 *
 * <code title="Bind to object property">
 * <neos.media:form.checkbox property="interests" value="Neos" />
 * </code>
 * <output>
 * <input type="checkbox" name="user[interests][]" value="Neos" checked="checked" />
 * (depending on property "interests")
 * </output>
 *
 * @api
 */
class CheckboxViewHelper_Original extends AbstractFormFieldViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'input';

    /**
     * Initialize the arguments.
     *
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerTagAttribute('disabled', 'string', 'Specifies that the input element should be disabled when the page loads');
        $this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this view helper', false, 'f3-form-error');
        $this->overrideArgument('value', 'mixed', 'Value of input tag. Required for checkboxes', true);
        $this->registerUniversalTagAttributes();
    }

    /**
     * Renders the checkbox.
     *
     * This is changed to use the actual provided value of the value attribute
     * to support selecting object values.
     *
     * @param boolean $checked Specifies that the input element should be preselected
     * @param boolean $multiple Specifies whether this checkbox belongs to a multivalue (is part of a checkbox group)
     *
     * @return string
     * @api
     */
    public function render($checked = null, $multiple = null)
    {
        $this->tag->addAttribute('type', 'checkbox');

        $nameAttribute = $this->getName();
        $valueAttribute = $this->getValueAttribute(true);
        if ($this->isObjectAccessorMode()) {
            if ($this->hasMappingErrorOccurred()) {
                $propertyValue = $this->getLastSubmittedFormData();
            } else {
                $propertyValue = $this->getPropertyValue();
            }

            if ($propertyValue instanceof \Traversable) {
                $propertyValue = iterator_to_array($propertyValue);
            }
            if (is_array($propertyValue)) {
                if ($checked === null) {
                    $checked = in_array($this->arguments['value'], $propertyValue, true);
                }
                $nameAttribute .= '[]';
            } elseif ($multiple === true) {
                $nameAttribute .= '[]';
            } elseif ($checked === null && $propertyValue !== null) {
                $checked = (boolean)$propertyValue === (boolean)$valueAttribute;
            }
        }

        $this->registerFieldNameForFormTokenGeneration($nameAttribute);
        $this->tag->addAttribute('name', $nameAttribute);
        $this->tag->addAttribute('value', $valueAttribute);
        if ($checked) {
            $this->tag->addAttribute('checked', 'checked');
        }

        $this->setErrorClassAttribute();

        $this->renderHiddenFieldForEmptyValue();
        return $this->tag->render();
    }

    /**
     * Get the name of this form element, without prefix.
     *
     * This is done to prevent the extra __identity being added for objects
     * since it leading to property mapping errors and it works without it.
     *
     * @return string name
     */
    protected function getNameWithoutPrefix()
    {
        $name = parent::getNameWithoutPrefix();
        return str_replace('[__identity]', '', $name);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Media\ViewHelpers\Form;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * View Helper which creates a simple checkbox (<input type="checkbox">).
 * 
 * = Examples =
 * 
 * <code title="Example">
 * <neos.media:form.checkbox name="myCheckBox" value="someValue" />
 * </code>
 * <output>
 * <input type="checkbox" name="myCheckBox" value="someValue" />
 * </output>
 * 
 * <code title="Preselect">
 * <neos.media:form.checkbox name="myCheckBox" value="someValue" checked="{object.value} == 5" />
 * </code>
 * <output>
 * <input type="checkbox" name="myCheckBox" value="someValue" checked="checked" />
 * (depending on $object)
 * </output>
 * 
 * <code title="Bind to object property">
 * <neos.media:form.checkbox property="interests" value="Neos" />
 * </code>
 * <output>
 * <input type="checkbox" name="user[interests][]" value="Neos" checked="checked" />
 * (depending on property "interests")
 * </output>
 */
class CheckboxViewHelper extends CheckboxViewHelper_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        parent::__construct();
        if ('Neos\Media\ViewHelpers\Form\CheckboxViewHelper' === get_class($this)) {
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
  'tagName' => 'string',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'escapeOutput' => 'boolean',
  'tag' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\TagBuilder',
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
        $this->injectPersistenceManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'));
        $this->injectTagBuilder(new \TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder());
        $this->injectObjectManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'));
        $this->injectSystemLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'));
        $this->injectLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Psr\Log\LoggerInterface'));
        $this->Flow_Injected_Properties = array (
  0 => 'persistenceManager',
  1 => 'tagBuilder',
  2 => 'objectManager',
  3 => 'systemLogger',
  4 => 'logger',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Media/Classes/ViewHelpers/Form/CheckboxViewHelper.php
#