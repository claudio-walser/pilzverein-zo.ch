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
use Neos\Neos\Utility\NodeUriPathSegmentGenerator;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\NodeServiceInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\ContentRepository\Domain\Utility\NodePaths;
use Neos\ContentRepository\Exception\NodeException;

/**
 * Centralizes common operations like moving and copying of Nodes with Neos specific additional handling.
 *
 * @Flow\Scope("singleton")
 */
class NodeOperations_Original
{
    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @Flow\Inject
     * @var NodeServiceInterface
     */
    protected $nodeService;

    /**
     * @Flow\Inject
     * @var NodeUriPathSegmentGenerator
     */
    protected $nodeUriPathSegmentGenerator;

    /**
     * Helper method for creating a new node.
     *
     * @param NodeInterface $referenceNode
     * @param array $nodeData
     * @param string $position
     * @return NodeInterface
     * @throws \InvalidArgumentException
     */
    public function create(NodeInterface $referenceNode, array $nodeData, $position)
    {
        if (!in_array($position, ['before', 'into', 'after'], true)) {
            throw new \InvalidArgumentException('The position should be one of the following: "before", "into", "after".', 1347133640);
        }
        $nodeType = $this->nodeTypeManager->getNodeType($nodeData['nodeType']);

        if ($nodeType->isOfType('Neos.Neos:Document') && !isset($nodeData['properties']['uriPathSegment']) && isset($nodeData['properties']['title'])) {
            $nodeData['properties']['uriPathSegment'] = $this->nodeUriPathSegmentGenerator->generateUriPathSegment($referenceNode, $nodeData['properties']['title']);
        }

        $proposedNodeName = isset($nodeData['nodeName']) ? $nodeData['nodeName'] : null;
        $nodeData['nodeName'] = $this->nodeService->generateUniqueNodeName($this->getDesignatedParentNode($referenceNode, $position)->getPath(), $proposedNodeName);

        if ($position === 'into') {
            $newNode = $referenceNode->createNode($nodeData['nodeName'], $nodeType);
        } else {
            $parentNode = $referenceNode->getParent();
            $newNode = $parentNode->createNode($nodeData['nodeName'], $nodeType);

            if ($position === 'before') {
                $newNode->moveBefore($referenceNode);
            } else {
                $newNode->moveAfter($referenceNode);
            }
        }

        if (isset($nodeData['properties']) && is_array($nodeData['properties'])) {
            foreach ($nodeData['properties'] as $propertyName => $propertyValue) {
                $newNode->setProperty($propertyName, $propertyValue);
            }
        }

        return $newNode;
    }

    /**
     * Move $node before, into or after $targetNode
     *
     * @param NodeInterface $node
     * @param NodeInterface $targetNode
     * @param string $position where the node should be added (allowed: before, into, after)
     * @return NodeInterface The same node given as first argument
     * @throws NodeException
     */
    public function move(NodeInterface $node, NodeInterface $targetNode, $position)
    {
        if (!in_array($position, ['before', 'into', 'after'], true)) {
            throw new NodeException('The position should be one of the following: "before", "into", "after".', 1296132542);
        }

        $designatedParentNode = $this->getDesignatedParentNode($targetNode, $position);
        // If we stay inside the same parent we basically just reorder, no rename needed or wanted.
        if ($designatedParentNode !== $node->getParent()) {
            $designatedNodePath = NodePaths::addNodePathSegment($designatedParentNode->getPath(), $node->getName());
            if ($this->nodeService->nodePathAvailableForNode($designatedNodePath, $node) === false) {
                $nodeName = $this->nodeService->generateUniqueNodeName($designatedParentNode->getPath(), $node->getName());
                if ($nodeName !== $node->getName()) {
                    // FIXME: This can be removed if $node->move* supports additionally changing the name of the node.
                    $node->setName($nodeName);
                }
            }
        }

        switch ($position) {
            case 'before':
                $node->moveBefore($targetNode);
                break;
            case 'into':
                $node->moveInto($targetNode);
                break;
            case 'after':
                $node->moveAfter($targetNode);
        }

        return $node;
    }

    /**
     * Copy $node before, into or after $targetNode
     *
     * @param NodeInterface $node the node to be copied
     * @param NodeInterface $targetNode the target node to be copied "to", see $position
     * @param string $position where the node should be added in relation to $targetNode (allowed: before, into, after)
     * @param string $nodeName optional node name (if empty random node name will be generated)
     * @return NodeInterface The copied node
     * @throws NodeException
     */
    public function copy(NodeInterface $node, NodeInterface $targetNode, $position, $nodeName = null)
    {
        if (!in_array($position, ['before', 'into', 'after'], true)) {
            throw new NodeException('The position should be one of the following: "before", "into", "after".', 1346832303);
        }

        $nodeName = $this->nodeService->generateUniqueNodeName($this->getDesignatedParentNode($targetNode, $position)->getPath(), (!empty($nodeName) ? $nodeName : null));

        switch ($position) {
            case 'before':
                $copiedNode = $node->copyBefore($targetNode, $nodeName);
                break;
            case 'after':
                $copiedNode = $node->copyAfter($targetNode, $nodeName);
                break;
            case 'into':
            default:
                $copiedNode = $node->copyInto($targetNode, $nodeName);
        }

        return $copiedNode;
    }

    /**
     * @param NodeInterface $targetNode
     * @param string $position
     * @return NodeInterface
     */
    protected function getDesignatedParentNode(NodeInterface $targetNode, $position)
    {
        $referenceNode = $targetNode;
        if (in_array($position, ['before', 'after'])) {
            $referenceNode = $targetNode->getParent();
        }

        return $referenceNode;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Centralizes common operations like moving and copying of Nodes with Neos specific additional handling.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class NodeOperations extends NodeOperations_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Neos\Service\NodeOperations') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Service\NodeOperations', $this);
        if ('Neos\Neos\Service\NodeOperations' === get_class($this)) {
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
  'nodeTypeManager' => 'Neos\\ContentRepository\\Domain\\Service\\NodeTypeManager',
  'nodeService' => 'Neos\\ContentRepository\\Domain\\Service\\NodeServiceInterface',
  'nodeUriPathSegmentGenerator' => 'Neos\\Neos\\Utility\\NodeUriPathSegmentGenerator',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Neos\Service\NodeOperations') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Service\NodeOperations', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\NodeTypeManager', 'Neos\ContentRepository\Domain\Service\NodeTypeManager', 'nodeTypeManager', 'e525e2ecb65f7f9731d6537ddecd16d4', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\NodeTypeManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\NodeServiceInterface', 'Neos\Neos\TYPO3CR\NeosNodeService', 'nodeService', 'eb555e5f05a6142820b0894069a20195', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\NodeServiceInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Utility\NodeUriPathSegmentGenerator', 'Neos\Neos\Utility\NodeUriPathSegmentGenerator', 'nodeUriPathSegmentGenerator', '9aa7dd66ec5f7dcc785cc7b8fd3d7fa3', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Utility\NodeUriPathSegmentGenerator'); });
        $this->Flow_Injected_Properties = array (
  0 => 'nodeTypeManager',
  1 => 'nodeService',
  2 => 'nodeUriPathSegmentGenerator',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Service/NodeOperations.php
#