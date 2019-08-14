<?php 
namespace Neos\Media\TypeConverter;

/*
 * This file is part of the Neos.Media package.
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
use Neos\Media\Domain\Model\Asset;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\AssetVariantInterface;
use Neos\Media\Domain\Model\Image;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\Media\Domain\Model\ImageVariant;
use Neos\Flow\Property\TypeConverter\AbstractTypeConverter;

/**
 * This converter transforms Neos.Media AssetInterface instances to arrays.
 *
 * @api
 * @Flow\Scope("singleton")
 */
class ArrayConverter_Original extends AbstractTypeConverter
{
    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var array
     */
    protected $sourceTypes = [AssetInterface::class, ImageInterface::class, Image::class, Asset::class];

    /**
     * @var string
     */
    protected $targetType = 'array';

    /**
     * @var integer
     */
    protected $priority = 2;

    /**
     * @param mixed $source
     * @param string $targetType
     * @return boolean
     */
    public function canConvertFrom($source, $targetType)
    {
        return ($source instanceof AssetInterface);
    }


    /**
     * Return a list of sub-properties inside the source object.
     * The "key" is the sub-property name, and the "value" is the value of the sub-property.
     *
     * @param mixed $source
     * @return array
     */
    public function getSourceChildPropertiesToBeConverted($source)
    {
        $sourceChildPropertiesToBeConverted = [
            'resource' => $source->getResource()
        ];

        if ($source instanceof AssetVariantInterface) {
            $sourceChildPropertiesToBeConverted['originalAsset'] = $source->getOriginalAsset();
        }
        if ($source instanceof ImageVariant) {
            $sourceChildPropertiesToBeConverted['adjustments'] = $source->getAdjustments();
        }

        return $sourceChildPropertiesToBeConverted;
    }

    /**
     * Return the type of a given sub-property inside the $targetType, in this case always "array"
     *
     * @param string $targetType is ignored
     * @param string $propertyName is ignored
     * @param PropertyMappingConfigurationInterface $configuration is ignored
     * @return string always "array"
     */
    public function getTypeOfChildProperty($targetType, $propertyName, PropertyMappingConfigurationInterface $configuration)
    {
        return 'array';
    }

    /**
     * Convert an object from $source to an array
     *
     * @param AssetInterface $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param PropertyMappingConfigurationInterface $configuration
     * @return array The converted asset or NULL
     */
    public function convertFrom($source, $targetType, array $convertedChildProperties = [], PropertyMappingConfigurationInterface $configuration = null)
    {
        $identity = $this->persistenceManager->getIdentifierByObject($source);
        switch (true) {
            case $source instanceof ImageVariant:
                if (!isset($convertedChildProperties['originalAsset']) || !is_array($convertedChildProperties['originalAsset'])) {
                    return null;
                }

                $convertedChildProperties['originalAsset']['__identity'] = $this->persistenceManager->getIdentifierByObject($source->getOriginalAsset());

                return [
                    '__identity' => $identity,
                    '__type' => \Neos\Media\Domain\Model\ImageVariant::class,
                    'originalAsset' => $convertedChildProperties['originalAsset'],
                    'adjustments' => $convertedChildProperties['adjustments']
                ];
            case $source instanceof AssetInterface:
                if (!isset($convertedChildProperties['resource']) || !is_array($convertedChildProperties['resource'])) {
                    return null;
                }

                $convertedChildProperties['resource']['__identity'] = $this->persistenceManager->getIdentifierByObject($source->getResource());

                return [
                    '__identity' => $identity,
                    '__type' => \Neos\Utility\TypeHandling::getTypeForValue($source),
                    'title' => $source->getTitle(),
                    'copyrightNotice' => $source->getCopyrightNotice(),
                    'caption' => $source->getCaption(),
                    'resource' => $convertedChildProperties['resource']
                ];
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Media\TypeConverter;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * This converter transforms Neos.Media AssetInterface instances to arrays.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class ArrayConverter extends ArrayConverter_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Media\TypeConverter\ArrayConverter') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Media\TypeConverter\ArrayConverter', $this);
        if ('Neos\Media\TypeConverter\ArrayConverter' === get_class($this)) {
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
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'sourceTypes' => 'array',
  'targetType' => 'string',
  'priority' => 'integer',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Media\TypeConverter\ArrayConverter') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Media\TypeConverter\ArrayConverter', $this);

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
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Media/Classes/TypeConverter/ArrayConverter.php
#