<?php 
namespace Neos\Fusion\FusionObjects;

/*
 * This file is part of the Neos.Fusion package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Fusion\Exception as FusionException;

/**
 * Map a collection of items using the itemRenderer
 *
 * //fusionPath items *Collection
 * //fusionPath itemRenderer the Fusion object which is triggered for each item
 */
class MapImplementation_Original extends AbstractFusionObject
{
    /**
     * The number of rendered nodes, filled only after evaluate() was called.
     *
     * @var integer
     */
    protected $numberOfRenderedNodes;

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->fusionValue('items');
    }

    /**
     * @return string
     */
    public function getItemName()
    {
        return $this->fusionValue('itemName');
    }

    /**
     * @return string
     */
    public function getItemKey()
    {
        return $this->fusionValue('itemKey');
    }

    /**
     * If set iteration data (index, cycle, isFirst, isLast) is available in context with the name given.
     *
     * @return string
     */
    public function getIterationName()
    {
        return $this->fusionValue('iterationName');
    }

    /**
     * Evaluate the collection nodes as array
     *
     * @return array
     * @throws FusionException
     */
    public function evaluate()
    {
        $collection = $this->getItems();

        $result = [];
        if ($collection === null) {
            return $result;
        }
        $this->numberOfRenderedNodes = 0;
        $itemName = $this->getItemName();
        if ($itemName === null) {
            throw new FusionException('The Collection needs an itemName to be set.', 1344325771);
        }
        $itemKey = $this->getItemKey();
        $iterationName = $this->getIterationName();
        $collectionTotalCount = count($collection);

        $itemRenderPath = $this->path . '/itemRenderer';
        $fallbackRenderPath =  $this->path . '/content';
        if ($this->runtime->canRender($itemRenderPath) === false && $this->runtime->canRender($fallbackRenderPath)) {
            $itemRenderPath = $fallbackRenderPath;
        }

        foreach ($collection as $collectionKey => $collectionElement) {
            $context = $this->runtime->getCurrentContext();
            $context[$itemName] = $collectionElement;
            if ($itemKey !== null) {
                $context[$itemKey] = $collectionKey;
            }
            if ($iterationName !== null) {
                $context[$iterationName] = $this->prepareIterationInformation($collectionTotalCount);
            }

            $this->runtime->pushContextArray($context);
            $result[$collectionKey] =  $this->runtime->render($itemRenderPath);
            $this->runtime->popContext();
            $this->numberOfRenderedNodes++;
        }

        return $result;
    }

    /**
     * @param integer $collectionCount
     * @return array
     */
    protected function prepareIterationInformation($collectionCount)
    {
        $iteration = [
            'index' => $this->numberOfRenderedNodes,
            'cycle' => ($this->numberOfRenderedNodes + 1),
            'isFirst' => false,
            'isLast' => false,
            'isEven' => false,
            'isOdd' => false
        ];

        if ($this->numberOfRenderedNodes === 0) {
            $iteration['isFirst'] = true;
        }
        if (($this->numberOfRenderedNodes + 1) === $collectionCount) {
            $iteration['isLast'] = true;
        }
        if (($this->numberOfRenderedNodes + 1) % 2 === 0) {
            $iteration['isEven'] = true;
        } else {
            $iteration['isOdd'] = true;
        }

        return $iteration;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Fusion\FusionObjects;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Map a collection of items using the itemRenderer
 * 
 * //fusionPath items *Collection
 * //fusionPath itemRenderer the Fusion object which is triggered for each item
 */
class MapImplementation extends MapImplementation_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
  'numberOfRenderedNodes' => 'integer',
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
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Fusion/Classes/FusionObjects/MapImplementation.php
#