<?php 
namespace Neos\ContentRepository\Eel\FlowQueryOperations;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\NodeType\NodeTypeConstraintFactory;
use Neos\ContentRepository\Domain\Projection\Content\TraversableNodeInterface;
use Neos\ContentRepository\Domain\NodeAggregate\NodeName;
use Neos\ContentRepository\Exception\NodeException;
use Neos\Eel\FlowQuery\FizzleParser;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Eel\FlowQuery\Operations\AbstractOperation;
use Neos\Flow\Annotations as Flow;

/**
 * "children" operation working on ContentRepository nodes. It iterates over all
 * context elements and returns all child nodes or only those matching
 * the filter expression specified as optional argument.
 */
class ChildrenOperation_Original extends AbstractOperation
{
    /**
     * {@inheritdoc}
     *
     * @var string
     */
    protected static $shortName = 'children';

    /**
     * {@inheritdoc}
     *
     * @var integer
     */
    protected static $priority = 100;

    /**
     * @Flow\Inject
     * @var NodeTypeConstraintFactory
     */
    protected $nodeTypeConstraintFactory;

    /**
     * {@inheritdoc}
     *
     * @param array (or array-like object) $context onto which this operation should be applied
     * @return boolean true if the operation can be applied onto the $context, false otherwise
     */
    public function canEvaluate($context)
    {
        return isset($context[0]) && ($context[0] instanceof TraversableNodeInterface);
    }

    /**
     * {@inheritdoc}
     *
     * @param FlowQuery $flowQuery the FlowQuery object
     * @param array $arguments the arguments for this operation
     * @return void
     * @throws \Neos\Eel\FlowQuery\FizzleException
     * @throws \Neos\Eel\Exception
     */
    public function evaluate(FlowQuery $flowQuery, array $arguments)
    {
        $output = [];
        $outputNodeAggregateIdentifiers = [];
        if (isset($arguments[0]) && !empty($arguments[0])) {
            $parsedFilter = FizzleParser::parseFilterGroup($arguments[0]);
            if ($this->earlyOptimizationOfFilters($flowQuery, $parsedFilter)) {
                return;
            }
        }

        /** @var TraversableNodeInterface $contextNode */
        foreach ($flowQuery->getContext() as $contextNode) {
            foreach ($contextNode->findChildNodes() as $childNode) {
                if (!isset($outputNodeAggregateIdentifiers[(string)$childNode->getNodeAggregateIdentifier()])) {
                    $output[] = $childNode;
                    $outputNodeAggregateIdentifiers[(string)$childNode->getNodeAggregateIdentifier()] = true;
                }
            }
        }
        $flowQuery->setContext($output);

        if (isset($arguments[0]) && !empty($arguments[0])) {
            $flowQuery->pushOperation('filter', $arguments);
        }
    }

