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
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Media\Domain\Service\ImageService;
use Neos\Media\Exception\ImageFileException;

/**
 * An image
 *
 * @Flow\Entity
 */
class Image_Original extends Asset implements ImageInterface, VariantSupportInterface
{
    use DimensionsTrait;

    /**
     * @var Collection<\Neos\Media\Domain\Model\ImageVariant>
     * @ORM\OneToMany(orphanRemoval=true, cascade={"all"}, mappedBy="originalAsset")
     */
    protected $variants;

    /**
     * @Flow\Inject
     * @var ImageService
     */
    protected $imageService;

    /**
     * Constructor
     *
     * @param PersistentResource $resource
     */
    public function __construct(PersistentResource $resource)
    {
        parent::__construct($resource);
        $this->variants = new ArrayCollection();
    }

    /**
     * @param integer $initializationCause
     * @throws ImageFileException
     * @return void
     */
    public function initializeObject($initializationCause)
    {
        // FIXME: This is a workaround for after the resource management changes that introduced the property.
        if ($this->variants === null) {
            $this->variants = new ArrayCollection();
        }
        if ($initializationCause === ObjectManagerInterface::INITIALIZATIONCAUSE_CREATED) {
            $this->calculateDimensionsFromResource($this->resource);
        }
        parent::initializeObject($initializationCause);
    }

    /**
     * Calculates image width and height from the image resource.
     *
     * @throws ImageFileException
     * @return void
     */
    public function refresh()
    {
        $this->calculateDimensionsFromResource($this->resource);
        parent::refresh();
    }

    /**
     * Adds a variant of this image
     *
     * Note that you should try to re-use variants if you need to adjust them, rather than creating a new
     * variant for every change. Non-used variants will remain in the database and block resource disk space
     * until they are removed explicitly or the original image is deleted.
     *
     * @param ImageVariant $variant The new variant
     * @return void
     * @throws \InvalidArgumentException
     */
    public function addVariant(ImageVariant $variant)
    {
        if ($variant->getOriginalAsset() !== $this) {
            throw new \InvalidArgumentException('Could not add the given ImageVariant to the list of this Image\'s variants because the variant refers to a different original asset.', 1381416726);
        }
        $this->variants->add($variant);
    }

    /**
     * Returns all variants (if any) derived from this asset
     *
     * @return array
     * @api
     */
    public function getVariants()
    {
        return $this->variants->toArray();
    }

    /**
     * Calculates and sets the width and height of this Image asset based
     * on the given PersistentResource.
     *
     * @param PersistentResource $resource
     * @return void
     * @throws ImageFileException
     */
    protected function calculateDimensionsFromResource(PersistentResource $resource)
    {
        try {
            $imageSize = $this->imageService->getImageSize($resource);
        } catch (ImageFileException $imageFileException) {
            throw new ImageFileException(sprintf('Tried to refresh the dimensions and meta data of Image asset "%s" but the file of resource "%s" does not exist or is not a valid image.', $this->getTitle(), $resource->getSha1()), 1381141468, $imageFileException);
        }

        $this->width = is_int($imageSize['width']) ? $imageSize['width'] : null;
        $this->height = is_int($imageSize['height']) ? $imageSize['height'] : null;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Media\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * An image
 * @\Neos\Flow\Annotations\Entity
 */
class Image extends Image_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface, \Neos\Flow\Persistence\Aspect\PersistenceMagicInterface {

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
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Image', '__construct', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Image', '__construct', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
            return;
        }
        if ('Neos\Media\Domain\Model\Image' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Media\Domain\Model\Image';
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

        $isSameClass = get_class($this) === 'Neos\Media\Domain\Model\Image';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Media\Domain\Model\Image', $classParents) !== false && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== false;

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
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Image', '__clone', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Image', '__clone', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Image', '__clone', $methodArguments, NULL, $result);
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
            
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Image', 'emitAssetCreated', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAssetCreated']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAssetCreated']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Image', 'emitAssetCreated', $methodArguments, NULL, $result);
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
  'variants' => 'Doctrine\\Common\\Collections\\Collection<\\Neos\\Media\\Domain\\Model\\ImageVariant>',
  'imageService' => 'Neos\\Media\\Domain\\Service\\ImageService',
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
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Media/Classes/Domain/Model/Image.php
#