<?php 
namespace Neos\ContentRepository\Domain\Model;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\NodeAggregate\NodeName;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Utility\ObjectAccess;
use Neos\Utility\Arrays;
use Neos\Utility\PositionalArraySorter;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\ContentRepository\Exception\InvalidNodeTypePostprocessorException;
use Neos\ContentRepository\NodeTypePostprocessor\NodeTypePostprocessorInterface;
use Neos\ContentRepository\Utility;

/**
 * A Node Type
 *
 * Although methods contained in this class belong to the public API, you should
 * not need to deal with creating or managing node types manually. New node types
 * should be defined in a NodeTypes.yaml file.
 *
 * @api
 */
class NodeType_Original
{
    /**
     * Name of this node type. Example: "ContentRepository:Folder"
     *
     * @var string
     */
    protected $name;

    /**
     * Configuration for this node type, can be an arbitrarily nested array. Does not include inherited configuration.
     *
     * @var array
     */
    protected $localConfiguration;

    /**
     * Full configuration for this node type, can be an arbitrarily nested array. Includes any inherited configuration.
     *
     * @var array
     */
    protected $fullConfiguration;

    /**
     * Is this node type marked abstract
     *
     * @var boolean
     */
    protected $abstract = false;

    /**
     * Is this node type marked final
     *
     * @var boolean
     */
    protected $final = false;

    /**
     * node types this node type directly inherits from
     *
     * @var array<\Neos\ContentRepository\Domain\Model\NodeType>
     */
    protected $declaredSuperTypes;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @var NodeLabelGeneratorInterface
     */
    protected $nodeLabelGenerator;

    /**
     * Whether or not this node type has been initialized (e.g. if it has been postprocessed)
     *
     * @var boolean
     */
    protected $initialized = false;

    /**
     * Constructs this node type
     *
     * @param string $name Name of the node type
     * @param array $declaredSuperTypes Parent types of this node type
     * @param array $configuration the configuration for this node type which is defined in the schema
     * @throws \InvalidArgumentException
     */
    public function __construct($name, array $declaredSuperTypes, array $configuration, array $fullConfiguration = null)
    {
        $this->name = $name;

        foreach ($declaredSuperTypes as $type) {
            if ($type !== null && !$type instanceof NodeType) {
                throw new \InvalidArgumentException('$declaredSuperTypes must be an array of NodeType objects', 1291300950);
            }
        }
        $this->declaredSuperTypes = $declaredSuperTypes;

        if (isset($configuration['abstract']) && $configuration['abstract'] === true) {
            $this->abstract = true;
            unset($configuration['abstract']);
        }

        if (isset($configuration['final']) && $configuration['final'] === true) {
            $this->final = true;
            unset($configuration['final']);
        }

        $this->localConfiguration = $configuration;

        $this->fullConfiguration = $fullConfiguration;
        if ($fullConfiguration !== null) {
            $this->initialized = true;
        }
    }

    /**
     * Initializes this node type
     *
     * @return void
     */
    protected function initialize()
    {
        if ($this->initialized === true) {
            return;
        }
        $this->initialized = true;
        $this->buildFullConfiguration();
        $this->applyPostprocessing();
    }

    /**
     * Builds the full configuration by merging configuration from the supertypes into the local configuration.
     *
     * @return void
     */
    protected function buildFullConfiguration()
    {
        $mergedConfiguration = [];
        $applicableSuperTypes = static::getFlattenedSuperTypes($this);
        foreach ($applicableSuperTypes as $key => $superType) {
            $mergedConfiguration = Arrays::arrayMergeRecursiveOverrule($mergedConfiguration, $superType->getLocalConfiguration());
        }
        $this->fullConfiguration = Arrays::arrayMergeRecursiveOverrule($mergedConfiguration, $this->localConfiguration);

        if (isset($this->fullConfiguration['childNodes']) && is_array($this->fullConfiguration['childNodes']) && $this->fullConfiguration['childNodes'] !== []) {
            $sorter = new PositionalArraySorter($this->fullConfiguration['childNodes']);
            $this->fullConfiguration['childNodes'] = $sorter->toArray();
        }
    }

