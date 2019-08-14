<?php 
namespace Neos\Media\Domain\Model;

/*
 * This file is part of the Neos.Media package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\Exception\InvalidConfigurationException;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\ResourceManagement\Exception;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Media\Domain\Model\Adjustment\ImageAdjustmentInterface;
use Neos\Media\Domain\Service\ImageService;
use Neos\Media\Exception\ImageFileException;
use Neos\Utility\ObjectAccess;
use Neos\Utility\TypeHandling;

/**
 * A user defined variant (working copy) of an original Image asset
 *
 * @Flow\Entity
 */
class ImageVariant_Original extends Asset implements AssetVariantInterface, ImageInterface
{
    use DimensionsTrait;

    /**
     * @var ImageService
     * @Flow\Inject
     */
    protected $imageService;

    /**
     * @var Image
     * @ORM\ManyToOne(inversedBy="variants")
     * @ORM\JoinColumn(nullable=false)
     * @Flow\Validate(type="NotEmpty")
     */
    protected $originalAsset;

    /**
     * @var ArrayCollection<\Neos\Media\Domain\Model\Adjustment\AbstractImageAdjustment>
     * @ORM\OneToMany(mappedBy="imageVariant", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $adjustments;

    /**
     * @var string
     * @Flow\Validate(type="StringLength", options={ "maximum"=255 })
     */
    protected $name = '';

    /**
     * @var string
     * @ORM\Column(nullable=true)
     * @Flow\Validate(type="StringLength", options={ "maximum"=255 })
     */
    protected $presetIdentifier;

    /**
     * @var string
     * @ORM\Column(nullable=true)
     * @Flow\Validate(type="StringLength", options={ "maximum"=255 })
     */
    protected $presetVariantName;

    /**
     * Constructs a new Image Variant based on the given original
     *
     * @param Image $originalAsset The original Image asset this variant is derived from
     */
    public function __construct(Image $originalAsset)
    {
        $this->originalAsset = $originalAsset;

        $this->thumbnails = new ArrayCollection();
        $this->adjustments = new ArrayCollection();
        $this->tags = new ArrayCollection();
        try {
            $this->lastModified = new \DateTime();
        } catch (\Exception $e) {
            // This won't happen, because we create DateTime without any parameters.
        }
    }

    /**
     * Initialize this image variant
     *
     * This method will generate the resource of this asset when this object has just been newly created.
     * We can't run renderResource() in the constructor since not all dependencies have been injected then. Generating
     * resources lazily in the getResource() method is not feasible either, because getters will be triggered
     * by the validation mechanism on flushing the persistence which will result in undefined behavior.
     *
     * We don't call refresh() here because we only want the resource to be rendered, not all other refresh actions
     * from parent classes being executed.
     *
     * @param integer $initializationCause
     * @return void
     * @throws Exception
     * @throws ImageFileException
     * @throws InvalidConfigurationException
     */
    public function initializeObject($initializationCause)
    {
        parent::initializeObject($initializationCause);
        if ($initializationCause === ObjectManagerInterface::INITIALIZATIONCAUSE_CREATED) {
            $this->renderResource();
        }
    }

    /**
     * Returns the original image this variant is based on
     *
     * @return Image
     */
    public function getOriginalAsset()
    {
        return $this->originalAsset;
    }

    /**
     * Returns the resource of this image variant
     *
     * @return PersistentResource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Refreshes this image variant: according to the added adjustments, a new image is rendered and stored as this
     * image variant's resource.
     *
     * @return void
     * @throws Exception
     * @throws ImageFileException
     * @throws InvalidConfigurationException
     * @see getResource()
     */
    public function refresh()
    {
        // Several refresh() calls might happen during one request. If that is the case, the Resource Manager can't know
        // that the first created resource object doesn't have to be persisted / published anymore. Thus we need to
        // delete the resource manually in order to avoid orphaned resource objects:
        if ($this->resource !== null) {
            $this->resourceManager->deleteResource($this->resource);
        }

        parent::refresh();
        $this->renderResource();
    }

    /**
     * File extension of the image without leading dot.
     * This will return the file extension of the original image as this should not be different in image variants
     *
     * @return string
     */
    public function getFileExtension()
    {
        return $this->originalAsset->getFileExtension();
    }

