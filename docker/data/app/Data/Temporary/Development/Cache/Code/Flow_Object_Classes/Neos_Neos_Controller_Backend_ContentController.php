<?php 
namespace Neos\Neos\Controller\Backend;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\EelHelper\TranslationHelper;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Property\PropertyMapper;
use Neos\Flow\Property\PropertyMappingConfiguration;
use Neos\Flow\Property\TypeConverter\PersistentObjectConverter;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\Asset;
use Neos\Media\Domain\Model\Image;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\Media\Domain\Model\ImageVariant;
use Neos\Media\Domain\Repository\AssetRepository;
use Neos\Media\Domain\Repository\ImageRepository;
use Neos\Media\Domain\Service\ThumbnailService;
use Neos\Media\TypeConverter\AssetInterfaceConverter;
use Neos\Media\TypeConverter\ImageInterfaceArrayPresenter;
use Neos\Neos\Controller\BackendUserTranslationTrait;
use Neos\Neos\Controller\CreateContentContextTrait;
use Neos\Neos\Domain\Model\PluginViewDefinition;
use Neos\Neos\Domain\Model\Site;
use Neos\Neos\Service\PluginService;
use Neos\Neos\TypeConverter\EntityToIdentityConverter;

/**
 * The Neos ContentModule controller; providing backend functionality for the Content Module.
 *
 * @Flow\Scope("singleton")
 */
class ContentController_Original extends ActionController
{
    use BackendUserTranslationTrait;
    use CreateContentContextTrait;

    /**
     * @Flow\Inject
     * @var AssetRepository
     */
    protected $assetRepository;

    /**
     * @Flow\Inject
     * @var ImageRepository
     */
    protected $imageRepository;

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
     * The pluginService
     *
     * @var PluginService
     * @Flow\Inject
     */
    protected $pluginService;

    /**
     * @Flow\Inject
     * @var ImageInterfaceArrayPresenter
     */
    protected $imageInterfaceArrayPresenter;

    /**
     * @Flow\Inject
     * @var EntityToIdentityConverter
     */
    protected $entityToIdentityConverter;

    /**
     * @Flow\Inject
     * @var ThumbnailService
     */
    protected $thumbnailService;

    /**
     * @Flow\Inject
     * @var PropertyMapper
     */
    protected $propertyMapper;

