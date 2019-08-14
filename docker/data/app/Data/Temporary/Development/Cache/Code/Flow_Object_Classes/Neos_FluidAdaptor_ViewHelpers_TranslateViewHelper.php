<?php 

namespace Neos\FluidAdaptor\ViewHelpers;

/*
 * This file is part of the Neos.FluidAdaptor package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\I18n\Exception\InvalidLocaleIdentifierException;
use Neos\Flow\I18n\Locale;
use Neos\Flow\I18n\Translator;
use Neos\Flow\Mvc\ActionRequest;
use Neos\FluidAdaptor\Core\ViewHelper;
use Neos\FluidAdaptor\Core\ViewHelper\Exception as ViewHelperException;

/**
 * Returns translated message using source message or key ID.
 *
 * Also replaces all placeholders with formatted versions of provided values.
 *
 * = Examples =
 *
 * <code title="Translation by id">
 * <f:translate id="user.unregistered">Unregistered User</f:translate>
 * </code>
 * <output>
 * translation of label with the id "user.unregistered" and a fallback to "Unregistered User"
 * </output>
 *
 * <code title="Inline notation">
 * {f:translate(id: 'some.label.id', value: 'fallback result')}
 * </code>
 * <output>
 * translation of label with the id "some.label.id" and a fallback to "fallback result"
 * </output>
 *
 * <code title="Custom source and locale">
 * <f:translate id="some.label.id" source="LabelsCatalog" locale="de_DE"/>
 * </code>
 * <output>
 * translation from custom source "SomeLabelsCatalog" for locale "de_DE"
 * </output>
 *
 * <code title="Custom source from other package">
 * <f:translate id="some.label.id" source="LabelsCatalog" package="OtherPackage"/>
 * </code>
 * <output>
 * translation from custom source "LabelsCatalog" in "OtherPackage"
 * </output>
 *
 * <code title="Arguments">
 * <f:translate arguments="{0: 'foo', 1: '99.9'}"><![CDATA[Untranslated {0} and {1,number}]]></f:translate>
 * </code>
 * <output>
 * translation of the label "Untranslated foo and 99.9"
 * </output>
 *
 * <code title="Translation by label">
 * <f:translate>Untranslated label</f:translate>
 * </code>
 * <output>
 * translation of the label "Untranslated label"
 * </output>
 *
 */
class TranslateViewHelper_Original extends ViewHelper\AbstractViewHelper
{
    /**
     * @var Translator
     */
    protected $translator;

    /**
     * Initialize arguments
     *
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        $this->registerArgument('id', 'string', 'Id to use for finding translation (trans-unit id in XLIFF)', false, null);
        $this->registerArgument('value', 'string', 'If $key is not specified or could not be resolved, this value is used. If this argument is not set, child nodes will be used to render the default', false, null);
        $this->registerArgument('arguments', 'array', 'Numerically indexed array of values to be inserted into placeholders', false, []);
        $this->registerArgument('source', 'string', 'Name of file with translations (use / as a directory separator)', false, 'Main');
        $this->registerArgument('package', 'string', 'Target package key. If not set, the current package key will be used', false, null);
        $this->registerArgument('quantity', 'mixed', 'A number to find plural form for (float or int), NULL to not use plural forms', false, null);
        $this->registerArgument('locale', 'string', 'An identifier of locale to use (NULL for use the default locale)', false, null);
    }


    /**
     * Renders the translated label.
     * Replaces all placeholders with corresponding values if they exist in the
     * translated label.
     *
     * @return string Translated label or source label / ID key
     * @throws ViewHelperException
     */
    public function render()
    {
        $id = $this->arguments['id'];
        $value = $this->arguments['value'];
        $arguments = $this->arguments['arguments'];
        $source = $this->arguments['source'];
        $package = $this->arguments['package'];
        $quantity  = $this->arguments['quantity'];
        $locale = $this->arguments['locale'];

        $localeObject = null;
        if ($locale !== null) {
            try {
                $localeObject = new Locale($locale);
            } catch (InvalidLocaleIdentifierException $e) {
                throw new ViewHelperException(sprintf('"%s" is not a valid locale identifier.', $locale), 1279815885);
            }
        }
        if ($package === null) {
            $request = $this->renderingContext->getControllerContext()->getRequest();
            if ($request instanceof ActionRequest) {
                $package = $request->getControllerPackageKey();
            }
            if ($package === null) {
                throw new ViewHelperException('The current package key can\'t be resolved. Make sure to initialize the Fluid view with a proper ActionRequest and/or specify the "package" argument when using the f:translate ViewHelper',
                    1416832309);
            }
        }
        $originalLabel = $value === null ? $this->renderChildren() : $value;

        if ($id === null) {
            return (string)$this->translator->translateByOriginalLabel($originalLabel, $arguments, $quantity, $localeObject, $source, $package);
        }

        $translation = $this->translator->translateById($id, $arguments, $quantity, $localeObject, $source, $package);
        if ($translation !== null) {
            return (string)$translation;
        }
        if ($originalLabel !== null) {
            return $originalLabel;
        }
        return (string)$id;
    }

    /**
     * @param Translator $translator
     */
    public function injectTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\FluidAdaptor\ViewHelpers;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Returns translated message using source message or key ID.
 * 
 * Also replaces all placeholders with formatted versions of provided values.
 * 
 * = Examples =
 * 
 * <code title="Translation by id">
 * <f:translate id="user.unregistered">Unregistered User</f:translate>
 * </code>
 * <output>
 * translation of label with the id "user.unregistered" and a fallback to "Unregistered User"
 * </output>
 * 
 * <code title="Inline notation">
 * {f:translate(id: 'some.label.id', value: 'fallback result')}
 * </code>
 * <output>
 * translation of label with the id "some.label.id" and a fallback to "fallback result"
 * </output>
 * 
 * <code title="Custom source and locale">
 * <f:translate id="some.label.id" source="LabelsCatalog" locale="de_DE"/>
 * </code>
 * <output>
 * translation from custom source "SomeLabelsCatalog" for locale "de_DE"
 * </output>
 * 
 * <code title="Custom source from other package">
 * <f:translate id="some.label.id" source="LabelsCatalog" package="OtherPackage"/>
 * </code>
 * <output>
 * translation from custom source "LabelsCatalog" in "OtherPackage"
 * </output>
 * 
 * <code title="Arguments">
 * <f:translate arguments="{0: 'foo', 1: '99.9'}"><![CDATA[Untranslated {0} and {1,number}]]></f:translate>
 * </code>
 * <output>
 * translation of the label "Untranslated foo and 99.9"
 * </output>
 * 
 * <code title="Translation by label">
 * <f:translate>Untranslated label</f:translate>
 * </code>
 * <output>
 * translation of the label "Untranslated label"
 * </output>
 */
class TranslateViewHelper extends TranslateViewHelper_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\FluidAdaptor\ViewHelpers\TranslateViewHelper' === get_class($this)) {
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
  'translator' => 'Neos\\Flow\\I18n\\Translator',
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
        $this->injectTranslator(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\Translator'));
        $this->injectObjectManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'));
        $this->injectSystemLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'));
        $this->injectLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Psr\Log\LoggerInterface'));
        $this->Flow_Injected_Properties = array (
  0 => 'translator',
  1 => 'objectManager',
  2 => 'systemLogger',
  3 => 'logger',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Framework/Neos.FluidAdaptor/Classes/ViewHelpers/TranslateViewHelper.php
#