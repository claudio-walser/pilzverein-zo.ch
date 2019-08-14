<?php 
namespace Neos\ContentRepository\Domain\Model\IntraDimension;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * The intra dimensional fallback graph domain model
 * Represents the fallback mechanism within each content subgraph dimension
 */
class IntraDimensionalFallbackGraph_Original
{
    /**
     * @var array
     */
    protected $dimensions = [];

    /**
     * @param string $dimensionName
     * @param string|null $label
     * @return ContentDimension
     */
    public function createDimension(string $dimensionName, string $label = null): ContentDimension
    {
        $dimension = new ContentDimension($dimensionName, $label);
        $this->dimensions[$dimension->getName()] = $dimension;

        return $dimension;
    }

    /**
     * @return array|ContentDimension[]
     */
    public function getDimensions(): array
    {
        return $this->dimensions;
    }

    /**
     * @param string $dimensionName
     * @return ContentDimension|null
     */
    public function getDimension(string $dimensionName)
    {
        return $this->dimensions[$dimensionName] ?: null;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\ContentRepository\Domain\Model\IntraDimension;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * The intra dimensional fallback graph domain model
 * Represents the fallback mechanism within each content subgraph dimension
 */
class IntraDimensionalFallbackGraph extends IntraDimensionalFallbackGraph_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
  'dimensions' => 'array',
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
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.ContentRepository/Classes/Domain/Model/IntraDimension/IntraDimensionalFallbackGraph.php
#