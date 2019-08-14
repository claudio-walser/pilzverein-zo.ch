<?php 
namespace Neos\Flow\Mvc\Controller;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Property\PropertyMappingConfiguration;
use Neos\Flow\Property\TypeConverter\PersistentObjectConverter;

/**
 * The default property mapping configuration is available
 * inside the Argument-object.
 *
 * @api
 */
class MvcPropertyMappingConfiguration_Original extends PropertyMappingConfiguration
{
    /**
     * Allow creation of a certain sub property
     *
     * @param string $propertyPath
     * @return MvcPropertyMappingConfiguration this
     * @api
     */
    public function allowCreationForSubProperty($propertyPath)
    {
        $this->forProperty($propertyPath)->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, true);
        return $this;
    }

    /**
     * Allow modification for a given property path
     *
     * @param string $propertyPath
     * @return MvcPropertyMappingConfiguration this
     * @api
     */
    public function allowModificationForSubProperty($propertyPath)
    {
        $this->forProperty($propertyPath)->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, true);
        return $this;
    }

    /**
     * Allow override of the target type through a special "__type" parameter
     *
     * @return MvcPropertyMappingConfiguration this
     * @api
     */
    public function allowOverrideTargetType()
    {
        $this->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_OVERRIDE_TARGET_TYPE_ALLOWED, true);
        return $this;
    }

    /**
     * Set the target type for a certain property. Especially useful
     * if there is an object which has a nested object which is abstract,
     * and you want to instantiate a concrete object instead.
     *
     * @param string $propertyPath
     * @param string $targetType
     * @return MvcPropertyMappingConfiguration this
     * @api
     */
    public function setTargetTypeForSubProperty($propertyPath, $targetType)
    {
        $this->forProperty($propertyPath)->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_TARGET_TYPE, $targetType);
        return $this;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Mvc\Controller;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * The default property mapping configuration is available
 * inside the Argument-object.
 */
class MvcPropertyMappingConfiguration extends MvcPropertyMappingConfiguration_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
  'configuration' => 'array',
  'subConfigurationForProperty' => 'array<Neos\\Flow\\Property\\PropertyMappingConfigurationInterface>',
  'mapping' => 'array',
  'typeConverter' => 'Neos\\Flow\\Property\\TypeConverterInterface',
  'propertiesToBeMapped' => 'array',
  'propertiesToSkip' => 'array',
  'propertiesNotToBeMapped' => 'array',
  'skipUnknownProperties' => 'boolean',
  'mapUnknownProperties' => 'boolean',
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
# PathAndFilename: /var/www/html/neos/Packages/Framework/Neos.Flow/Classes/Mvc/Controller/MvcPropertyMappingConfiguration.php
#