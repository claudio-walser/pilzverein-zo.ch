<?php 
namespace Neos\Neos\Ui\Aspects;

/*
 * This file is part of the Neos.Neos.Ui package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Service\AuthorizationService;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Flow\Session\SessionInterface;
use Neos\FluidAdaptor\Core\Rendering\FlowAwareRenderingContextInterface;
use Neos\Fusion\Service\HtmlAugmenter;
use Neos\Neos\Domain\Service\ContentContext;
use Neos\Neos\Ui\Domain\Service\UserLocaleService;
use Neos\Neos\Ui\Fusion\Helper\NodeInfoHelper;

/**
 * - Serialize all nodes related to the currently rendered document
 * - Analyze the rendered response for nodes that are foreign to the current document and serialize them too
 *
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class AugmentationAspect_Original
{

    /**
     * @Flow\Inject
     * @var AuthorizationService
     */
    protected $nodeAuthorizationService;

    /**
     * @Flow\Inject
     * @var UserLocaleService
     */
    protected $userLocaleService;

    /**
     * @Flow\Inject
     * @var HtmlAugmenter
     */
    protected $htmlAugmenter;

    /**
     * @Flow\Inject
     * @var NodeInfoHelper
     */
    protected $nodeInfoHelper;

    /**
     * @Flow\Inject
     * @var SessionInterface
     */
    protected $session;

    /**
     * @Flow\InjectConfiguration(package="Neos.Neos.Ui", path="nodeTypeRoles")
     * @var array
     */
    protected $nodeTypeRoles;

    /**
     * Current controller context, will be set by advices
     *
     * This is a workaround to have the controller context available
     * inside the wrapContentObject() advice. It will not be necessary once we implement a custom content element
     * wrapping implementation.
     *
     * @var \Neos\Flow\Mvc\Controller\ControllerContext
     */
    protected $controllerContext = null;

    /**
     * All editable nodes rendered in the document
     *
     * @var array
     */
    protected $renderedNodes = [];

    /**
     * String containing `<script>` tags for non rendered nodes
     *
     * @var string
     */
    protected $nonRenderedContentNodeMetadata;

    /**
     * @Flow\Before("method(Neos\Neos\Fusion\ContentElementWrappingImplementation->evaluate())")
     * @param JoinPointInterface $joinPoint
     * @return mixed
     */
    public function setControllerContextFromContentElementWrappingImplementation(JoinPointInterface $joinPoint)
    {
        /** @var \Neos\Neos\Fusion\ContentElementWrappingImplementation $proxy */
        $proxy = $joinPoint->getProxy();
        $runtime = $proxy->getRuntime();
        $this->controllerContext = $runtime->getControllerContext();
    }

    /**
     * @Flow\Before("method(Neos\Neos\ViewHelpers\ContentElement\WrapViewHelper->setRenderingContext())")
     * @param JoinPointInterface $joinPoint
     * @return mixed
     */
    public function setControllerContextFromWrapViewHelper(JoinPointInterface $joinPoint)
    {
        $renderingContext = $joinPoint->getMethodArgument('renderingContext');
        if ($renderingContext instanceof FlowAwareRenderingContextInterface) {
            $this->controllerContext = $renderingContext->getControllerContext();
        }
    }

    /**
     * Hooks into standard content element wrapping to render those attributes needed for the package to identify
     * nodes and Fusion paths
     *
     * @Flow\Around("method(Neos\Neos\Service\ContentElementWrappingService->wrapContentObject())")
     * @param JoinPointInterface $joinPoint the join point
     * @return mixed
     * @throws IllegalObjectTypeException
     */
    public function contentElementAugmentation(JoinPointInterface $joinPoint)
    {
        /** @var NodeInterface $node */
        $node = $joinPoint->getMethodArgument('node');

        $content = $joinPoint->getMethodArgument('content');

        // Stay compatible with Neos 3.0. When we remove this compatibility, we can convert everything to "fusionPath").
        $fusionPath = ($joinPoint->isMethodArgument('typoScriptPath') ? $joinPoint->getMethodArgument('typoScriptPath') : $joinPoint->getMethodArgument('fusionPath'));

        if (!$this->needsMetadata($node, false)) {
            return $content;
        }

        $attributes = $joinPoint->isMethodArgument('additionalAttributes') ? $joinPoint->getMethodArgument('additionalAttributes') : [];
        $attributes['data-__neos-node-contextpath'] = $node->getContextPath();
        $attributes['data-__neos-fusion-path'] = $fusionPath;

        $this->renderedNodes[] = $node->getIdentifier();

        $this->userLocaleService->switchToUILocale();

        $serializedNode = json_encode($this->nodeInfoHelper->renderNodeWithPropertiesAndChildrenInformation($node, $this->controllerContext));

        $this->userLocaleService->switchToUILocale(true);

        $wrappedContent = $this->htmlAugmenter->addAttributes($content, $attributes, 'div');
        $wrappedContent .= "<script data-neos-nodedata>(function(){(this['@Neos.Neos.Ui:Nodes'] = this['@Neos.Neos.Ui:Nodes'] || {})['{$node->getContextPath()}'] = {$serializedNode}})()</script>";

        return $wrappedContent;
    }

    /**
     * Hooks into the editable viewhelper to render those attributes needed for the package's inline editing
     *
     * @Flow\Around("method(Neos\Neos\Service\ContentElementEditableService->wrapContentProperty())")
     * @param JoinPointInterface $joinPoint the join point
     * @return mixed
     * @throws IllegalObjectTypeException
     */
    public function editableElementAugmentation(JoinPointInterface $joinPoint)
    {
        $property = $joinPoint->getMethodArgument('property');
        $node = $joinPoint->getMethodArgument('node');
        $content = $joinPoint->getMethodArgument('content');

        /** @var ContentContext $contentContext */
        $contentContext = $node->getContext();
        if (!$contentContext->isInBackend()) {
            return $content;
        }

        $content = $joinPoint->getAdviceChain()->proceed($joinPoint);

        $attributes = [
            'data-__neos-property' => $property,
            'data-__neos-editable-node-contextpath' => $node->getContextPath()
        ];

        return $this->htmlAugmenter->addAttributes($content, $attributes, 'span');
    }

    /**
     * @param NodeInterface $node
     * @param boolean $renderCurrentDocumentMetadata
     * @return boolean
     * @throws IllegalObjectTypeException
     */
    protected function needsMetadata(NodeInterface $node, $renderCurrentDocumentMetadata)
    {
        /** @var $contentContext ContentContext */
        $contentContext = $node->getContext();

        return ($contentContext->isInBackend() === true && ($renderCurrentDocumentMetadata === true || $this->nodeAuthorizationService->isGrantedToEditNode($node) === true));
    }

    /**
     * Concatenate strings containing `<script>` tags for all child nodes not rendered
     * within the current document node. This way we can show e.g. content collections
     * within the structure tree which are not actually rendered.
     *
     * @param NodeInterface $documentNode
     * @return void
     * @throws IllegalObjectTypeException
     */
    protected function appendNonRenderedContentNodeMetadata(NodeInterface $documentNode)
    {
        if ($documentNode->getContext()->getWorkspace()->isPublicWorkspace()) {
            return;
        }

        foreach ($documentNode->getChildNodes($this->buildFilterForNonRenderedContent()) as $node) {
            if (in_array($node->getIdentifier(), $this->renderedNodes) === false) {
                $serializedNode = json_encode($this->nodeInfoHelper->renderNodeWithPropertiesAndChildrenInformation($node, $this->controllerContext));
                $this->nonRenderedContentNodeMetadata .= "<script>(function(){(this['@Neos.Neos.Ui:Nodes'] = this['@Neos.Neos.Ui:Nodes'] || {})['{$node->getContextPath()}'] = {$serializedNode}})()</script>";
            }

            if ($node->hasChildNodes() === true) {
                $this->appendNonRenderedContentNodeMetadata($node);
            }
        }
    }

    /**
     * Clear rendered nodes helper array to prevent possible side effects.
     */
    protected function clearRenderedNodesArray()
    {
        $this->renderedNodes = [];
    }

    /**
     * Clear non rendered content node metadata to prevent possible side effects.
     */
    protected function clearNonRenderedContentNodeMetadata()
    {
        $this->nonRenderedContentNodeMetadata = '';
    }

    /**
     * @param NodeInterface $documentNode
     * @return string
     * @throws IllegalObjectTypeException
     */
    public function getNonRenderedContentNodeMetadata(NodeInterface $documentNode)
    {
        $this->userLocaleService->switchToUILocale();

        $this->appendNonRenderedContentNodeMetadata($documentNode);
        $nonRenderedContentNodeMetadata = $this->nonRenderedContentNodeMetadata;
        $this->clearNonRenderedContentNodeMetadata();
        $this->clearRenderedNodesArray();

        $this->userLocaleService->switchToUILocale(true);

        return $nonRenderedContentNodeMetadata;
    }

    /**
     * @return string
     */
    protected function buildFilterForNonRenderedContent()
    {
        $documentNodeTypes = explode(',', $this->nodeTypeRoles['document']);
        $ignoredNodeTypes = explode(',', $this->nodeTypeRoles['ignored']);

        $allFilteredNodeTypes = array_merge($documentNodeTypes, $ignoredNodeTypes);
        $negatedNodeTypes = array_map(function ($nodeTypeName) {
            return '!' . trim($nodeTypeName);
        }, $allFilteredNodeTypes);
        return implode(',', $negatedNodeTypes);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Ui\Aspects;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * - Serialize all nodes related to the currently rendered document
 * - Analyze the rendered response for nodes that are foreign to the current document and serialize them too
 * @\Neos\Flow\Annotations\Scope("singleton")
 * @\Neos\Flow\Annotations\Aspect
 */
class AugmentationAspect extends AugmentationAspect_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Neos\Ui\Aspects\AugmentationAspect') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Ui\Aspects\AugmentationAspect', $this);
        if ('Neos\Neos\Ui\Aspects\AugmentationAspect' === get_class($this)) {
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
  'nodeAuthorizationService' => 'Neos\\ContentRepository\\Service\\AuthorizationService',
  'userLocaleService' => 'Neos\\Neos\\Ui\\Domain\\Service\\UserLocaleService',
  'htmlAugmenter' => 'Neos\\Fusion\\Service\\HtmlAugmenter',
  'nodeInfoHelper' => 'Neos\\Neos\\Ui\\Fusion\\Helper\\NodeInfoHelper',
  'session' => 'Neos\\Flow\\Session\\SessionInterface',
  'nodeTypeRoles' => 'array',
  'controllerContext' => '\\Neos\\Flow\\Mvc\\Controller\\ControllerContext',
  'renderedNodes' => 'array',
  'nonRenderedContentNodeMetadata' => 'string',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Neos\Ui\Aspects\AugmentationAspect') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Ui\Aspects\AugmentationAspect', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Service\AuthorizationService', 'Neos\ContentRepository\Service\AuthorizationService', 'nodeAuthorizationService', 'be5161f8650c76e42dacce00c340510b', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Service\AuthorizationService'); });
        $this->userLocaleService = new \Neos\Neos\Ui\Domain\Service\UserLocaleService();
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Fusion\Service\HtmlAugmenter', 'Neos\Fusion\Service\HtmlAugmenter', 'htmlAugmenter', 'e86465d15d3ea464979563a77314bbba', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Fusion\Service\HtmlAugmenter'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Ui\Fusion\Helper\NodeInfoHelper', 'Neos\Neos\Ui\Fusion\Helper\NodeInfoHelper', 'nodeInfoHelper', '869e7259705909bcf999e4af4c5490fc', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Ui\Fusion\Helper\NodeInfoHelper'); });
        $this->session = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Session\SessionInterface');
        $this->nodeTypeRoles = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Neos.Ui.nodeTypeRoles');
        $this->Flow_Injected_Properties = array (
  0 => 'nodeAuthorizationService',
  1 => 'userLocaleService',
  2 => 'htmlAugmenter',
  3 => 'nodeInfoHelper',
  4 => 'session',
  5 => 'nodeTypeRoles',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos.Ui/Classes/Aspects/AugmentationAspect.php
#