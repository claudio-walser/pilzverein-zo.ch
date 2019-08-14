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
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\PsrSystemLoggerInterface;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\AssetSource\AssetNotFoundExceptionInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceConnectionExceptionInterface;
use Neos\Media\Domain\Repository\ImportedAssetRepository;
use Neos\Media\Domain\Repository\AssetRepository;
use Neos\Media\Domain\Service\AssetService;
use Neos\Media\Domain\Service\ThumbnailService;

/**
 * An Asset, the base for all more specific assets in this package.
 *
 * It can be used as is to represent any asset for which no better match is available.
 *
 * @Flow\Entity
 * @ORM\InheritanceType("JOINED")
 */
class Asset_Original implements AssetInterface
{
    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var PsrSystemLoggerInterface
     */
    protected $systemLogger;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var ThumbnailService
     */
    protected $thumbnailService;

    /**
     * @Flow\Inject
     * @var AssetService
     */
    protected $assetService;

    /**
     * @Flow\Inject
     * @var AssetRepository
     */
    protected $assetRepository;

    /**
     * @Flow\Inject()
     * @var ImportedAssetRepository
     */
    protected $importedAssetRepository;

    /**
     * @var \DateTime
     */
    protected $lastModified;

    /**
     * @var string
     * @Flow\Validate(type="StringLength", options={ "maximum"=255 })
     */
    protected $title = '';

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $caption = '';

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $copyrightNotice = '';

    /**
     * @var PersistentResource
     * @ORM\OneToOne(orphanRemoval=true, cascade={"all"})
     */
    protected $resource;

    /**
     * @var Collection<\Neos\Media\Domain\Model\Thumbnail>
     * @ORM\OneToMany(orphanRemoval=true, cascade={"all"}, mappedBy="originalAsset")
     */
    protected $thumbnails;

    /**
     * @var Collection<\Neos\Media\Domain\Model\Tag>
     * @ORM\ManyToMany
     * @ORM\OrderBy({"label"="ASC"})
     * @Flow\Lazy
     */
    protected $tags;

    /**
     * @var Collection<\Neos\Media\Domain\Model\AssetCollection>
     * @ORM\ManyToMany(mappedBy="assets", cascade={"persist"})
     * @ORM\OrderBy({"title"="ASC"})
     * @Flow\Lazy
     */
    protected $assetCollections;

    /**
     * @var string
     */
    public $assetSourceIdentifier = 'neos';

    /**
     * @Flow\InjectConfiguration(path="assetSources")
     * @var array
     */
    protected $assetSourcesConfiguration;

    /**
     * @Flow\Transient()
     * @var AssetSourceInterface[]
     */
    protected $assetSources = [];

    /**
     * Constructs an asset. The resource is set internally and then initialize()
     * is called.
     *
     * @param PersistentResource $resource
     */
    public function __construct(PersistentResource $resource)
    {
        $this->tags = new ArrayCollection();
        $this->thumbnails = new ArrayCollection();
        $this->resource = $resource;
        $this->lastModified = new \DateTime();
        $this->assetCollections = new ArrayCollection();
    }

    /**
     * @param integer $initializationCause
     * @return void
     */
    public function initializeObject($initializationCause)
    {
        // FIXME: This is a workaround for after the resource management changes that introduced the property.
        if ($this->thumbnails === null) {
            $this->thumbnails = new ArrayCollection();
        }
        if ($initializationCause === ObjectManagerInterface::INITIALIZATIONCAUSE_CREATED) {
            $this->emitAssetCreated($this);
        }
    }

    /**
     * Override this to initialize upon instantiation.
     *
     * @return void
     */
    protected function initialize()
    {
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->persistenceManager->getIdentifierByObject($this);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        if (empty($this->title)) {
            return $this->getResource()->getFilename() ?: $this->getIdentifier();
        }
        return $this->getTitle();
    }

    /**
     * Returns the last modification timestamp for this asset
     *
     * @return \DateTime The date and time of last modification.
     * @api
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * Sets the asset resource and (re-)initializes the asset.
     *
     * @param PersistentResource $resource
     * @return void
     */
    public function setResource(PersistentResource $resource)
    {
        $this->lastModified = new \DateTime();
        $this->resource = $resource;
        $this->refresh();
    }

