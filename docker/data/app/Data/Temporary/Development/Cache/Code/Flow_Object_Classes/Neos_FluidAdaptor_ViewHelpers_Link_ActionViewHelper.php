<?php 
namespace Neos\FluidAdaptor\ViewHelpers\Link;

/*
 * This file is part of the Neos.FluidAdaptor package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\FluidAdaptor\Core\ViewHelper\AbstractTagBasedViewHelper;
use Neos\FluidAdaptor\Core\ViewHelper;

/**
 * A view helper for creating links to actions.
 *
 * = Examples =
 *
 * <code title="Defaults">
 * <f:link.action>some link</f:link.action>
 * </code>
 * <output>
 * <a href="currentpackage/currentcontroller">some link</a>
 * (depending on routing setup and current package/controller/action)
 * </output>
 *
 * <code title="Additional arguments">
 * <f:link.action action="myAction" controller="MyController" package="YourCompanyName.MyPackage" subpackage="YourCompanyName.MySubpackage" arguments="{key1: 'value1', key2: 'value2'}">some link</f:link.action>
 * </code>
 * <output>
 * <a href="mypackage/mycontroller/mysubpackage/myaction?key1=value1&amp;key2=value2">some link</a>
 * (depending on routing setup)
 * </output>
 *
 * @api
 */
class ActionViewHelper_Original extends AbstractTagBasedViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'a';

    /**
     * Initialize arguments
     *
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        $this->registerUniversalTagAttributes();
        $this->registerTagAttribute('name', 'string', 'Specifies the name of an anchor');
        $this->registerTagAttribute('rel', 'string', 'Specifies the relationship between the current document and the linked document');
        $this->registerTagAttribute('rev', 'string', 'Specifies the relationship between the linked document and the current document');
        $this->registerTagAttribute('target', 'string', 'Specifies where to open the linked document');
        $this->registerArgument('action', 'string', 'Target action', true);
        $this->registerArgument('arguments', 'array', 'Arguments', false, []);
        $this->registerArgument('controller', 'string', 'Target controller. If NULL current controllerName is used', false, null);
        $this->registerArgument('package', 'string', 'Target package. if NULL current package is used', false, null);
        $this->registerArgument('subpackage', 'string', 'Target subpackage. if NULL current subpackage is used', false, null);
        $this->registerArgument('section', 'string', 'The anchor to be added to the URI', false, '');
        $this->registerArgument('format', 'string', 'The requested format, e.g. ".html"', false, '');
        $this->registerArgument('additionalParams', 'array', 'additional query parameters that won\'t be prefixed like $arguments (overrule $arguments)', false, []);
        $this->registerArgument('addQueryString', 'boolean', 'If set, the current query parameters will be kept in the URI', false, false);
        $this->registerArgument('argumentsToBeExcludedFromQueryString', 'array', 'arguments to be removed from the URI. Only active if $addQueryString = true', false, []);
        $this->registerArgument('useParentRequest', 'boolean', 'If set, the parent Request will be used instead of the current one. Note: using this argument can be a sign of undesired tight coupling, use with care', false, false);
        $this->registerArgument('absolute', 'boolean', 'By default this ViewHelper renders links with absolute URIs. If this is false, a relative URI is created instead', false, true);
        $this->registerArgument('useMainRequest', 'boolean', 'If set, the main Request will be used instead of the current one. Note: using this argument can be a sign of undesired tight coupling, use with care', false, false);
    }

    /**
     * Render the link.
     *
     * @return string The rendered link
     * @throws ViewHelper\Exception
     * @api
     */
    public function render()
    {
        $uriBuilder = $this->controllerContext->getUriBuilder();
        if ($this->arguments['useParentRequest']) {
            $request = $this->controllerContext->getRequest();
            if ($request->isMainRequest()) {
                throw new ViewHelper\Exception('You can\'t use the parent Request, you are already in the MainRequest.', 1360163536);
            }
            $uriBuilder = clone $uriBuilder;
            $uriBuilder->setRequest($request->getParentRequest());
        } elseif ($this->arguments['useMainRequest'] === true) {
            $request = $this->controllerContext->getRequest();
            if (!$request->isMainRequest()) {
                $uriBuilder = clone $uriBuilder;
                $uriBuilder->setRequest($request->getMainRequest());
            }
        }

        $uriBuilder
            ->reset()
            ->setSection($this->arguments['section'])
            ->setCreateAbsoluteUri($this->arguments['absolute'])
            ->setArguments($this->arguments['additionalParams'])
            ->setAddQueryString($this->arguments['addQueryString'])
            ->setArgumentsToBeExcludedFromQueryString($this->arguments['argumentsToBeExcludedFromQueryString'])
            ->setFormat($this->arguments['format']);
        try {
            $uri = $uriBuilder->uriFor($this->arguments['action'], $this->arguments['arguments'], $this->arguments['controller'], $this->arguments['package'], $this->arguments['subpackage']);
        } catch (\Exception $exception) {
            throw new ViewHelper\Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
        $this->tag->addAttribute('href', $uri);
        $this->tag->setContent($this->renderChildren());
        $this->tag->forceClosingTag(true);

        return $this->tag->render();
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\FluidAdaptor\ViewHelpers\Link;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A view helper for creating links to actions.
 * 
 * = Examples =
 * 
 * <code title="Defaults">
 * <f:link.action>some link</f:link.action>
 * </code>
 * <output>
 * <a href="currentpackage/currentcontroller">some link</a>
 * (depending on routing setup and current package/controller/action)
 * </output>
 * 
 * <code title="Additional arguments">
 * <f:link.action action="myAction" controller="MyController" package="YourCompanyName.MyPackage" subpackage="YourCompanyName.MySubpackage" arguments="{key1: 'value1', key2: 'value2'}">some link</f:link.action>
 * </code>
 * <output>
 * <a href="mypackage/mycontroller/mysubpackage/myaction?key1=value1&amp;key2=value2">some link</a>
 * (depending on routing setup)
 * </output>
 */
class ActionViewHelper extends ActionViewHelper_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        parent::__construct();
        if ('Neos\FluidAdaptor\ViewHelpers\Link\ActionViewHelper' === get_class($this)) {
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
        $this->injectTagBuilder(new \TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder());
        $this->injectObjectManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'));
        $this->injectSystemLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'));
        $this->injectLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Psr\Log\LoggerInterface'));
        $this->Flow_Injected_Properties = array (
  0 => 'tagBuilder',
  1 => 'objectManager',
  2 => 'systemLogger',
  3 => 'logger',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Framework/Neos.FluidAdaptor/Classes/ViewHelpers/Link/ActionViewHelper.php
#