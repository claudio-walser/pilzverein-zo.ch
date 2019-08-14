<?php 
namespace Neos\ContentRepository\Domain\Model\InterDimension;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\Model\IntraDimension;

/**
 * The inter dimensional fallback graph domain model
 * Represents the fallback mechanism between content subgraphs
 */
class InterDimensionalFallbackGraph_Original
{
    /**
     * @var array
     */
    protected $subgraphs = [];

    /**
     * @var array|IntraDimension\ContentDimension[]
     */
    protected $prioritizedContentDimensions = [];

    /**
     * @param array $prioritizedContentDimensions
     */
    public function __construct(array $prioritizedContentDimensions)
    {
        $this->prioritizedContentDimensions = $prioritizedContentDimensions;
    }

    /**
     * @param array $dimensionValues
     * @return ContentSubgraph
     */
    public function createContentSubgraph(array $dimensionValues): ContentSubgraph
    {
        $subgraph = new ContentSubgraph($dimensionValues);
        $this->subgraphs[$subgraph->getIdentityHash()] = $subgraph;

        return $subgraph;
    }

    /**
     * @param ContentSubgraph $variant
     * @param ContentSubgraph $fallback
     * @return VariationEdge
     * @throws IntraDimension\Exception\InvalidFallbackException
     */
    public function connectSubgraphs(ContentSubgraph $variant, ContentSubgraph $fallback): VariationEdge
    {
        if ($variant === $fallback) {
            throw new IntraDimension\Exception\InvalidFallbackException();
        }
        return new VariationEdge($variant, $fallback, $this->calculateFallbackWeight($variant, $fallback));
    }

    /**
     * @param ContentSubgraph $variant
     * @param ContentSubgraph $fallback
     * @return array
     */
    public function calculateFallbackWeight(ContentSubgraph $variant, ContentSubgraph $fallback)
    {
        $weight = [];
        foreach ($this->prioritizedContentDimensions as $contentDimension) {
            $weight[$contentDimension->getName()] = $variant->getDimensionValue($contentDimension->getName())
                ->calculateFallbackDepth($fallback->getDimensionValue($contentDimension->getName())
            );
        }

        return $weight;
    }

    /**
     * @param array $weight
     * @return int
     */
    public function normalizeWeight(array $weight): int
    {
        $base = $this->determineWeightNormalizationBase();
        $normalizedWeight = 0;
        $exponent = 0;
        foreach (array_reverse($weight) as $dimensionName => $dimensionFallbackWeight) {
            $normalizedWeight += pow($base, $exponent) * $dimensionFallbackWeight;
            $exponent++;
        }

        return $normalizedWeight;
    }

    /**
     * @return int
     */
    public function determineWeightNormalizationBase(): int
    {
        $base = 0;
        foreach ($this->prioritizedContentDimensions as $contentDimension) {
            $base = max($base, $contentDimension->getDepth() + 1);
        }

        return $base;
    }

    /**
     * @param ContentSubgraph $contentSubgraph
     * @return ContentSubgraph|null
     * @api
     */
    public function getPrimaryFallback(ContentSubgraph $contentSubgraph)
    {
        $fallbackEdges = $contentSubgraph->getFallbackEdges();
        if (empty($fallbackEdges)) {
            return null;
        }

        uasort($fallbackEdges, function (VariationEdge $edgeA, VariationEdge $edgeB) {
            return $this->normalizeWeight($edgeA->getWeight()) <=> $this->normalizeWeight($edgeB->getWeight());
        });

        return reset($fallbackEdges)->getFallback();
    }

    /**
     * @return array|ContentSubgraph[]
     * @api
     */
    public function getSubgraphs(): array
    {
        return $this->subgraphs;
    }

    /**
     * @param string $identityHash
     * @return ContentSubgraph|null
     * @api
     */
    public function getSubgraph(string $identityHash)
    {
        return $this->subgraphs[$identityHash] ?: null;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\ContentRepository\Domain\Model\InterDimension;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * The inter dimensional fallback graph domain model
 * Represents the fallback mechanism between content subgraphs
 */
class InterDimensionalFallbackGraph extends InterDimensionalFallbackGraph_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param array $prioritizedContentDimensions
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $prioritizedContentDimensions in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
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
  'subgraphs' => 'array',
  'prioritizedContentDimensions' => 'array<array|IntraDimension\\ContentDimension>',
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
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.ContentRepository/Classes/Domain/Model/InterDimension/InterDimensionalFallbackGraph.php
#