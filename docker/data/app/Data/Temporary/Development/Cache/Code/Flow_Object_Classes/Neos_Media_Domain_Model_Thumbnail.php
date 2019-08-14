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

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Media\Domain\Service\ThumbnailService;
use Neos\Utility\Arrays;
use Neos\Media\Domain\Strategy\ThumbnailGeneratorStrategy;

/**
 * A system-generated preview version of an Asset
 *
 * @Flow\Entity
 * @ORM\Table(
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="originalasset_configurationhash",columns={"originalasset", "configurationhash"})
 *  }
 * )
 */
class Thumbnail_Original implements ImageInterface
{
    use DimensionsTrait;
    use QualityTrait;

    /**
     * @var ThumbnailGeneratorStrategy
     * @Flow\Inject
     */
    protected $generatorStrategy;

    /**
     * @var ThumbnailService
     * @Flow\Inject
     */
    protected $thumbnailService;

    /**
     * @var Asset
     * @ORM\ManyToOne(cascade={"persist", "merge"}, inversedBy="thumbnails")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $originalAsset;

    /**
     * @var PersistentResource
     * @ORM\OneToOne(orphanRemoval = true, cascade={"all"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $resource;

    /**
     * @var string Supports the 'resource://Package.Key/Public/File' format
     * @ORM\Column(nullable=true)
     */
    protected $staticResource;

    /**
     * @var array<string>
     * @ORM\Column(type="flow_json_array")
     */
    protected $configuration;

    /**
     * @var string
     * @ORM\Column(length=32)
     */
    protected $configurationHash;

    /**
     * @var bool
     * @Flow\Transient
     */
    protected $async;

    /**
     * Constructs a new Thumbnail
     *
     * @param AssetInterface $originalAsset The original asset this variant is derived from
     * @param ThumbnailConfiguration $configuration
     * @throws \Neos\Media\Exception
     */
    public function __construct(AssetInterface $originalAsset, ThumbnailConfiguration $configuration)
    {
        $this->originalAsset = $originalAsset;
        $this->setConfiguration($configuration);
        $this->async = $configuration->isAsync();
    }

    /**
     * Initializes this thumbnail
     *
     * @param integer $initializationCause
     * @return void
     */
    public function initializeObject($initializationCause)
    {
        if ($initializationCause === ObjectManagerInterface::INITIALIZATIONCAUSE_CREATED) {
            if ($this->async === false) {
                $this->refresh();
            }
            $this->emitThumbnailCreated($this);
        }
    }

    /**
     * Post persistence lifecycle callback
     *
     * @ORM\PostPersist
     */
    public function onPostPersist()
    {
        $this->thumbnailService->emitThumbnailPersisted($this);
    }

    /**
     * Returns the Asset this thumbnail is derived from
     *
     * @return ImageInterface
     */
    public function getOriginalAsset()
    {
        return $this->originalAsset;
    }

    /**
     * @param ThumbnailConfiguration $configuration
     * @return void
     */
    protected function setConfiguration(ThumbnailConfiguration $configuration)
    {
        $this->configuration = $configuration->toArray();
        $this->configurationHash = $configuration->getHash();
    }

    /**
     * @param string $value
     * @return mixed
     */
    public function getConfigurationValue($value)
    {
        return Arrays::getValueByPath($this->configuration, $value);
    }