    /**
     * Optimize for typical use cases, filter by node name and filter
     * by NodeType (instanceof). These cases are now optimized and will
     * only load the nodes that match the filters.
     *
     * @param FlowQuery $flowQuery
     * @param array $parsedFilter
     * @return boolean
     * @throws \Neos\Eel\Exception
     */
    protected function earlyOptimizationOfFilters(FlowQuery $flowQuery, array $parsedFilter)
    {
        $optimized = false;
        $output = [];
        $outputNodeAggregateIdentifiers = [];
        foreach ($parsedFilter['Filters'] as $filter) {
            $instanceOfFilters = [];
            $attributeFilters = [];
            if (isset($filter['AttributeFilters'])) {
                foreach ($filter['AttributeFilters'] as $attributeFilter) {
                    if ($attributeFilter['Operator'] === 'instanceof' && $attributeFilter['Identifier'] === null) {
                        $instanceOfFilters[] = $attributeFilter;
                    } else {
                        $attributeFilters[] = $attributeFilter;
                    }
                }
            }

            // Only apply optimization if there's a property name filter or a instanceof filter or another filter already did optimization
            if ((isset($filter['PropertyNameFilter']) || isset($filter['PathFilter'])) || count($instanceOfFilters) > 0 || $optimized === true) {
                $optimized = true;
                $filteredOutput = [];
                $filteredOutputNodeIdentifiers = [];
                // Optimize property name filter if present
                if (isset($filter['PropertyNameFilter']) || isset($filter['PathFilter'])) {
                    $nodePath = isset($filter['PropertyNameFilter']) ? $filter['PropertyNameFilter'] : $filter['PathFilter'];
                    $nodePathSegments = explode('/', $nodePath);
                    /** @var TraversableNodeInterface $contextNode */
                    foreach ($flowQuery->getContext() as $contextNode) {
                        $currentPathSegments = $nodePathSegments;
                        $resolvedNode = $contextNode;
                        while (($nodePathSegment = array_shift($currentPathSegments)) && !is_null($resolvedNode)) {
                            try {
                                $resolvedNode = $resolvedNode->findNamedChildNode(NodeName::fromString($nodePathSegment));
                            } catch (NodeException $exception) {
                                $resolvedNode = null;
                            }
                        }

                        if (!is_null($resolvedNode) && !isset($filteredOutputNodeIdentifiers[(string)$resolvedNode->getNodeAggregateIdentifier()])) {
                            $filteredOutput[] = $resolvedNode;
                            $filteredOutputNodeIdentifiers[(string)$resolvedNode->getNodeAggregateIdentifier()] = true;
                        }
                    }
                } elseif (count($instanceOfFilters) > 0) {
                    // Optimize node type filter if present
                    $allowedNodeTypes = array_map(function ($instanceOfFilter) {
                        return $instanceOfFilter['Operand'];
                    }, $instanceOfFilters);
                    /** @var TraversableNodeInterface $contextNode */
                    foreach ($flowQuery->getContext() as $contextNode) {
                        /** @var TraversableNodeInterface $childNode */
                        foreach ($contextNode->findChildNodes($this->nodeTypeConstraintFactory->parseFilterString(implode($allowedNodeTypes, ','))) as $childNode) {
                            if (!isset($filteredOutputNodeIdentifiers[(string)$childNode->getNodeAggregateIdentifier()])) {
                                $filteredOutput[] = $childNode;
                                $filteredOutputNodeIdentifiers[(string)$childNode->getNodeAggregateIdentifier()] = true;
                            }
                        }
                    }
                }

                // Apply attribute filters if present
                if (isset($filter['AttributeFilters'])) {
                    $attributeFilters = array_reduce($filter['AttributeFilters'], function ($filters, $attributeFilter) {
                        return $filters . $attributeFilter['text'];
                    });
                    $filteredFlowQuery = new FlowQuery($filteredOutput);
                    $filteredFlowQuery->pushOperation('filter', [$attributeFilters]);
                    $filteredOutput = $filteredFlowQuery->get();
                }

                // Add filtered nodes to output
                foreach ($filteredOutput as $filteredNode) {
                    /** @var TraversableNodeInterface $filteredNode */
                    if (!isset($outputNodeAggregateIdentifiers[(string)$filteredNode->getNodeAggregateIdentifier()])) {
                        $output[] = $filteredNode;
                    }
                }
            }
        }

        if ($optimized === true) {
            $flowQuery->setContext($output);
        }

        return $optimized;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\ContentRepository\Eel\FlowQueryOperations;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * "children" operation working on ContentRepository nodes. It iterates over all
 * context elements and returns all child nodes or only those matching
 * the filter expression specified as optional argument.
 */
class ChildrenOperation extends ChildrenOperation_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\ContentRepository\Eel\FlowQueryOperations\ChildrenOperation' === get_class($this)) {
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
  'shortName' => 'string',
  'priority' => 'integer',
  'nodeTypeConstraintFactory' => 'Neos\\ContentRepository\\Domain\\NodeType\\NodeTypeConstraintFactory',
  'final' => 'boolean',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\NodeType\NodeTypeConstraintFactory', 'Neos\ContentRepository\Domain\NodeType\NodeTypeConstraintFactory', 'nodeTypeConstraintFactory', '29b2ed34631ed7e636b598b2e2aa35e6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\NodeType\NodeTypeConstraintFactory'); });
        $this->Flow_Injected_Properties = array (
  0 => 'nodeTypeConstraintFactory',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.ContentRepository/Classes/Eel/FlowQueryOperations/ChildrenOperation.php
#