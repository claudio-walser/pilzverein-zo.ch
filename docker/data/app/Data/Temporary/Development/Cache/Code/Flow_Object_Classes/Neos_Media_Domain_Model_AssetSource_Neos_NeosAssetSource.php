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

use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Http\HttpRequestHandlerInterface;
use Neos\Flow\Http\Uri;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyRepositoryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceInterface;
use Neos\Media\Domain\Service\AssetService;
use Neos\Media\Domain\Service\ThumbnailService;
use Neos\Flow\Annotations as Flow;
use Neos\Media\Exception\AssetServiceException;
use Neos\Media\Exception\ThumbnailServiceException;
use Psr\Http\Message\UriInterface;

/**
 * Asset source for Neos native assets
 */
class NeosAssetSource_Original implements AssetSourceInterface
{
    /**
     * @var string
     */
    private $assetSourceIdentifier;

    /**
     * @var NeosAssetProxyRepository
     */
    private $assetProxyRepository;

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
     * @var Bootstrap
     */
    protected $bootstrap;

    /**
     * @Flow\InjectConfiguration(path="asyncThumbnails")
     * @var bool
     */
    protected $asyncThumbnails;

    /**
     * @param string $assetSourceIdentifier
     * @param array $assetSourceOptions
     */
    public function __construct(string $assetSourceIdentifier, array $assetSourceOptions)
    {
        if (preg_match('/^[a-z][a-z0-9-]{0,62}[a-z]$/', $assetSourceIdentifier) !== 1) {
            throw new \InvalidArgumentException(sprintf('Invalid asset source identifier "%s". The identifier must match /^[a-z][a-z0-9-]{0,62}[a-z]$/', $assetSourceIdentifier), 1513329665);
        }
        $this->assetSourceIdentifier = $assetSourceIdentifier;

        foreach ($assetSourceOptions as $optionName => $optionValue) {
            switch ($optionName) {
                case 'asyncThumbnails':
                    // If the option value is empty, preserve the default value injected from the Neos:Media:asyncThumbnails setting:
                    if (!empty($optionValue)) {
                        if (!is_bool($optionValue)) {
                            throw new \InvalidArgumentException(sprintf('Asset source option "%s" specified for Neos asset source "%s" must be either true or false. Please check your settings.', $optionName, $assetSourceIdentifier), 1522927471);
                        }
                        $this->asyncThumbnails = $optionValue;
                    }
                break;
                default:
                    throw new \InvalidArgumentException(sprintf('Unknown asset source option "%s" specified for Neos asset source "%s". Please check your settings.', $optionName, $assetSourceIdentifier), 1513327774);
            }
        }
    }

    /**
     * @param string $assetSourceIdentifier
     * @param array $assetSourceOptions
     * @return AssetSourceInterface
     */
    public static function createFromConfiguration(string $assetSourceIdentifier, array $assetSourceOptions): AssetSourceInterface
    {
        return new static($assetSourceIdentifier, $assetSourceOptions);
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->assetSourceIdentifier;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return 'Neos';
    }

    /**
     * @return AssetProxyRepositoryInterface
     */
    public function getAssetProxyRepository(): AssetProxyRepositoryInterface
    {
        if ($this->assetProxyRepository === null) {
            $this->assetProxyRepository = new NeosAssetProxyRepository($this);
        }

        return $this->assetProxyRepository;
    }

    /**
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return false;
    }

    /**
     * Internal method used by NeosAssetProxy
     *
     * @param AssetInterface $asset
     * @return Uri|null
     * @throws ThumbnailServiceException
     * @throws AssetServiceException
     */
    public function getThumbnailUriForAsset(AssetInterface $asset): ?UriInterface
    {
        $actionRequest = ($this->asyncThumbnails ? $this->createActionRequest() : null);
        $thumbnailConfiguration = $this->thumbnailService->getThumbnailConfigurationForPreset('Neos.Media.Browser:Thumbnail', ($actionRequest !== null));
        $thumbnailData = $this->assetService->getThumbnailUriAndSizeForAsset($asset, $thumbnailConfiguration, $actionRequest);
        return isset($thumbnailData['src']) ? new Uri($thumbnailData['src']) : null;
    }

    /**
     * Internal method used by NeosAssetProxy
     *
     * @param AssetInterface $asset
     * @return Uri|null
     * @throws ThumbnailServiceException
     * @throws AssetServiceException
     */
    public function getPreviewUriForAsset(AssetInterface $asset): ?UriInterface
    {
        $actionRequest = ($this->asyncThumbnails ? $this->createActionRequest() : null);
        $thumbnailConfiguration = $this->thumbnailService->getThumbnailConfigurationForPreset('Neos.Media.Browser:Preview', ($actionRequest !== null));
        $thumbnailData = $this->assetService->getThumbnailUriAndSizeForAsset($asset, $thumbnailConfiguration, $actionRequest);
        return isset($thumbnailData['src']) ? new Uri($thumbnailData['src']) : null;
    }

    /**
     * @return ActionRequest|null
     */
    private function createActionRequest(): ?ActionRequest
    {
        $requestHandler = $this->bootstrap->getActiveRequestHandler();
        if ($requestHandler instanceof HttpRequestHandlerInterface) {
            return new ActionRequest($requestHandler->getHttpRequest());
        }
        return null;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Media\Domain\Model\AssetSource\Neos;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Asset source for Neos native assets
 */
final class NeosAssetSource extends NeosAssetSource_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param string $assetSourceIdentifier
     * @param array $assetSourceOptions
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $assetSourceIdentifier in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $assetSourceOptions in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Media\Domain\Model\AssetSource\Neos\NeosAssetSource' === get_class($this)) {
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
  'assetSourceIdentifier' => 'string',
  'assetProxyRepository' => 'Neos\\Media\\Domain\\Model\\AssetSource\\Neos\\NeosAssetProxyRepository',
  'thumbnailService' => 'Neos\\Media\\Domain\\Service\\ThumbnailService',
  'assetService' => 'Neos\\Media\\Domain\\Service\\AssetService',
  'bootstrap' => 'Neos\\Flow\\Core\\Bootstrap',
  'asyncThumbnails' => 'boolean',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Service\ThumbnailService', 'Neos\Media\Domain\Service\ThumbnailService', 'thumbnailService', 'b18abfdc1787cb03caeb052cad3d7c0c', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Service\ThumbnailService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Service\AssetService', 'Neos\Media\Domain\Service\AssetService', 'assetService', 'b8a3f9ba29596737396943e4de630328', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Service\AssetService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Core\Bootstrap', 'Neos\Flow\Core\Bootstrap', 'bootstrap', 'aed14e789673142988a77dfdf496f415', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Core\Bootstrap'); });
        $this->asyncThumbnails = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Media.asyncThumbnails');
        $this->Flow_Injected_Properties = array (
  0 => 'thumbnailService',
  1 => 'assetService',
  2 => 'bootstrap',
  3 => 'asyncThumbnails',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Media/Classes/Domain/Model/AssetSource/Neos/NeosAssetSource.php
#