    /**
     * Returns the title of the original image
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->originalAsset->getTitle();
    }

    /**
     * Returns the caption of the original image
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->originalAsset->getCaption();
    }

    /**
     * @return string
     */
    public function getCopyrightNotice(): string
    {
        return $this->originalAsset->getCopyrightNotice();
    }

    /**
     * Sets a name which can be used for identifying this variant
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Returns the name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Setting the image resource on an ImageVariant is not allowed, this method will
     * throw a RuntimeException.
     *
     * @param PersistentResource $resource
     * @return void
     * @throws \RuntimeException
     */
    public function setResource(PersistentResource $resource): void
    {
        throw new \RuntimeException('Setting the resource on an ImageVariant is not supported.', 1366627480);
    }

    /**
     * Setting the title on an ImageVariant is not allowed, this method will throw a
     * RuntimeException.
     *
     * @param string $title
     * @return void
     * @throws \RuntimeException
     */
    public function setTitle($title)
    {
        throw new \RuntimeException('Setting the title on an ImageVariant is not supported.', 1366627475);
    }

    /**
     * Add a single tag to this asset
     *
     * @param Tag $tag
     * @return void
     */
    public function addTag(Tag $tag)
    {
        throw new \RuntimeException('Adding a tag on an ImageVariant is not supported.', 1371237593);
    }

    /**
     * Set the tags assigned to this asset
     *
     * @param Collection $tags
     * @return void
     */
    public function setTags(Collection $tags)
    {
        throw new \RuntimeException('Settings tags on an ImageVariant is not supported.', 1371237597);
    }

    /**
     * Adding variants to variants is not supported.
     *
     * @param ImageVariant $variant
     * @return void
     */
    public function addVariant(ImageVariant $variant): void
    {
        throw new \RuntimeException('Adding variants to an ImageVariant is not supported.', 1381419461);
    }

    /**
     * Retrieving variants from variants is not supported (no-operation)
     *
     * @return array
     */
    public function getVariants(): array
    {
        return [];
    }

    /**
     * Sets the identifier of the image variant preset which created this variant (if any)
     *
     * @param string $presetIdentifier For example: 'Acme.Demo:Preset1'
     */
    public function setPresetIdentifier(string $presetIdentifier): void
    {
        $this->presetIdentifier = $presetIdentifier;
    }

    /**
     * Returns the identifier of the image variant preset which created this variant (if any)
     *
     * @return string|null
     */
    public function getPresetIdentifier(): ?string
    {
        return $this->presetIdentifier;
    }

    /**
     * @param string $presetVariantName
     */
    public function setPresetVariantName(string $presetVariantName): void
    {
        $this->presetVariantName = $presetVariantName;
    }

    /**
     * @return string|null
     */
    public function getPresetVariantName(): ?string
    {
        return $this->presetVariantName;
    }

    /**
     * Adds the given adjustment to the list of adjustments applied to this image variant.
     *
     * If an adjustment of the given type already exists, the existing one will be overridden by the new one.
     *
     * @param ImageAdjustmentInterface $adjustment The adjustment to apply
     * @return void
     * @throws Exception
     * @throws ImageFileException
     * @throws InvalidConfigurationException
     * @throws \Exception
     */
    public function addAdjustment(ImageAdjustmentInterface $adjustment): void
    {
        $this->applyAdjustment($adjustment);
        $this->refresh();
    }

    /**
     * Adds the given adjustments to the list of adjustments applied to this image variant.
     *
     * If an adjustment of one of the given types already exists, the existing one will be overridden by the new one.
     *
     * @param array<ImageAdjustmentInterface> $adjustments
     * @return void
     * @throws Exception
     * @throws ImageFileException
     * @throws InvalidConfigurationException
     * @throws \Exception
     */
    public function addAdjustments(array $adjustments): void
    {
        foreach ($adjustments as $adjustment) {
            $this->applyAdjustment($adjustment);
        }
        $this->refresh();
    }