    /**
     * Returns a flat list of super types to inherit from.
     *
     * @param NodeType $nodeType
     *
     * @return array
     */
    protected static function getFlattenedSuperTypes(NodeType $nodeType) : array
    {
        $flattenedSuperTypes = [];
        foreach ($nodeType->declaredSuperTypes as $superTypeName => $superType) {
            if ($superType !== null) {
                $flattenedSuperTypes += static::getFlattenedSuperTypes($superType);
                $flattenedSuperTypes[$superTypeName] = $superType;
            }
        }

        foreach ($nodeType->declaredSuperTypes as $superTypeName => $superType) {
            if ($superType === null) {
                unset($flattenedSuperTypes[$superTypeName]);
            }
        }

        return $flattenedSuperTypes;
    }

    /**
     * Iterates through configured postprocessors and invokes them
     *
     * @return void
     * @throws InvalidNodeTypePostprocessorException
     */
    protected function applyPostprocessing()
    {
        if (!isset($this->fullConfiguration['postprocessors'])) {
            return;
        }
        foreach ($this->fullConfiguration['postprocessors'] as $postprocessorConfiguration) {
            $postprocessor = new $postprocessorConfiguration['postprocessor']();
            if (!$postprocessor instanceof NodeTypePostprocessorInterface) {
                throw new InvalidNodeTypePostprocessorException(sprintf('Expected NodeTypePostprocessorInterface but got "%s"', get_class($postprocessor)), 1364759955);
            }
            $postprocessorOptions = [];
            if (isset($postprocessorConfiguration['postprocessorOptions'])) {
                $postprocessorOptions = $postprocessorConfiguration['postprocessorOptions'];
            }
            $postprocessor->process($this, $this->fullConfiguration, $postprocessorOptions);
        }
    }

    /**
     * Returns the name of this node type
     *
     * @return string
     * @api
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return boolean true if marked abstract
     *
     * @return boolean
     */
    public function isAbstract()
    {
        return $this->abstract;
    }

    /**
     * Return boolean true if marked final
     *
     * @return boolean
     */
    public function isFinal()
    {
        return $this->final;
    }

    /**
     * Returns the direct, explicitly declared super types
     * of this node type.
     *
     * Note: NULL values are skipped since they are used only internally.
     *
     * @return array<NodeType>
     * @api
     */
    public function getDeclaredSuperTypes()
    {
        return array_filter($this->declaredSuperTypes, function ($value) {
            return $value !== null;
        });
    }

    /**
     * Returns whether this node type (or any parent type) is an *aggregate*.
     *
     * The most prominent *aggregate* is a Document and everything which inherits from it, like a Page.
     *
     * If a node type is marked as aggregate, it means that:
     *
     * - the node type can "live on its own", i.e. can be part of an external URL
     * - when moving this node, all node variants are also moved (across all dimensions)
     * - Recursive copying only happens *inside* this aggregate, and stops at nested aggregates.
     *
     * @return boolean true if the node type is an aggregate
     * @api
     */
    public function isAggregate()
    {
        return $this->getConfiguration('aggregate') === true;
    }

