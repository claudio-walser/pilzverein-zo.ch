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
use Neos\ContentRepository\Domain\Model\NodeType;

/**
 * Renders the Node Type Schema in a format the User Interface understands; additionally pre-calculating node constraints
 *
 * @Flow\Scope("singleton")
 */
class NodeTypeSchemaBuilder_Original
{
    /**
     * @Flow\Inject
     * @var \Neos\ContentRepository\Domain\Service\NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * The preprocessed node type schema contains everything we need for the UI:
     *
     * - "nodeTypes" contains the original (merged) node type schema
     * - "inheritanceMap.subTypes" contains for every parent type the transitive list of subtypes
     * - "constraints" contains for each node type, the list of allowed child node types; normalizing
     *   whitelists and blacklists:
     *   - [node type]
     *     - nodeTypes:
     *       [child node type name]: true
     *     - childNodes:
     *       - [child node name]
     *         - nodeTypes:
     *          [child node type name]: true
     *
     * @return array the node type schema ready to be used by the JavaScript code
     */
    public function generateNodeTypeSchema()
    {
        $schema = [
            'inheritanceMap' => [
                'subTypes' => []
            ],
            'nodeTypes' => [],
            'constraints' => $this->generateConstraints()
        ];

        $nodeTypes = $this->nodeTypeManager->getNodeTypes(true);
        /** @var NodeType $nodeType */
        foreach ($nodeTypes as $nodeTypeName => $nodeType) {
            if ($nodeType->isAbstract() === false) {
                $configuration = $nodeType->getFullConfiguration();
                $this->flattenAlohaFormatOptions($configuration);
                $schema['nodeTypes'][$nodeTypeName] = $configuration;
                $schema['nodeTypes'][$nodeTypeName]['label'] = $nodeType->getLabel();
            }

            $schema['inheritanceMap']['subTypes'][$nodeTypeName] = [];
            foreach ($this->nodeTypeManager->getSubNodeTypes($nodeType->getName(), true) as $subNodeType) {
                /** @var NodeType $subNodeType */
                $schema['inheritanceMap']['subTypes'][$nodeTypeName][] = $subNodeType->getName();
            }
        }

        return $schema;
    }

    /**
     * In order to allow unsetting options via the YAML settings merging, the
     * formatting options can be set via 'option': true, however, the frontend
     * schema expects a flattened plain numeric array. This methods adjust the setting
     * accordingly.
     *
     * @param array $options The options array, passed by reference
     * @return void
     */
    protected function flattenAlohaFormatOptions(array &$options)
    {
        if (isset($options['properties'])) {
            foreach (array_keys($options['properties']) as $propertyName) {
                if (isset($options['properties'][$propertyName]['ui']['aloha'])) {
                    foreach ($options['properties'][$propertyName]['ui']['aloha'] as $formatGroup => $settings) {
                        if (!is_array($settings) || in_array($formatGroup, ['formatlesspaste'])) {
                            continue;
                        }
                        $flattenedSettings = [];
                        foreach ($settings as $key => $option) {
                            if (is_numeric($key) && is_string($option)) {
                                $flattenedSettings[] = $option;
                            } elseif ($option === true) {
                                $flattenedSettings[] = $key;
                            }
                        }
                        $options['properties'][$propertyName]['ui']['aloha'][$formatGroup] = $flattenedSettings;
                    }
                }
            }
        }
    }

    /**
     * Generate the list of allowed sub-node-types per parent-node-type and child-node-name.
     *
     * @return array constraints
     */
    protected function generateConstraints()
    {
        $constraints = [];
        $nodeTypes = $this->nodeTypeManager->getNodeTypes(true);
        /** @var NodeType $nodeType */
        foreach ($nodeTypes as $nodeTypeName => $nodeType) {
            $constraints[$nodeTypeName] = [
                'nodeTypes' => [],
                'childNodes' => []
            ];
            foreach ($nodeTypes as $innerNodeTypeName => $innerNodeType) {
                if ($nodeType->allowsChildNodeType($innerNodeType)) {
                    $constraints[$nodeTypeName]['nodeTypes'][$innerNodeTypeName] = true;
                }
            }

            foreach ($nodeType->getAutoCreatedChildNodes() as $key => $_x) {
                foreach ($nodeTypes as $innerNodeTypeName => $innerNodeType) {
                    if ($nodeType->allowsGrandchildNodeType($key, $innerNodeType)) {
                        $constraints[$nodeTypeName]['childNodes'][$key]['nodeTypes'][$innerNodeTypeName] = true;
                    }
                }
            }
        }

        return $constraints;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Renders the Node Type Schema in a format the User Interface understands; additionally pre-calculating node constraints
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class NodeTypeSchemaBuilder extends NodeTypeSchemaBuilder_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Neos\Service\NodeTypeSchemaBuilder') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Service\NodeTypeSchemaBuilder', $this);
        if ('Neos\Neos\Service\NodeTypeSchemaBuilder' === get_class($this)) {
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
  'nodeTypeManager' => '\\Neos\\ContentRepository\\Domain\\Service\\NodeTypeManager',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Neos\Service\NodeTypeSchemaBuilder') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Service\NodeTypeSchemaBuilder', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\NodeTypeManager', 'Neos\ContentRepository\Domain\Service\NodeTypeManager', 'nodeTypeManager', 'e525e2ecb65f7f9731d6537ddecd16d4', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\NodeTypeManager'); });
        $this->Flow_Injected_Properties = array (
  0 => 'nodeTypeManager',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Service/NodeTypeSchemaBuilder.php
#