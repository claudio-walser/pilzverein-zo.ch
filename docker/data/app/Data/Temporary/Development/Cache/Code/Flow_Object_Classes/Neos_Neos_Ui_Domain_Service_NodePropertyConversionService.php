<?php 
namespace Neos\Neos\Ui\Domain\Service;

/*
 * This file is part of the Neos.Neos.Ui package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Service\Context;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\MvcPropertyMappingConfiguration;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Property\PropertyMapper;
use Neos\Flow\Property\TypeConverter\PersistentObjectConverter;
use Neos\Utility\Exception\InvalidTypeException;
use Neos\Utility\TypeHandling;

/**
 * @Flow\Scope("singleton")
 */
class NodePropertyConversionService_Original
{

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\Inject
     * @var PropertyMapper
     */
    protected $propertyMapper;

    /**
     * Convert raw property values to the correct type according to a node type configuration
     *
     * @param NodeType $nodeType
     * @param string $propertyName
     * @param string $rawValue
     * @param Context $context
     * @return mixed
     */
    public function convert(NodeType $nodeType, $propertyName, $rawValue, Context $context)
    {
        $propertyType = $nodeType->getPropertyType($propertyName);

        switch ($propertyType) {
            case 'string':
                return $rawValue;

            case 'reference':
                return $this->convertReference($rawValue, $context);

            case 'references':
                return $this->convertReferences($rawValue, $context);

            case 'DateTime':
                return $this->convertDateTime($rawValue);

            case 'integer':
                return $this->convertInteger($rawValue);

            case 'boolean':
                return $this->convertBoolean($rawValue);

            case 'array':
                return $this->convertArray($rawValue);

            default:
                $innerType = $propertyType;
                if ($propertyType !== null) {
                    try {
                        $parsedType = TypeHandling::parseType($propertyType);
                        $innerType = $parsedType['elementType'] ?: $parsedType['type'];
                    } catch (InvalidTypeException $exception) {
                    }
                }

                if ((is_string($rawValue) || is_array($rawValue)) && $this->objectManager->isRegistered($innerType) && $rawValue !== '') {
                    $propertyMappingConfiguration = new MvcPropertyMappingConfiguration();
                    $propertyMappingConfiguration->allowOverrideTargetType();
                    $propertyMappingConfiguration->allowAllProperties();
                    $propertyMappingConfiguration->skipUnknownProperties();
                    $propertyMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, true);
                    $propertyMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, true);

                    return $this->propertyMapper->convert($rawValue, $propertyType, $propertyMappingConfiguration);
                } else {
                    return $rawValue;
                }
        }
    }

    /**
     * Convert raw value to reference
     *
     * @param string $rawValue
     * @param Context $context
     * @return NodeInterface
     */
    protected function convertReference($rawValue, Context $context)
    {
        return $context->getNodeByIdentifier($rawValue);
    }

    /**
     * Convert raw value to references
     *
     * @param string $rawValue
     * @param Context $context
     * @return array<NodeInterface>
     */
    protected function convertReferences($rawValue, Context $context)
    {
        $nodeIdentifiers = $rawValue;
        $result = [];

        if (is_array($nodeIdentifiers)) {
            foreach ($nodeIdentifiers as $nodeIdentifier) {
                $referencedNode = $context->getNodeByIdentifier($nodeIdentifier);
                if ($referencedNode !== null) {
                    $result[] = $referencedNode;
                }
            }
        }

        return $result;
    }

    /**
     * Convert raw value to \DateTime
     *
     * @param string $rawValue
     * @return \DateTime|null
     */
    protected function convertDateTime($rawValue)
    {
        if ($rawValue !== '') {
            $result = \DateTime::createFromFormat(\DateTime::W3C, $rawValue);
            $result->setTimezone(new \DateTimeZone(date_default_timezone_get()));

            return $result;
        }
    }

    /**
     * Convert raw value to integer
     *
     * @param mixed $rawValue
     * @return integer
     */
    protected function convertInteger($rawValue)
    {
        return (int)$rawValue;
    }

    /**
     * Convert raw value to boolean
     *
     * @param mixed $rawValue
     * @return boolean
     */
    protected function convertBoolean($rawValue)
    {
        if (is_string($rawValue) && strtolower($rawValue) === 'false') {
            return false;
        }

        return (bool)$rawValue;
    }

    /**
     * Convert raw value to array
     *
     * @param string|array $rawValue
     * @return array
     */
    protected function convertArray($rawValue)
    {
        if (is_string($rawValue)) {
            return json_decode($rawValue, true);
        }

        return $rawValue;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Ui\Domain\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class NodePropertyConversionService extends NodePropertyConversionService_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Neos\Ui\Domain\Service\NodePropertyConversionService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Ui\Domain\Service\NodePropertyConversionService', $this);
        if ('Neos\Neos\Ui\Domain\Service\NodePropertyConversionService' === get_class($this)) {
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
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'propertyMapper' => 'Neos\\Flow\\Property\\PropertyMapper',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Neos\Ui\Domain\Service\NodePropertyConversionService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Ui\Domain\Service\NodePropertyConversionService', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Property\PropertyMapper', 'Neos\Flow\Property\PropertyMapper', 'propertyMapper', '2ab4a1ce2ee31715713d0f207f0ac637', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Property\PropertyMapper'); });
        $this->Flow_Injected_Properties = array (
  0 => 'objectManager',
  1 => 'propertyMapper',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos.Ui/Classes/Domain/Service/NodePropertyConversionService.php
#