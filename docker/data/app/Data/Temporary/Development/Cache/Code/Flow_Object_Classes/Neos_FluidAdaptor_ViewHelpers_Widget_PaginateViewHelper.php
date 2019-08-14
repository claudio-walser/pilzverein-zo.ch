<?php 
namespace Neos\FluidAdaptor\ViewHelpers\Widget;

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
use Neos\Flow\Persistence\QueryResultInterface;
use Neos\FluidAdaptor\Core\Widget\AbstractWidgetViewHelper;

/**
 * This ViewHelper renders a Pagination of objects.
 *
 * = Examples =
 *
 * <code title="simple configuration">
 * <f:widget.paginate objects="{blogs}" as="paginatedBlogs" configuration="{itemsPerPage: 5}">
 *   // use {paginatedBlogs} as you used {blogs} before, most certainly inside
 *   // a <f:for> loop.
 * </f:widget.paginate>
 * </code>
 *
 * <code title="full configuration">
 * <f:widget.paginate objects="{blogs}" as="paginatedBlogs" configuration="{itemsPerPage: 5, insertAbove: true, insertBelow: false, maximumNumberOfLinks: 10}">
 *   // This example will display at the maximum 10 links and tries to the settings
 *   // pagesBefore and pagesAfter into account to get the best result
 * </f:widget.paginate>
 * </code>
 * = Performance characteristics =
 *
 * In the above example, it looks like {blogs} contains all Blog objects, thus
 * you might wonder if all objects were fetched from the database.
 * However, the blogs are NOT fetched from the database until you actually use them,
 * so the paginate ViewHelper will adjust the query sent to the database and receive
 * only the small subset of objects.
 * So, there is no negative performance overhead in using the Paginate Widget.
 *
 * @api
 */
class PaginateViewHelper_Original extends AbstractWidgetViewHelper
{
    /**
     * @Flow\Inject
     * @var Controller\PaginateController
     */
    protected $controller;

    /**
     * Render this view helper
     *
     * @param QueryResultInterface $objects
     * @param string $as
     * @param array $configuration
     * @return string
     */
    public function render(QueryResultInterface $objects, $as, array $configuration = ['itemsPerPage' => 10, 'insertAbove' => false, 'insertBelow' => true, 'maximumNumberOfLinks' => 99])
    {
        $response = $this->initiateSubRequest();
        return $response->getContent();
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\FluidAdaptor\ViewHelpers\Widget;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * This ViewHelper renders a Pagination of objects.
 * 
 * = Examples =
 * 
 * <code title="simple configuration">
 * <f:widget.paginate objects="{blogs}" as="paginatedBlogs" configuration="{itemsPerPage: 5}">
 *   // use {paginatedBlogs} as you used {blogs} before, most certainly inside
 *   // a <f:for> loop.
 * </f:widget.paginate>
 * </code>
 * 
 * <code title="full configuration">
 * <f:widget.paginate objects="{blogs}" as="paginatedBlogs" configuration="{itemsPerPage: 5, insertAbove: true, insertBelow: false, maximumNumberOfLinks: 10}">
 *   // This example will display at the maximum 10 links and tries to the settings
 *   // pagesBefore and pagesAfter into account to get the best result
 * </f:widget.paginate>
 * </code>
 * = Performance characteristics =
 * 
 * In the above example, it looks like {blogs} contains all Blog objects, thus
 * you might wonder if all objects were fetched from the database.
 * However, the blogs are NOT fetched from the database until you actually use them,
 * so the paginate ViewHelper will adjust the query sent to the database and receive
 * only the small subset of objects.
 * So, there is no negative performance overhead in using the Paginate Widget.
 */
class PaginateViewHelper extends PaginateViewHelper_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\FluidAdaptor\ViewHelpers\Widget\PaginateViewHelper' === get_class($this)) {
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
  'controller' => 'Neos\\FluidAdaptor\\ViewHelpers\\Widget\\Controller\\PaginateController',
  'escapeOutput' => 'boolean',
  'ajaxWidget' => 'boolean',
  'storeConfigurationInSession' => 'boolean',
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
        $this->injectAjaxWidgetContextHolder(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\FluidAdaptor\Core\Widget\AjaxWidgetContextHolder'));
        $this->injectWidgetContext(new \Neos\FluidAdaptor\Core\Widget\WidgetContext());
        $this->injectObjectManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'));
        $this->injectSystemLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'));
        $this->injectLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Psr\Log\LoggerInterface'));
        $this->controller = new \Neos\FluidAdaptor\ViewHelpers\Widget\Controller\PaginateController();
        $this->Flow_Injected_Properties = array (
  0 => 'ajaxWidgetContextHolder',
  1 => 'widgetContext',
  2 => 'objectManager',
  3 => 'systemLogger',
  4 => 'logger',
  5 => 'controller',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Framework/Neos.FluidAdaptor/Classes/ViewHelpers/Widget/PaginateViewHelper.php
#