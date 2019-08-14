<?php 
namespace Neos\FluidAdaptor\ViewHelpers\Format;

/*
 * This file is part of the Neos.FluidAdaptor package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Cldr\Reader\NumbersReader;
use Neos\Flow\I18n\Exception as I18nException;
use Neos\Flow\I18n\Formatter\NumberFormatter;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractLocaleAwareViewHelper;
use Neos\FluidAdaptor\Core\ViewHelper\Exception\InvalidVariableException;
use Neos\FluidAdaptor\Core\ViewHelper\Exception as ViewHelperException;

/**
 * Formats a given float to a currency representation.
 *
 * = Examples =
 *
 * <code title="Defaults">
 * <f:format.currency>123.456</f:format.currency>
 * </code>
 * <output>
 * 123,46
 * </output>
 *
 * <code title="All parameters">
 * <f:format.currency currencySign="$" decimalSeparator="." thousandsSeparator="," prependCurrency="false", separateCurrency="true", decimals="2">54321</f:format.currency>
 * </code>
 * <output>
 * 54,321.00 $
 * </output>
 *
 * <code title="Inline notation">
 * {someNumber -> f:format.currency(thousandsSeparator: ',', currencySign: '€')}
 * </code>
 * <output>
 * 54,321,00 €
 * (depending on the value of {someNumber})
 * </output>
 *
 * <code title="Inline notation with current locale used">
 * {someNumber -> f:format.currency(currencySign: '€', forceLocale: true)}
 * </code>
 * <output>
 * 54.321,00 €
 * (depending on the value of {someNumber} and the current locale)
 * </output>
 *
 * <code title="Inline notation with specific locale used">
 * {someNumber -> f:format.currency(currencySign: 'EUR', forceLocale: 'de_DE')}
 * </code>
 * <output>
 * 54.321,00 EUR
 * (depending on the value of {someNumber})
 * </output>
 *
 * <code title="Inline notation with different position for the currency sign">
 * {someNumber -> f:format.currency(currencySign: '€', prependCurrency: 'true')}
 * </code>
 * <output>
 * € 54.321,00
 * (depending on the value of {someNumber})
 * </output>
 *
 * <code title="Inline notation with no space between the currency and no decimal places">
 * {someNumber -> f:format.currency(currencySign: '€', separateCurrency: 'false', decimals: '0')}
 * </code>
 * <output>
 * 54.321€
 * (depending on the value of {someNumber})
 * </output>
 *
 * Note: This ViewHelper is intended to help you with formatting numbers into monetary units.
 * Complex calculations and/or conversions should be done before the number is passed.
 *
 * Also be aware that if the ``locale`` is set, all arguments except for the currency sign (which
 * then becomes mandatory) are ignored and the CLDR (Common Locale Data Repository) is used for formatting.
 * Fore more information about localization see section ``Internationalization & Localization Framework`` in the
 * Flow documentation.
 *
 * Additionally, if ``currencyCode`` is set, rounding and decimal digits are replaced by the rules for the
 * respective currency (e.g. JPY never has decimal digits, CHF is rounded using 5 decimals.)
 *
 * @api
 */
class CurrencyViewHelper_Original extends AbstractLocaleAwareViewHelper
{
    /**
     * @Flow\Inject
     * @var NumberFormatter
     */
    protected $numberFormatter;