    /**
     * PersistentResource of the original file
     *
     * @return PersistentResource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Returns a file extension fitting to the media type of this asset
     *
     * @return string
     */
    public function getFileExtension()
    {
        return $this->resource->getFileExtension();
    }

    /**
     * Returns the IANA media type of this asset
     *
     * @return string
     */
    public function getMediaType()
    {
        return $this->resource->getMediaType();
    }

    /**
     * Sets the title of this image (optional)
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->lastModified = new \DateTime();
        $this->title = $title;
    }

    /**
     * The title of this image
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the caption of this asset (optional)
     *
     * @param string $caption
     * @return void
     */
    public function setCaption($caption)
    {
        $this->lastModified = new \DateTime();
        $this->caption = $caption;
    }

    /**
     * The caption of this asset
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * @return string
     */
    public function getCopyrightNotice(): string
    {
        return $this->copyrightNotice;
    }

    /**
     * @param string $copyrightNotice
     */
    public function setCopyrightNotice(string $copyrightNotice): void
    {
        $this->copyrightNotice = $copyrightNotice;
    }

    /**
     * Return the tags assigned to this asset
     *
     * @return Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Add a single tag to this asset
     *
     * @param Tag $tag The tag to add
     * @return boolean true if the tag added was new, false if it already existed
     */
    public function addTag(Tag $tag)
    {
        if (!$this->tags->contains($tag)) {
            $this->lastModified = new \DateTime();
            $this->tags->add($tag);
            return true;
        }

        return false;
    }

    /**
     * Returns a thumbnail of this asset
     *
     * If the maximum width / height is not specified or exceeds the original asset's dimensions, the width / height of
     * the original asset is used.
     *
     * @param integer $maximumWidth The thumbnail's maximum width in pixels
     * @param integer $maximumHeight The thumbnail's maximum height in pixels
     * @param string $ratioMode Whether the resulting image should be cropped if both edge's sizes are supplied that would hurt the aspect ratio
     * @param boolean $allowUpScaling Whether the resulting image should be upscaled
     * @return Thumbnail
     * @throws \Exception
     * @api
     * @throws \Exception
     */
    public function getThumbnail($maximumWidth = null, $maximumHeight = null, $ratioMode = ImageInterface::RATIOMODE_INSET, $allowUpScaling = null)
    {
        $thumbnailConfiguration = new ThumbnailConfiguration(null, $maximumWidth, null, $maximumHeight, $ratioMode === ImageInterface::RATIOMODE_OUTBOUND, $allowUpScaling);
        return $this->thumbnailService->getThumbnail($this, $thumbnailConfiguration);
    }

    /**
     * An internal method which adds a thumbnail which was generated by the ThumbnailService.
     *
     * @param Thumbnail $thumbnail
     * @return void
     * @see getThumbnail()
     */
    public function addThumbnail(Thumbnail $thumbnail)
    {
        $this->thumbnails->add($thumbnail);
    }

    /**
     * Refreshes this asset after the Resource or any other parameters affecting thumbnails have been modified
     *
     * @return void
     */
    public function refresh()
    {
        $assetClassType = str_replace('Neos\Media\Domain\Model\\', '', get_class($this));
        $this->systemLogger->debug(sprintf('%s: refresh() called, clearing all thumbnails. Filename: %s. PersistentResource SHA1: %s', $assetClassType, $this->getResource()->getFilename(), $this->getResource()->getSha1()));

        // whitelist objects so they can be deleted (even during safe requests)
        $this->persistenceManager->whitelistObject($this);
        foreach ($this->thumbnails as $thumbnail) {
            $this->persistenceManager->whitelistObject($thumbnail);
        }

        $this->thumbnails->clear();
    }

    /**
     * Set the tags assigned to this asset
     *
     * @param Collection $tags
     * @return void
     */
    public function setTags(Collection $tags)
    {
        $this->lastModified = new \DateTime();
        $this->tags = $tags;
    }

