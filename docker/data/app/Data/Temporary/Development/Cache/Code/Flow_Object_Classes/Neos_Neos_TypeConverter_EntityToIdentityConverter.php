<?php 
namespace Neos\Neos\TypeConverter;

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
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Property\PropertyMappingConfigurationInterface;
use Neos\Flow\Property\TypeConverter\AbstractTypeConverter;
use Neos\Utility\TypeHandling;

/**
 * Converts the given entity to a JSON representation containing the identity and object type
 */
class EntityToIdentityConverter_Original extends AbstractTypeConverter
{
    /**
     * The source types this converter can convert.
     *
     * @var array<string>
     */
    protected $sourceTypes = ['object'];

    /**
     * The target type this converter can convert to.
     *
     * @var string
     */
    protected $targetType = 'array';

    /**
     * The priority for this converter.
     *
     * @var integer
     */
    protected $priority = 0;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * Check if the given object has an identity.
     *
     * @param object $source the source data
     * @param string $targetType the type to convert to.
     * @return boolean true if this TypeConverter can convert from $source to $targetType, false otherwise.
     */
    public function canConvertFrom($source, $targetType)
    {
        $identifier = $this->persistenceManager->getIdentifierByObject($source);
        return ($identifier !== null);
    }


    /**
     * Converts the given source object to an array containing the type and identity.
     *
     * @param object $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param PropertyMappingConfigurationInterface $configuration
     * @return array
     */
    public function convertFrom($source, $targetType, array $convertedChildProperties = [], PropertyMappingConfigurationInterface $configuration = null)
    {
        return [
            '__identity' => $this->persistenceManager->getIdentifierByObject($source),
            '__type' => TypeHandling::getTypeForValue($source)
        ];
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\TypeConverter;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Converts the given entity to a JSON representation containing the identity and object type
 */
class EntityToIdentityConverter extends EntityToIdentityConverter_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\Neos\TypeConverter\EntityToIdentityConverter' === get_class($this)) {
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
  'sourceTypes' => 'array<string>',
  'targetType' => 'string',
  'priority' => 'integer',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
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
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/TypeConverter/EntityToIdentityConverter.php
#