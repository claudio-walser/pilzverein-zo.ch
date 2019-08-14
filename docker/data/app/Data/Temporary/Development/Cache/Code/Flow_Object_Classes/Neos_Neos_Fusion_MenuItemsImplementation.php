<?php 
namespace Neos\Neos\Fusion;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Fusion\Exception as FusionException;
use Neos\Fusion\Exception;

/**
 * A Fusion MenuItems object
 */
class MenuItemsImplementation_Original extends AbstractMenuItemsImplementation
{
    /**
     * Hard limit for the maximum number of levels supported by this menu
     */
    const MAXIMUM_LEVELS_LIMIT = 100;

    /**
     * Internal cache for the startingPoint tsValue.
     *
     * @var NodeInterface
     */
    protected $startingPoint;

    /**
     * Internal cache for the lastLevel value.
     *
     * @var integer
     */
    protected $lastLevel;

    /**
     * Internal cache for the maximumLevels tsValue.
     *
     * @var integer
     */
    protected $maximumLevels;

    /**
     * The last navigation level which should be rendered.
     *
     * 1 = first level of the site
     * 2 = second level of the site
     * ...
     * 0  = same level as the current page
     * -1 = one level above the current page
     * -2 = two levels above the current page
     * ...
     *
     * @return integer
     */
    public function getEntryLevel()
    {
        return $this->fusionValue('entryLevel');
    }

    /**
     * NodeType filter for nodes displayed in menu
     *
     * @return string
     */
    public function getFilter()
    {
        $filter = $this->fusionValue('filter');
        if ($filter === null) {
            $filter = 'Neos.Neos:Document';
        }
        return $filter;
    }

    /**
     * Maximum number of levels which should be rendered in this menu.
     *
     * @return integer
     */
    public function getMaximumLevels()
    {
        if ($this->maximumLevels === null) {
            $this->maximumLevels = $this->fusionValue('maximumLevels');
            if ($this->maximumLevels > self::MAXIMUM_LEVELS_LIMIT) {
                $this->maximumLevels = self::MAXIMUM_LEVELS_LIMIT;
            }
        }

        return $this->maximumLevels;
    }

    /**
     * Return evaluated lastLevel value.
     *
     * @return integer
     */
    public function getLastLevel()
    {
        if ($this->lastLevel === null) {
            $this->lastLevel = $this->fusionValue('lastLevel');
            if ($this->lastLevel > self::MAXIMUM_LEVELS_LIMIT) {
                $this->lastLevel = self::MAXIMUM_LEVELS_LIMIT;
            }
        }

        return $this->lastLevel;
    }

    /**
     * @return NodeInterface
     */
    public function getStartingPoint()
    {
        if ($this->startingPoint === null) {
            $this->startingPoint = $this->fusionValue('startingPoint');
        }

        return $this->startingPoint;
    }

    /**
     * @return array
     */
    public function getItemCollection()
    {
        return $this->fusionValue('itemCollection');
    }

    /**
     * Builds the array of menu items containing those items which match the
     * configuration set for this Menu object.
     *
     * @throws FusionException
     * @return array An array of menu items and further information
     */
    protected function buildItems()
    {
        $items = [];

        if ($this->getItemCollection() !== null) {
            $menuLevelCollection = $this->getItemCollection();
        } else {
            $entryParentNode = $this->findMenuStartingPoint();
            if ($entryParentNode === null) {
                return $items;
            }
            $menuLevelCollection = $entryParentNode->getChildNodes($this->getFilter());
        }

        $items = $this->buildMenuLevelRecursive($menuLevelCollection);

        return $items;
    }

    /**
     * @param array $menuLevelCollection
     * @return array
     */
    protected function buildMenuLevelRecursive(array $menuLevelCollection)
    {
        $items = [];
        foreach ($menuLevelCollection as $currentNode) {
            $item = $this->buildMenuItemRecursive($currentNode);
            if ($item === null) {
                continue;
            }

            $items[] = $item;
        }

        return $items;
    }

    /**
     * Prepare the menu item with state and sub items if this isn't the last menu level.
     *
     * @param NodeInterface $currentNode
     * @return array
     */
    protected function buildMenuItemRecursive(NodeInterface $currentNode)
    {
        if ($this->isNodeHidden($currentNode)) {
            return null;
        }

        $item = [
            'node' => $currentNode,
            'state' => self::STATE_NORMAL,
            'label' => $currentNode->getLabel(),
            'menuLevel' => $this->currentLevel
        ];

        $item['state'] = $this->calculateItemState($currentNode);
        if (!$this->isOnLastLevelOfMenu($currentNode)) {
            $this->currentLevel++;
            $item['subItems'] = $this->buildMenuLevelRecursive($currentNode->getChildNodes($this->getFilter()));
            $this->currentLevel--;
        }

        return $item;
    }