    /**
     * Remove a single tag from this asset
     *
     * @param Tag $tag
     * @return boolean
     */
    public function removeTag(Tag $tag)
    {
        if ($this->tags->contains($tag)) {
            $this->lastModified = new \DateTime();
            $this->tags->removeElement($tag);

            return true;
        }

        return false;
    }

    /**
     * Return the asset collections this asset is included in
     *
     * @return Collection
     */
    public function getAssetCollections()
    {
        return $this->assetCollections;
    }

    /**
     * Set the asset collections that include this asset
     *
     * @param Collection $assetCollections
     * @return void
     */
    public function setAssetCollections(Collection $assetCollections)
    {
        $this->lastModified = new \DateTime();
        foreach ($this->assetCollections as $existingAssetCollection) {
            if (!$assetCollections->contains($existingAssetCollection)) {
                $existingAssetCollection->removeAsset($this);
            }
        }
        foreach ($assetCollections as $newAssetCollection) {
            $newAssetCollection->addAsset($this);
        }
        foreach ($this->assetCollections as $assetCollection) {
            if (!$assetCollections->contains($assetCollection)) {
                $assetCollections->add($assetCollection);
            }
        }
        $this->assetCollections = $assetCollections;
    }


    /**
     * Set the asset source identifier for this asset
     *
     * This is an internal method which allows Neos / Flow to keep track of assets which were imported from
     * external asset sources.
     *
     * @param string $assetSourceIdentifier
     */
    public function setAssetSourceIdentifier(string $assetSourceIdentifier): void
    {
        $this->assetSourceIdentifier = $assetSourceIdentifier;
    }

    /**
     * @return string
     */
    public function getAssetSourceIdentifier(): string
    {
        return $this->assetSourceIdentifier;
    }

    /**
     * @return AssetProxyInterface|null
     */
    public function getAssetProxy(): ?AssetProxyInterface
    {
        $assetSource = $this->getAssetSource();
        if ($assetSource === null) {
            $this->systemLogger->notice(sprintf('Asset %s: Invalid asset source "%s"', $this->getIdentifier(), $this->getAssetSourceIdentifier()), LogEnvironment::fromMethodName(__METHOD__));
            return null;
        }
        $importedAsset = $this->importedAssetRepository->findOneByLocalAssetIdentifier($this->getIdentifier());
        if ($importedAsset === null) {
            $this->systemLogger->notice(sprintf('Asset %s: Imported asset not found for asset source %s (%s)', $this->getIdentifier(), $assetSource->getIdentifier(), $assetSource->getLabel()), LogEnvironment::fromMethodName(__METHOD__));
            return null;
        }

        try {
            if ($importedAsset instanceof ImportedAsset) {
                return $assetSource->getAssetProxyRepository()->getAssetProxy($importedAsset->getRemoteAssetIdentifier());
            } else {
                return $assetSource->getAssetProxyRepository()->getAssetProxy($this->getIdentifier());
            }
        } catch (AssetNotFoundExceptionInterface $e) {
            $this->systemLogger->notice(sprintf('Asset %s: Not found in asset source %s (%s)', $this->getIdentifier(), $assetSource->getIdentifier(), $assetSource->getLabel()), LogEnvironment::fromMethodName(__METHOD__));
            return null;
        } catch (AssetSourceConnectionExceptionInterface $e) {
            $this->systemLogger->notice(sprintf('Asset %s: Failed connecting to asset source %s (%s): %s', $this->getIdentifier(), $assetSource->getIdentifier(), $assetSource->getLabel(), $e->getMessage()), LogEnvironment::fromMethodName(__METHOD__));
            return null;
        }
    }

    /**
     * Signals that an asset was created.
     * @deprecated Will be removed with next major version of Neos.Media.
     * Use AssetService::emitAssetCreated signal instead.
     *
     * @Flow\Signal
     * @param AssetInterface $asset
     * @return void
     */
    protected function emitAssetCreated(AssetInterface $asset)
    {
    }

    /**
     * Returns true if the asset is still in use.
     *
     * @return boolean
     * @api
     */
    public function isInUse()
    {
        return $this->assetService->isInUse($this);
    }