    /**
     * Initialize the arguments.
     *
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        $this->registerArgument('currencySign', 'string', '(optional) The currency sign, eg $ or €.', false, '');
        $this->registerArgument('decimalSeparator', 'string', '(optional) The separator for the decimal point.', false, ',');
        $this->registerArgument('thousandsSeparator', 'string', '(optional) The thousands separator.', false, '.');
        $this->registerArgument('prependCurrency', 'boolean', '(optional) Indicates if currency symbol should be placed before or after the numeric value.', false, false);
        $this->registerArgument('separateCurrency', 'boolean', '(optional) Indicates if a space character should be placed between the number and the currency sign.', false, true);
        $this->registerArgument('decimals', 'integer', '(optional) The number of decimal places.', false, 2);
        $this->registerArgument('currencyCode', 'string', '(optional) The ISO 4217 currency code of the currency to format. Used to set decimal places and rounding.', false, null);
    }

    /**
     *
     * @throws InvalidVariableException
     * @return string the formatted amount.
     * @throws ViewHelperException
     * @api
     */
    public function render()
    {
        $stringToFormat = $this->renderChildren();
        $currencySign = $this->arguments['currencySign'];
        $separateCurrency = $this->arguments['separateCurrency'];

        $useLocale = $this->getLocale();
        if ($useLocale !== null) {
            if ($currencySign === '') {
                throw new InvalidVariableException('Using the Locale requires a currencySign.', 1326378320);
            }
            try {
                $output = $this->numberFormatter->formatCurrencyNumber($stringToFormat, $useLocale, $currencySign, NumbersReader::FORMAT_LENGTH_DEFAULT, $this->arguments['currencyCode']);
            } catch (I18nException $exception) {
                throw new ViewHelperException($exception->getMessage(), 1382350428, $exception);
            }

            return $output;
        }

        $output = number_format((float)$stringToFormat, $this->arguments['decimals'], $this->arguments['decimalSeparator'], $this->arguments['thousandsSeparator']);
        if (empty($currencySign)) {
            return $output;
        }
        if ($this->arguments['prependCurrency'] === true) {
            $output = $currencySign . ($separateCurrency === true ? ' ' : '') . $output;

            return $output;
        }
        $output .= ($separateCurrency === true ? ' ' : '') . $currencySign;

        return $output;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\FluidAdaptor\ViewHelpers\Format;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Formats a given float to a currency representation.
 * 
 * = Examples =
 * 
 * <code title="Defaults">
 * <f:format.currency>123.456</f:format.currency>
 * </code>
 * <output>
 * 123,46
 * </output>
 * 
 * <code title="All parameters">
 * <f:format.currency currencySign="$" decimalSeparator="." thousandsSeparator="," prependCurrency="false", separateCurrency="true", decimals="2">54321</f:format.currency>
 * </code>
 * <output>
 * 54,321.00 $
 * </output>
 * 
 * <code title="Inline notation">
 * {someNumber -> f:format.currency(thousandsSeparator: ',', currencySign: '€')}
 * </code>
 * <output>
 * 54,321,00 €
 * (depending on the value of {someNumber})
 * </output>
 * 
 * <code title="Inline notation with current locale used">
 * {someNumber -> f:format.currency(currencySign: '€', forceLocale: true)}
 * </code>
 * <output>
 * 54.321,00 €
 * (depending on the value of {someNumber} and the current locale)
 * </output>
 * 
 * <code title="Inline notation with specific locale used">
 * {someNumber -> f:format.currency(currencySign: 'EUR', forceLocale: 'de_DE')}
 * </code>
 * <output>
 * 54.321,00 EUR
 * (depending on the value of {someNumber})
 * </output>
 * 
 * <code title="Inline notation with different position for the currency sign">
 * {someNumber -> f:format.currency(currencySign: '€', prependCurrency: 'true')}
 * </code>
 * <output>
 * € 54.321,00
 * (depending on the value of {someNumber})
 * </output>
 * 
 * <code title="Inline notation with no space between the currency and no decimal places">
 * {someNumber -> f:format.currency(currencySign: '€', separateCurrency: 'false', decimals: '0')}
 * </code>
 * <output>
 * 54.321€
 * (depending on the value of {someNumber})
 * </output>
 * 
 * Note: This ViewHelper is intended to help you with formatting numbers into monetary units.
 * Complex calculations and/or conversions should be done before the number is passed.
 * 
 * Also be aware that if the ``locale`` is set, all arguments except for the currency sign (which
 * then becomes mandatory) are ignored and the CLDR (Common Locale Data Repository) is used for formatting.
 * Fore more information about localization see section ``Internationalization & Localization Framework`` in the
 * Flow documentation.
 * 
 * Additionally, if ``currencyCode`` is set, rounding and decimal digits are replaced by the rules for the
 * respective currency (e.g. JPY never has decimal digits, CHF is rounded using 5 decimals.)
 */
class CurrencyViewHelper extends CurrencyViewHelper_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        parent::__construct();
        if ('Neos\FluidAdaptor\ViewHelpers\Format\CurrencyViewHelper' === get_class($this)) {
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
  'numberFormatter' => 'Neos\\Flow\\I18n\\Formatter\\NumberFormatter',
  'localizationService' => 'Neos\\Flow\\I18n\\Service',
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
  'escapeOutput' => 'boolean',
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
        $this->injectLocalizationService(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\Service'));
        $this->injectObjectManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'));
        $this->injectSystemLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'));
        $this->injectLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Psr\Log\LoggerInterface'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\I18n\Formatter\NumberFormatter', 'Neos\Flow\I18n\Formatter\NumberFormatter', 'numberFormatter', '1a36d77493ad57d9e710a574d1f5edd7', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\Formatter\NumberFormatter'); });
        $this->Flow_Injected_Properties = array (
  0 => 'localizationService',
  1 => 'objectManager',
  2 => 'systemLogger',
  3 => 'logger',
  4 => 'numberFormatter',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Framework/Neos.FluidAdaptor/Classes/ViewHelpers/Format/CurrencyViewHelper.php
#