    /**
     * Initialize property mapping as the upload usually comes from the Inspector JavaScript
     */
    public function initializeUploadAssetAction()
    {
        $propertyMappingConfiguration = $this->arguments->getArgument('asset')->getPropertyMappingConfiguration();
        $propertyMappingConfiguration->allowAllProperties();
        $propertyMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, true);
        $propertyMappingConfiguration->setTypeConverterOption(AssetInterfaceConverter::class, AssetInterfaceConverter::CONFIGURATION_ONE_PER_RESOURCE, true);
        $propertyMappingConfiguration->allowCreationForSubProperty('resource');
    }

    /**
     * Upload a new image, and return its metadata.
     *
     * Depending on the $metadata argument it will return asset metadata for the AssetEditor
     * or image metadata for the ImageEditor
     *
     * Note: This action triggers the AssetUploaded signal that can be used to adjust the asset based on the
     * (site) node it was attached to.
     *
     * @param Asset $asset
     * @param string $metadata Type of metadata to return ("Asset" or "Image")
     * @param NodeInterface $node The node the new asset should be assigned to
     * @param string $propertyName The node property name the new asset should be assigned to
     * @return string
     */
    public function uploadAssetAction(Asset $asset, string $metadata, NodeInterface $node, string $propertyName)
    {
        $this->response->setHeader('Content-Type', 'application/json');

        if ($metadata !== 'Asset' && $metadata !== 'Image') {
            $this->response->setStatus(400);
            $result = ['error' => 'Invalid "metadata" type: ' . $metadata];
        } else {
            if ($asset instanceof ImageInterface && $metadata === 'Image') {
                $result = $this->getImageInterfacePreviewData($asset);
            } else {
                $result = $this->getAssetProperties($asset);
            }
            if ($this->persistenceManager->isNewObject($asset)) {
                $this->assetRepository->add($asset);
            }
            $this->emitAssetUploaded($asset, $node, $propertyName);
        }
        return json_encode($result);
    }

    /**
     * Configure property mapping for adding a new image variant.
     *
     * @return void
     */
    public function initializeCreateImageVariantAction()
    {
        $this->arguments->getArgument('asset')->getPropertyMappingConfiguration()
            ->allowOverrideTargetType()
            ->allowAllProperties()
            ->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, true);
    }

    /**
     * Generate a new image variant from given data.
     *
     * @param ImageVariant $asset
     * @return string
     */
    public function createImageVariantAction(ImageVariant $asset)
    {
        if ($this->persistenceManager->isNewObject($asset)) {
            $this->assetRepository->add($asset);
        }

        $propertyMappingConfiguration = new PropertyMappingConfiguration();
        // This will not be sent as "application/json" as we need the JSON string and not the single variables.
        return json_encode($this->entityToIdentityConverter->convertFrom($asset, 'array', [], $propertyMappingConfiguration));
    }

    /**
     * Fetch the metadata for a given image
     *
     * @param ImageInterface $image
     *
     * @return string JSON encoded response
     */
    public function imageWithMetadataAction(ImageInterface $image)
    {
        $this->response->setHeader('Content-Type', 'application/json');
        $imageProperties = $this->getImageInterfacePreviewData($image);

        return json_encode($imageProperties);
    }

    /**
     * Returns important meta data for the given object implementing ImageInterface.
     *
     * Will return an array with the following keys:
     *
     *   "originalImageResourceUri": Uri for the original resource
     *   "previewImageResourceUri": Uri for a preview image with reduced size
     *   "originalDimensions": Dimensions for the original image (width, height, aspectRatio)
     *   "previewDimensions": Dimensions for the preview image (width, height)
     *   "object": object properties like the __identity and __type of the object
     *
     * @param ImageInterface $image The image to retrieve meta data for
     * @return array
     */
    protected function getImageInterfacePreviewData(ImageInterface $image)
    {
        // TODO: Now that we try to support all ImageInterface implementations we should use a strategy here to get the image properties for custom implementations
        if ($image instanceof ImageVariant) {
            $imageProperties = $this->getImageVariantPreviewData($image);
        } else {
            $imageProperties = $this->getImagePreviewData($image);
        }

        $imageProperties['object'] = $this->imageInterfaceArrayPresenter->convertFrom($image, 'string');
        return $imageProperties;
    }

    /**
     * @param Image $image
     * @return array
     */
    protected function getImagePreviewData(Image $image)
    {
        $imageProperties = [
            'originalImageResourceUri' => $this->resourceManager->getPublicPersistentResourceUri($image->getResource()),
            'originalDimensions' => [
                'width' => $image->getWidth(),
                'height' => $image->getHeight(),
                'aspectRatio' => $image->getAspectRatio()
            ],
            'mediaType' => $image->getResource()->getMediaType()
        ];
        $thumbnail = $this->thumbnailService->getThumbnail($image, $this->thumbnailService->getThumbnailConfigurationForPreset('Neos.Media.Browser:Preview'));
        if ($thumbnail !== null) {
            $imageProperties['previewImageResourceUri'] = $this->thumbnailService->getUriForThumbnail($thumbnail);
            $imageProperties['previewDimensions'] = [
                'width' => $thumbnail->getWidth(),
                'height' => $thumbnail->getHeight()
            ];
        }
        return $imageProperties;
    }

    /**
     * @param ImageVariant $imageVariant
     * @return array
     */
    protected function getImageVariantPreviewData(ImageVariant $imageVariant)
    {
        $image = $imageVariant->getOriginalAsset();
        $imageProperties = $this->getImagePreviewData($image);
        return $imageProperties;
    }

    /**
     * @return void
     */
    protected function initializeAssetsWithMetadataAction()
    {
        $propertyMappingConfiguration = $this->arguments->getArgument('assets')->getPropertyMappingConfiguration();
        $propertyMappingConfiguration->allowAllProperties();
        $propertyMappingConfiguration->setTypeConverterOption(AssetInterfaceConverter::class, AssetInterfaceConverter::CONFIGURATION_OVERRIDE_TARGET_TYPE_ALLOWED, true);
        $propertyMappingConfiguration->forProperty('*')->setTypeConverterOption(AssetInterfaceConverter::class, AssetInterfaceConverter::CONFIGURATION_OVERRIDE_TARGET_TYPE_ALLOWED, true);
    }

    /**
     * Fetch the metadata for multiple assets
     *
     * @param array<Neos\Media\Domain\Model\AssetInterface> $assets
     * @return string JSON encoded response
     */
    public function assetsWithMetadataAction(array $assets)
    {
        $this->response->setHeader('Content-Type', 'application/json');

        $result = [];
        foreach ($assets as $asset) {
            $result[] = $this->getAssetProperties($asset);
        }
        return json_encode($result);
    }

    /**
     * @param Asset $asset
     * @return array
     */
    protected function getAssetProperties(Asset $asset)
    {
        $assetProperties = [
            'assetUuid' => $this->persistenceManager->getIdentifierByObject($asset),
            'filename' => $asset->getResource()->getFilename()
        ];
        $thumbnail = $this->thumbnailService->getThumbnail($asset, $this->thumbnailService->getThumbnailConfigurationForPreset('Neos.Media.Browser:Thumbnail'));
        if ($thumbnail !== null) {
            $assetProperties['previewImageResourceUri'] = $this->thumbnailService->getUriForThumbnail($thumbnail);
            $assetProperties['previewSize'] = ['w' => $thumbnail->getWidth(), 'h' => $thumbnail->getHeight()];
        }

        return $assetProperties;
    }

    /**
     * Fetch the configured views for the given master plugin
     *
     * @param string $identifier Specifies the node to look up
     * @param string $workspaceName Name of the workspace to use for querying the node
     * @param array $dimensions Optional list of dimensions and their values which should be used for querying the specified node
     * @return string
     */
    public function pluginViewsAction($identifier = null, $workspaceName = 'live', array $dimensions = [])
    {
        $this->response->setHeader('Content-Type', 'application/json');

        $contentContext = $this->createContentContext($workspaceName, $dimensions);
        /** @var $node NodeInterface */
        $node = $contentContext->getNodeByIdentifier($identifier);

        $views = [];
        if ($node !== null) {
            /** @var $pluginViewDefinition PluginViewDefinition */
            $pluginViewDefinitions = $this->pluginService->getPluginViewDefinitionsByPluginNodeType($node->getNodeType());
            foreach ($pluginViewDefinitions as $pluginViewDefinition) {
                $label = $pluginViewDefinition->getLabel();

                $views[$pluginViewDefinition->getName()] = ['label' => $label];

                $pluginViewNode = $this->pluginService->getPluginViewNodeByMasterPlugin($node, $pluginViewDefinition->getName());
                if ($pluginViewNode === null) {
                    continue;
                }
                $q = new FlowQuery([$pluginViewNode]);
                $page = $q->closest('[instanceof Neos.Neos:Document]')->get(0);
                $uri = $this->uriBuilder
                    ->reset()
                    ->uriFor('show', ['node' => $page], 'Frontend\Node', 'Neos.Neos');
                $views[$pluginViewDefinition->getName()] = [
                    'label' => $label,
                    'pageNode' => [
                        'title' => $page->getLabel(),
                        'uri' => $uri
                    ]
                ];
            }
        }
        return json_encode((object) $views);
    }

    /**
     * Fetch all master plugins that are available in the current
     * workspace.
     *
     * @param string $workspaceName Name of the workspace to use for querying the node
     * @param array $dimensions Optional list of dimensions and their values which should be used for querying the specified node
     * @return string JSON encoded array of node path => label
     */
    public function masterPluginsAction($workspaceName = 'live', array $dimensions = [])
    {
        $this->response->setHeader('Content-Type', 'application/json');

        $contentContext = $this->createContentContext($workspaceName, $dimensions);
        $pluginNodes = $this->pluginService->getPluginNodesWithViewDefinitions($contentContext);

        $masterPlugins = [];
        if (is_array($pluginNodes)) {
            /** @var $pluginNode NodeInterface */
            foreach ($pluginNodes as $pluginNode) {
                if ($pluginNode->isRemoved()) {
                    continue;
                }
                $q = new FlowQuery([$pluginNode]);
                $page = $q->closest('[instanceof Neos.Neos:Document]')->get(0);
                if ($page === null) {
                    continue;
                }
                $translationHelper = new TranslationHelper();
                $masterPlugins[$pluginNode->getIdentifier()] = $translationHelper->translate(
                    'masterPlugins.nodeTypeOnPageLabel',
                    null,
                    ['nodeTypeName' => $translationHelper->translate($pluginNode->getNodeType()->getLabel()), 'pageLabel' => $page->getLabel()],
                    'Main',
                    'Neos.Neos'
               );
            }
        }
        return json_encode((object) $masterPlugins);
    }

    /**
     * Signals that a new asset has been uploaded through the Neos Backend
     *
     * @param Asset $asset The uploaded asset
     * @param NodeInterface $node The node the asset belongs to
     * @param string $propertyName The node property name the asset is assigned to
     * @return void
     * @Flow\Signal
     */
    protected function emitAssetUploaded(Asset $asset, NodeInterface $node, string $propertyName)
    {
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Controller\Backend;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * The Neos ContentModule controller; providing backend functionality for the Content Module.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class ContentController extends ContentController_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        if (get_class($this) === 'Neos\Neos\Controller\Backend\ContentController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Controller\Backend\ContentController', $this);
        if ('Neos\Neos\Controller\Backend\ContentController' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Neos\Controller\Backend\ContentController';
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
            'uploadAssetAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'createImageVariantAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'imageWithMetadataAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'assetsWithMetadataAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'pluginViewsAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'masterPluginsAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'errorAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'emitAssetUploaded' => array(
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
        if (get_class($this) === 'Neos\Neos\Controller\Backend\ContentController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Controller\Backend\ContentController', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();

        $isSameClass = get_class($this) === 'Neos\Neos\Controller\Backend\ContentController';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Neos\Controller\Backend\ContentController', $classParents) !== false && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== false;

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
    }

    /**
     * Autogenerated Proxy Method
     * @param Asset $asset
     * @param string $metadata Type of metadata to return ("Asset" or "Image")
     * @param NodeInterface $node The node the new asset should be assigned to
     * @param string $propertyName The node property name the new asset should be assigned to
     * @return string
     */
    public function uploadAssetAction(\Neos\Media\Domain\Model\Asset $asset, string $metadata, \Neos\ContentRepository\Domain\Model\NodeInterface $node, string $propertyName)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['uploadAssetAction'])) {
            $result = parent::uploadAssetAction($asset, $metadata, $node, $propertyName);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['uploadAssetAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['asset'] = $asset;
                $methodArguments['metadata'] = $metadata;
                $methodArguments['node'] = $node;
                $methodArguments['propertyName'] = $propertyName;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('uploadAssetAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Backend\ContentController', 'uploadAssetAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['uploadAssetAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['uploadAssetAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param ImageVariant $asset
     * @return string
     */
    public function createImageVariantAction(\Neos\Media\Domain\Model\ImageVariant $asset)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createImageVariantAction'])) {
            $result = parent::createImageVariantAction($asset);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['createImageVariantAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['asset'] = $asset;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('createImageVariantAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Backend\ContentController', 'createImageVariantAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createImageVariantAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createImageVariantAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param ImageInterface $image
     * @return string JSON encoded response
     */
    public function imageWithMetadataAction(\Neos\Media\Domain\Model\ImageInterface $image)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['imageWithMetadataAction'])) {
            $result = parent::imageWithMetadataAction($image);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['imageWithMetadataAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['image'] = $image;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('imageWithMetadataAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Backend\ContentController', 'imageWithMetadataAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['imageWithMetadataAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['imageWithMetadataAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param array<Neos\Media\Domain\Model\AssetInterface> $assets
     * @return string JSON encoded response
     */
    public function assetsWithMetadataAction(array $assets)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['assetsWithMetadataAction'])) {
            $result = parent::assetsWithMetadataAction($assets);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['assetsWithMetadataAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['assets'] = $assets;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('assetsWithMetadataAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Backend\ContentController', 'assetsWithMetadataAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['assetsWithMetadataAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['assetsWithMetadataAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param string $identifier Specifies the node to look up
     * @param string $workspaceName Name of the workspace to use for querying the node
     * @param array $dimensions Optional list of dimensions and their values which should be used for querying the specified node
     * @return string
     */
    public function pluginViewsAction($identifier = NULL, $workspaceName = 'live', array $dimensions = array())
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['pluginViewsAction'])) {
            $result = parent::pluginViewsAction($identifier, $workspaceName, $dimensions);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['pluginViewsAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['identifier'] = $identifier;
                $methodArguments['workspaceName'] = $workspaceName;
                $methodArguments['dimensions'] = $dimensions;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('pluginViewsAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Backend\ContentController', 'pluginViewsAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['pluginViewsAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['pluginViewsAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param string $workspaceName Name of the workspace to use for querying the node
     * @param array $dimensions Optional list of dimensions and their values which should be used for querying the specified node
     * @return string JSON encoded array of node path => label
     */
    public function masterPluginsAction($workspaceName = 'live', array $dimensions = array())
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['masterPluginsAction'])) {
            $result = parent::masterPluginsAction($workspaceName, $dimensions);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['masterPluginsAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['workspaceName'] = $workspaceName;
                $methodArguments['dimensions'] = $dimensions;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('masterPluginsAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Backend\ContentController', 'masterPluginsAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['masterPluginsAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['masterPluginsAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return string
     */
    protected function errorAction()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['errorAction'])) {
            $result = parent::errorAction();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['errorAction'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('errorAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Backend\ContentController', 'errorAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['errorAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['errorAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Asset $asset The uploaded asset
     * @param NodeInterface $node The node the asset belongs to
     * @param string $propertyName The node property name the asset is assigned to
     * @return void
     * @\Neos\Flow\Annotations\Signal
     */
    protected function emitAssetUploaded(\Neos\Media\Domain\Model\Asset $asset, \Neos\ContentRepository\Domain\Model\NodeInterface $node, string $propertyName)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAssetUploaded'])) {
            $result = parent::emitAssetUploaded($asset, $node, $propertyName);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAssetUploaded'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['asset'] = $asset;
                $methodArguments['node'] = $node;
                $methodArguments['propertyName'] = $propertyName;
            
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Backend\ContentController', 'emitAssetUploaded', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAssetUploaded']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAssetUploaded']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Backend\ContentController', 'emitAssetUploaded', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAssetUploaded']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAssetUploaded']);
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
);
        $propertyVarTags = array (
  'assetRepository' => 'Neos\\Media\\Domain\\Repository\\AssetRepository',
  'imageRepository' => 'Neos\\Media\\Domain\\Repository\\ImageRepository',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'resourceManager' => 'Neos\\Flow\\ResourceManagement\\ResourceManager',
  'pluginService' => 'Neos\\Neos\\Service\\PluginService',
  'imageInterfaceArrayPresenter' => 'Neos\\Media\\TypeConverter\\ImageInterfaceArrayPresenter',
  'entityToIdentityConverter' => 'Neos\\Neos\\TypeConverter\\EntityToIdentityConverter',
  'thumbnailService' => 'Neos\\Media\\Domain\\Service\\ThumbnailService',
  'propertyMapper' => 'Neos\\Flow\\Property\\PropertyMapper',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'reflectionService' => 'Neos\\Flow\\Reflection\\ReflectionService',
  'mvcPropertyMappingConfigurationService' => 'Neos\\Flow\\Mvc\\Controller\\MvcPropertyMappingConfigurationService',
  'viewConfigurationManager' => 'Neos\\Flow\\Mvc\\ViewConfigurationManager',
  'view' => 'Neos\\Flow\\Mvc\\View\\ViewInterface',
  'viewObjectNamePattern' => 'string',
  'viewFormatToObjectNameMap' => 'array',
  'defaultViewObjectName' => 'string',
  'defaultViewImplementation' => 'string',
  'actionMethodName' => 'string',
  'errorMethodName' => 'string',
  'settings' => 'array',
  'systemLogger' => 'Neos\\Flow\\Log\\SystemLoggerInterface',
  'logger' => 'Psr\\Log\\LoggerInterface',
  'uriBuilder' => 'Neos\\Flow\\Mvc\\Routing\\UriBuilder',
  'validatorResolver' => 'Neos\\Flow\\Validation\\ValidatorResolver',
  'request' => 'Neos\\Flow\\Mvc\\ActionRequest',
  'response' => 'Neos\\Flow\\Mvc\\ActionResponse',
  'arguments' => 'Neos\\Flow\\Mvc\\Controller\\Arguments',
  'controllerContext' => 'Neos\\Flow\\Mvc\\Controller\\ControllerContext',
  'flashMessageContainer' => 'Neos\\Flow\\Mvc\\FlashMessageContainer',
  'supportedMediaTypes' => 'array',
  '_localizationService' => '\\Neos\\Flow\\I18n\\Service',
  '_userService' => '\\Neos\\Neos\\Service\\UserService',
  '_contextFactory' => '\\Neos\\Neos\\Domain\\Service\\ContentContextFactory',
  '_siteRepository' => '\\Neos\\Neos\\Domain\\Repository\\SiteRepository',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Neos'));
        $this->injectLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Psr\Log\LoggerInterface'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Repository\AssetRepository', 'Neos\Media\Domain\Repository\AssetRepository', 'assetRepository', '45191f771a429c7decedb6fc0abbcc74', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Repository\AssetRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Repository\ImageRepository', 'Neos\Media\Domain\Repository\ImageRepository', 'imageRepository', '33339ceb76d9e5c1379db44d4d4723fd', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Repository\ImageRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ResourceManagement\ResourceManager', 'Neos\Flow\ResourceManagement\ResourceManager', 'resourceManager', '5c4c2fb284addde18c78849a54b02875', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ResourceManagement\ResourceManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\PluginService', 'Neos\Neos\Service\PluginService', 'pluginService', '9a5e6d9c8043f9403867eb9224792c01', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\PluginService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\TypeConverter\ImageInterfaceArrayPresenter', 'Neos\Media\TypeConverter\ImageInterfaceArrayPresenter', 'imageInterfaceArrayPresenter', 'b2d1db260ae6d867d914d09494e8299d', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\TypeConverter\ImageInterfaceArrayPresenter'); });
        $this->entityToIdentityConverter = new \Neos\Neos\TypeConverter\EntityToIdentityConverter();
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Service\ThumbnailService', 'Neos\Media\Domain\Service\ThumbnailService', 'thumbnailService', 'b18abfdc1787cb03caeb052cad3d7c0c', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Service\ThumbnailService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Property\PropertyMapper', 'Neos\Flow\Property\PropertyMapper', 'propertyMapper', '2ab4a1ce2ee31715713d0f207f0ac637', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Property\PropertyMapper'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Reflection\ReflectionService', 'Neos\Flow\Reflection\ReflectionService', 'reflectionService', '464c26aa94c66579c050985566cbfc1f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Reflection\ReflectionService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService', 'Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService', 'mvcPropertyMappingConfigurationService', '245f31ad31ca22b8c2b2255e0f65f847', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\ViewConfigurationManager', 'Neos\Flow\Mvc\ViewConfigurationManager', 'viewConfigurationManager', '40e27e95b530777b9b476762cf735a69', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\ViewConfigurationManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\SystemLoggerInterface', 'Neos\Flow\Log\Logger', 'systemLogger', '717e9de4d0309f4f47c821b9257eb5c2', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Validation\ValidatorResolver', 'Neos\Flow\Validation\ValidatorResolver', 'validatorResolver', 'e992f50de62d81bfe770d5c5f1242621', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Validation\ValidatorResolver'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\FlashMessageContainer', 'Neos\Flow\Mvc\FlashMessageContainer', 'flashMessageContainer', 'a5f5265657df54eb081324fb2ff5b8e1', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\FlashMessageContainer'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\I18n\Service', 'Neos\Flow\I18n\Service', '_localizationService', 'bdcad09aa1b6973b35f2987887987892', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\Service'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\UserService', 'Neos\Neos\Service\UserService', '_userService', '3af75a289d0337400c3d43d557f82c49', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\UserService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Service\ContentContextFactory', 'Neos\Neos\Domain\Service\ContentContextFactory', '_contextFactory', 'bf6447fb48e80589ca3a024bc3882005', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Service\ContentContextFactory'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\SiteRepository', 'Neos\Neos\Domain\Repository\SiteRepository', '_siteRepository', '42785f5eca4dff104f1860b84f531a9f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\SiteRepository'); });
        $this->defaultViewImplementation = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow.mvc.view.defaultImplementation');
        $this->Flow_Injected_Properties = array (
  0 => 'settings',
  1 => 'logger',
  2 => 'assetRepository',
  3 => 'imageRepository',
  4 => 'persistenceManager',
  5 => 'resourceManager',
  6 => 'pluginService',
  7 => 'imageInterfaceArrayPresenter',
  8 => 'entityToIdentityConverter',
  9 => 'thumbnailService',
  10 => 'propertyMapper',
  11 => 'objectManager',
  12 => 'reflectionService',
  13 => 'mvcPropertyMappingConfigurationService',
  14 => 'viewConfigurationManager',
  15 => 'systemLogger',
  16 => 'validatorResolver',
  17 => 'flashMessageContainer',
  18 => '_localizationService',
  19 => '_userService',
  20 => '_contextFactory',
  21 => '_siteRepository',
  22 => 'defaultViewImplementation',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Controller/Backend/ContentController.php
#