    /**
     * If this node type or any of the direct or indirect super types
     * has the given name.
     *
     * @param string $nodeType
     * @return boolean true if this node type is of the given kind, otherwise false
     * @api
     */
    public function isOfType($nodeType)
    {
        if ($nodeType === $this->name) {
            return true;
        }
        if (array_key_exists($nodeType, $this->declaredSuperTypes) && $this->declaredSuperTypes[$nodeType] === null) {
            return false;
        }
        foreach ($this->declaredSuperTypes as $superType) {
            if ($superType !== null && $superType->isOfType($nodeType) === true) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the local configuration of the node type. Should only be used internally.
     *
     * Note: post processing is not applied to this.
     *
     * @return array
     */
    public function getLocalConfiguration()
    {
        return $this->localConfiguration;
    }

    /**
     * Get the full configuration of the node type. Should only be used internally.
     *
     * Instead, use the hasConfiguration()/getConfiguration() methods to check/retrieve single configuration values.
     *
     * @return array
     */
    public function getFullConfiguration()
    {
        $this->initialize();
        return $this->fullConfiguration;
    }

    /**
     * Checks if the configuration of this node type contains a setting for the given $configurationPath
     *
     * @param string $configurationPath The name of the configuration option to verify
     * @return boolean
     * @api
     */
    public function hasConfiguration($configurationPath)
    {
        return $this->getConfiguration($configurationPath) !== null;
    }

    /**
     * Returns the configuration option with the specified $configurationPath or NULL if it does not exist
     *
     * @param string $configurationPath The name of the configuration option to retrieve
     * @return mixed
     * @api
     */
    public function getConfiguration($configurationPath)
    {
        $this->initialize();
        return ObjectAccess::getPropertyPath($this->fullConfiguration, $configurationPath);
    }

    /**
     * Get the human-readable label of this node type
     *
     * @return string
     * @api
     */
    public function getLabel()
    {
        $this->initialize();
        return isset($this->fullConfiguration['ui']['label']) ? $this->fullConfiguration['ui']['label'] : '';
    }

    /**
     * Get additional options (if specified)
     *
     * @return array
     * @api
     */
    public function getOptions()
    {
        $this->initialize();
        return (isset($this->fullConfiguration['options']) ? $this->fullConfiguration['options'] : []);
    }

    /**
     * Return the node label generator class for the given node
     *
     * @return NodeLabelGeneratorInterface
     */
    public function getNodeLabelGenerator()
    {
        $this->initialize();

        if ($this->nodeLabelGenerator === null) {
            if ($this->hasConfiguration('label.generatorClass')) {
                $nodeLabelGenerator = $this->objectManager->get($this->getConfiguration('label.generatorClass'));
            } elseif ($this->hasConfiguration('label') && is_string($this->getConfiguration('label'))) {
                $nodeLabelGenerator = $this->objectManager->get(ExpressionBasedNodeLabelGenerator::class);
                $nodeLabelGenerator->setExpression($this->getConfiguration('label'));
            } else {
                $nodeLabelGenerator = $this->objectManager->get(NodeLabelGeneratorInterface::class);
            }

            $this->nodeLabelGenerator = $nodeLabelGenerator;
        }

        return $this->nodeLabelGenerator;
    }

    /**
     * Return the array with the defined properties. The key is the property name,
     * the value the property configuration. There are no guarantees on how the
     * property configuration looks like.
     *
     * @return array
     * @api
     */
    public function getProperties()
    {
        $this->initialize();
        return (isset($this->fullConfiguration['properties']) ? $this->fullConfiguration['properties'] : []);
    }

    /**
     * Returns the configured type of the specified property
     *
     * @param string $propertyName Name of the property
     * @return string
     */
    public function getPropertyType($propertyName)
    {
        if (!isset($this->fullConfiguration['properties']) || !isset($this->fullConfiguration['properties'][$propertyName]) || !isset($this->fullConfiguration['properties'][$propertyName]['type'])) {
            return 'string';
        }
        return $this->fullConfiguration['properties'][$propertyName]['type'];
    }

    /**
     * Return an array with the defined default values for each property, if any.
     *
     * The default value is configured for each property under the "default" key.
     *
     * @return array
     * @api
     */
    public function getDefaultValuesForProperties()
    {
        $this->initialize();
        if (!isset($this->fullConfiguration['properties'])) {
            return [];
        }

        $defaultValues = [];
        foreach ($this->fullConfiguration['properties'] as $propertyName => $propertyConfiguration) {
            if (isset($propertyConfiguration['defaultValue'])) {
                $type = isset($propertyConfiguration['type']) ? $propertyConfiguration['type'] : '';
                switch ($type) {
                    case 'DateTime':
                        $defaultValues[$propertyName] = new \DateTime($propertyConfiguration['defaultValue']);
                    break;
                    default:
                        $defaultValues[$propertyName] = $propertyConfiguration['defaultValue'];
                }
            }
        }

        return $defaultValues;
    }

    /**
     * Return an array with child nodes which should be automatically created
     *
     * @return self[] the key of this array is the name of the child, and the value its NodeType.
     * @api
     */
    public function getAutoCreatedChildNodes()
    {
        $this->initialize();
        if (!isset($this->fullConfiguration['childNodes'])) {
            return [];
        }

        $autoCreatedChildNodes = [];
        foreach ($this->fullConfiguration['childNodes'] as $childNodeName => $childNodeConfiguration) {
            if (isset($childNodeConfiguration['type'])) {
                $autoCreatedChildNodes[Utility::renderValidNodeName($childNodeName)] = $this->nodeTypeManager->getNodeType($childNodeConfiguration['type']);
            }
        }

        return $autoCreatedChildNodes;
    }

    /**
     * @param NodeName $nodeName
     * @return bool true if $nodeName is an autocreated child node, false otherwise
     */
    public function hasAutoCreatedChildNode(NodeName $nodeName): bool
    {
        return isset($this->fullConfiguration['childNodes'][(string)$nodeName]);
    }

    /**
     * @param NodeName $nodeName
     * @return NodeType|null
     * @throws \Neos\ContentRepository\Exception\NodeTypeNotFoundException
     */
    public function getTypeOfAutoCreatedChildNode(NodeName $nodeName): ?NodeType
    {
        return isset($this->fullConfiguration['childNodes'][(string)$nodeName]['type'])
            ? $this->nodeTypeManager->getNodeType($this->fullConfiguration['childNodes'][(string)$nodeName]['type'])
            : null;
    }


    /**
     * Checks if the given NodeType is acceptable as sub-node with the configured constraints,
     * not taking constraints of auto-created nodes into account. Thus, this method only returns
     * the correct result if called on NON-AUTO-CREATED nodes!
     *
     * Otherwise, allowsGrandchildNodeType() needs to be called on the *parent node type*.
     *
     * @param NodeType $nodeType
     * @return boolean true if the $nodeType is allowed as child node, false otherwise.
     */
    public function allowsChildNodeType(NodeType $nodeType)
    {
        $constraints = $this->getConfiguration('constraints.nodeTypes') ?: [];
        return $this->isNodeTypeAllowedByConstraints($nodeType, $constraints);
    }

    /**
     * Checks if the given $nodeType is allowed as a childNode of the given $childNodeName
     * (which must be auto-created in $this NodeType).
     *
     * Only allowed to be called if $childNodeName is auto-created.
     *
     * @param string $childNodeName The name of a configured childNode of this NodeType
     * @param NodeType $nodeType The NodeType to check constraints for.
     * @return boolean true if the $nodeType is allowed as grandchild node, false otherwise.
     * @throws \InvalidArgumentException If the given $childNodeName is not configured to be auto-created in $this.
     */
    public function allowsGrandchildNodeType($childNodeName, NodeType $nodeType)
    {
        $autoCreatedChildNodes = $this->getAutoCreatedChildNodes();
        if (!isset($autoCreatedChildNodes[$childNodeName])) {
            throw new \InvalidArgumentException('The method "allowsGrandchildNodeType" can only be used on auto-created childNodes, given $childNodeName "' . $childNodeName . '" is not auto-created.', 1403858395);
        }
        $constraints = $autoCreatedChildNodes[$childNodeName]->getConfiguration('constraints.nodeTypes') ?: [];

        $childNodeConfiguration = [];
        foreach ($this->getConfiguration('childNodes') as $name => $configuration) {
            $childNodeConfiguration[Utility::renderValidNodeName($name)] = $configuration;
        }
        $childNodeConstraintConfiguration = ObjectAccess::getPropertyPath($childNodeConfiguration, $childNodeName . '.constraints.nodeTypes') ?: [];

        $constraints = Arrays::arrayMergeRecursiveOverrule($constraints, $childNodeConstraintConfiguration);

        return $this->isNodeTypeAllowedByConstraints($nodeType, $constraints);
    }

    /**
     * Internal method to check whether the passed-in $nodeType is allowed by the $constraints array.
     *
     * $constraints is an associative array where the key is the Node Type Name. If the value is "true",
     * the node type is explicitly allowed. If the value is "false", the node type is explicitly denied.
     * If nothing is specified, the fallback "*" is used. If that one is also not specified, we DENY by
     * default.
     *
     * Super types of the given node types are also checked, so if a super type is constrained
     * it will also take affect on the inherited node types. The closest constrained super type match is used.
     *
     * @param NodeType $nodeType
     * @param array $constraints
     * @return boolean
     */
    protected function isNodeTypeAllowedByConstraints(NodeType $nodeType, array $constraints)
    {
        $directConstraintsResult = $this->isNodeTypeAllowedByDirectConstraints($nodeType, $constraints);
        if ($directConstraintsResult !== null) {
            return $directConstraintsResult;
        }

        $inheritanceConstraintsResult = $this->isNodeTypeAllowedByInheritanceConstraints($nodeType, $constraints);
        if ($inheritanceConstraintsResult !== null) {
            return $inheritanceConstraintsResult;
        }

        if (isset($constraints['*'])) {
            return (boolean)$constraints['*'];
        }

        return false;
    }

    /**
     * @param NodeType $nodeType
     * @param array $constraints
     * @return boolean true if the passed $nodeType is allowed by the $constraints
     */
    protected function isNodeTypeAllowedByDirectConstraints(NodeType $nodeType, array $constraints)
    {
        if ($constraints === []) {
            return true;
        }

        if (array_key_exists($nodeType->getName(), $constraints) && $constraints[$nodeType->getName()] === true) {
            return true;
        }

        if (array_key_exists($nodeType->getName(), $constraints) && $constraints[$nodeType->getName()] === false) {
            return false;
        }

        return null;
    }

    /**
     * This method loops over the constraints and finds node types that the given node type inherits from. For all
     * matched super types, their super types are traversed to find the closest super node with a constraint which
     * is used to evaluated if the node type is allowed. It finds the closest results for true and false, and uses
     * the distance to choose which one wins (lowest). If no result is found the node type is allowed.
     *
     * @param NodeType $nodeType
     * @param array $constraints
     * @return boolean|NULL if no constraint matched
     */
    protected function isNodeTypeAllowedByInheritanceConstraints(NodeType $nodeType, array $constraints)
    {
        $constraintDistanceForTrue = null;
        $constraintDistanceForFalse = null;
        foreach ($constraints as $superType => $constraint) {
            if ($nodeType->isOfType($superType)) {
                $distance = $this->traverseSuperTypes($nodeType, $superType, 0);

                if ($constraint === true && ($constraintDistanceForTrue === null || $constraintDistanceForTrue > $distance)) {
                    $constraintDistanceForTrue = $distance;
                }
                if ($constraint === false && ($constraintDistanceForFalse === null || $constraintDistanceForFalse > $distance)) {
                    $constraintDistanceForFalse = $distance;
                }
            }
        }

        if ($constraintDistanceForTrue !== null && $constraintDistanceForFalse !== null) {
            return $constraintDistanceForTrue < $constraintDistanceForFalse ? true : false;
        }

        if ($constraintDistanceForFalse !== null) {
            return false;
        }

        if ($constraintDistanceForTrue !== null) {
            return true;
        }

        return null;
    }

    /**
     * This method traverses the given node type to find the first super type that matches the constraint node type.
     * In case the hierarchy has more than one way of finding a path to the node type it's not taken into account,
     * since the first matched is returned. This is accepted on purpose for performance reasons and due to the fact
     * that such hierarchies should be avoided.
     *
     * @param NodeType $currentNodeType
     * @param string $constraintNodeTypeName
     * @param integer $distance
     * @return integer or NULL if no NodeType matched
     */
    protected function traverseSuperTypes(NodeType $currentNodeType, $constraintNodeTypeName, $distance)
    {
        if ($currentNodeType->getName() === $constraintNodeTypeName) {
            return $distance;
        }

        $distance++;
        foreach ($currentNodeType->getDeclaredSuperTypes() as $superType) {
            $result = $this->traverseSuperTypes($superType, $constraintNodeTypeName, $distance);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Alias for getName().
     *
     * @return string
     * @api
     */
    public function __toString()
    {
        return $this->getName();
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\ContentRepository\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A Node Type
 * 
 * Although methods contained in this class belong to the public API, you should
 * not need to deal with creating or managing node types manually. New node types
 * should be defined in a NodeTypes.yaml file.
 */
class NodeType extends NodeType_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     * @param string $name Name of the node type
     * @param array $declaredSuperTypes Parent types of this node type
     * @param array $configuration the configuration for this node type which is defined in the schema
     * @throws \InvalidArgumentException
     */
    public function __construct()
    {
        $arguments = func_get_args();

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'])) {
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $name in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $declaredSuperTypes in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(2, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $configuration in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'] = true;
            try {
            
                $methodArguments = [];

                if (array_key_exists(0, $arguments)) $methodArguments['name'] = $arguments[0];
                if (array_key_exists(1, $arguments)) $methodArguments['declaredSuperTypes'] = $arguments[1];
                if (array_key_exists(2, $arguments)) $methodArguments['configuration'] = $arguments[2];
                if (array_key_exists(3, $arguments)) $methodArguments['fullConfiguration'] = $arguments[3];
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('__construct');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\ContentRepository\Domain\Model\NodeType', '__construct', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
            return;
        }
        if ('Neos\ContentRepository\Domain\Model\NodeType' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }
    }

    /**
     * Autogenerated Proxy Method
     */
    protected function Flow_Aop_Proxy_buildMethodsAndAdvicesArray()
    {
        if (method_exists(get_parent_class(), 'Flow_Aop_Proxy_buildMethodsAndAdvicesArray') && is_callable('parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray')) parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        $objectManager = \Neos\Flow\Core\Bootstrap::$staticObjectManager;
        $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array(
            '__construct' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Neos\Aspects\NodeTypeConfigurationEnrichmentAspect', 'enrichNodeTypeConfiguration', $objectManager, NULL),
                ),
            ),
        );
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __clone()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
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
  'name' => 'string',
  'localConfiguration' => 'array',
  'fullConfiguration' => 'array',
  'abstract' => 'boolean',
  'final' => 'boolean',
  'declaredSuperTypes' => 'array<\\Neos\\ContentRepository\\Domain\\Model\\NodeType>',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'nodeTypeManager' => 'Neos\\ContentRepository\\Domain\\Service\\NodeTypeManager',
  'nodeLabelGenerator' => 'Neos\\ContentRepository\\Domain\\Model\\NodeLabelGeneratorInterface',
  'initialized' => 'boolean',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\NodeTypeManager', 'Neos\ContentRepository\Domain\Service\NodeTypeManager', 'nodeTypeManager', 'e525e2ecb65f7f9731d6537ddecd16d4', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\NodeTypeManager'); });
        $this->Flow_Injected_Properties = array (
  0 => 'objectManager',
  1 => 'nodeTypeManager',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.ContentRepository/Classes/Domain/Model/NodeType.php
#