    /**
     * Apply the given adjustment to the image variant.
     * If an adjustment of the given type already exists, the existing one will be overridden by the new one.
     *
     * @param ImageAdjustmentInterface $adjustment
     * @return void
     * @throws \Exception
     */
    protected function applyAdjustment(ImageAdjustmentInterface $adjustment): void
    {
        $existingAdjustmentFound = false;
        $newAdjustmentClassName = TypeHandling::getTypeForValue($adjustment);

        foreach ($this->adjustments as $existingAdjustment) {
            if (TypeHandling::getTypeForValue($existingAdjustment) === $newAdjustmentClassName) {
                foreach (ObjectAccess::getGettableProperties($adjustment) as $propertyName => $propertyValue) {
                    ObjectAccess::setProperty($existingAdjustment, $propertyName, $propertyValue);
                }
                $existingAdjustmentFound = true;
            }
        }
        if (!$existingAdjustmentFound) {
            $this->adjustments->add($adjustment);
            $adjustment->setImageVariant($this);
            $this->adjustments = $this->adjustments->matching(new Criteria(null, ['position' => 'ASC']));
        }

        $this->lastModified = new \DateTime();
    }

    /**
     * @return Collection
     */
    public function getAdjustments(): Collection
    {
        return $this->adjustments;
    }

