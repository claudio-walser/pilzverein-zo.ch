<?php 
namespace Neos\ContentRepository\Security\Authorization\Privilege\Node;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Validation\Validator\NodeIdentifierValidator;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Context as SecurityContext;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\ContentDimensionPresetSourceInterface;
use Neos\ContentRepository\Domain\Service\ContextFactory;

/**
 * An Eel context matching expression for the node privileges
 */
class NodePrivilegeContext_Original
{
    /**
     * @Flow\Inject
     * @var ContextFactory
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var SecurityContext
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var ContentDimensionPresetSourceInterface
     */
    protected $contentDimensionPresetSource;

    /**
     * @var NodeInterface
     */
    protected $node;

    /**
     * @param NodeInterface $node
     */
    public function __construct(NodeInterface $node = null)
    {
        $this->node = $node;
    }

    /**
     * @param NodeInterface $node
     * @return void
     */
    public function setNode(NodeInterface $node)
    {
        $this->node = $node;
    }

    /**
     * Matches if the selected node is an *ancestor* of the given node specified by $nodePathOrIdentifier
     *
     * Example: isAncestorNodeOf('/sites/some/path') matches for the nodes "/sites", "/sites/some" and "/sites/some/path" but not for "/sites/some/other"
     *
     * @param string $nodePathOrIdentifier The identifier or absolute path of the node to match
     * @return boolean true if the given node matches otherwise false
     */
    public function isAncestorNodeOf($nodePathOrIdentifier)
    {
        $nodePath = $this->resolveNodePath($nodePathOrIdentifier);
        if (is_bool($nodePath)) {
            return $nodePath;
        }

        return substr($nodePath, 0, strlen($this->node->getPath())) === $this->node->getPath();
    }

    /**
     * Matches if the selected node is a *descendant* of the given node specified by $nodePathOrIdentifier
     *
     * Example: isDescendantNodeOf('/sites/some/path') matches for the nodes "/sites/some/path", "/sites/some/path/subnode" but not for "/sites/some/other"
     *
     * @param string $nodePathOrIdentifier The identifier or absolute path of the node to match
     * @return boolean true if the given node matches otherwise false
     */
    public function isDescendantNodeOf($nodePathOrIdentifier)
    {
        $nodePath = $this->resolveNodePath($nodePathOrIdentifier);
        if (is_bool($nodePath)) {
            return $nodePath;
        }
        return substr($this->node->getPath() . '/', 0, strlen($nodePath)) === $nodePath;
    }

    /**
     * Matches if the selected node is a *descendant* or *ancestor* of the given node specified by $nodePathOrIdentifier
     *
     * Example: isAncestorOrDescendantNodeOf('/sites/some') matches for the nodes "/sites", "/sites/some", "/sites/some/sub" but not "/sites/other"
     *
     * @param string $nodePathOrIdentifier The identifier or absolute path of the node to match
     * @return boolean true if the given node matches otherwise false
     */
    public function isAncestorOrDescendantNodeOf($nodePathOrIdentifier)
    {
        return $this->isAncestorNodeOf($nodePathOrIdentifier) || $this->isDescendantNodeOf($nodePathOrIdentifier);
    }

