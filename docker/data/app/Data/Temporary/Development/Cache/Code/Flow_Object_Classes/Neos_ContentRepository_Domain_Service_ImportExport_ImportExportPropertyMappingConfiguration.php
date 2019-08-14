<?php 
namespace Neos\ContentRepository\Domain\Service\ImportExport;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Persistence\Doctrine\ArrayTypeConverter;
use Neos\Flow\Property\PropertyMappingConfigurationInterface;
use Neos\Flow\Property\TypeConverter\ArrayConverter;
use Neos\Flow\Property\TypeConverter\ObjectConverter;
use Neos\Flow\Property\TypeConverter\PersistentObjectConverter;
use Neos\Flow\Property\TypeConverter\StringConverter;
use Neos\Flow\Property\TypeConverterInterface;
use Neos\Flow\ResourceManagement\ResourceTypeConverter;

/**
 * Property mapping configuration which is used for import / export:
 *
 * - works for all levels of the PropertyMapping (recursively)
 * - sets the correct export and import configuration for the type converters
 */
class ImportExportPropertyMappingConfiguration_Original implements PropertyMappingConfigurationInterface
{
    /**
     * @var string the resource-load-save-path, or NULL if it does not exist.
     */
    protected $resourceLoadSavePath;

    /**
     * @param $resourceLoadSavePath
     */
    public function __construct($resourceLoadSavePath)
    {
        $this->resourceLoadSavePath = $resourceLoadSavePath;
    }

    /**
     * The sub-configuration to be used is the current one.
     *
     * @param string $propertyName
     * @return PropertyMappingConfigurationInterface the property mapping configuration for the given $propertyName.
     * @api
     */
    public function getConfigurationFor($propertyName)
    {
        return $this;
    }

    /**
     * @param string $typeConverterClassName
     * @param string $key
     * @return mixed configuration value for the specific $typeConverterClassName. Can be used by Type Converters to fetch converter-specific configuration
     * @api
     */
    public function getConfigurationValue($typeConverterClassName, $key)
    {
        // needed in EXPORT
        if ($typeConverterClassName === StringConverter::class && $key === StringConverter::CONFIGURATION_ARRAY_FORMAT) {
            return StringConverter::ARRAY_FORMAT_JSON;
        }

        if ($this->resourceLoadSavePath !== null && $typeConverterClassName === ArrayConverter::class && $key === ArrayConverter::CONFIGURATION_RESOURCE_EXPORT_TYPE) {
            return ArrayConverter::RESOURCE_EXPORT_TYPE_FILE;
        }

        if ($typeConverterClassName === ArrayConverter::class && $key === ArrayConverter::CONFIGURATION_RESOURCE_SAVE_PATH) {
            return $this->resourceLoadSavePath;
        }

        if ($typeConverterClassName === ArrayTypeConverter::class && $key === ArrayTypeConverter::CONFIGURATION_CONVERT_ELEMENTS) {
            return true;
        }

        // needed in IMPORT
        if ($typeConverterClassName === PersistentObjectConverter::class && $key === PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED) {
            return true;
        }

        if ($typeConverterClassName === PersistentObjectConverter::class && $key === PersistentObjectConverter::CONFIGURATION_IDENTITY_CREATION_ALLOWED) {
            return true;
        }

        if ($typeConverterClassName === ObjectConverter::class && $key === ObjectConverter::CONFIGURATION_OVERRIDE_TARGET_TYPE_ALLOWED) {
            return true;
        }

        if ($typeConverterClassName === ArrayConverter::class && $key === ArrayConverter::CONFIGURATION_STRING_FORMAT) {
            return ArrayConverter::STRING_FORMAT_JSON;
        }

        if ($typeConverterClassName === ResourceTypeConverter::class && $key === ResourceTypeConverter::CONFIGURATION_IDENTITY_CREATION_ALLOWED) {
            return true;
        }

        if ($typeConverterClassName === ResourceTypeConverter::class && $key === ResourceTypeConverter::CONFIGURATION_RESOURCE_LOAD_PATH) {
            return $this->resourceLoadSavePath;
        }

        // fallback
        return null;
    }


    // starting from here, we just implement the interface in the "default" way without modifying things
    /**
     * @param string $propertyName
     * @return boolean true if the given propertyName should be mapped, false otherwise.
     * @api
     */
    public function shouldMap($propertyName)
    {
        return true;
    }

    /**
     * Check if the given $propertyName should be skipped during mapping.
     *
     * @param string $propertyName
     * @return boolean
     * @api
     */
    public function shouldSkip($propertyName)
    {
        return false;
    }

    /**
     * Whether unknown (unconfigured) properties should be skipped during
     * mapping, instead if causing an error.
     *
     * @return boolean
     * @api
     */
    public function shouldSkipUnknownProperties()
    {
        return false;
    }

    /**
     * Maps the given $sourcePropertyName to a target property name.
     * Can be used to rename properties from source to target.
     *
     * @param string $sourcePropertyName
     * @return string property name of target
     * @api
     */
    public function getTargetPropertyName($sourcePropertyName)
    {
        // TODO: This if statement is necessary for smooth migration to the new resource/media management. "originalImage" is deprecated, this can be removed in 3 versions.
        if ($sourcePropertyName === 'originalImage') {
            return 'originalAsset';
        }
        return $sourcePropertyName;
    }

    /**
     * This method can be used to explicitely force a TypeConverter to be used for this Configuration.
     *
     * @return TypeConverterInterface The type converter to be used for this particular PropertyMappingConfiguration, or NULL if the system-wide configured type converter should be used.
     * @api
     */
    public function getTypeConverter()
    {
        return null;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\ContentRepository\Domain\Service\ImportExport;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Property mapping configuration which is used for import / export:
 * 
 * - works for all levels of the PropertyMapping (recursively)
 * - sets the correct export and import configuration for the type converters
 */
class ImportExportPropertyMappingConfiguration extends ImportExportPropertyMappingConfiguration_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param $resourceLoadSavePath
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $resourceLoadSavePath in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
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
  'resourceLoadSavePath' => 'string the resource-load-save-path, or NULL if it does not exist.',
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
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.ContentRepository/Classes/Domain/Service/ImportExport/ImportExportPropertyMappingConfiguration.php
#