<?php 
namespace Neos\Media\Domain\Model\AssetSource\Neos;

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
use Neos\Flow\Http\Uri;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\Asset;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceInterface;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\Media\Exception\AssetServiceException;
use Neos\Media\Exception\ThumbnailServiceException;
use Psr\Http\Message\UriInterface;

class NeosAssetProxy_Original implements AssetProxyInterface
{
    /**
     * @var NeosAssetSource
     */
    private $assetSource;

    /**
     * @var Asset
     */
    private $asset;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @param AssetInterface $asset
     * @param NeosAssetSource $assetSource
     */
    public function __construct(AssetInterface $asset, NeosAssetSource $assetSource)
    {
        if (!$asset instanceof Asset) {
            throw new \RuntimeException(sprintf('%s currently only support the concrete Asset implementation because several methods are not part of the AssetInterface yet.', __CLASS__), 1524128585);
        }

        $this->asset = $asset;
        $this->assetSource = $assetSource;
    }

    /**
     * @return AssetSourceInterface
     */
    public function getAssetSource(): AssetSourceInterface
    {
        return $this->assetSource;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->persistenceManager->getIdentifierByObject($this->asset);
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        if (empty($this->asset->getTitle())) {
            return $this->asset->getResource()->getFilename();
        }
        return $this->asset->getTitle();
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->asset->getResource()->getFilename();
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastModified(): \DateTimeInterface
    {
        return $this->asset->getLastModified();
    }

    /**
     * @return int
     */
    public function getFileSize(): int
    {
        return $this->asset->getResource()->getFileSize() ?? 0;
    }

    /**
     * @return string
     */
    public function getMediaType(): string
    {
        return $this->asset->getMediaType() ?? 'application/octet-stream';
    }

    /**
     * @return int|null
     */
    public function getWidthInPixels(): ?int
    {
        if ($this->asset instanceof ImageInterface) {
            return $this->asset->getWidth() ?? null;
        }
        return 0;
    }

    /**
     * @return int|null
     */
    public function getHeightInPixels(): ?int
    {
        if ($this->asset instanceof ImageInterface) {
            return $this->asset->getHeight() ?? 0;
        }
        return 0;
    }

    /**
     * @return AssetInterface
     */
    public function getAsset(): AssetInterface
    {
        return $this->asset;
    }

    /**
     * @return UriInterface
     * @throws AssetServiceException
     * @throws ThumbnailServiceException
     */
    public function getThumbnailUri(): ?UriInterface
    {
        return $this->assetSource->getThumbnailUriForAsset($this->asset);
    }

    /**
     * @return UriInterface
     * @throws AssetServiceException
     * @throws ThumbnailServiceException
     */
    public function getPreviewUri(): ?UriInterface
    {
        return $this->assetSource->getPreviewUriForAsset($this->asset);
    }

    /**
     * @return null|UriInterface
     */
    public function getImportStream(): ?UriInterface
    {
        $uriString = $this->resourceManager->getPublicPersistentResourceUri($this->asset->getResource());
        return $uriString !== false ? new Uri($uriString) : null;
    }

    /**
     * @return string
     */
    public function getLocalAssetIdentifier(): ?string
    {
        return $this->persistenceManager->getIdentifierByObject($this->asset);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Media\Domain\Model\AssetSource\Neos;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 */
final class NeosAssetProxy extends NeosAssetProxy_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param AssetInterface $asset
     * @param NeosAssetSource $assetSource
     */
    public function __construct()
    {
        $arguments = func_get_args();

        if (!array_key_exists(1, $arguments)) $arguments[1] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Model\AssetSource\Neos\NeosAssetSource');
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $asset in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $assetSource in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Media\Domain\Model\AssetSource\Neos\NeosAssetProxy' === get_class($this)) {
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
  'assetSource' => 'Neos\\Media\\Domain\\Model\\AssetSource\\Neos\\NeosAssetSource',
  'asset' => 'Neos\\Media\\Domain\\Model\\Asset',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'resourceManager' => 'Neos\\Flow\\ResourceManagement\\ResourceManager',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ResourceManagement\ResourceManager', 'Neos\Flow\ResourceManagement\ResourceManager', 'resourceManager', '5c4c2fb284addde18c78849a54b02875', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ResourceManagement\ResourceManager'); });
        $this->Flow_Injected_Properties = array (
  0 => 'persistenceManager',
  1 => 'resourceManager',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Media/Classes/Domain/Model/AssetSource/Neos/NeosAssetProxy.php
#