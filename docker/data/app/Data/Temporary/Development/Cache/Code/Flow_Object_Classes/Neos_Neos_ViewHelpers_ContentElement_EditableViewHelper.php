<?php 
namespace Neos\Neos\ViewHelpers\ContentElement;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Authorization\PrivilegeManagerInterface;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractTagBasedViewHelper;
use Neos\FluidAdaptor\Core\ViewHelper\Exception as ViewHelperException;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Service\AuthorizationService;
use Neos\Fusion\ViewHelpers\FusionContextTrait;
use Neos\Neos\Service\ContentElementEditableService;

/**
 * Renders a wrapper around the inner contents of the tag to enable frontend editing.
 *
 * The wrapper contains the property name which should be made editable, and is by default
 * a "div" tag. The tag to use can be given as `tag` argument to the ViewHelper.
 *
 * In live workspace this just renders a tag with the specified $tag-name containing the value of the given $property.
 * For logged in users with access to the Backend this also adds required attributes for the RTE to work.
 *
 * Note: when passing a node you have to make sure a metadata wrapper is used around this that matches the given node
 * (see contentElement.wrap - i.e. the WrapViewHelper).
 */
class EditableViewHelper_Original extends AbstractTagBasedViewHelper
{
    use FusionContextTrait;

    /**
     * @Flow\Inject
     * @var PrivilegeManagerInterface
     */
    protected $privilegeManager;

    /**
     * @Flow\Inject
     * @var AuthorizationService
     */
    protected $nodeAuthorizationService;

    /**
     * @Flow\Inject
     * @var ContentElementEditableService
     */
    protected $contentElementEditableService;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
    }

    /**
     * In live workspace this just renders a tag; for logged in users with access to the Backend this also adds required
     * attributes for the editing.
     *
     * @param string $property Name of the property to render. Note: If this tag has child nodes, they overrule this argument!
     * @param string $tag The name of the tag that should be wrapped around the property. By default this is a <div>
     * @param NodeInterface $node The node of the content element. Optional, will be resolved from the Fusion context by default.
     * @return string The rendered property with a wrapping tag. In the user workspace this adds some required attributes for the RTE to work
     * @throws ViewHelperException
     */
    public function render($property, $tag = 'div', NodeInterface $node = null)
    {
        $this->tag->setTagName($tag);
        $this->tag->forceClosingTag(true);
        $content = $this->renderChildren();

        if ($node === null) {
            $node = $this->getNodeFromFusionContext();
        }

        if ($node === null) {
            throw new ViewHelperException('A node is required, but one was not supplied and could not be found in the Fusion context.', 1408521638);
        }

        if ($content === null) {
            if (!$this->templateVariableContainer->exists($property)) {
                throw new ViewHelperException(sprintf('The property "%1$s" was not set as a template variable. If you use this ViewHelper in a partial, make sure to pass the node property "%1$s" as an argument.', $property), 1384507046);
            }
            $content = $this->templateVariableContainer->get($property);
        }
        $this->tag->setContent($content);

        return $this->contentElementEditableService->wrapContentProperty($node, $property, $this->tag->render());
    }

    /**
     * @return NodeInterface
     * @throws ViewHelperException
     */
    protected function getNodeFromFusionContext()
    {
        $node = $this->getContextVariable('node');
        if ($node === null) {
            throw new ViewHelperException('This ViewHelper can only be used in a Fusion content element. You have to specify the "node" argument if it cannot be resolved from the Fusion context.', 1385737102);
        }

        return $node;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\ViewHelpers\ContentElement;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Renders a wrapper around the inner contents of the tag to enable frontend editing.
 * 
 * The wrapper contains the property name which should be made editable, and is by default
 * a "div" tag. The tag to use can be given as `tag` argument to the ViewHelper.
 * 
 * In live workspace this just renders a tag with the specified $tag-name containing the value of the given $property.
 * For logged in users with access to the Backend this also adds required attributes for the RTE to work.
 * 
 * Note: when passing a node you have to make sure a metadata wrapper is used around this that matches the given node
 * (see contentElement.wrap - i.e. the WrapViewHelper).
 */
class EditableViewHelper extends EditableViewHelper_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        parent::__construct();
        if ('Neos\Neos\ViewHelpers\ContentElement\EditableViewHelper' === get_class($this)) {
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
  'privilegeManager' => 'Neos\\Flow\\Security\\Authorization\\PrivilegeManagerInterface',
  'nodeAuthorizationService' => 'Neos\\ContentRepository\\Service\\AuthorizationService',
  'contentElementEditableService' => 'Neos\\Neos\\Service\\ContentElementEditableService',
  'escapeOutput' => 'boolean',
  'tag' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\TagBuilder',
  'tagName' => 'string',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Authorization\PrivilegeManagerInterface', 'Neos\Flow\Security\Authorization\PrivilegeManager', 'privilegeManager', '68ada25ea2828278e185a684d1c86739', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Authorization\PrivilegeManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Service\AuthorizationService', 'Neos\ContentRepository\Service\AuthorizationService', 'nodeAuthorizationService', 'be5161f8650c76e42dacce00c340510b', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Service\AuthorizationService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\ContentElementEditableService', 'Neos\Neos\Service\ContentElementEditableService', 'contentElementEditableService', '9259893dbe5fc68460c362160aa292a5', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\ContentElementEditableService'); });
        $this->Flow_Injected_Properties = array (
  0 => 'tagBuilder',
  1 => 'objectManager',
  2 => 'systemLogger',
  3 => 'logger',
  4 => 'privilegeManager',
  5 => 'nodeAuthorizationService',
  6 => 'contentElementEditableService',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/ViewHelpers/ContentElement/EditableViewHelper.php
#