    /**
     * Returns the number of times the asset is in use.
     *
     * @return integer
     * @api
     */
    public function getUsageCount()
    {
        return $this->assetService->getUsageCount($this);
    }

    /**
     * @return AssetSourceInterface|null
     */
    private function getAssetSource(): ?AssetSourceInterface
    {
        if ($this->assetSources === []) {
            foreach ($this->assetSourcesConfiguration as $assetSourceIdentifier => $assetSourceConfiguration) {
                if (is_array($assetSourceConfiguration)) {
                    $this->assetSources[$assetSourceIdentifier] = $assetSourceConfiguration['assetSource']::createFromConfiguration($assetSourceIdentifier, $assetSourceConfiguration['assetSourceOptions']);
                }
            }
        }

        $assetSourceIdentifier = $this->getAssetSourceIdentifier();
        if ($assetSourceIdentifier === null) {
            return null;
        }

        return $this->assetSources[$assetSourceIdentifier] ?? null;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Media\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * An Asset, the base for all more specific assets in this package.
 * 
 * It can be used as is to represent any asset for which no better match is available.
 * @\Neos\Flow\Annotations\Entity
 * @\Doctrine\ORM\Mapping\InheritanceType("JOINED")
 */
class Asset extends Asset_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface, \Neos\Flow\Persistence\Aspect\PersistenceMagicInterface {

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
     * @param PersistentResource $resource
     */
    public function __construct()
    {
        $arguments = func_get_args();

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'])) {

        if (!array_key_exists(0, $arguments)) $arguments[0] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ResourceManagement\PersistentResource');
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $resource in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'] = true;
            try {
            
                $methodArguments = [];

                if (array_key_exists(0, $arguments)) $methodArguments['resource'] = $arguments[0];
            
                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['Neos\Flow\Aop\Advice\BeforeAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['Neos\Flow\Aop\Advice\BeforeAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Asset', '__construct', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Asset', '__construct', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
            return;
        }
        if ('Neos\Media\Domain\Model\Asset' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Media\Domain\Model\Asset';
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

        $isSameClass = get_class($this) === 'Neos\Media\Domain\Model\Asset';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Media\Domain\Model\Asset', $classParents) !== false && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== false;

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
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Asset', '__clone', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Asset', '__clone', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Asset', '__clone', $methodArguments, NULL, $result);
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
            
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Asset', 'emitAssetCreated', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAssetCreated']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAssetCreated']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Asset', 'emitAssetCreated', $methodArguments, NULL, $result);
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\PsrSystemLoggerInterface', '', 'systemLogger', '35a47897f04aa042c1ab70dc21ff014f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\PsrSystemLoggerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ResourceManagement\ResourceManager', 'Neos\Flow\ResourceManagement\ResourceManager', 'resourceManager', '5c4c2fb284addde18c78849a54b02875', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ResourceManagement\ResourceManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Service\ThumbnailService', 'Neos\Media\Domain\Service\ThumbnailService', 'thumbnailService', 'b18abfdc1787cb03caeb052cad3d7c0c', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Service\ThumbnailService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Service\AssetService', 'Neos\Media\Domain\Service\AssetService', 'assetService', 'b8a3f9ba29596737396943e4de630328', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Service\AssetService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Repository\AssetRepository', 'Neos\Media\Domain\Repository\AssetRepository', 'assetRepository', '45191f771a429c7decedb6fc0abbcc74', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Repository\AssetRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Repository\ImportedAssetRepository', 'Neos\Media\Domain\Repository\ImportedAssetRepository', 'importedAssetRepository', '663a5f5ad5a4995b3de0fb5853bd6e81', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Repository\ImportedAssetRepository'); });
        $this->assetSourcesConfiguration = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Media.assetSources');
        $this->Flow_Injected_Properties = array (
  0 => 'persistenceManager',
  1 => 'systemLogger',
  2 => 'resourceManager',
  3 => 'thumbnailService',
  4 => 'assetService',
  5 => 'assetRepository',
  6 => 'importedAssetRepository',
  7 => 'assetSourcesConfiguration',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Media/Classes/Domain/Model/Asset.php
#