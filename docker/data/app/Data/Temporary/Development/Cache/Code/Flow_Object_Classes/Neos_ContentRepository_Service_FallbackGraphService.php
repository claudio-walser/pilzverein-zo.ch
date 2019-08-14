<?php 
namespace Neos\ContentRepository\Service;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\Model\InterDimension;
use Neos\ContentRepository\Domain\Model\IntraDimension;
use Neos\ContentRepository\Domain\Service\ContentDimensionPresetSourceInterface;
use Neos\Flow\Annotations as Flow;

/**
 * The fallback graph application service
 *
 * To be used as a read-only source of fallback information for graph-related projectors
 *
 * Never use this on the read side since its initialization time grows linearly
 * by the amount of possible combinations of content dimension values
 *
 * @Flow\Scope("singleton")
 * @api
 */
class FallbackGraphService_Original
{
    /**
     * @Flow\Inject
     * @var ContentDimensionPresetSourceInterface
     */
    protected $contentDimensionPresetSource;

    /**
     * @var IntraDimension\IntraDimensionalFallbackGraph
     */
    protected $intraDimensionalFallbackGraph;

    /**
     * @var InterDimension\InterDimensionalFallbackGraph
     */
    protected $interDimensionalFallbackGraph;

    /**
     * @throws Exception\InvalidDimensionConfigurationException
     */
    public function initializeObject()
    {
        $prioritizedContentDimensions = $this->populateIntraDimensionalFallbackGraph();
        $this->populateInterDimensionalFallbackGraph($prioritizedContentDimensions);
    }

    /**
     * @return array|IntraDimension\ContentDimension[]
     */
    protected function populateIntraDimensionalFallbackGraph(): array
    {
        $prioritizedContentDimensions = [];
        $this->intraDimensionalFallbackGraph = new IntraDimension\IntraDimensionalFallbackGraph();

        $this->populatePresetDimensions($prioritizedContentDimensions);

        return $prioritizedContentDimensions;
    }

    /**
     * @param array $prioritizedContentDimensions
     * @return void
     * @throws Exception\InvalidDimensionConfigurationException
     */
    protected function populatePresetDimensions(array & $prioritizedContentDimensions)
    {
        foreach ($this->contentDimensionPresetSource->getAllPresets() as $dimensionName => $dimensionConfiguration) {
            $presetDimension = $this->intraDimensionalFallbackGraph->createDimension($dimensionName, $dimensionConfiguration['label'] ?? null);
            foreach ($dimensionConfiguration['presets'] as $valueName => $valueConfiguration) {
                if (!isset($valueConfiguration['values'])) {
                    continue;
                }
                $fallbackConfiguration = array_slice($valueConfiguration['values'], 0, 2);
                if (isset($fallbackConfiguration[1])) {
                    if ($presetDimension->getValue($fallbackConfiguration[1])) {
                        $fallbackValue = $presetDimension->getValue($fallbackConfiguration[1]);
                    } else {
                        throw new Exception\InvalidDimensionConfigurationException('Unknown fallback value ' . $fallbackConfiguration[1] . ' was for defined for value ' . $fallbackConfiguration[0], 1487617770);
                    }
                } else {
                    $fallbackValue = null;
                }
                $presetDimension->createValue($fallbackConfiguration[0], $fallbackValue);
            }
            $prioritizedContentDimensions[] = $presetDimension;
        }
    }

    /**
     * @param array|IntraDimension\ContentDimension[] $prioritizedContentDimensions
     */
    protected function populateInterDimensionalFallbackGraph(array $prioritizedContentDimensions)
    {
        $this->interDimensionalFallbackGraph = new InterDimension\InterDimensionalFallbackGraph($prioritizedContentDimensions);

        $dimensionValueCombinations = [[]];
        foreach ($prioritizedContentDimensions as $contentDimension) {
            $nextLevelValueCombinations = [];
            foreach ($dimensionValueCombinations as $previousCombination) {
                foreach ($contentDimension->getValues() as $value) {
                    $newCombination = $previousCombination;
                    $newCombination[$contentDimension->getName()] = $value;
                    if (!$this->contentDimensionPresetSource->isPresetCombinationAllowedByConstraints(
                        $this->translateDimensionValueCombinationToPresetCombination($newCombination)
                    )) {
                        continue;
                    }

                    $nextLevelValueCombinations[] = $newCombination;
                }
            }

            $dimensionValueCombinations = $nextLevelValueCombinations;
        }

        $edgeCount = 0;
        foreach ($dimensionValueCombinations as $dimensionValues) {
            $newContentSubgraph = $this->interDimensionalFallbackGraph->createContentSubgraph($dimensionValues);
            foreach ($this->interDimensionalFallbackGraph->getSubgraphs() as $presentContentSubgraph) {
                if ($presentContentSubgraph === $newContentSubgraph
                    || $this->interDimensionalFallbackGraph->normalizeWeight($newContentSubgraph->getWeight())
                    < $this->interDimensionalFallbackGraph->normalizeWeight($presentContentSubgraph->getWeight())
                ) {
                    continue 2;
                }
                try {
                    $this->interDimensionalFallbackGraph->connectSubgraphs($newContentSubgraph, $presentContentSubgraph);
                    $edgeCount++;
                } catch (IntraDimension\Exception\InvalidFallbackException $e) {
                    continue;
                }
            }
        }
    }