    /**
     * Tells the ImageService to render the resource of this ImageVariant according to the existing adjustments.
     *
     * @return void
     * @throws InvalidConfigurationException
     * @throws Exception
     * @throws ImageFileException
     */
    protected function renderResource(): void
    {
        $processedImageInfo = $this->imageService->processImage($this->originalAsset->getResource(), $this->adjustments->toArray());
        $this->resource = $processedImageInfo['resource'];
        $this->width = $processedImageInfo['width'];
        $this->height = $processedImageInfo['height'];
        $this->persistenceManager->whitelistObject($this->resource);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Media\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A user defined variant (working copy) of an original Image asset
 * @\Neos\Flow\Annotations\Entity
 */
class ImageVariant extends ImageVariant_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface, \Neos\Flow\Persistence\Aspect\PersistenceMagicInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(length=40)
     * introduced by Neos\Flow\Persistence\Aspect\PersistenceMagicAspect
     */
    protected $Persistence_Object_Identifier = NULL;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     * @param Image $originalAsset The original Image asset this variant is derived from
     */
    public function __construct()
    {
        $arguments = func_get_args();

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'])) {

        if (!array_key_exists(0, $arguments)) $arguments[0] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Model\Image');
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $originalAsset in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'] = true;
            try {
            
                $methodArguments = [];

                if (array_key_exists(0, $arguments)) $methodArguments['originalAsset'] = $arguments[0];
            
                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['Neos\Flow\Aop\Advice\BeforeAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['Neos\Flow\Aop\Advice\BeforeAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\ImageVariant', '__construct', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\ImageVariant', '__construct', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
            return;
        }
        if ('Neos\Media\Domain\Model\ImageVariant' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Media\Domain\Model\ImageVariant';
        if ($isSameClass) {
            $this->initializeObject(1);
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
            '__clone' => array(
                'Neos\Flow\Aop\Advice\BeforeAdvice' => array(
                    new \Neos\Flow\Aop\Advice\BeforeAdvice('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', 'generateUuid', $objectManager, NULL),
                ),
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', 'cloneObject', $objectManager, NULL),
                ),
            ),
            '__construct' => array(
                'Neos\Flow\Aop\Advice\BeforeAdvice' => array(
                    new \Neos\Flow\Aop\Advice\BeforeAdvice('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', 'generateUuid', $objectManager, NULL),
                ),
            ),
            'emitAssetCreated' => array(
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\SignalSlot\SignalAspect', 'forwardSignalToDispatcher', $objectManager, NULL),
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

        $isSameClass = get_class($this) === 'Neos\Media\Domain\Model\ImageVariant';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Media\Domain\Model\ImageVariant', $classParents) !== false && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== false;

        if ($isSameClass || $isClassProxy) {
            $this->initializeObject(2);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __clone()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone'])) {
            $result = NULL;

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone'] = true;
            try {
            
                $methodArguments = [];

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\BeforeAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\BeforeAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\ImageVariant', '__clone', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\ImageVariant', '__clone', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\ImageVariant', '__clone', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param AssetInterface $asset
     * @return void
     * @\Neos\Flow\Annotations\Signal
     */
    protected function emitAssetCreated(\Neos\Media\Domain\Model\AssetInterface $asset)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAssetCreated'])) {
            $result = parent::emitAssetCreated($asset);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAssetCreated'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['asset'] = $asset;
            
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\ImageVariant', 'emitAssetCreated', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAssetCreated']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAssetCreated']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\ImageVariant', 'emitAssetCreated', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAssetCreated']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAssetCreated']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __sleep()
    {
            $result = NULL;
        $this->Flow_Object_PropertiesToSerialize = array();

        $transientProperties = array (
  0 => 'assetSources',
);
        $propertyVarTags = array (
  'imageService' => 'Neos\\Media\\Domain\\Service\\ImageService',
  'originalAsset' => 'Neos\\Media\\Domain\\Model\\Image',
  'adjustments' => 'Doctrine\\Common\\Collections\\ArrayCollection<\\Neos\\Media\\Domain\\Model\\Adjustment\\AbstractImageAdjustment>',
  'name' => 'string',
  'presetIdentifier' => 'string',
  'presetVariantName' => 'string',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'systemLogger' => 'Neos\\Flow\\Log\\PsrSystemLoggerInterface',
  'resourceManager' => 'Neos\\Flow\\ResourceManagement\\ResourceManager',
  'thumbnailService' => 'Neos\\Media\\Domain\\Service\\ThumbnailService',
  'assetService' => 'Neos\\Media\\Domain\\Service\\AssetService',
  'assetRepository' => 'Neos\\Media\\Domain\\Repository\\AssetRepository',
  'importedAssetRepository' => 'Neos\\Media\\Domain\\Repository\\ImportedAssetRepository',
  'lastModified' => '\\DateTime',
  'title' => 'string',
  'caption' => 'string',
  'copyrightNotice' => 'string',
  'resource' => 'Neos\\Flow\\ResourceManagement\\PersistentResource',
  'thumbnails' => 'Doctrine\\Common\\Collections\\Collection<\\Neos\\Media\\Domain\\Model\\Thumbnail>',
  'tags' => 'Doctrine\\Common\\Collections\\Collection<\\Neos\\Media\\Domain\\Model\\Tag>',
  'assetCollections' => 'Doctrine\\Common\\Collections\\Collection<\\Neos\\Media\\Domain\\Model\\AssetCollection>',
  'assetSourceIdentifier' => 'string',
  'assetSourcesConfiguration' => 'array',
  'assetSources' => 'array<Neos\\Media\\Domain\\Model\\AssetSource\\AssetSourceInterface>',
  'width' => 'integer',
  'height' => 'integer',
  'Persistence_Object_Identifier' => 'string',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Service\ImageService', 'Neos\Media\Domain\Service\ImageService', 'imageService', '7b342e21f2438a00b80abb708ce6db88', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Service\ImageService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\PsrSystemLoggerInterface', '', 'systemLogger', '35a47897f04aa042c1ab70dc21ff014f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\PsrSystemLoggerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ResourceManagement\ResourceManager', 'Neos\Flow\ResourceManagement\ResourceManager', 'resourceManager', '5c4c2fb284addde18c78849a54b02875', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ResourceManagement\ResourceManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Service\ThumbnailService', 'Neos\Media\Domain\Service\ThumbnailService', 'thumbnailService', 'b18abfdc1787cb03caeb052cad3d7c0c', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Service\ThumbnailService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Service\AssetService', 'Neos\Media\Domain\Service\AssetService', 'assetService', 'b8a3f9ba29596737396943e4de630328', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Service\AssetService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Repository\AssetRepository', 'Neos\Media\Domain\Repository\AssetRepository', 'assetRepository', '45191f771a429c7decedb6fc0abbcc74', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Repository\AssetRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Repository\ImportedAssetRepository', 'Neos\Media\Domain\Repository\ImportedAssetRepository', 'importedAssetRepository', '663a5f5ad5a4995b3de0fb5853bd6e81', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Repository\ImportedAssetRepository'); });
        $this->assetSourcesConfiguration = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Media.assetSources');
        $this->Flow_Injected_Properties = array (
  0 => 'imageService',
  1 => 'persistenceManager',
  2 => 'systemLogger',
  3 => 'resourceManager',
  4 => 'thumbnailService',
  5 => 'assetService',
  6 => 'assetRepository',
  7 => 'importedAssetRepository',
  8 => 'assetSourcesConfiguration',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Media/Classes/Domain/Model/ImageVariant.php
#