    /**
     * Matches if the selected node is of the given NodeType(s). If multiple types are specified, only one entry has to match
     *
     * Example: nodeIsOfType(['Neos.ContentRepository:NodeType1', 'Neos.ContentRepository:NodeType2']) matches if the selected node is of (sub) type *Neos.ContentRepository:NodeType1* or *Neos.ContentRepository:NodeType1*
     *
     * @param string|array $nodeTypes A single or an array of fully qualified NodeType name(s), e.g. "Neos.Neos:Document"
     * @return boolean true if the selected node matches the $nodeTypes, otherwise false
     */
    public function nodeIsOfType($nodeTypes)
    {
        if ($this->node === null) {
            return true;
        }
        if (!is_array($nodeTypes)) {
            $nodeTypes = [$nodeTypes];
        }

        foreach ($nodeTypes as $nodeType) {
            if ($this->node->getNodeType()->isOfType($nodeType)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Matches if the selected node belongs to one of the given $workspaceNames
     *
     * Example: isInWorkspace(['live', 'user-admin']) matches if the selected node is in one of the workspaces "user-admin" or "live"
     *
     * @param array $workspaceNames An array of workspace names, e.g. ["live", "user-admin"]
     * @return boolean true if the selected node matches the $workspaceNames, otherwise false
     */
    public function isInWorkspace($workspaceNames)
    {
        if ($this->node === null) {
            return true;
        }

        return in_array($this->node->getWorkspace()->getName(), $workspaceNames);
    }

    /**
     * Matches if the currently-selected preset in the passed $dimensionName is one of $presets.
     *
     * Example: isInDimensionPreset('language', 'de') checks whether the currently-selected language
     * preset (in the Neos backend) is "de".
     *
     * Implementation Note: We deliberately work on the Dimension Preset Name, and not on the
     * dimension values itself; as the preset is user-visible and the actual dimension-values
     * for a preset are just implementation details.
     *
     * @param string $dimensionName
     * @param string|array $presets
     * @return boolean
     */
    public function isInDimensionPreset($dimensionName, $presets)
    {
        if ($this->node === null) {
            return true;
        }

        $dimensionValues = $this->node->getContext()->getDimensions();
        if (!isset($dimensionValues[$dimensionName])) {
            return false;
        }

        $preset = $this->contentDimensionPresetSource->findPresetByDimensionValues($dimensionName, $dimensionValues[$dimensionName]);

        if ($preset === null) {
            return false;
        }
        $presetIdentifier = $preset['identifier'];

        if (!is_array($presets)) {
            $presets = [$presets];
        }

        return in_array($presetIdentifier, $presets);
    }

    /**
     * Resolves the given $nodePathOrIdentifier and returns its absolute path and or a boolean if the result directly matches the currently selected node
     *
     * @param string $nodePathOrIdentifier identifier or absolute path for the node to resolve
     * @return bool|string true if the node matches the selected node, false if the corresponding node does not exist. Otherwise the resolved absolute path with trailing slash
     */
    protected function resolveNodePath($nodePathOrIdentifier)
    {
        if ($this->node === null) {
            return true;
        }
        if (preg_match(NodeIdentifierValidator::PATTERN_MATCH_NODE_IDENTIFIER, $nodePathOrIdentifier) !== 1) {
            return rtrim($nodePathOrIdentifier, '/') . '/';
        }
        if ($this->node->getIdentifier() === $nodePathOrIdentifier) {
            return true;
        }
        $node = $this->getNodeByIdentifier($nodePathOrIdentifier);
        if ($node === null) {
            return false;
        }
        return $node->getPath() . '/';
    }

    /**
     * Returns a node from the given $nodeIdentifier (disabling authorization checks)
     *
     * @param string $nodeIdentifier
     * @return NodeInterface
     */
    protected function getNodeByIdentifier($nodeIdentifier)
    {
        $context = $this->contextFactory->create();
        $node = null;
        $this->securityContext->withoutAuthorizationChecks(function () use ($nodeIdentifier, $context, &$node) {
            $node = $context->getNodeByIdentifier($nodeIdentifier);
        });
        $context->getFirstLevelNodeCache()->setByIdentifier($nodeIdentifier, null);
        return $node;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\ContentRepository\Security\Authorization\Privilege\Node;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * An Eel context matching expression for the node privileges
 */
class NodePrivilegeContext extends NodePrivilegeContext_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param NodeInterface $node
     */
    public function __construct()
    {
        $arguments = func_get_args();
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\ContentRepository\Security\Authorization\Privilege\Node\NodePrivilegeContext' === get_class($this)) {
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
  'contextFactory' => 'Neos\\ContentRepository\\Domain\\Service\\ContextFactory',
  'securityContext' => 'Neos\\Flow\\Security\\Context',
  'contentDimensionPresetSource' => 'Neos\\ContentRepository\\Domain\\Service\\ContentDimensionPresetSourceInterface',
  'node' => 'Neos\\ContentRepository\\Domain\\Model\\NodeInterface',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\ContextFactory', 'Neos\ContentRepository\Domain\Service\ContextFactory', 'contextFactory', 'fe29cc43adf119dd42e0028ba09ce06b', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\ContextFactory'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Context', 'Neos\Flow\Security\Context', 'securityContext', 'f7e2ddeaebd191e228b8c2e4dc7f1f83', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Context'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\ContentDimensionPresetSourceInterface', 'Neos\Neos\Domain\Service\ConfigurationContentDimensionPresetSource', 'contentDimensionPresetSource', '33404cce491062aa2636da962a6cf674', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\ContentDimensionPresetSourceInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'contextFactory',
  1 => 'securityContext',
  2 => 'contentDimensionPresetSource',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.ContentRepository/Classes/Security/Authorization/Privilege/Node/NodePrivilegeContext.php
#