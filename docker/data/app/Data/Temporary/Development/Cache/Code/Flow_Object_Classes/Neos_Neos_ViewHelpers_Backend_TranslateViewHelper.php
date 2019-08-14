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

use Neos\Flow\I18n\EelHelper\TranslationHelper;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Exception;
use Neos\FluidAdaptor\ViewHelpers\TranslateViewHelper as FluidTranslateViewHelper;
use Neos\FluidAdaptor\Core\ViewHelper;

/**
 * Returns translated message using source message or key ID.
 * uses the selected backend language
 * * Also replaces all placeholders with formatted versions of provided values.
 *
 * = Examples =
 *
 * {namespace neos=Neos\Neos\ViewHelpers}
 * <code title="Translation by id">
 * <neos:backend.translate id="user.unregistered">Unregistered User</neos:backend.translate>
 * </code>
 * <output>
 * translation of label with the id "user.unregistered" and a fallback to "Unregistered User"
 * </output>
 *
 * <code title="Inline notation">
 * {neos:backend.translate(id: 'some.label.id', value: 'fallback result')}
 * </code>
 * <output>
 * translation of label with the id "some.label.id" and a fallback to "fallback result"
 * </output>
 *
 * <code title="Custom source and locale">
 * <neos:backend.translate id="some.label.id" source="SomeLabelsCatalog" locale="de_DE"/>
 * </code>
 * <output>
 * translation from custom source "SomeLabelsCatalog" for locale "de_DE"
 * </output>
 *
 * <code title="Custom source from other package">
 * <neos:backend.translate id="some.label.id" source="LabelsCatalog" package="OtherPackage"/>
 * </code>
 * <output>
 * translation from custom source "LabelsCatalog" in "OtherPackage"
 * </output>
 *
 * <code title="Arguments">
 * <neos:backend.translate arguments="{0: 'foo', 1: '99.9'}"><![CDATA[Untranslated {0} and {1,number}]]></neos:backend.translate>
 * </code>
 * <output>
 * translation of the label "Untranslated foo and 99.9"
 * </output>
 *
 * <code title="Translation by label">
 * <neos:backend.translate>Untranslated label</neos:backend.translate>
 * </code>
 * <output>
 * translation of the label "Untranslated label"
 * </output>
 */
class TranslateViewHelper_Original extends FluidTranslateViewHelper
{
    /**
     * @Flow\Inject
     * @var \Neos\Neos\Service\UserService
     */
    protected $userService;

    /**
     * Renders the translated label.
     *
     * Replaces all placeholders with corresponding values if they exist in the
     * translated label.
     *
     * @param string $id Id to use for finding translation (trans-unit id in XLIFF)
     * @param string $value If $key is not specified or could not be resolved, this value is used. If this argument is not set, child nodes will be used to render the default
     * @param array $arguments Numerically indexed array of values to be inserted into placeholders
     * @param string $source Name of file with translations
     * @param string $package Target package key. If not set, the current package key will be used
     * @param mixed $quantity A number to find plural form for (float or int), NULL to not use plural forms
     * @param string $locale An identifier of a language to use (NULL for using the default language)
     * @return string Translated label or source label / ID key
     * @throws ViewHelper\Exception
     */
    public function render()
    {
        $id = $this->arguments['id'];
        $value = $this->arguments['value'];
        $locale = $this->arguments['locale'];

        if (preg_match(TranslationHelper::I18N_LABEL_ID_PATTERN, $id) === 1) {
            // In the longer run, this "extended ID" format should directly be resolved in the localization service
            list($package, $source, $id) = explode(':', $id, 3);
            $this->arguments['id'] = $id;
            $this->arguments['package'] = $package;
            $this->arguments['source'] = str_replace('.', '/', $source);
        }

        if ($locale === null) {
            $this->arguments['locale'] = $this->userService->getInterfaceLanguage();
        }

        // Catch exception in case the translation file doesn't exist, should be fixed in Flow 3.1
        try {
            $translation = parent::render();
            // Fallback to english label if label was not available in specific language
            if ($translation === $id && $locale !== 'en') {
                $this->arguments['locale'] = 'en';
                $translation = parent::render();
            }
            return $translation;
        } catch (Exception $exception) {
            return $value ?: $id;
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\ViewHelpers\Backend;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Returns translated message using source message or key ID.
 * uses the selected backend language
 * * Also replaces all placeholders with formatted versions of provided values.
 * 
 * = Examples =
 * 
 * {namespace neos=Neos\Neos\ViewHelpers}
 * <code title="Translation by id">
 * <neos:backend.translate id="user.unregistered">Unregistered User</neos:backend.translate>
 * </code>
 * <output>
 * translation of label with the id "user.unregistered" and a fallback to "Unregistered User"
 * </output>
 * 
 * <code title="Inline notation">
 * {neos:backend.translate(id: 'some.label.id', value: 'fallback result')}
 * </code>
 * <output>
 * translation of label with the id "some.label.id" and a fallback to "fallback result"
 * </output>
 * 
 * <code title="Custom source and locale">
 * <neos:backend.translate id="some.label.id" source="SomeLabelsCatalog" locale="de_DE"/>
 * </code>
 * <output>
 * translation from custom source "SomeLabelsCatalog" for locale "de_DE"
 * </output>
 * 
 * <code title="Custom source from other package">
 * <neos:backend.translate id="some.label.id" source="LabelsCatalog" package="OtherPackage"/>
 * </code>
 * <output>
 * translation from custom source "LabelsCatalog" in "OtherPackage"
 * </output>
 * 
 * <code title="Arguments">
 * <neos:backend.translate arguments="{0: 'foo', 1: '99.9'}"><![CDATA[Untranslated {0} and {1,number}]]></neos:backend.translate>
 * </code>
 * <output>
 * translation of the label "Untranslated foo and 99.9"
 * </output>
 * 
 * <code title="Translation by label">
 * <neos:backend.translate>Untranslated label</neos:backend.translate>
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
        if ('Neos\Neos\ViewHelpers\Backend\TranslateViewHelper' === get_class($this)) {
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
  'userService' => '\\Neos\\Neos\\Service\\UserService',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\UserService', 'Neos\Neos\Service\UserService', 'userService', '3af75a289d0337400c3d43d557f82c49', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\UserService'); });
        $this->Flow_Injected_Properties = array (
  0 => 'translator',
  1 => 'objectManager',
  2 => 'systemLogger',
  3 => 'logger',
  4 => 'userService',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/ViewHelpers/Backend/TranslateViewHelper.php
#