<?php 
namespace Neos\Form\ViewHelpers\Form;

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
use Neos\Flow\Utility\Algorithms;
use Neos\FluidAdaptor\ViewHelpers\Form\AbstractFormFieldViewHelper;

/**
 * Display a jQuery date picker.
 *
 * Note: Requires jQuery UI to be included on the page.
 */
class DatePickerViewHelper_Original extends AbstractFormFieldViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'input';

    /**
     * @var \Neos\Flow\Property\PropertyMapper
     * @Flow\Inject
     */
    protected $propertyMapper;

    /**
     * Initialize the arguments.
     *
     * @return void

     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerTagAttribute('size', 'int', 'The size of the input field');
        $this->registerTagAttribute('placeholder', 'string', 'Specifies a short hint that describes the expected value of an input element');
        $this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this view helper', false, 'f3-form-error');
        $this->registerArgument('initialDate', 'string', 'Initial date (@see http://www.php.net/manual/en/datetime.formats.php for supported formats)');
        $this->registerUniversalTagAttributes();
    }

    /**
     * Renders the text field, hidden field and required javascript
     *
     * @param string $dateFormat
     * @param boolean $enableDatePicker
     * @return string
     */
    public function render($dateFormat = 'Y-m-d', $enableDatePicker = true)
    {
        $name = $this->getName();
        $this->registerFieldNameForFormTokenGeneration($name);

        $this->tag->addAttribute('type', 'date');
        $this->tag->addAttribute('name', $name . '[date]');
        if ($enableDatePicker) {
            $this->tag->addAttribute('readonly', true);
        }
        $date = $this->getSelectedDate();
        if ($date !== null) {
            $this->tag->addAttribute('value', $date->format($dateFormat));
        }

        if ($this->hasArgument('id')) {
            $id = $this->arguments['id'];
        } else {
            $id = 'field' . md5(Algorithms::generateRandomString(13));
            $this->tag->addAttribute('id', $id);
        }
        $this->setErrorClassAttribute();
        $content = '';
        $content .= $this->tag->render();
        $content .= '<input type="hidden" name="' . $name . '[dateFormat]" value="' . htmlspecialchars($dateFormat) . '" />';

        if ($enableDatePicker) {
            $datePickerDateFormat = $this->convertDateFormatToDatePickerFormat($dateFormat);
            $content .= '<script type="text/javascript">//<![CDATA[
				$(function() {
					$("#' . $id . '").datepicker({
						dateFormat: "' . $datePickerDateFormat . '"
					}).keydown(function(e) {
							// By using "backspace" or "delete", you can clear the datepicker again.
						if(e.keyCode == 8 || e.keyCode == 46) {
							e.preventDefault();
							$.datepicker._clearDate(this);
						}
					});
				});
				//]]></script>';
        }
        return $content;
    }

    /**
     * @return \DateTime|null
     */
    protected function getSelectedDate()
    {
        $date = $this->getPropertyValue();
        if ($date instanceof \DateTime) {
            return $date;
        }
        if ($date !== null) {
            $date = $this->propertyMapper->convert($date, 'DateTime');
            if (!$date instanceof \DateTime) {
                return null;
            }
            return $date;
        }
        if (!$this->hasArgument('initialDate')) {
            return null;
        }
        return new \DateTime($this->arguments['initialDate']);
    }

    /**
     * @param string $dateFormat
     * @return string
     */
    protected function convertDateFormatToDatePickerFormat($dateFormat)
    {
        $replacements = array(
            'd' => 'dd',
            'D' => 'D',
            'j' => 'o',
            'l' => 'DD',

            'F' => 'MM',
            'm' => 'mm',
            'M' => 'M',
            'n' => 'm',

            'Y' => 'yy',
            'y' => 'y'
        );
        return strtr($dateFormat, $replacements);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Form\ViewHelpers\Form;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Display a jQuery date picker.
 * 
 * Note: Requires jQuery UI to be included on the page.
 */
class DatePickerViewHelper extends DatePickerViewHelper_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        parent::__construct();
        if ('Neos\Form\ViewHelpers\Form\DatePickerViewHelper' === get_class($this)) {
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
  'propertyMapper' => '\\Neos\\Flow\\Property\\PropertyMapper',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Property\PropertyMapper', 'Neos\Flow\Property\PropertyMapper', 'propertyMapper', '2ab4a1ce2ee31715713d0f207f0ac637', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Property\PropertyMapper'); });
        $this->Flow_Injected_Properties = array (
  0 => 'persistenceManager',
  1 => 'tagBuilder',
  2 => 'objectManager',
  3 => 'systemLogger',
  4 => 'logger',
  5 => 'propertyMapper',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Form/Classes/ViewHelpers/Form/DatePickerViewHelper.php
#