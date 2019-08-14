<?php 
class Backend_action_index_42f410dbc0346ff2a4be9da1c68eda9d11c314b9 extends \TYPO3Fluid\Fluid\Core\Compiler\AbstractCompiledTemplate {

public function getLayoutName(\TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext) {
$self = $this; 
return (string) '';
}
public function hasLayout() {
return FALSE;
}
public function addCompiledNamespaces(\TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext) {
$renderingContext->getViewHelperResolver()->addNamespaces(array (
  'f' => 
  array (
    0 => 'TYPO3Fluid\\Fluid\\ViewHelpers',
    1 => 'Neos\\FluidAdaptor\\ViewHelpers',
  ),
  'psr.simplecache' => 
  array (
    0 => 'Psr\\SimpleCache\\ViewHelpers',
  ),
  'psr.cache' => 
  array (
    0 => 'Psr\\Cache\\ViewHelpers',
  ),
  'neos.errormessages' => 
  array (
    0 => 'Neos\\Error\\Messages\\ViewHelpers',
  ),
  'neos.utility.files' => 
  array (
    0 => 'Neos\\Utility\\ViewHelpers',
  ),
  'neos.utility.pdo' => 
  array (
    0 => 'Neos\\Utility\\ViewHelpers',
  ),
  'neos.utility.opcodecache' => 
  array (
    0 => 'Neos\\Utility\\ViewHelpers',
  ),
  'neos.cache' => 
  array (
    0 => 'Neos\\Cache\\ViewHelpers',
  ),
  'neos.utilityunicode' => 
  array (
    0 => 'Neos\\Utility\\Unicode\\ViewHelpers',
  ),
  'neos.utility.objecthandling' => 
  array (
    0 => 'Neos\\Utility\\ViewHelpers',
  ),
  'neos.utility.arrays' => 
  array (
    0 => 'Neos\\Utility\\ViewHelpers',
  ),
  'neos.utility.mediatypes' => 
  array (
    0 => 'Neos\\Utility\\ViewHelpers',
  ),
  'neos.utility.schema' => 
  array (
    0 => 'Neos\\Utility\\ViewHelpers',
  ),
  'typo3fluid.fluid' => 
  array (
    0 => 'TYPO3Fluid\\Fluid\\ViewHelpers',
  ),
  'psr.log' => 
  array (
    0 => 'Psr\\Log\\ViewHelpers',
  ),
  'neos.flow.log' => 
  array (
    0 => 'Neos\\Flow\\Log\\ViewHelpers',
  ),
  'psr.httpmessage' => 
  array (
    0 => 'Psr\\Http\\Message\\ViewHelpers',
  ),
  'psr.container' => 
  array (
    0 => 'Psr\\Container\\ViewHelpers',
  ),
  'symfony.polyfillctype' => 
  array (
    0 => 'Symfony\\Polyfill\\Ctype\\ViewHelpers',
  ),
  'ramsey.uuid' => 
  array (
    0 => 'Ramsey\\Uuid\\ViewHelpers',
  ),
  'doctrine.lexer' => 
  array (
    0 => 'Doctrine\\Common\\Lexer\\ViewHelpers',
  ),
  'doctrine.annotations' => 
  array (
    0 => 'Doctrine\\Common\\Annotations\\ViewHelpers',
  ),
  'doctrine.cache' => 
  array (
    0 => 'Doctrine\\Common\\Cache\\ViewHelpers',
  ),
  'doctrine.collections' => 
  array (
    0 => 'Doctrine\\Common\\Collections\\ViewHelpers',
  ),
  'doctrine.inflector' => 
  array (
    0 => 'Doctrine\\Common\\Inflector\\ViewHelpers',
  ),
  'doctrine.eventmanager' => 
  array (
    0 => 'Doctrine\\Common\\ViewHelpers',
  ),
  'doctrine.reflection' => 
  array (
    0 => 'Doctrine\\Common\\ViewHelpers',
  ),
  'doctrine.persistence' => 
  array (
    0 => 'Doctrine\\Common\\ViewHelpers',
  ),
  'doctrine.common' => 
  array (
    0 => 'Doctrine\\Common\\ViewHelpers',
  ),
  'doctrine.dbal' => 
  array (
    0 => 'Doctrine\\DBAL\\ViewHelpers',
  ),
  'doctrine.instantiator' => 
  array (
    0 => 'Doctrine\\Instantiator\\ViewHelpers',
  ),
  'symfony.polyfillmbstring' => 
  array (
    0 => 'Symfony\\Polyfill\\Mbstring\\ViewHelpers',
  ),
  'symfony.polyfillphp73' => 
  array (
    0 => 'Symfony\\Polyfill\\Php73\\ViewHelpers',
  ),
  'symfony.servicecontracts' => 
  array (
    0 => 'Symfony\\Contracts\\Service\\ViewHelpers',
  ),
  'symfony.console' => 
  array (
    0 => 'Symfony\\Component\\Console\\ViewHelpers',
  ),
  'doctrine.orm' => 
  array (
    0 => 'Doctrine\\ORM\\ViewHelpers',
  ),
  'zendframework.zendeventmanager' => 
  array (
    0 => 'Zend\\EventManager\\ViewHelpers',
  ),
  'zendframework.zendcode' => 
  array (
    0 => 'Zend\\Code\\ViewHelpers',
  ),
  'ocramius.packageversions' => 
  array (
    0 => 'PackageVersions\\ViewHelpers',
  ),
  'ocramius.proxymanager' => 
  array (
    0 => 'ProxyManager\\ViewHelpers',
  ),
  'doctrine.migrations' => 
  array (
    0 => 'Doctrine\\DBAL\\Migrations\\ViewHelpers',
    1 => 'Doctrine\\Migrations\\ViewHelpers',
  ),
  'symfony.yaml' => 
  array (
    0 => 'Symfony\\Component\\Yaml\\ViewHelpers',
  ),
  'symfony.domcrawler' => 
  array (
    0 => 'Symfony\\Component\\DomCrawler\\ViewHelpers',
  ),
  'neos.composerplugin' => 
  array (
    0 => 'Neos\\ComposerPlugin\\ViewHelpers',
  ),
  'neos.diff' => 
  array (
    0 => 'Neos\\Diff\\ViewHelpers',
  ),
  'imagine.imagine' => 
  array (
    0 => 'Imagine\\ViewHelpers',
  ),
  'behat.transliterator' => 
  array (
    0 => 'Behat\\Transliterator\\ViewHelpers',
  ),
  'gedmo.doctrineextensions' => 
  array (
    0 => 'Gedmo\\ViewHelpers',
  ),
  'neos.eel' => 
  array (
    0 => 'Neos\\Eel\\ViewHelpers',
  ),
  'neos.utilitylock' => 
  array (
    0 => 'Neos\\Utility\\Lock\\ViewHelpers',
  ),
  'neos.fluidadaptor' => 
  array (
    0 => 'Neos\\FluidAdaptor\\ViewHelpers',
  ),
  'neos.flow' => 
  array (
    0 => 'Neos\\Flow\\ViewHelpers',
    1 => 'Neos\\Flow\\Core\\Migrations\\ViewHelpers',
  ),
  'neos.form' => 
  array (
    0 => 'Neos\\Form\\ViewHelpers',
  ),
  'neos.twitter.bootstrap' => 
  array (
    0 => 'Neos\\Twitter\\Bootstrap\\ViewHelpers',
  ),
  'neos.setup' => 
  array (
    0 => 'Neos\\Setup\\ViewHelpers',
  ),
  'neos.contentrepository' => 
  array (
    0 => 'Neos\\ContentRepository\\ViewHelpers',
  ),
  'neos.fusion' => 
  array (
    0 => 'Neos\\Fusion\\ViewHelpers',
  ),
  'neos.imagine' => 
  array (
    0 => 'Neos\\Imagine\\ViewHelpers',
  ),
  'neos.media' => 
  array (
    0 => 'Neos\\Media\\ViewHelpers',
  ),
  'neos.party' => 
  array (
    0 => 'Neos\\Party\\ViewHelpers',
  ),
  'neos.kickstarter' => 
  array (
    0 => 'Neos\\Kickstarter\\ViewHelpers',
  ),
  'neos.sitekickstarter' => 
  array (
    0 => 'Neos\\SiteKickstarter\\ViewHelpers',
  ),
  'neos.behat' => 
  array (
    0 => 'Neos\\Behat\\ViewHelpers',
  ),
  'neos.redirecthandler' => 
  array (
    0 => 'Neos\\RedirectHandler\\ViewHelpers',
  ),
  'neos.neos.ui.compiled' => 
  array (
    0 => 'Neos\\Neos\\Ui\\Compiled\\ViewHelpers',
  ),
  'packagefactory.afx' => 
  array (
    0 => 'PackageFactory\\Afx\\ViewHelpers',
  ),
  'neos.fusion.afx' => 
  array (
    0 => 'Neos\\Fusion\\Afx\\ViewHelpers',
  ),
  'neos.redirecthandler.databasestorage' => 
  array (
    0 => 'Neos\\RedirectHandler\\DatabaseStorage\\ViewHelpers',
  ),
  'neos.media.browser' => 
  array (
    0 => 'Neos\\Media\\Browser\\ViewHelpers',
  ),
  'neos.neos' => 
  array (
    0 => 'Neos\\Neos\\ViewHelpers',
  ),
  'neos.redirecthandler.neosadapter' => 
  array (
    0 => 'Neos\\RedirectHandler\\NeosAdapter\\ViewHelpers',
  ),
  'neos.neos.ui' => 
  array (
    0 => 'Neos\\Neos\\Ui\\ViewHelpers',
  ),
  'neos.seo' => 
  array (
    0 => 'Neos\\Seo\\ViewHelpers',
  ),
  'neos.nodetypes.basemixins' => 
  array (
    0 => 'Neos\\NodeTypes\\BaseMixins\\ViewHelpers',
  ),
  'neos.nodetypes.html' => 
  array (
    0 => 'Neos\\NodeTypes\\Html\\ViewHelpers',
  ),
  'neos.nodetypes.columnlayouts' => 
  array (
    0 => 'Neos\\NodeTypes\\ColumnLayouts\\ViewHelpers',
  ),
  'neos.nodetypes.assetlist' => 
  array (
    0 => 'Neos\\NodeTypes\\AssetList\\ViewHelpers',
  ),
  'neos.nodetypes.navigation' => 
  array (
    0 => 'Neos\\NodeTypes\\Navigation\\ViewHelpers',
  ),
  'neos.nodetypes.form' => 
  array (
    0 => 'Neos\\NodeTypes\\Form\\ViewHelpers',
  ),
  'neos.nodetypes.contentreferences' => 
  array (
    0 => 'Neos\\NodeTypes\\ContentReferences\\ViewHelpers',
  ),
  'neos.nodetypes' => 
  array (
    0 => 'Neos\\NodeTypes\\ViewHelpers',
  ),
  'claudio.ui.render' => 
  array (
    0 => 'Claudio\\Ui\\Render\\ViewHelpers',
  ),
  'flowpack.neos.frontendlogin' => 
  array (
    0 => 'Flowpack\\Neos\\FrontendLogin\\ViewHelpers',
  ),
  'neos.demo' => 
  array (
    0 => 'Neos\\Demo\\ViewHelpers',
  ),
  'flowpack.fusionbp' => 
  array (
    0 => 'Flowpack\\FusionBP\\ViewHelpers',
  ),
  'claudio.site.render' => 
  array (
    0 => 'Claudio\\Site\\Render\\ViewHelpers',
  ),
  'phpdocumentor.reflectioncommon' => 
  array (
    0 => 'phpDocumentor\\Reflection\\ViewHelpers',
  ),
  'phpdocumentor.typeresolver' => 
  array (
    0 => 'phpDocumentor\\Reflection\\ViewHelpers',
  ),
  'webmozart.assert' => 
  array (
    0 => 'Webmozart\\Assert\\ViewHelpers',
  ),
  'phpdocumentor.reflectiondocblock' => 
  array (
    0 => 'phpDocumentor\\Reflection\\ViewHelpers',
  ),
  'phpspec.prophecy' => 
  array (
    0 => 'Prophecy\\ViewHelpers',
  ),
  'symfony.cssselector' => 
  array (
    0 => 'Symfony\\Component\\CssSelector\\ViewHelpers',
  ),
  'myclabs.deepcopy' => 
  array (
    0 => 'DeepCopy\\ViewHelpers',
  ),
  'org.bovigo.vfs' => 
  array (
    0 => 'org\\bovigo\\vfs\\ViewHelpers',
  ),
  'neos' => 
  array (
    0 => 'Neos\\Neos\\ViewHelpers',
  ),
));
}

/**
 * Main Render function
 */
public function render(\TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext) {
$self = $this;
$output0 = '';

$output0 .= '
';
// Rendering ViewHelper Neos\Neos\ViewHelpers\Backend\ConfigurationCacheVersionViewHelper
$renderChildrenClosure2 = function() use ($renderingContext, $self) {
return NULL;
};
$arguments1 = array();

$output0 .= call_user_func_array( function ($var) { return (is_string($var) || (is_object($var) && method_exists($var, '__toString')) ? htmlspecialchars((string) $var, ENT_QUOTES) : $var); }, [Neos\Neos\ViewHelpers\Backend\ConfigurationCacheVersionViewHelper::renderStatic($arguments1, $renderChildrenClosure2, $renderingContext)]);

$output0 .= '
';

return $output0;
}


}
#0             9590      