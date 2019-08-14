<?php 
namespace Neos\Neos\Service;

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
use Neos\Neos\Domain\Service\ContentContext;
use Neos\Neos\Service\Mapping\NodePropertyConverterService;
use Neos\ContentRepository\Domain\Model\Node;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Service\AuthorizationService;
use Neos\Fusion\Service\HtmlAugmenter as FusionHtmlAugmenter;

/**
 * The content element wrapping service adds the necessary markup around
 * a content element such that it can be edited using the Content Module
 * of the Neos Backend.
 *
 * @Flow\Scope("singleton")
 */
class ContentElementWrappingService_Original
{
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
     * @var FusionHtmlAugmenter
     */
    protected $htmlAugmenter;

    /**
     * @Flow\Inject
     * @var NodePropertyConverterService
     */
    protected $nodePropertyConverterService;

    /**
     * Wrap the $content identified by $node with the needed markup for the backend.
     *
     * @param NodeInterface $node
     * @param string $content
     * @param string $fusionPath
     * @param array $additionalAttributes additional attributes in the form ['<attribute-name>' => '<attibute-value>', ...] to be rendered in the element wrapping
     * @return string
     */
    public function wrapContentObject(NodeInterface $node, $content, $fusionPath, array $additionalAttributes = [])
    {
        if ($this->needsMetadata($node, false) === false) {
            return $content;
        }

        $attributes = $additionalAttributes;
        $attributes['data-node-__typoscript-path'] = $fusionPath; // @deprecated
        $attributes['data-node-__fusion-path'] = $fusionPath;
        $attributes['tabindex'] = 0;
        $attributes = $this->addGenericEditingMetadata($attributes, $node);
        $attributes = $this->addNodePropertyAttributes($attributes, $node);
        $attributes = $this->addCssClasses($attributes, $node, $this->collectEditingClassNames($node));

        return $this->htmlAugmenter->addAttributes($content, $attributes, 'div', ['typeof']);
    }

    /**
     * @param NodeInterface $node
     * @param string $content
     * @param string $fusionPath
     * @param array $additionalAttributes additional attributes in the form ['<attribute-name>' => '<attibute-value>', ...] to be rendered in the element wrapping
     * @return string
     */
    public function wrapCurrentDocumentMetadata(NodeInterface $node, $content, $fusionPath, array $additionalAttributes = [])
    {
        if ($this->needsMetadata($node, true) === false) {
            return $content;
        }

        $attributes = $additionalAttributes;
        $attributes['data-node-__typoscript-path'] = $fusionPath; // @deprecated
        $attributes['data-node-__fusion-path'] = $fusionPath;
        $attributes = $this->addGenericEditingMetadata($attributes, $node);
        $attributes = $this->addNodePropertyAttributes($attributes, $node);
        $attributes = $this->addDocumentMetadata($attributes, $node);
        $attributes = $this->addCssClasses($attributes, $node, []);

        return $this->htmlAugmenter->addAttributes($content, $attributes, 'div', ['typeof']);
    }

    /**
     * Adds node properties to the given $attributes collection and returns the extended array
     *
     * @param array $attributes
     * @param NodeInterface $node
     * @return array the merged attributes
     */
    protected function addNodePropertyAttributes(array $attributes, NodeInterface $node)
    {
        foreach (array_keys($node->getNodeType()->getProperties()) as $propertyName) {
            if ($propertyName[0] === '_' && $propertyName[1] === '_') {
                // skip fully-private properties
                continue;
            }
            $attributes = array_merge($attributes, $this->renderNodePropertyAttribute($node, $propertyName));
        }

        return $attributes;
    }

    /**
     * Renders data attributes needed for the given node property.
     *
     * @param NodeInterface $node
     * @param string $propertyName
     * @return array
     */
    protected function renderNodePropertyAttribute(NodeInterface $node, $propertyName)
    {
        $attributes = [];
        /** @var $contentContext ContentContext */
        $contentContext = $node->getContext();
        // skip the node name of the site node - TODO: Why do we need this?
        if ($propertyName === '_name' && $node === $contentContext->getCurrentSiteNode()) {
            return $attributes;
        }

        $dataType = $node->getNodeType()->getPropertyType($propertyName);
        $dasherizedPropertyName = $this->dasherize($propertyName);

        $propertyValue = $this->nodePropertyConverterService->getProperty($node, $propertyName);
        $propertyValue = $propertyValue === null ? '' : $propertyValue;
        $propertyValue = !is_string($propertyValue) ? json_encode($propertyValue) : $propertyValue;

        if ($dataType !== 'string') {
            $attributes['data-nodedatatype-' . $dasherizedPropertyName] = 'xsd:' . $dataType;
        }

        $attributes['data-node-' . $dasherizedPropertyName] = $propertyValue;

        return $attributes;
    }

