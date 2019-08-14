<?php 
namespace Neos\ContentRepository\Domain\Service\Cache;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\Model\NodeInterface;

/**
 * A first level cache for the NodeDataRepository. It is used to keep
 * Nodes in memory (indexed by identifier and path) and allows to fetch
 * them by path or identifier as single node.
 *
 * The caching of multiple nodes below a certain path is possible as well,
 * using the *ChildNodesByPathAndNodeTypeFilter() methods.
 */
class FirstLevelNodeCache_Original
{
    /**
     * @var array<\Neos\ContentRepository\Domain\Model\NodeInterface>
     */
    protected $nodesByPath = [];

    /**
     * @var array<\Neos\ContentRepository\Domain\Model\NodeInterface>
     */
    protected $nodesByIdentifier = [];

    /**
     * @var array<\Neos\ContentRepository\Domain\Model\NodeInterface>
     */
    protected $childNodesByPathAndNodeTypeFilter = [];

    /**
     * If the cache contains a node for the given path, it is returned.
     *
     * Otherwise false is returned.
     *
     * @param string $path
     * @return NodeInterface
     */
    public function getByPath($path)
    {
        if (isset($this->nodesByPath[$path])) {
            return $this->nodesByPath[$path];
        }

        return false;
    }

    /**
     * Adds the given node to the cache for the given path. The node
     * will also be added under it's identifier.
     *
     * @param string $path
     * @param NodeInterface $node
     * @return void
     */
    public function setByPath($path, NodeInterface $node = null)
    {
        $this->nodesByPath[$path] = $node;
        if ($node !== null) {
            $this->nodesByIdentifier[$node->getIdentifier()] = $node;
        }
    }

    /**
     * If the cache contains a node with the given identifier, it is returned.
     *
     * Otherwise false is returned.
     *
     * @param string $identifier
     * @return NodeInterface|boolean
     */
    public function getByIdentifier($identifier)
    {
        if (isset($this->nodesByIdentifier[$identifier])) {
            return $this->nodesByIdentifier[$identifier];
        }

        return false;
    }

    /**
     * Adds the given node to the cache for the given identifier. The node
     * will also be added with is's path.
     *
     * @param string $identifier
     * @param NodeInterface $node
     * @return void
     */
    public function setByIdentifier($identifier, NodeInterface $node = null)
    {
        $this->nodesByIdentifier[$identifier] = $node;
        if ($node !== null) {
            $this->nodesByPath[$node->getPath()] = $node;
        }
    }

    /**
     * Returns the cached child nodes for the given path and node type filter.
     *
     * @param string $path
     * @param string $nodeTypeFilter
     * @return boolean
     */
    public function getChildNodesByPathAndNodeTypeFilter($path, $nodeTypeFilter)
    {
        if (isset($this->childNodesByPathAndNodeTypeFilter[$path][$nodeTypeFilter])) {
            return $this->childNodesByPathAndNodeTypeFilter[$path][$nodeTypeFilter];
        }

        return false;
    }

    /**
     * Sets the given nodes as child nodes for the given path and node type filter.
     * The nodes will each be added with their path and identifier as well.
     *
     * @param string $path
     * @param string $nodeTypeFilter
     * @param array $nodes
     * @return void
     */
    public function setChildNodesByPathAndNodeTypeFilter($path, $nodeTypeFilter, array $nodes)
    {
        if (!isset($this->childNodesByPathAndNodeTypeFilter[$path])) {
            $this->childNodesByPathAndNodeTypeFilter[$path] = [];
        }

        foreach ($nodes as $node) {
            /** @var NodeInterface $node */
            $this->nodesByPath[$node->getPath()] = $node;
            $this->nodesByIdentifier[$node->getIdentifier()] = $node;
        }

        $this->childNodesByPathAndNodeTypeFilter[$path][$nodeTypeFilter] = $nodes;
    }

    /**
     * Flush the cache.
     *
     * @return void
     */
    public function flush()
    {
        $this->nodesByPath = [];
        $this->nodesByIdentifier = [];
        $this->childNodesByPathAndNodeTypeFilter = [];
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\ContentRepository\Domain\Service\Cache;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A first level cache for the NodeDataRepository. It is used to keep
 * Nodes in memory (indexed by identifier and path) and allows to fetch
 * them by path or identifier as single node.
 * 
 * The caching of multiple nodes below a certain path is possible as well,
 * using the *ChildNodesByPathAndNodeTypeFilter() methods.
 */
class FirstLevelNodeCache extends FirstLevelNodeCache_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


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
  'nodesByPath' => 'array<\\Neos\\ContentRepository\\Domain\\Model\\NodeInterface>',
  'nodesByIdentifier' => 'array<\\Neos\\ContentRepository\\Domain\\Model\\NodeInterface>',
  'childNodesByPathAndNodeTypeFilter' => 'array<\\Neos\\ContentRepository\\Domain\\Model\\NodeInterface>',
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
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.ContentRepository/Classes/Domain/Service/Cache/FirstLevelNodeCache.php
#