    /**
     * @param array|IntraDimension\ContentDimensionValue[] $dimensionValueCombination
     * @return array
     */
    protected function translateDimensionValueCombinationToPresetCombination(array $dimensionValueCombination)
    {
        $presetCombination = [];
        foreach ($dimensionValueCombination as $dimensionName => $dimensionValue) {
            $presetCombination[$dimensionName] = $dimensionValue->getValue();
        }

        return $presetCombination;
    }

    /**
     * @param string $subgraphIdentifier
     * @return array
     */
    public function determineAffectedVariantSubgraphIdentifiers(string $subgraphIdentifier): array
    {
        $affectedVariantIdentifiers = [$subgraphIdentifier];
        $subgraph = $this->getInterDimensionalFallbackGraph()->getSubgraph($subgraphIdentifier);
        foreach ($subgraph->getVariants() as $variantSubgraph) {
            $affectedVariantIdentifiers[] = $variantSubgraph->getIdentityHash();
        }

        return $affectedVariantIdentifiers;
    }

    /**
     * @param string $subgraphIdentifier
     * @return array
     */
    public function determineConnectedSubgraphIdentifiers(string $subgraphIdentifier): array
    {
        $subgraph = $this->getInterDimensionalFallbackGraph()->getSubgraph($subgraphIdentifier);
        while ($subgraph->getFallback()) {
            $subgraph = $subgraph->getFallback();
        }
        $connectedVariantIdentifiers = [$subgraph->getIdentityHash()];
        foreach ($subgraph->getVariants() as $variantSubgraph) {
            $connectedVariantIdentifiers[] = $variantSubgraph->getIdentityHash();
        }
        return $connectedVariantIdentifiers;
    }

    /**
     * @return IntraDimension\IntraDimensionalFallbackGraph
     * @api
     */
    public function getIntraDimensionalFallbackGraph(): IntraDimension\IntraDimensionalFallbackGraph
    {
        return $this->intraDimensionalFallbackGraph;
    }

    /**
     * @return InterDimension\InterDimensionalFallbackGraph
     * @api
     */
    public function getInterDimensionalFallbackGraph(): InterDimension\InterDimensionalFallbackGraph
    {
        return $this->interDimensionalFallbackGraph;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\ContentRepository\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * The fallback graph application service
 * 
 * To be used as a read-only source of fallback information for graph-related projectors
 * 
 * Never use this on the read side since its initialization time grows linearly
 * by the amount of possible combinations of content dimension values
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class FallbackGraphService extends FallbackGraphService_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\ContentRepository\Service\FallbackGraphService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\ContentRepository\Service\FallbackGraphService', $this);
        if ('Neos\ContentRepository\Service\FallbackGraphService' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\ContentRepository\Service\FallbackGraphService';
        if ($isSameClass) {
            $this->initializeObject(1);
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
  'contentDimensionPresetSource' => 'Neos\\ContentRepository\\Domain\\Service\\ContentDimensionPresetSourceInterface',
  'intraDimensionalFallbackGraph' => 'Neos\\ContentRepository\\Domain\\Model\\IntraDimension\\IntraDimensionalFallbackGraph',
  'interDimensionalFallbackGraph' => 'Neos\\ContentRepository\\Domain\\Model\\InterDimension\\InterDimensionalFallbackGraph',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\ContentRepository\Service\FallbackGraphService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\ContentRepository\Service\FallbackGraphService', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;

        $isSameClass = get_class($this) === 'Neos\ContentRepository\Service\FallbackGraphService';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\ContentRepository\Service\FallbackGraphService', $classParents) !== false && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== false;

        if ($isSameClass || $isClassProxy) {
            $this->initializeObject(2);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\ContentDimensionPresetSourceInterface', 'Neos\Neos\Domain\Service\ConfigurationContentDimensionPresetSource', 'contentDimensionPresetSource', '33404cce491062aa2636da962a6cf674', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\ContentDimensionPresetSourceInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'contentDimensionPresetSource',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.ContentRepository/Classes/Service/FallbackGraphService.php
#