    /**
     * Add required CSS classes to the attributes.
     *
     * @param array $attributes
     * @param NodeInterface $node
     * @param array $initialClasses
     * @return array
     */
    protected function addCssClasses(array $attributes, NodeInterface $node, array $initialClasses = [])
    {
        $classNames = $initialClasses;
        // FIXME: The `dimensionsAreMatchingTargetDimensionValues` method should become part of the NodeInterface if it is used here .
        if ($node instanceof Node && !$node->dimensionsAreMatchingTargetDimensionValues()) {
            $classNames[] = 'neos-contentelement-shine-through';
        }

        if ($classNames !== []) {
            $attributes['class'] = implode(' ', $classNames);
        }

        return $attributes;
    }

    /**
     * Collects metadata for the Neos backend specifically for document nodes.
     *
     * @param array $attributes
     * @param NodeInterface $node
     * @return array
     */
    protected function addDocumentMetadata(array $attributes, NodeInterface $node)
    {
        /** @var ContentContext $contentContext */
        $contentContext = $node->getContext();
        $attributes['data-neos-site-name'] = $contentContext->getCurrentSite()->getName();
        $attributes['data-neos-site-node-context-path'] = $contentContext->getCurrentSiteNode()->getContextPath();
        // Add the workspace of the content repository context to the attributes
        $attributes['data-neos-context-workspace-name'] = $contentContext->getWorkspaceName();
        $attributes['data-neos-context-dimensions'] = json_encode($contentContext->getDimensions());

        if (!$this->nodeAuthorizationService->isGrantedToEditNode($node)) {
            $attributes['data-node-__read-only'] = 'true';
            $attributes['data-nodedatatype-__read-only'] = 'boolean';
        }

        return $attributes;
    }

    /**
     * Collects metadata attributes used to allow editing of the node in the Neos backend.
     *
     * @param array $attributes
     * @param NodeInterface $node
     * @return array
     */
    protected function addGenericEditingMetadata(array $attributes, NodeInterface $node)
    {
        $attributes['typeof'] = 'typo3:' . $node->getNodeType()->getName();
        $attributes['about'] = $node->getContextPath();
        $attributes['data-node-_identifier'] = $node->getIdentifier();
        $attributes['data-node-__workspace-name'] = $node->getWorkspace()->getName();
        $attributes['data-node-__label'] = $node->getLabel();

        if ($node->getNodeType()->isOfType('Neos.Neos:ContentCollection')) {
            $attributes['rel'] = 'typo3:content-collection';
        }

        // these properties are needed together with the current NodeType to evaluate Node Type Constraints
        // TODO: this can probably be greatly cleaned up once we do not use CreateJS or VIE anymore.
        if ($node->getParent()) {
            $attributes['data-node-__parent-node-type'] = $node->getParent()->getNodeType()->getName();
        }

        if ($node->isAutoCreated()) {
            $attributes['data-node-_name'] = $node->getName();
            $attributes['data-node-_is-autocreated'] = 'true';
        }

        if ($node->getParent() && $node->getParent()->isAutoCreated()) {
            $attributes['data-node-_parent-is-autocreated'] = 'true';
            // we shall only add these properties if the parent is actually auto-created; as the Node-Type-Switcher in the UI relies on that.
            $attributes['data-node-__parent-node-name'] = $node->getParent()->getName();
            $attributes['data-node-__grandparent-node-type'] = $node->getParent()->getParent()->getNodeType()->getName();
        }

        return $attributes;
    }

    /**
     * Collects CSS class names used for styling editable elements in the Neos backend.
     *
     * @param NodeInterface $node
     * @return array
     */
    protected function collectEditingClassNames(NodeInterface $node)
    {
        $classNames = [];

        if ($node->getNodeType()->isOfType('Neos.Neos:ContentCollection')) {
            // This is needed since the backend relies on this class (should not be necessary)
            $classNames[] = 'neos-contentcollection';
        } else {
            $classNames[] = 'neos-contentelement';
        }

        if ($node->isRemoved()) {
            $classNames[] = 'neos-contentelement-removed';
        }

        if ($node->isHidden()) {
            $classNames[] = 'neos-contentelement-hidden';
        }

        if ($this->isInlineEditable($node) === false) {
            $classNames[] = 'neos-not-inline-editable';
        }

        return $classNames;
    }

    /**
     * Determine if the Node or one of it's properties is inline editable.
            $parsedType = TypeHandling::parseType($dataType);
     *
     * @param NodeInterface $node
     * @return boolean
     */
    protected function isInlineEditable(NodeInterface $node)
    {
        $uiConfiguration = $node->getNodeType()->hasConfiguration('ui') ? $node->getNodeType()->getConfiguration('ui') : [];
        return (
            (isset($uiConfiguration['inlineEditable']) && $uiConfiguration['inlineEditable'] === true) ||
            $this->hasInlineEditableProperties($node)
        );
    }