    /**
     * PersistentResource of this thumbnail
     *
     * @return PersistentResource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param PersistentResource $resource
     * @return void
     */
    public function setResource(PersistentResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function getStaticResource()
    {
        return $this->staticResource;
    }

    /**
     * @param string $staticResource
     * @return void
     */
    public function setStaticResource($staticResource)
    {
        $this->staticResource = $staticResource;
    }

    /**
     * @param integer $width
     * @return void
     */
    public function setWidth($width)
    {
        $this->width = (integer)$width;
    }

    /**
     * @param integer $height
     * @return void
     */
    public function setHeight($height)
    {
        $this->height = (integer)$height;
    }

    /**
     * @param integer $quality
     * @return void
     */
    public function setQuality($quality)
    {
        $this->quality = (integer)$quality;
    }

    /**
     * Refreshes this asset after the PersistentResource has been modified
     *
     * @return void
     */
    public function refresh()
    {
        $this->generatorStrategy->refresh($this);
    }

    /**
     * Signals that a thumbnail was created.
     * @deprecated Will be removed with next major version of Neos.Media.
     * Use ThumbnailService::emitThumbnailCreated signal instead.
     *
     * @Flow\Signal
     * @param Thumbnail $thumbnail
     * @return void
     */
    protected function emitThumbnailCreated(Thumbnail $thumbnail)
    {
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Media\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A system-generated preview version of an Asset
 * @\Neos\Flow\Annotations\Entity
 * @\Doctrine\ORM\Mapping\Table(uniqueConstraints={ @\Doctrine\ORM\Mapping\UniqueConstraint(name="originalasset_configurationhash", columns={ "originalasset", "configurationhash" }) })
 */
class Thumbnail extends Thumbnail_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface, \Neos\Flow\Persistence\Aspect\PersistenceMagicInterface {

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
     * @param AssetInterface $originalAsset The original asset this variant is derived from
     * @param ThumbnailConfiguration $configuration
     * @throws \Neos\Media\Exception
     */
    public function __construct()
    {
        $arguments = func_get_args();

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'])) {

        if (!array_key_exists(1, $arguments)) $arguments[1] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Model\ThumbnailConfiguration');
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $originalAsset in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $configuration in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'] = true;
            try {
            
                $methodArguments = [];

                if (array_key_exists(0, $arguments)) $methodArguments['originalAsset'] = $arguments[0];
                if (array_key_exists(1, $arguments)) $methodArguments['configuration'] = $arguments[1];
            
                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['Neos\Flow\Aop\Advice\BeforeAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['Neos\Flow\Aop\Advice\BeforeAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Thumbnail', '__construct', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Thumbnail', '__construct', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
            return;
        }
        if ('Neos\Media\Domain\Model\Thumbnail' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Media\Domain\Model\Thumbnail';
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
            'emitThumbnailCreated' => array(
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

        $isSameClass = get_class($this) === 'Neos\Media\Domain\Model\Thumbnail';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Media\Domain\Model\Thumbnail', $classParents) !== false && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== false;

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
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Thumbnail', '__clone', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Thumbnail', '__clone', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Thumbnail', '__clone', $methodArguments, NULL, $result);
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
     * @param Thumbnail $thumbnail
     * @return void
     * @\Neos\Flow\Annotations\Signal
     */
    protected function emitThumbnailCreated(\Neos\Media\Domain\Model\Thumbnail $thumbnail)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitThumbnailCreated'])) {
            $result = parent::emitThumbnailCreated($thumbnail);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitThumbnailCreated'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['thumbnail'] = $thumbnail;
            
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Thumbnail', 'emitThumbnailCreated', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitThumbnailCreated']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitThumbnailCreated']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Thumbnail', 'emitThumbnailCreated', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitThumbnailCreated']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitThumbnailCreated']);
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
  0 => 'async',
);
        $propertyVarTags = array (
  'generatorStrategy' => 'Neos\\Media\\Domain\\Strategy\\ThumbnailGeneratorStrategy',
  'thumbnailService' => 'Neos\\Media\\Domain\\Service\\ThumbnailService',
  'originalAsset' => 'Neos\\Media\\Domain\\Model\\Asset',
  'resource' => 'Neos\\Flow\\ResourceManagement\\PersistentResource',
  'staticResource' => 'string Supports the \'resource://Package.Key/Public/File\' format',
  'configuration' => 'array<string>',
  'configurationHash' => 'string',
  'async' => 'boolean',
  'width' => 'integer',
  'height' => 'integer',
  'quality' => 'integer',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Strategy\ThumbnailGeneratorStrategy', 'Neos\Media\Domain\Strategy\ThumbnailGeneratorStrategy', 'generatorStrategy', 'ea6b455dfa28d2932bd653e0cb74ed1d', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Strategy\ThumbnailGeneratorStrategy'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Service\ThumbnailService', 'Neos\Media\Domain\Service\ThumbnailService', 'thumbnailService', 'b18abfdc1787cb03caeb052cad3d7c0c', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Service\ThumbnailService'); });
        $this->Flow_Injected_Properties = array (
  0 => 'generatorStrategy',
  1 => 'thumbnailService',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Media/Classes/Domain/Model/Thumbnail.php
#