<?php 

declare(strict_types = 1);

namespace Neos\Media\Domain\Service;

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
use Neos\Flow\Log\PsrSystemLoggerInterface;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\ResourceManagement\Exception;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\Asset;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\HasRemoteOriginalInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\SupportsIptcMetadataInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceAwareInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceInterface;
use Neos\Media\Domain\Model\ImportedAsset;
use Neos\Media\Domain\Model\ImportedAssetManager;
use Neos\Media\Domain\Repository\AssetRepository;
use Neos\Media\Domain\Repository\ImportedAssetRepository;
use Neos\Media\Domain\Strategy\AssetModelMappingStrategyInterface;
use Neos\Media\Exception\AssetSourceServiceException;

/**
 * A service for Asset Sources and Asset Proxies
 *
 * @Flow\Scope("singleton")
 */
class AssetSourceService_Original
{
    /**
     * @Flow\InjectConfiguration(path="assetSources")
     * @var array
     */
    protected $assetSourcesConfiguration;

    /**
     * @Flow\Inject
     * @var ImportedAssetManager
     */
    protected $importedAssetManager;

    /**
     * @Flow\Inject
     * @var ImportedAssetRepository
     */
    protected $importedAssetRepository;

    /**
     * @Flow\Inject
     * @var AssetModelMappingStrategyInterface
     */
    protected $assetModelMappingStrategy;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var AssetRepository
     */
    protected $assetRepository;

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
     * @var AssetSourceInterface[]
     */
    private $assetSources = [];

    /**
     * @return AssetSourceInterface[]
     */
    public function getAssetSources(): array
    {
        $this->initialize();
        return $this->assetSources;
    }

    /**
     * Import a specified asset from the given Asset Source
     *
     * @param string $assetSourceIdentifier Identifier of the asset source to import from, e.g. "neos"
     * @param string $assetIdentifier The asset-source specific identifier of the asset to import
     * @return ImportedAsset
     * @throws AssetSourceServiceException
     * @throws \Exception
     */
    public function importAsset(string $assetSourceIdentifier, string $assetIdentifier): ImportedAsset
    {
        $this->initialize();

        if (!isset($this->assetSources[$assetSourceIdentifier])) {
            throw new AssetSourceServiceException(sprintf('Asset source %s was not found.', $assetSourceIdentifier), 1538753977);
        }

        $assetProxyRepository = $this->assetSources[$assetSourceIdentifier]->getAssetProxyRepository();
        $assetProxy = $assetProxyRepository->getAssetProxy($assetIdentifier);

        if (!$assetProxy instanceof HasRemoteOriginalInterface) {
            $this->systemLogger->error(sprintf('Failed importing an the asset %s from asset source %s because it does not have a remote original.', $assetProxy->getFilename(), $assetSourceIdentifier), LogEnvironment::fromMethodName(__METHOD__));
            throw new AssetSourceServiceException(sprintf('Failed importing an the asset %s from asset source %s because it does not have a remote original.', $assetProxy->getFilename(), $assetSourceIdentifier), 1538754066);
        }

        $importedAsset = $this->importedAssetRepository->findOneByAssetSourceIdentifierAndRemoteAssetIdentifier($assetSourceIdentifier, $assetIdentifier);
        if (!$importedAsset instanceof ImportedAsset) {
            try {
                $assetResource = $this->resourceManager->importResource($assetProxy->getImportStream());
                $assetResource->setFilename($assetProxy->getFilename());
            } catch (Exception $exception) {
                $this->systemLogger->error(sprintf('Failed importing an the asset %s from asset source %s. Original URI: %s. Error: %s', $assetProxy->getFilename(), $assetSourceIdentifier, $assetProxy->getImportStream(), $exception->getMessage()), LogEnvironment::fromMethodName(__METHOD__));
                throw $exception;
            }

            /** @var Asset $asset */
            $assetModelClassName = $this->assetModelMappingStrategy->map($assetResource);
            $asset = new $assetModelClassName($assetResource);

            if (!$asset instanceof AssetSourceAwareInterface) {
                throw new AssetSourceServiceException('The asset type ' . $assetModelClassName . ' does not implement the required MediaAssetsSourceAware interface.', 1516630096);
            }

            $asset->setAssetSourceIdentifier($assetSourceIdentifier);
            if ($assetProxy instanceof SupportsIptcMetadataInterface) {
                $asset->setTitle($assetProxy->getIptcProperty('Title'));
                $asset->setCaption($assetProxy->getIptcProperty('CaptionAbstract'));
                $asset->setCopyrightNotice($assetProxy->getIptcProperty('CopyrightNotice'));
            }

            $this->assetRepository->add($asset);

            $localAssetIdentifier = $this->persistenceManager->getIdentifierByObject($asset);

            $importedAsset = new ImportedAsset(
                $assetSourceIdentifier,
                $assetIdentifier,
                $localAssetIdentifier,
                new \DateTimeImmutable(),
                null
            );
            $this->importedAssetManager->registerImportedAsset($importedAsset);
        }
        return $importedAsset;
    }