    /**
     * Checks if the given Node has any properties configured as 'inlineEditable'
     *
     * @param NodeInterface $node
     * @return boolean
     */
    protected function hasInlineEditableProperties(NodeInterface $node)
    {
        return array_reduce(array_values($node->getNodeType()->getProperties()), function ($hasInlineEditableProperties, $propertyConfiguration) {
            return ($hasInlineEditableProperties || (isset($propertyConfiguration['ui']['inlineEditable']) && $propertyConfiguration['ui']['inlineEditable'] === true));
        }, false);
    }

    /**
     * @param NodeInterface $node
     * @param boolean $renderCurrentDocumentMetadata
     * @return boolean
     */
    protected function needsMetadata(NodeInterface $node, $renderCurrentDocumentMetadata)
    {
        /** @var $contentContext ContentContext */
        $contentContext = $node->getContext();
        return ($contentContext->isInBackend() === true && ($renderCurrentDocumentMetadata === true || $this->nodeAuthorizationService->isGrantedToEditNode($node) === true));
    }

    /**
     * Converts camelCased strings to lower cased and non-camel-cased strings
     *
     * @param string $value
     * @return string
     */
    protected function dasherize($value)
    {
        return strtolower(trim(preg_replace('/[A-Z]/', '-$0', $value), '-'));
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * The content element wrapping service adds the necessary markup around
 * a content element such that it can be edited using the Content Module
 * of the Neos Backend.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class ContentElementWrappingService extends ContentElementWrappingService_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        if (get_class($this) === 'Neos\Neos\Service\ContentElementWrappingService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Service\ContentElementWrappingService', $this);
        if ('Neos\Neos\Service\ContentElementWrappingService' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }
    }

    /**
     * Autogenerated Proxy Method
     */
    protected function Flow_Aop_Proxy_buildMethodsAndAdvicesArray()
    {
        if (method_exists(get_parent_class(), 'Flow_Aop_Proxy_buildMethodsAndAdvicesArray') && is_callable('parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray')) parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        $objectManager = \Neos\Flow\Core\Bootstrap::$staticObjectManager;
        $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array(
            'wrapContentObject' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Neos\Ui\Aspects\AugmentationAspect', 'contentElementAugmentation', $objectManager, NULL),
                ),
            ),
        );
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        if (get_class($this) === 'Neos\Neos\Service\ContentElementWrappingService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Service\ContentElementWrappingService', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __clone()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
    }

    /**
     * Autogenerated Proxy Method
     * @param NodeInterface $node
     * @param string $content
     * @param string $fusionPath
     * @param array $additionalAttributes additional attributes in the form ['<attribute-name>' => '<attibute-value>', ...] to be rendered in the element wrapping
     * @return string
     */
    public function wrapContentObject(\Neos\ContentRepository\Domain\Model\NodeInterface $node, $content, $fusionPath, array $additionalAttributes = array())
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['wrapContentObject'])) {
            $result = parent::wrapContentObject($node, $content, $fusionPath, $additionalAttributes);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['wrapContentObject'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
                $methodArguments['content'] = $content;
                $methodArguments['fusionPath'] = $fusionPath;
                $methodArguments['additionalAttributes'] = $additionalAttributes;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('wrapContentObject');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Service\ContentElementWrappingService', 'wrapContentObject', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['wrapContentObject']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['wrapContentObject']);
        }
        return $result;
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
  'htmlAugmenter' => 'Neos\\Fusion\\Service\\HtmlAugmenter',
  'nodePropertyConverterService' => 'Neos\\Neos\\Service\\Mapping\\NodePropertyConverterService',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Authorization\PrivilegeManagerInterface', 'Neos\Flow\Security\Authorization\PrivilegeManager', 'privilegeManager', '68ada25ea2828278e185a684d1c86739', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Authorization\PrivilegeManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Service\AuthorizationService', 'Neos\ContentRepository\Service\AuthorizationService', 'nodeAuthorizationService', 'be5161f8650c76e42dacce00c340510b', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Service\AuthorizationService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Fusion\Service\HtmlAugmenter', 'Neos\Fusion\Service\HtmlAugmenter', 'htmlAugmenter', 'e86465d15d3ea464979563a77314bbba', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Fusion\Service\HtmlAugmenter'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\Mapping\NodePropertyConverterService', 'Neos\Neos\Service\Mapping\NodePropertyConverterService', 'nodePropertyConverterService', 'fcc7b444cbe0af01c7f7bd7f2fe01850', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\Mapping\NodePropertyConverterService'); });
        $this->Flow_Injected_Properties = array (
  0 => 'privilegeManager',
  1 => 'nodeAuthorizationService',
  2 => 'htmlAugmenter',
  3 => 'nodePropertyConverterService',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Service/ContentElementWrappingService.php
#