    /**
     * Find the starting point for this menu. depending on given startingPoint
     * If startingPoint is given, this is taken as starting point for this menu level,
     * as a fallback the Fusion context variable node is used.
     *
     * If entryLevel is configured this will be taken into account as well.
     *
     * @return NodeInterface
     * @throws Exception
     */
    protected function findMenuStartingPoint()
    {
        $fusionContext = $this->runtime->getCurrentContext();
        $startingPoint = $this->getStartingPoint();

        if (!isset($fusionContext['node']) && !$startingPoint) {
            throw new FusionException('You must either set a "startingPoint" for the menu or "node" must be set in the Fusion context.', 1369596980);
        }
        $startingPoint = $startingPoint ? : $fusionContext['node'];
        $entryParentNode = $this->findParentNodeInBreadcrumbPathByLevel($this->getEntryLevel(), $startingPoint);

        return $entryParentNode;
    }

    /**
     * Checks if the given menuItem is on the last level for this menu, either defined by maximumLevels or lastLevels.
     *
     * @param NodeInterface $menuItemNode
     * @return boolean
     */
    protected function isOnLastLevelOfMenu(NodeInterface $menuItemNode)
    {
        if ($this->currentLevel >= $this->getMaximumLevels()) {
            return true;
        }

        if (($this->getLastLevel() !== null)) {
            if ($this->getNodeLevelInSite($menuItemNode) >= $this->calculateNodeDepthFromRelativeLevel($this->getLastLevel(), $this->currentNode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Finds the node in the current breadcrumb path between current site node and
     * current node whose level matches the specified entry level.
     *
     * @param integer $givenSiteLevel The site level child nodes of the to be found parent node should have. See $this->entryLevel for possible values.
     * @param NodeInterface $startingPoint
     * @return NodeInterface The parent node of the node at the specified level or NULL if none was found
     */
    protected function findParentNodeInBreadcrumbPathByLevel($givenSiteLevel, NodeInterface $startingPoint)
    {
        $parentNode = null;
        if ($givenSiteLevel === 0) {
            return $startingPoint;
        }

        $absoluteDepth = $this->calculateNodeDepthFromRelativeLevel($givenSiteLevel, $startingPoint);
        if (($absoluteDepth - 1) > $this->getNodeLevelInSite($startingPoint)) {
            return null;
        }

        $currentSiteNode = $this->currentNode->getContext()->getCurrentSiteNode();
        $breadcrumbNodes = $currentSiteNode->getContext()->getNodesOnPath($currentSiteNode, $startingPoint);

        if (isset($breadcrumbNodes[$absoluteDepth - 1])) {
            $parentNode = $breadcrumbNodes[$absoluteDepth - 1];
        }

        return $parentNode;
    }

    /**
     * Calculates an absolute depth value for a relative level given.
     *
     * @param integer $relativeLevel
     * @param NodeInterface $referenceNode
     * @return integer
     */
    protected function calculateNodeDepthFromRelativeLevel($relativeLevel, NodeInterface $referenceNode)
    {
        if ($relativeLevel > 0) {
            $depth = $relativeLevel;
        } else {
            $currentSiteDepth = $this->getNodeLevelInSite($referenceNode);
            if ($currentSiteDepth + $relativeLevel < 1) {
                $depth = 1;
            } else {
                $depth = $currentSiteDepth + $relativeLevel + 1;
            }
        }

        return $depth;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Fusion;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A Fusion MenuItems object
 */
class MenuItemsImplementation extends MenuItemsImplementation_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param Runtime $runtime
     * @param string $path
     * @param string $fusionObjectName
     */
    public function __construct()
    {
        $arguments = func_get_args();

        if (!array_key_exists(0, $arguments)) $arguments[0] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Fusion\Core\Runtime');
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $runtime in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $path in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(2, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $fusionObjectName in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
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
  'startingPoint' => 'Neos\\ContentRepository\\Domain\\Model\\NodeInterface',
  'lastLevel' => 'integer',
  'maximumLevels' => 'integer',
  'items' => 'array',
  'currentNode' => 'Neos\\ContentRepository\\Domain\\Model\\NodeInterface',
  'currentLevel' => 'integer',
  'renderHiddenInIndex' => 'boolean',
  'currentNodeRootline' => 'array<Neos\\ContentRepository\\Domain\\Model\\NodeInterface>',
  'runtime' => 'Neos\\Fusion\\Core\\Runtime',
  'path' => 'string',
  'fusionObjectName' => 'string',
  'tsValueCache' => 'array',
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
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Fusion/MenuItemsImplementation.php
#