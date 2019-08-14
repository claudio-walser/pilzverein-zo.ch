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

use Neos\ContentRepository\Domain\Projection\Content\NodeInterface;
use Neos\ContentRepository\Domain\Projection\Content\TraversableNodeInterface;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Utility\ObjectAccess;
use Neos\ContentRepository\Domain\Model\Node;

/**
 * This filter implementation contains specific behavior for use on ContentRepository
 * nodes. It will not evaluate any elements that are not instances of the
 * `NodeInterface`.
 *
 * The implementation changes the behavior of the `instanceof` operator to
 * work on node types instead of PHP object types, so that::
 *
 * 	[instanceof Neos.NodeTypes:Page]
 *
 * will in fact use `isOfType()` on the `NodeType` of context elements to
 * filter. This filter allow also to filter the current context by a given
 * node. Anything else remains unchanged.
 */
class FilterOperation_Original extends \Neos\Eel\FlowQuery\Operations\Object\FilterOperation
{
    /**
     * {@inheritdoc}
     *
     * @var integer
     */
    protected static $priority = 100;

    /**
     * {@inheritdoc}
     *
     * @param array (or array-like object) $context onto which this operation should be applied
     * @return boolean true if the operation can be applied onto the $context, false otherwise
     */
    public function canEvaluate($context)
    {
        return (isset($context[0]) && ($context[0] instanceof NodeInterface));
    }

    /**
     * {@inheritdoc}
     *
     * @param FlowQuery $flowQuery
     * @param array $arguments
     * @return void
     */
    public function evaluate(FlowQuery $flowQuery, array $arguments)
    {
        if (!isset($arguments[0]) || empty($arguments[0])) {
            return;
        }

        if ($arguments[0] instanceof NodeInterface) {
            $filteredContext = [];
            $context = $flowQuery->getContext();
            foreach ($context as $element) {
                if ($element === $arguments[0]) {
                    $filteredContext[] = $element;
                    break;
                }
            }
            $flowQuery->setContext($filteredContext);
        } else {
            parent::evaluate($flowQuery, $arguments);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param object $element
     * @param string $propertyNameFilter
     * @return boolean true if the property name filter matches
     */
    protected function matchesPropertyNameFilter($element, $propertyNameFilter)
    {
        /* @var NodeInterface $element */
        try {
            return ((string)$element->getNodeName() === $propertyNameFilter);
        } catch (\InvalidArgumentException $e) {
            // in case the Element has no valid node name, we do not match!
            return false;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param NodeInterface $element
     * @param string $identifier
     * @return boolean
     */
    protected function matchesIdentifierFilter($element, $identifier)
    {
        return (strtolower((string)$element->getNodeAggregateIdentifier()) === strtolower($identifier));
    }

    /**
     * {@inheritdoc}
     *
     * @param NodeInterface $element
     * @param string $propertyPath
     * @return mixed
     */
    protected function getPropertyPath($element, $propertyPath)
    {
        if ($propertyPath[0] === '_') {
            return ObjectAccess::getPropertyPath($element, substr($propertyPath, 1));
        } else {
            return $element->getProperty($propertyPath);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $value
     * @param string $operator
     * @param mixed $operand
     * @return boolean
     */
    protected function evaluateOperator($value, $operator, $operand)
    {
        if ($operator === 'instanceof' && $value instanceof NodeInterface) {
            if ($this->operandIsSimpleType($operand)) {
                return $this->handleSimpleTypeOperand($operand, $value);
            } elseif ($operand === NodeInterface::class || $operand === Node::class || $operand === \Neos\ContentRepository\Domain\Model\NodeInterface::class || $operand === TraversableNodeInterface::class) {
                return true;
            } else {
                $isOfType = $value->getNodeType()->isOfType($operand[0] === '!' ? substr($operand, 1) : $operand);
                return $operand[0] === '!' ? $isOfType === false : $isOfType;
            }
        } elseif ($operator === '!instanceof' && $value instanceof NodeInterface) {
            return !$this->evaluateOperator($value, 'instanceof', $operand);
        }
        return parent::evaluateOperator($value, $operator, $operand);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\ContentRepository\Eel\FlowQueryOperations;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * This filter implementation contains specific behavior for use on ContentRepository
 * nodes. It will not evaluate any elements that are not instances of the
 * `NodeInterface`.
 * 
 * The implementation changes the behavior of the `instanceof` operator to
 * work on node types instead of PHP object types, so that::
 * 
 * 	[instanceof Neos.NodeTypes:Page]
 * 
 * will in fact use `isOfType()` on the `NodeType` of context elements to
 * filter. This filter allow also to filter the current context by a given
 * node. Anything else remains unchanged.
 */
class FilterOperation extends FilterOperation_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\ContentRepository\Eel\FlowQueryOperations\FilterOperation' === get_class($this)) {
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
  'priority' => 'integer',
  'shortName' => 'string',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'persistenceManager',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.ContentRepository/Classes/Eel/FlowQueryOperations/FilterOperation.php
#