    /**
     * @return void
     */
    private function initialize(): void
    {
        if ($this->assetSources === []) {
            foreach ($this->assetSourcesConfiguration as $assetSourceIdentifier => $assetSourceConfiguration) {
                if (is_array($assetSourceConfiguration)) {
                    $this->assetSources[$assetSourceIdentifier] = new $assetSourceConfiguration['assetSource']($assetSourceIdentifier, $assetSourceConfiguration['assetSourceOptions'] ?? []);
                }
            }
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Media\Domain\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A service for Asset Sources and Asset Proxies
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
final class AssetSourceService extends AssetSourceService_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Media\Domain\Service\AssetSourceService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Media\Domain\Service\AssetSourceService', $this);
        if ('Neos\Media\Domain\Service\AssetSourceService' === get_class($this)) {
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
  'assetSourcesConfiguration' => 'array',
  'importedAssetManager' => 'Neos\\Media\\Domain\\Model\\ImportedAssetManager',
  'importedAssetRepository' => 'Neos\\Media\\Domain\\Repository\\ImportedAssetRepository',
  'assetModelMappingStrategy' => 'Neos\\Media\\Domain\\Strategy\\AssetModelMappingStrategyInterface',
  'resourceManager' => 'Neos\\Flow\\ResourceManagement\\ResourceManager',
  'assetRepository' => 'Neos\\Media\\Domain\\Repository\\AssetRepository',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'systemLogger' => 'Neos\\Flow\\Log\\PsrSystemLoggerInterface',
  'assetSources' => 'array<Neos\\Media\\Domain\\Model\\AssetSource\\AssetSourceInterface>',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Media\Domain\Service\AssetSourceService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Media\Domain\Service\AssetSourceService', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->importedAssetManager = new \Neos\Media\Domain\Model\ImportedAssetManager();
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Repository\ImportedAssetRepository', 'Neos\Media\Domain\Repository\ImportedAssetRepository', 'importedAssetRepository', '663a5f5ad5a4995b3de0fb5853bd6e81', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Repository\ImportedAssetRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Strategy\AssetModelMappingStrategyInterface', 'Neos\Media\Domain\Strategy\ConfigurationAssetModelMappingStrategy', 'assetModelMappingStrategy', 'b23095604be2f63a7f617ee8f8cf92d5', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Strategy\AssetModelMappingStrategyInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ResourceManagement\ResourceManager', 'Neos\Flow\ResourceManagement\ResourceManager', 'resourceManager', '5c4c2fb284addde18c78849a54b02875', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ResourceManagement\ResourceManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Repository\AssetRepository', 'Neos\Media\Domain\Repository\AssetRepository', 'assetRepository', '45191f771a429c7decedb6fc0abbcc74', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Repository\AssetRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\PsrSystemLoggerInterface', '', 'systemLogger', '35a47897f04aa042c1ab70dc21ff014f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\PsrSystemLoggerInterface'); });
        $this->assetSourcesConfiguration = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Media.assetSources');
        $this->Flow_Injected_Properties = array (
  0 => 'importedAssetManager',
  1 => 'importedAssetRepository',
  2 => 'assetModelMappingStrategy',
  3 => 'resourceManager',
  4 => 'assetRepository',
  5 => 'persistenceManager',
  6 => 'systemLogger',
  7 => 'assetSourcesConfiguration',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Media/Classes/Domain/Service/AssetSourceService.php
#