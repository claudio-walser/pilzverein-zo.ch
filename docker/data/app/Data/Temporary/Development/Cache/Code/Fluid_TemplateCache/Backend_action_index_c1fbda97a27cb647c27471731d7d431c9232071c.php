<?php 
class Backend_action_index_c1fbda97a27cb647c27471731d7d431c9232071c extends \TYPO3Fluid\Fluid\Core\Compiler\AbstractCompiledTemplate {

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

$output0 .= '<!doctype html>

<html>
    <head>
        <title>Neos CMS</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
        <script>_NEOS_UI_configuration = ';
// Rendering ViewHelper TYPO3Fluid\Fluid\ViewHelpers\Format\RawViewHelper
$renderChildrenClosure2 = function() use ($renderingContext, $self) {
$array3 = array (
);return $renderingContext->getVariableProvider()->getByPath('configuration', $array3);
};
$arguments1 = array();
$arguments1['value'] = NULL;

$output0 .= isset($arguments1['value']) ? $arguments1['value'] : $renderChildrenClosure2();

$output0 .= ';</script>
        <script>_NEOS_UI_nodeTypes = ';
// Rendering ViewHelper TYPO3Fluid\Fluid\ViewHelpers\Format\RawViewHelper
$renderChildrenClosure5 = function() use ($renderingContext, $self) {
$array6 = array (
);return $renderingContext->getVariableProvider()->getByPath('nodeTypes', $array6);
};
$arguments4 = array();
$arguments4['value'] = NULL;

$output0 .= isset($arguments4['value']) ? $arguments4['value'] : $renderChildrenClosure5();

$output0 .= ';</script>
        <script>_NEOS_UI_frontendConfiguration = ';
// Rendering ViewHelper TYPO3Fluid\Fluid\ViewHelpers\Format\RawViewHelper
$renderChildrenClosure8 = function() use ($renderingContext, $self) {
$array9 = array (
);return $renderingContext->getVariableProvider()->getByPath('frontendConfiguration', $array9);
};
$arguments7 = array();
$arguments7['value'] = NULL;

$output0 .= isset($arguments7['value']) ? $arguments7['value'] : $renderChildrenClosure8();

$output0 .= ';</script>
        <script>_NEOS_UI_menu = ';
// Rendering ViewHelper TYPO3Fluid\Fluid\ViewHelpers\Format\RawViewHelper
$renderChildrenClosure11 = function() use ($renderingContext, $self) {
$array12 = array (
);return $renderingContext->getVariableProvider()->getByPath('menu', $array12);
};
$arguments10 = array();
$arguments10['value'] = NULL;

$output0 .= isset($arguments10['value']) ? $arguments10['value'] : $renderChildrenClosure11();

$output0 .= ';</script>
        <script>_NEOS_UI_initialState = ';
// Rendering ViewHelper TYPO3Fluid\Fluid\ViewHelpers\Format\RawViewHelper
$renderChildrenClosure14 = function() use ($renderingContext, $self) {
$array15 = array (
);return $renderingContext->getVariableProvider()->getByPath('initialState', $array15);
};
$arguments13 = array();
$arguments13['value'] = NULL;

$output0 .= isset($arguments13['value']) ? $arguments13['value'] : $renderChildrenClosure14();

$output0 .= ';</script>
        <script>_NEOS_UI_routes = ';
// Rendering ViewHelper TYPO3Fluid\Fluid\ViewHelpers\Format\RawViewHelper
$renderChildrenClosure17 = function() use ($renderingContext, $self) {
$array18 = array (
);return $renderingContext->getVariableProvider()->getByPath('routes', $array18);
};
$arguments16 = array();
$arguments16['value'] = NULL;

$output0 .= isset($arguments16['value']) ? $arguments16['value'] : $renderChildrenClosure17();

$output0 .= ';</script>

        ';
// Rendering ViewHelper TYPO3Fluid\Fluid\ViewHelpers\Format\RawViewHelper
$renderChildrenClosure20 = function() use ($renderingContext, $self) {
$array21 = array (
);return $renderingContext->getVariableProvider()->getByPath('headStylesheets', $array21);
};
$arguments19 = array();
$arguments19['value'] = NULL;

$output0 .= isset($arguments19['value']) ? $arguments19['value'] : $renderChildrenClosure20();

$output0 .= '
        ';
// Rendering ViewHelper TYPO3Fluid\Fluid\ViewHelpers\Format\RawViewHelper
$renderChildrenClosure23 = function() use ($renderingContext, $self) {
$array24 = array (
);return $renderingContext->getVariableProvider()->getByPath('headScripts', $array24);
};
$arguments22 = array();
$arguments22['value'] = NULL;

$output0 .= isset($arguments22['value']) ? $arguments22['value'] : $renderChildrenClosure23();

$output0 .= '
        ';
// Rendering ViewHelper TYPO3Fluid\Fluid\ViewHelpers\Format\RawViewHelper
$renderChildrenClosure26 = function() use ($renderingContext, $self) {
$array27 = array (
);return $renderingContext->getVariableProvider()->getByPath('headIcons', $array27);
};
$arguments25 = array();
$arguments25['value'] = NULL;

$output0 .= isset($arguments25['value']) ? $arguments25['value'] : $renderChildrenClosure26();

$output0 .= '
    </head>
    <body>
        <div id="appContainer" data-csrf-token="';
// Rendering ViewHelper Neos\FluidAdaptor\ViewHelpers\Security\CsrfTokenViewHelper
$renderChildrenClosure29 = function() use ($renderingContext, $self) {
return NULL;
};
$arguments28 = array();

$output0 .= call_user_func_array( function ($var) { return (is_string($var) || (is_object($var) && method_exists($var, '__toString')) ? htmlspecialchars((string) $var, ENT_QUOTES) : $var); }, [$renderingContext->getObjectManager()->get(\Neos\Flow\Security\Context::class)->getCsrfProtectionToken()]);

$output0 .= '" data-first-tab="';
$array30 = array (
);
$output0 .= call_user_func_array( function ($var) { return (is_string($var) || (is_object($var) && method_exists($var, '__toString')) ? htmlspecialchars((string) $var, ENT_QUOTES) : $var); }, [$renderingContext->getVariableProvider()->getByPath('documentNodeUri', $array30)]);

$output0 .= '" data-env="';
$array31 = array (
);
$output0 .= call_user_func_array( function ($var) { return (is_string($var) || (is_object($var) && method_exists($var, '__toString')) ? htmlspecialchars((string) $var, ENT_QUOTES) : $var); }, [$renderingContext->getVariableProvider()->getByPath('env', $array31)]);

$output0 .= '">
            ';
// Rendering ViewHelper TYPO3Fluid\Fluid\ViewHelpers\RenderViewHelper
$renderChildrenClosure33 = function() use ($renderingContext, $self) {
return NULL;
};
$arguments32 = array();
$arguments32['section'] = NULL;
$arguments32['partial'] = NULL;
$arguments32['delegate'] = NULL;
$arguments32['renderable'] = NULL;
$arguments32['arguments'] = array (
);
$arguments32['optional'] = false;
$arguments32['default'] = NULL;
$arguments32['contentAs'] = NULL;
$array34 = array (
);$arguments32['partial'] = $renderingContext->getVariableProvider()->getByPath('splashScreenPartial', $array34);

$output0 .= TYPO3Fluid\Fluid\ViewHelpers\RenderViewHelper::renderStatic($arguments32, $renderChildrenClosure33, $renderingContext);

$output0 .= '
        </div>
    </body>
</html>
';

return $output0;
}


}
#0             15354     