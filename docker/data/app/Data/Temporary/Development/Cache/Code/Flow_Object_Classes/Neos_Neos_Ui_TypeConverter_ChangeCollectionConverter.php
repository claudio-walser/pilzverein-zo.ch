<?php 
namespace Neos\Neos\Ui\TypeConverter;

/*
 * This file is part of the Neos.Neos.Ui package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Error\Messages\Error;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Property\PropertyMapper;
use Neos\Flow\Property\PropertyMappingConfigurationInterface;
use Neos\Flow\Property\TypeConverter\AbstractTypeConverter;
use Neos\Flow\Reflection\ReflectionService;
use Neos\Neos\Ui\ContentRepository\Service\NodeService;
use Neos\Neos\Ui\Domain\Model\ChangeCollection;
use Neos\Neos\Ui\Domain\Model\ChangeInterface;
use Neos\Neos\Ui\Domain\Model\Changes\Property;
use Neos\Utility\ObjectAccess;

/**
 * An Object Converter for ChangeCollections.
 *
 * @Flow\Scope("singleton")
 */
class ChangeCollectionConverter_Original extends AbstractTypeConverter
{
    /**
     * @var array
     */
    protected $sourceTypes = ['array'];

    /**
     * @var string
     */
    protected $targetType = ChangeCollection::class;

    /**
     * @var integer
     */
    protected $priority = 1;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    protected $disallowedPayloadProperties = [
        'subject',
        'reference'
    ];

    /**
     * @Flow\InjectConfiguration(path="changes.types")
     * @var array
     */
    protected $typeMap;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\Inject
     * @var NodeService
     */
    protected $nodeService;

    /**
     * @Flow\Inject
     * @var PropertyMapper
     */
    protected $propertyMapper;

    /**
     * @Flow\Inject
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * Converts a accordingly formatted, associative array to a change collection
     *
     * @param array $source
     * @param string $targetType not used
     * @param array $subProperties not used
     * @param \Neos\Flow\Property\PropertyMappingConfigurationInterface $configuration not used
     * @return mixed An object or \Neos\Error\Messages\Error if the input format is not supported or could not be converted for other reasons
     * @throws \Exception
     */
    public function convertFrom($source, $targetType, array $subProperties = [], PropertyMappingConfigurationInterface $configuration = null)
    {
        if (!is_array($source)) {
            return new Error(sprintf('Cannot convert %s to ChangeCollection.', gettype($source)));
        }

        $changeCollection = new ChangeCollection();
        foreach ($source as $changeData) {
            $convertedData = $this->convertChangeData($changeData);

            if ($convertedData instanceof Error) {
                return $convertedData;
            }

            $changeCollection->add($convertedData);
        }

        return $changeCollection;
    }

    /**
     * Convert array to change interface
     *
     * @param array $changeData
     * @return ChangeInterface
     */
    protected function convertChangeData($changeData)
    {
        $type = $changeData['type'];

        if (!isset($this->typeMap[$type])) {
            return new Error(sprintf('Could not convert change type %s, it is unknown to the system', $type));
        }

        $changeClass = $this->typeMap[$type];
        $changeClassInstance = $this->objectManager->get($changeClass);
        $changeClassInstance->injectPersistenceManager($this->persistenceManager);

        $subjectContextPath = $changeData['subject'];
        $subject = $this->nodeService->getNodeFromContextPath($subjectContextPath);

        if ($subject instanceof Error) {
            return $subject;
        }

        $changeClassInstance->setSubject($subject);

        if (isset($changeData['reference']) && method_exists($changeClassInstance, 'setReference')) {
            $referenceContextPath = $changeData['reference'];
            $reference = $this->nodeService->getNodeFromContextPath($referenceContextPath);

            if ($reference instanceof Error) {
                return $reference;
            }

            $changeClassInstance->setReference($reference);
        }

        if (isset($changeData['payload'])) {
            foreach ($changeData['payload'] as $propertyName => $value) {
                if (!in_array($propertyName, $this->disallowedPayloadProperties)) {
                    $methodParameters = $this->reflectionService->getMethodParameters($changeClass, ObjectAccess::buildSetterMethodName($propertyName));
                    $methodParameter = current($methodParameters);
                    $targetType = $methodParameter['type'];

                    // Fixme: The type conversion runs depending on the target node property type inside Property::class
                    // This is why we are not allowed to modify the value in any way.
                    // Without this condition the object was parsed to a string leading to fatal errors when changing images
                    // in the UI.
                    if ($propertyName !== 'value' && $targetType !== Property::class) {
                        $value = $this->propertyMapper->convert($value, $targetType);
                    }

                    ObjectAccess::setProperty($changeClassInstance, $propertyName, $value);
                }
            }
        }

        return $changeClassInstance;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Ui\TypeConverter;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * An Object Converter for ChangeCollections.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class ChangeCollectionConverter extends ChangeCollectionConverter_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Neos\Ui\TypeConverter\ChangeCollectionConverter') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Ui\TypeConverter\ChangeCollectionConverter', $this);
        if ('Neos\Neos\Ui\TypeConverter\ChangeCollectionConverter' === get_class($this)) {
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
  'sourceTypes' => 'array',
  'targetType' => 'string',
  'priority' => 'integer',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'typeMap' => 'array',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'nodeService' => 'Neos\\Neos\\Ui\\ContentRepository\\Service\\NodeService',
  'propertyMapper' => 'Neos\\Flow\\Property\\PropertyMapper',
  'reflectionService' => 'Neos\\Flow\\Reflection\\ReflectionService',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Neos\Ui\TypeConverter\ChangeCollectionConverter') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Ui\TypeConverter\ChangeCollectionConverter', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Ui\ContentRepository\Service\NodeService', 'Neos\Neos\Ui\ContentRepository\Service\NodeService', 'nodeService', 'c1132e56328e2286433a0639d659934e', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Ui\ContentRepository\Service\NodeService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Property\PropertyMapper', 'Neos\Flow\Property\PropertyMapper', 'propertyMapper', '2ab4a1ce2ee31715713d0f207f0ac637', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Property\PropertyMapper'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Reflection\ReflectionService', 'Neos\Flow\Reflection\ReflectionService', 'reflectionService', '464c26aa94c66579c050985566cbfc1f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Reflection\ReflectionService'); });
        $this->typeMap = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Neos.Ui.changes.types');
        $this->Flow_Injected_Properties = array (
  0 => 'persistenceManager',
  1 => 'objectManager',
  2 => 'nodeService',
  3 => 'propertyMapper',
  4 => 'reflectionService',
  5 => 'typeMap',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos.Ui/Classes/TypeConverter/ChangeCollectionConverter.php
#