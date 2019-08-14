<?php 

namespace Neos\Media\Browser\Controller;

/*
 * This file is part of the Neos.Media.Browser package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\Common\Persistence\Proxy as DoctrineProxy;
use Doctrine\ORM\EntityNotFoundException;
use Neos\Error\Messages\Error;
use Neos\Error\Messages\Message;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Translator;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\Exception\ForwardException;
use Neos\Flow\Mvc\Exception\NoSuchArgumentException;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Flow\Mvc\Exception\UnsupportedRequestTypeException;
use Neos\Flow\Mvc\View\JsonView;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Flow\Package\PackageManagerInterface;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Flow\Property\TypeConverter\PersistentObjectConverter;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\FluidAdaptor\View\TemplateView;
use Neos\Media\Browser\Domain\ImageMapper;
use Neos\Media\Browser\Domain\Session\BrowserState;
use Neos\Media\Domain\Model\Adjustment\CropImageAdjustment;
use Neos\Media\Domain\Model\Asset;
use Neos\Media\Domain\Model\AssetCollection;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\AssetSource\AssetNotFoundExceptionInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyRepositoryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceConnectionExceptionInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceInterface;
use Neos\Media\Domain\Model\AssetSource\AssetTypeFilter;
use Neos\Media\Domain\Model\AssetSource\Neos\NeosAssetProxy;
use Neos\Media\Domain\Model\AssetSource\SupportsCollectionsInterface;
use Neos\Media\Domain\Model\AssetSource\SupportsSortingInterface;
use Neos\Media\Domain\Model\AssetSource\SupportsTaggingInterface;
use Neos\Media\Domain\Model\AssetVariantInterface;
use Neos\Media\Domain\Model\ImageVariant;
use Neos\Media\Domain\Model\Tag;
use Neos\Media\Domain\Model\VariantSupportInterface;
use Neos\Media\Domain\Repository\AssetCollectionRepository;
use Neos\Media\Domain\Repository\AssetRepository;
use Neos\Media\Domain\Repository\TagRepository;
use Neos\Media\Domain\Service\AssetService;
use Neos\Media\Exception\AssetServiceException;
use Neos\Media\TypeConverter\AssetInterfaceConverter;
use Neos\Neos\Controller\BackendUserTranslationTrait;
use Neos\Neos\Controller\CreateContentContextTrait;
use Neos\Neos\Domain\Repository\DomainRepository;
use Neos\Neos\Domain\Repository\SiteRepository;
use Neos\Utility\Exception\FilesException;
use Neos\Utility\Files;
use Neos\Utility\MediaTypes;

/**
 * Controller for asset handling
 *
 * @Flow\Scope("singleton")
 */
class AssetController_Original extends ActionController
{
    use CreateContentContextTrait;
    use BackendUserTranslationTrait;

    protected const TAG_GIVEN = 0;
    protected const TAG_ALL = 1;
    protected const TAG_NONE = 2;

    protected const COLLECTION_GIVEN = 0;
    protected const COLLECTION_ALL = 1;

    /**
     * @var array
     */
    protected $viewFormatToObjectNameMap = [
        'html' => TemplateView::class,
        'json' => JsonView::class
    ];

    /**
     * @Flow\Inject
     * @var SiteRepository
     */
    protected $siteRepository;

    /**
     * @Flow\Inject
     * @var DomainRepository
     */
    protected $domainRepository;

    /**
     * @Flow\Inject
     * @var AssetRepository
     */
    protected $assetRepository;

    /**
     * @Flow\Inject
     * @var TagRepository
     */
    protected $tagRepository;

    /**
     * @Flow\Inject
     * @var AssetCollectionRepository
     */
    protected $assetCollectionRepository;

    /**
     * @Flow\Inject
     * @var PackageManagerInterface
     */
    protected $packageManager;

    /**
     * @Flow\Inject(lazy = false)
     * @var BrowserState
     */
    protected $browserState;

    /**
     * @Flow\Inject
     * @var AssetService
     */
    protected $assetService;

    /**
     * @Flow\Inject
     * @var Translator
     */
    protected $translator;

    /**
     * @Flow\Inject
     * @var \Neos\Media\Domain\Service\AssetSourceService
     */
    protected $assetSourceService;

    /**
     * @var AssetSourceInterface[]
     */
    protected $assetSources = [];

    /**
     * @Flow\InjectConfiguration(path="imageProfiles", package="Neos.Media")
     * @var array
     */
    protected $imageProfilesConfiguration;

    /**
     * @return void
     */
    public function initializeObject(): void
    {
        $domain = $this->domainRepository->findOneByActiveRequest();

        // Set active asset collection to the current site's asset collection, if it has one, on the first view if a matching domain is found
        if ($domain !== null && !$this->browserState->get('activeAssetCollection') && $this->browserState->get('automaticAssetCollectionSelection') !== true && $domain->getSite()->getAssetCollection() !== null) {
            $this->browserState->set('activeAssetCollection', $domain->getSite()->getAssetCollection());
            $this->browserState->set('automaticAssetCollectionSelection', true);
        }

        $this->assetSources = $this->assetSourceService->getAssetSources();
    }

    /**
     * Set common variables on the view
     *
     * @param ViewInterface $view
     * @return void
     */
    protected function initializeView(ViewInterface $view): void
    {
        $view->assignMultiple([
            'view' => $this->browserState->get('view'),
            'sortBy' => $this->browserState->get('sortBy'),
            'sortDirection' => $this->browserState->get('sortDirection'),
            'filter' => $this->browserState->get('filter'),
            'activeTag' => $this->browserState->get('activeTag'),
            'activeAssetCollection' => $this->browserState->get('activeAssetCollection'),
            'assetSources' => $this->assetSources,
            'variantsTabFeatureEnabled' => $this->settings['features']['variantsTab']['enable']
        ]);
    }

    /**
     * List existing assets
     *
     * @param string $view
     * @param string $sortBy
     * @param string $sortDirection
     * @param string $filter
     * @param int $tagMode
     * @param Tag $tag
     * @param string $searchTerm
     * @param int $collectionMode
     * @param AssetCollection $assetCollection
     * @param string $assetSourceIdentifier
     * @return void
     * @throws FilesException
     */
    public function indexAction($view = null, $sortBy = null, $sortDirection = null, $filter = null, $tagMode = self::TAG_GIVEN, Tag $tag = null, $searchTerm = null, $collectionMode = self::COLLECTION_GIVEN, AssetCollection $assetCollection = null, $assetSourceIdentifier = null): void
    {
        $allCollectionsCount = 0;
        // Calculating the asset-count of all collections before applying filters.
        foreach ($this->assetSources as $assetSource) {
            $allCollectionsCount += $assetSource->getAssetProxyRepository()->countAll();
        }

        // First, apply all options given to indexAction() and save them in the BrowserState object.
        // Note that the order of these apply*() method calls plays a role, because they may depend on previous results:
        $this->applyActiveAssetSourceToBrowserState($assetSourceIdentifier);
        $this->applyAssetCollectionOptionsToBrowserState($collectionMode, $assetCollection);

        $activeAssetSource = $this->getAssetSourceFromBrowserState();
        $assetProxyRepository = $activeAssetSource->getAssetProxyRepository();
        $activeAssetCollection = $this->getActiveAssetCollectionFromBrowserState();

        $this->applyViewOptionsToBrowserState($view, $sortBy, $sortDirection, $filter);
        $this->applyTagToBrowserState($tagMode, $tag, $activeAssetCollection);

        // Second, apply the options from the browser state to the Asset Proxy Repository
        $this->applyAssetTypeFilterFromBrowserState($assetProxyRepository);
        $this->applySortingFromBrowserState($assetProxyRepository);
        $this->applyAssetCollectionFilterFromBrowserState($assetProxyRepository);

        $assetCollections = [];
        $tags = [];
        $assetProxies = [];

        $allCount = 0;
        $searchResultCount = 0;
        $untaggedCount = 0;

        try {
            foreach ($this->assetCollectionRepository->findAll()->toArray() as $retrievedAssetCollection) {
                assert($retrievedAssetCollection instanceof AssetCollection);
                $assetCollections[] = ['object' => $retrievedAssetCollection, 'count' => $this->assetRepository->countByAssetCollection($retrievedAssetCollection)];
            }

            foreach ($activeAssetCollection !== null ? $activeAssetCollection->getTags() : $this->tagRepository->findAll() as $retrievedTag) {
                assert($retrievedTag instanceof Tag);
                $tags[] = ['object' => $retrievedTag, 'count' => $this->assetRepository->countByTag($retrievedTag, $activeAssetCollection)];
            }

            if ($searchTerm !== null) {
                $assetProxies = $assetProxyRepository->findBySearchTerm($searchTerm);
                $this->view->assign('searchTerm', $searchTerm);
            } elseif ($this->browserState->get('tagMode') === self::TAG_NONE) {
                $assetProxies = $assetProxyRepository->findUntagged();
            } elseif ($this->browserState->get('activeTag') !== null) {
                $assetProxies = $assetProxyRepository->findByTag($this->browserState->get('activeTag'));
            } else {
                $assetProxies = $assetProxyRepository->findAll();
            }

            $allCount = ($activeAssetCollection ? $this->assetRepository->countByAssetCollection($activeAssetCollection) : $allCollectionsCount);
            $searchResultCount = isset($assetProxies) ? $assetProxies->count() : 0;
            $untaggedCount = ($assetProxyRepository instanceof SupportsTaggingInterface ? $assetProxyRepository->countUntagged() : 0);
        } catch (AssetSourceConnectionExceptionInterface $e) {
            $this->view->assign('connectionError', $e);
        }

        $this->view->assignMultiple([
            'tags' => $tags,
            'allCollectionsCount' => $allCollectionsCount,
            'allCount' => $allCount,
            'searchResultCount' => $searchResultCount,
            'untaggedCount' => $untaggedCount,
            'tagMode' => $this->browserState->get('tagMode'),
            'assetProxies' => $assetProxies,
            'assetCollections' => $assetCollections,
            'argumentNamespace' => $this->request->getArgumentNamespace(),
            'maximumFileUploadSize' => $this->getMaximumFileUploadSize(),
            'humanReadableMaximumFileUploadSize' => Files::bytesToSizeString($this->getMaximumFileUploadSize()),
            'activeAssetSource' => $activeAssetSource,
            'activeAssetSourceSupportsSorting' => $assetProxyRepository instanceof SupportsSortingInterface
        ]);
    }

    /**
     * New asset form
     *
     * @return void
     */
    public function newAction(): void
    {
        try {
            $maximumFileUploadSize = $this->getMaximumFileUploadSize();
        } catch (FilesException $e) {
            $maximumFileUploadSize = null;
        }

        $this->view->assignMultiple([
            'tags' => $this->tagRepository->findAll(),
            'assetCollections' => $this->assetCollectionRepository->findAll(),
            'maximumFileUploadSize' => $maximumFileUploadSize,
            'humanReadableMaximumFileUploadSize' => Files::bytesToSizeString($maximumFileUploadSize)
        ]);
    }

    /**
     * @param Asset $asset
     * @return void
     */
    public function replaceAssetResourceAction(Asset $asset): void
    {
        try {
            $maximumFileUploadSize = $this->getMaximumFileUploadSize();
        } catch (FilesException $e) {
            $maximumFileUploadSize = null;
        }

        $this->view->assignMultiple([
            'asset' => $asset,
            'maximumFileUploadSize' => $maximumFileUploadSize,
            'redirectPackageEnabled' => $this->packageManager->isPackageAvailable('Neos.RedirectHandler'),
            'humanReadableMaximumFileUploadSize' => Files::bytesToSizeString($maximumFileUploadSize)
        ]);
    }

    /**
     * Show an asset
     *
     * @param string $assetSourceIdentifier
     * @param string $assetProxyIdentifier
     * @return void
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     */
    public function showAction(string $assetSourceIdentifier, string $assetProxyIdentifier): void
    {
        if (!isset($this->assetSources[$assetSourceIdentifier])) {
            throw new \RuntimeException('Given asset source is not configured.', 1509702178);
        }

        $assetProxyRepository = $this->assetSources[$assetSourceIdentifier]->getAssetProxyRepository();
        try {
            $assetProxy = $assetProxyRepository->getAssetProxy($assetProxyIdentifier);

            $this->view->assignMultiple([
                'assetProxy' => $assetProxy,
                'assetCollections' => $this->assetCollectionRepository->findAll()
            ]);
        } catch (AssetNotFoundExceptionInterface $e) {
            $this->throwStatus(404, 'Asset not found');
        } catch (AssetSourceConnectionExceptionInterface $e) {
            $this->view->assign('connectionError', $e);
        }
    }

    /**
     * Edit an asset
     *
     * @param string $assetSourceIdentifier
     * @param string $assetProxyIdentifier
     * @return void
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     */
    public function editAction(string $assetSourceIdentifier, string $assetProxyIdentifier): void
    {
        if (!isset($this->assetSources[$assetSourceIdentifier])) {
            throw new \RuntimeException('Given asset source is not configured.', 1509632166);
        }

        $assetSource = $this->assetSources[$assetSourceIdentifier];
        $assetProxyRepository = $assetSource->getAssetProxyRepository();

        try {
            $assetProxy = $assetProxyRepository->getAssetProxy($assetProxyIdentifier);

            $tags = [];
            $contentPreview = 'ContentDefault';
            if ($assetProxyRepository instanceof SupportsTaggingInterface && $assetProxyRepository instanceof SupportsCollectionsInterface) {
                // TODO: For generic implementation (allowing other asset sources to provide asset collections), the following needs to be refactored:

                if ($assetProxy instanceof NeosAssetProxy) {
                    /** @var Asset $asset */
                    $asset = $assetProxy->getAsset();
                    $assetCollections = $asset->getAssetCollections();
                    $tags = $assetCollections->count() > 0 ? $this->tagRepository->findByAssetCollections($assetCollections->toArray()) : $this->tagRepository->findAll();

                    switch ($asset->getFileExtension()) {
                        case 'pdf':
                            $contentPreview = 'ContentPdf';
                            break;
                    }
                }
            }

            $this->view->assignMultiple([
                'tags' => $tags,
                'assetProxy' => $assetProxy,
                'assetCollections' => $this->assetCollectionRepository->findAll(),
                'contentPreview' => $contentPreview,
                'assetSource' => $assetSource,
                'canShowVariants' => ($assetProxy instanceof NeosAssetProxy) && ($assetProxy->getAsset() instanceof VariantSupportInterface)
            ]);
        } catch (AssetNotFoundExceptionInterface $e) {
            $this->throwStatus(404, 'Asset not found');
        } catch (AssetSourceConnectionExceptionInterface $e) {
            $this->view->assign('connectionError', $e);
        }
    }

    /**
     * Display variants of an asset
     *
     * @param string $assetSourceIdentifier
     * @param string $assetProxyIdentifier
     * @param string $overviewAction
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     */
    public function variantsAction(string $assetSourceIdentifier, string $assetProxyIdentifier, string $overviewAction): void
    {
        if (!isset($this->assetSources[$assetSourceIdentifier])) {
            throw new \RuntimeException('Given asset source is not configured.', 1509632166);
        }

        $assetSource = $this->assetSources[$assetSourceIdentifier];
        $assetProxyRepository = $assetSource->getAssetProxyRepository();

        try {
            $assetProxy = $assetProxyRepository->getAssetProxy($assetProxyIdentifier);
            $asset = $this->persistenceManager->getObjectByIdentifier($assetProxy->getLocalAssetIdentifier(), Asset::class);

            /** @var VariantSupportInterface $originalAsset */
            $originalAsset = ($asset instanceof AssetVariantInterface ? $asset->getOriginalAsset() : $asset);

            $variantInformation = array_map(static function (AssetVariantInterface $imageVariant) {
                return (new ImageMapper($imageVariant))->getMappingResult();
            }, $originalAsset->getVariants());

            $this->view->assignMultiple([
                'assetProxy' => $assetProxy,
                'asset' => $originalAsset,
                'assetSource' => $assetSource,
                'imageProfiles' => $this->imageProfilesConfiguration,
                'overviewAction' => $overviewAction,
                'originalInformation' => (new ImageMapper($asset))->getMappingResult(),
                'variantsInformation' => $variantInformation
            ]);
        } catch (AssetNotFoundExceptionInterface $e) {
            $this->throwStatus(404, 'Original asset not found');
        } catch (AssetSourceConnectionExceptionInterface $e) {
            $this->view->assign('connectionError', $e);
        }
    }

    /**
     * @return void
     * @throws NoSuchArgumentException
     */
    protected function initializeUpdateAction(): void
    {
        $assetMappingConfiguration = $this->arguments->getArgument('asset')->getPropertyMappingConfiguration();
        $assetMappingConfiguration->allowProperties('title', 'resource', 'tags', 'assetCollections');
        $assetMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, true);
    }

    /**
     * Update an asset
     *
     * @param Asset $asset
     * @return void
     * @throws StopActionException
     * @throws IllegalObjectTypeException
     */
    public function updateAction(Asset $asset): void
    {
        $this->assetRepository->update($asset);
        $this->addFlashMessage('assetHasBeenUpdated', '', Message::SEVERITY_OK, [htmlspecialchars($asset->getLabel())]);
        $this->redirect('index');
    }

    /**
     * Initialization for createAction
     *
     * @return void
     * @throws NoSuchArgumentException
     */
    protected function initializeCreateAction(): void
    {
        $assetMappingConfiguration = $this->arguments->getArgument('asset')->getPropertyMappingConfiguration();
        $assetMappingConfiguration->allowProperties('title', 'resource', 'tags', 'assetCollections');
        $assetMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, true);
        $assetMappingConfiguration->setTypeConverterOption(AssetInterfaceConverter::class, AssetInterfaceConverter::CONFIGURATION_ONE_PER_RESOURCE, true);
    }

    /**
     * Create a new asset
     *
     * @param Asset $asset
     * @return void
     * @throws StopActionException
     * @throws IllegalObjectTypeException
     */
    public function createAction(Asset $asset): void
    {
        if ($this->persistenceManager->isNewObject($asset)) {
            $this->assetRepository->add($asset);
        }
        $this->addFlashMessage('assetHasBeenAdded', '', Message::SEVERITY_OK, [htmlspecialchars($asset->getLabel())]);
        $this->redirect('index');
    }

    /**
     * Initialization for uploadAction
     *
     * @return void
     * @throws NoSuchArgumentException
     */
    protected function initializeUploadAction(): void
    {
        $assetMappingConfiguration = $this->arguments->getArgument('asset')->getPropertyMappingConfiguration();
        $assetMappingConfiguration->allowProperties('title', 'resource');
        $assetMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, true);
        $assetMappingConfiguration->setTypeConverterOption(AssetInterfaceConverter::class, AssetInterfaceConverter::CONFIGURATION_ONE_PER_RESOURCE, true);
    }

    /**
     * Upload a new asset. No redirection and no response body, for use by plupload (or similar).
     *
     * @param Asset $asset
     * @return string
     * @throws IllegalObjectTypeException
     */
    public function uploadAction(Asset $asset): string
    {
        if (($tag = $this->browserState->get('activeTag')) !== null) {
            $asset->addTag($tag);
        }

        if ($this->persistenceManager->isNewObject($asset)) {
            $this->assetRepository->add($asset);
        } else {
            $this->assetRepository->update($asset);
        }

        if (($assetCollection = $this->browserState->get('activeAssetCollection')) !== null && $assetCollection->addAsset($asset)) {
            $this->assetCollectionRepository->update($assetCollection);
        }

        $this->addFlashMessage('assetHasBeenAdded', '', Message::SEVERITY_OK, [htmlspecialchars($asset->getLabel())]);
        $this->response->setStatus(201);
        return '';
    }

    /**
     * Tags an asset with a tag.
     *
     * No redirection and no response body, no flash message, for use by plupload (or similar).
     *
     * @param Asset $asset
     * @param Tag $tag
     * @return void
     * @throws IllegalObjectTypeException
     */
    public function tagAssetAction(Asset $asset, Tag $tag): void
    {
        $success = false;
        if ($asset->addTag($tag)) {
            $this->assetRepository->update($asset);
            $success = true;
        }
        $this->view->assign('value', $success);
    }

    /**
     * Adds an asset to an asset collection
     *
     * @param Asset $asset
     * @param AssetCollection $assetCollection
     * @return void
     * @throws IllegalObjectTypeException
     */
    public function addAssetToCollectionAction(Asset $asset, AssetCollection $assetCollection): void
    {
        $success = false;
        if ($assetCollection->addAsset($asset)) {
            $this->assetCollectionRepository->update($assetCollection);
            $success = true;
        }
        $this->view->assign('value', $success);
    }

    /**
     * Delete an asset
     *
     * @param Asset $asset
     * @return void
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     * @throws AssetServiceException
     */
    public function deleteAction(Asset $asset): void
    {
        $usageReferences = $this->assetService->getUsageReferences($asset);
        if (count($usageReferences) > 0) {
            $this->addFlashMessage('deleteRelatedNodes', '', Message::SEVERITY_WARNING, [], 1412422767);
            $this->redirect('index');
        }

        $this->assetRepository->remove($asset);
        $this->addFlashMessage('assetHasBeenDeleted', '', Message::SEVERITY_OK, [$asset->getLabel()], 1412375050);
        $this->redirect('index');
    }

    /**
     * Update the resource on an asset.
     *
     * @param AssetInterface $asset
     * @param PersistentResource $resource
     * @param array $options
     * @return void
     * @throws StopActionException
     * @throws ForwardException
     */
    public function updateAssetResourceAction(AssetInterface $asset, PersistentResource $resource, array $options = []): void
    {
        $sourceMediaType = MediaTypes::parseMediaType($asset->getMediaType());
        $replacementMediaType = MediaTypes::parseMediaType($resource->getMediaType());

        // Prevent replacement of image, audio and video by a different mimetype because of possible rendering issues.
        if ($sourceMediaType['type'] !== $replacementMediaType['type'] && in_array($sourceMediaType['type'], ['image', 'audio', 'video'])) {
            $this->addFlashMessage(
                'resourceCanOnlyBeReplacedBySimilarResource',
                '',
                Message::SEVERITY_WARNING,
                [$sourceMediaType['type'], $resource->getMediaType()],
                1462308179
            );
            $this->redirect('index');
        }

        try {
            $this->assetService->replaceAssetResource($asset, $resource, $options);
        } catch (\Exception $exception) {
            $this->addFlashMessage('couldNotReplaceAsset', '', Message::SEVERITY_OK, [], 1463472606);
            $this->forwardToReferringRequest();
            return;
        }

        $assetLabel = (method_exists($asset, 'getLabel') ? $asset->getLabel() : $resource->getFilename());
        $this->addFlashMessage('assetHasBeenReplaced', '', Message::SEVERITY_OK, [htmlspecialchars($assetLabel)]);
        $this->redirect('index');
    }

    /**
     * Get Related Nodes for an asset (proxy action)
     *
     * @param AssetInterface $asset
     * @return void
     * @throws ForwardException
     */
    public function relatedNodesAction(AssetInterface $asset): void
    {
        $this->forward('relatedNodes', 'Usage', 'Neos.Media.Browser', ['asset' => $asset]);
    }

    /**
     * @param string $label
     * @return void
     * @Flow\Validate(argumentName="label", type="NotEmpty")
     * @Flow\Validate(argumentName="label", type="Label")
     * @throws ForwardException
     */
    public function createTagAction(string $label): void
    {
        $this->forward('create', 'Tag', 'Neos.Media.Browser', ['label' => $label]);
    }

    /**
     * @param Tag $tag
     * @return void
     * @throws ForwardException
     */
    public function editTagAction(Tag $tag): void
    {
        $this->forward('edit', 'Tag', 'Neos.Media.Browser', ['tag' => $tag]);
    }

    /**
     * @param Tag $tag
     * @return void
     * @throws ForwardException
     */
    public function updateTagAction(Tag $tag): void
    {
        $this->forward('update', 'Tag', 'Neos.Media.Browser', ['tag' => $tag]);
    }

    /**
     * @param Tag $tag
     * @return void
     * @throws ForwardException
     */
    public function deleteTagAction(Tag $tag): void
    {
        $this->forward('delete', 'Tag', 'Neos.Media.Browser', ['tag' => $tag]);
    }

    /**
     * @param string $title
     * @return void
     * @Flow\Validate(argumentName="title", type="NotEmpty")
     * @Flow\Validate(argumentName="title", type="Label")
     * @throws ForwardException
     */
    public function createAssetCollectionAction($title): void
    {
        $this->forward('create', 'AssetCollection', 'Neos.Media.Browser', ['title' => $title]);
    }

    /**
     * @param AssetCollection $assetCollection
     * @return void
     * @throws ForwardException
     */
    public function editAssetCollectionAction(AssetCollection $assetCollection): void
    {
        $this->forward('edit', 'AssetCollection', 'Neos.Media.Browser', ['assetCollection' => $assetCollection]);
    }

    /**
     * @param AssetCollection $assetCollection
     * @return void
     * @throws ForwardException
     */
    public function updateAssetCollectionAction(AssetCollection $assetCollection): void
    {
        $this->forward('update', 'AssetCollection', 'Neos.Media.Browser', ['assetCollection' => $assetCollection]);
    }

    /**
     * @param AssetCollection $assetCollection
     * @return void
     * @throws ForwardException
     */
    public function deleteAssetCollectionAction(AssetCollection $assetCollection): void
    {
        $this->forward('delete', 'AssetCollection', 'Neos.Media.Browser', ['assetCollection' => $assetCollection]);
    }

    /**
     * Prepare property mapping for updateImageVariantAction
     *
     * @throws \Neos\Flow\Mvc\Exception\NoSuchArgumentException
     */
    public function initializeUpdateImageVariantAction()
    {
        $mappingConfiguration = $this->arguments->getArgument('imageVariant')->getPropertyMappingConfiguration();
        $mappingConfiguration->allowAllProperties();
        $mappingConfiguration->getConfigurationFor('adjustments')->allowAllProperties();
        $mappingConfiguration->getConfigurationFor('adjustments')->getConfigurationFor('*')->allowAllProperties();
        $mappingConfiguration->getConfigurationFor('adjustments')->getConfigurationFor(CropImageAdjustment::class)->allowAllProperties();
        $mappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, true);
    }

    /**
     * @param ImageVariant $imageVariant
     */
    public function updateImageVariantAction(ImageVariant $imageVariant)
    {
        $this->assetRepository->update($imageVariant);
        $this->redirect('variants');
    }

    /**
     * This custom errorAction adds FlashMessages for validation results to give more information in the
     *
     * @return string
     */
    protected function errorAction(): string
    {
        foreach ($this->arguments->getValidationResults()->getFlattenedErrors() as $propertyPath => $errors) {
            foreach ($errors as $error) {
                $this->flashMessageContainer->addMessage($error);
            }
        }

        return parent::errorAction();
    }

    /**
     * Individual error FlashMessage that hides which action fails in production.
     *
     * @return Message|bool The flash message or false if no flash message should be set
     */
    protected function getErrorFlashMessage()
    {
        if ($this->arguments->getValidationResults()->hasErrors()) {
            return false;
        }
        $errorMessage = 'An error occurred';
        if ($this->objectManager->getContext()->isDevelopment()) {
            $errorMessage .= ' while trying to call %1$s->%2$s()';
        }

        return new Error($errorMessage, null, [get_class($this), $this->actionMethodName]);
    }

    /**
     * Add a translated flashMessage.
     *
     * @param string $messageBody The translation id for the message body.
     * @param string $messageTitle The translation id for the message title.
     * @param string $severity
     * @param array $messageArguments
     * @param integer $messageCode
     * @return void
     */
    public function addFlashMessage($messageBody, $messageTitle = '', $severity = Message::SEVERITY_OK, array $messageArguments = [], $messageCode = null): void
    {
        if (is_string($messageBody)) {
            $messageBody = $this->translator->translateById($messageBody, $messageArguments, null, null, 'Main', 'Neos.Media.Browser') ?: $messageBody;
        }

        $messageTitle = $this->translator->translateById($messageTitle, $messageArguments, null, null, 'Main', 'Neos.Media.Browser');
        parent::addFlashMessage($messageBody, $messageTitle, $severity, $messageArguments, $messageCode);
    }

    /**
     * Returns the lowest configured maximum upload file size
     *
     * @return int
     * @throws FilesException
     */
    private function getMaximumFileUploadSize(): int
    {
        return min(Files::sizeStringToBytes(ini_get('post_max_size')), Files::sizeStringToBytes(ini_get('upload_max_filesize')));
    }

    /**
     * @param string $view
     * @param string $sortBy
     * @param string $sortDirection
     * @param string $filter
     */
    private function applyViewOptionsToBrowserState(string $view = null, string $sortBy = null, string $sortDirection = null, string $filter = null): void
    {
        if (!empty($view)) {
            $this->browserState->set('view', $view);
        }
        if (!empty($sortBy)) {
            $this->browserState->set('sortBy', $sortBy);
        }
        if (!empty($sortDirection)) {
            $this->browserState->set('sortDirection', $sortDirection);
        }
        if (!empty($filter)) {
            $this->browserState->set('filter', $filter);
        }

        foreach (['view', 'sortBy', 'sortDirection', 'filter'] as $optionName) {
            $this->view->assign($optionName, $this->browserState->get($optionName));
        }
    }

    /**
     * @param $assetSourceIdentifier
     */
    private function applyActiveAssetSourceToBrowserState($assetSourceIdentifier): void
    {
        if ($assetSourceIdentifier !== null && isset($this->assetSources[$assetSourceIdentifier])) {
            $this->browserState->setActiveAssetSourceIdentifier($assetSourceIdentifier);
        }
    }

    /**
     * @param int $tagMode
     * @param Tag $tag
     * @param AssetCollection|null $activeAssetCollection
     */
    private function applyTagToBrowserState(int $tagMode = null, Tag $tag = null, AssetCollection $activeAssetCollection = null): void
    {
        if ($tagMode === self::TAG_GIVEN && $tag !== null) {
            $this->browserState->set('activeTag', $tag);
            $this->view->assign('activeTag', $tag);
        } elseif ($tagMode === self::TAG_NONE || $tagMode === self::TAG_ALL) {
            $this->browserState->set('activeTag', null);
            $this->view->assign('activeTag', null);
        }
        $this->browserState->set('tagMode', $tagMode);

        // Unset active tag if it isn't available in the active asset collection
        if ($activeAssetCollection !== null && $this->browserState->get('activeTag') && !$activeAssetCollection->getTags()->contains($this->browserState->get('activeTag'))) {
            $this->browserState->set('activeTag', null);
            $this->view->assign('activeTag', null);
        }

        if (!$this->browserState->get('activeTag') && $this->browserState->get('tagMode') === self::TAG_GIVEN) {
            $this->browserState->set('tagMode', self::TAG_ALL);
        }
    }

    /**
     * @return AssetSourceInterface
     */
    private function getAssetSourceFromBrowserState(): AssetSourceInterface
    {
        $assetSourceIdentifier = $this->browserState->getActiveAssetSourceIdentifier();
        if (!isset($this->assetSources[$assetSourceIdentifier])) {
            $assetSourceIdentifiers = array_keys($this->assetSources);
            $assetSourceIdentifier = reset($assetSourceIdentifiers);
        }
        return $this->assetSources[$assetSourceIdentifier];
    }

    /**
     * @param int $collectionMode
     * @param AssetCollection $assetCollection
     */
    private function applyAssetCollectionOptionsToBrowserState(int $collectionMode = null, AssetCollection $assetCollection = null): void
    {
        if ($collectionMode === self::COLLECTION_GIVEN && $assetCollection !== null) {
            $this->browserState->set('activeAssetCollection', $assetCollection);
            $this->view->assign('activeAssetCollection', $assetCollection);
        } elseif ($collectionMode === self::COLLECTION_ALL) {
            $this->browserState->set('activeAssetCollection', null);
            $this->view->assign('activeAssetCollection', null);
        }
        $this->browserState->set('collectionMode', $collectionMode);
    }

    /**
     * @return AssetCollection|null
     */
    private function getActiveAssetCollectionFromBrowserState(): ?AssetCollection
    {
        try {
            /** @var AssetCollection $activeAssetCollection */
            $activeAssetCollection = $this->browserState->get('activeAssetCollection');
            if ($activeAssetCollection instanceof DoctrineProxy) {
                // To trigger a possible EntityNotFound have to load the entity
                $activeAssetCollection->__load();
            }
        } catch (EntityNotFoundException $exception) {
            // If a removed asset collection is still in the browser state it can not be fetched
            $this->browserState->set('activeAssetCollection', null);
            $activeAssetCollection = null;
        }
        return $activeAssetCollection;
    }

    /**
     * @param AssetProxyRepositoryInterface $assetProxyRepository
     */
    private function applySortingFromBrowserState(AssetProxyRepositoryInterface $assetProxyRepository): void
    {
        if ($assetProxyRepository instanceof SupportsSortingInterface) {
            switch ($this->browserState->get('sortBy')) {
                case 'Name':
                    $assetProxyRepository->orderBy(['resource.filename' => $this->browserState->get('sortDirection')]);
                    break;
                case 'Modified':
                default:
                    $assetProxyRepository->orderBy(['lastModified' => $this->browserState->get('sortDirection')]);
                    break;
            }
        }
    }

    /**
     * @param AssetProxyRepositoryInterface $assetProxyRepository
     */
    private function applyAssetTypeFilterFromBrowserState(AssetProxyRepositoryInterface $assetProxyRepository): void
    {
        $assetProxyRepository->filterByType(new AssetTypeFilter($this->browserState->get('filter')));
    }

    /**
     * @param AssetProxyRepositoryInterface $assetProxyRepository
     */
    private function applyAssetCollectionFilterFromBrowserState(AssetProxyRepositoryInterface $assetProxyRepository): void
    {
        if ($assetProxyRepository instanceof SupportsCollectionsInterface) {
            $assetProxyRepository->filterByCollection($this->getActiveAssetCollectionFromBrowserState());
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Media\Browser\Controller;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Controller for asset handling
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class AssetController extends AssetController_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
        if (get_class($this) === 'Neos\Media\Browser\Controller\AssetController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Media\Browser\Controller\AssetController', $this);
        if ('Neos\Media\Browser\Controller\AssetController' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Media\Browser\Controller\AssetController';
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
            'indexAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'newAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'replaceAssetResourceAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'showAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'editAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'variantsAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'updateAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'initializeCreateAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'createAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'initializeUploadAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'uploadAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'tagAssetAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'addAssetToCollectionAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'deleteAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'updateAssetResourceAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'relatedNodesAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'createTagAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'editTagAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'updateTagAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'deleteTagAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'createAssetCollectionAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'editAssetCollectionAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'updateAssetCollectionAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'deleteAssetCollectionAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'updateImageVariantAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
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
        if (get_class($this) === 'Neos\Media\Browser\Controller\AssetController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Media\Browser\Controller\AssetController', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();

        $isSameClass = get_class($this) === 'Neos\Media\Browser\Controller\AssetController';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Media\Browser\Controller\AssetController', $classParents) !== false && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== false;

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
     * @param string $view
     * @param string $sortBy
     * @param string $sortDirection
     * @param string $filter
     * @param int $tagMode
     * @param Tag $tag
     * @param string $searchTerm
     * @param int $collectionMode
     * @param AssetCollection $assetCollection
     * @param string $assetSourceIdentifier
     * @return void
     * @throws FilesException
     */
    public function indexAction($view = NULL, $sortBy = NULL, $sortDirection = NULL, $filter = NULL, $tagMode = 0, ?\Neos\Media\Domain\Model\Tag $tag = NULL, $searchTerm = NULL, $collectionMode = 0, ?\Neos\Media\Domain\Model\AssetCollection $assetCollection = NULL, $assetSourceIdentifier = NULL) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction'])) {
            parent::indexAction($view, $sortBy, $sortDirection, $filter, $tagMode, $tag, $searchTerm, $collectionMode, $assetCollection, $assetSourceIdentifier);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['view'] = $view;
                $methodArguments['sortBy'] = $sortBy;
                $methodArguments['sortDirection'] = $sortDirection;
                $methodArguments['filter'] = $filter;
                $methodArguments['tagMode'] = $tagMode;
                $methodArguments['tag'] = $tag;
                $methodArguments['searchTerm'] = $searchTerm;
                $methodArguments['collectionMode'] = $collectionMode;
                $methodArguments['assetCollection'] = $assetCollection;
                $methodArguments['assetSourceIdentifier'] = $assetSourceIdentifier;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('indexAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'indexAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     */
    public function newAction() : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['newAction'])) {
            parent::newAction();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['newAction'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('newAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'newAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['newAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['newAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @param Asset $asset
     * @return void
     */
    public function replaceAssetResourceAction(\Neos\Media\Domain\Model\Asset $asset) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['replaceAssetResourceAction'])) {
            parent::replaceAssetResourceAction($asset);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['replaceAssetResourceAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['asset'] = $asset;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('replaceAssetResourceAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'replaceAssetResourceAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['replaceAssetResourceAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['replaceAssetResourceAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @param string $assetSourceIdentifier
     * @param string $assetProxyIdentifier
     * @return void
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     */
    public function showAction(string $assetSourceIdentifier, string $assetProxyIdentifier) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['showAction'])) {
            parent::showAction($assetSourceIdentifier, $assetProxyIdentifier);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['showAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['assetSourceIdentifier'] = $assetSourceIdentifier;
                $methodArguments['assetProxyIdentifier'] = $assetProxyIdentifier;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('showAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'showAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['showAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['showAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @param string $assetSourceIdentifier
     * @param string $assetProxyIdentifier
     * @return void
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     */
    public function editAction(string $assetSourceIdentifier, string $assetProxyIdentifier) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['editAction'])) {
            parent::editAction($assetSourceIdentifier, $assetProxyIdentifier);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['editAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['assetSourceIdentifier'] = $assetSourceIdentifier;
                $methodArguments['assetProxyIdentifier'] = $assetProxyIdentifier;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('editAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'editAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['editAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['editAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @param string $assetSourceIdentifier
     * @param string $assetProxyIdentifier
     * @param string $overviewAction
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     */
    public function variantsAction(string $assetSourceIdentifier, string $assetProxyIdentifier, string $overviewAction) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['variantsAction'])) {
            parent::variantsAction($assetSourceIdentifier, $assetProxyIdentifier, $overviewAction);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['variantsAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['assetSourceIdentifier'] = $assetSourceIdentifier;
                $methodArguments['assetProxyIdentifier'] = $assetProxyIdentifier;
                $methodArguments['overviewAction'] = $overviewAction;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('variantsAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'variantsAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['variantsAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['variantsAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @param Asset $asset
     * @return void
     * @throws StopActionException
     * @throws IllegalObjectTypeException
     */
    public function updateAction(\Neos\Media\Domain\Model\Asset $asset) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAction'])) {
            parent::updateAction($asset);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['asset'] = $asset;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('updateAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'updateAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     * @throws NoSuchArgumentException
     */
    protected function initializeCreateAction() : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeCreateAction'])) {
            parent::initializeCreateAction();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeCreateAction'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('initializeCreateAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'initializeCreateAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeCreateAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeCreateAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @param Asset $asset
     * @return void
     * @throws StopActionException
     * @throws IllegalObjectTypeException
     */
    public function createAction(\Neos\Media\Domain\Model\Asset $asset) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createAction'])) {
            parent::createAction($asset);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['createAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['asset'] = $asset;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('createAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'createAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     * @throws NoSuchArgumentException
     */
    protected function initializeUploadAction() : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeUploadAction'])) {
            parent::initializeUploadAction();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeUploadAction'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('initializeUploadAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'initializeUploadAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeUploadAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initializeUploadAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @param Asset $asset
     * @return string
     * @throws IllegalObjectTypeException
     */
    public function uploadAction(\Neos\Media\Domain\Model\Asset $asset) : string
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['uploadAction'])) {
            $result = parent::uploadAction($asset);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['uploadAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['asset'] = $asset;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('uploadAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'uploadAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['uploadAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['uploadAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Asset $asset
     * @param Tag $tag
     * @return void
     * @throws IllegalObjectTypeException
     */
    public function tagAssetAction(\Neos\Media\Domain\Model\Asset $asset, \Neos\Media\Domain\Model\Tag $tag) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['tagAssetAction'])) {
            parent::tagAssetAction($asset, $tag);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['tagAssetAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['asset'] = $asset;
                $methodArguments['tag'] = $tag;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('tagAssetAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'tagAssetAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['tagAssetAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['tagAssetAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @param Asset $asset
     * @param AssetCollection $assetCollection
     * @return void
     * @throws IllegalObjectTypeException
     */
    public function addAssetToCollectionAction(\Neos\Media\Domain\Model\Asset $asset, \Neos\Media\Domain\Model\AssetCollection $assetCollection) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['addAssetToCollectionAction'])) {
            parent::addAssetToCollectionAction($asset, $assetCollection);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['addAssetToCollectionAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['asset'] = $asset;
                $methodArguments['assetCollection'] = $assetCollection;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('addAssetToCollectionAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'addAssetToCollectionAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['addAssetToCollectionAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['addAssetToCollectionAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @param Asset $asset
     * @return void
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     * @throws AssetServiceException
     */
    public function deleteAction(\Neos\Media\Domain\Model\Asset $asset) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['deleteAction'])) {
            parent::deleteAction($asset);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['deleteAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['asset'] = $asset;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('deleteAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'deleteAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['deleteAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['deleteAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @param AssetInterface $asset
     * @param PersistentResource $resource
     * @param array $options
     * @return void
     * @throws StopActionException
     * @throws ForwardException
     */
    public function updateAssetResourceAction(\Neos\Media\Domain\Model\AssetInterface $asset, \Neos\Flow\ResourceManagement\PersistentResource $resource, array $options = array()) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAssetResourceAction'])) {
            parent::updateAssetResourceAction($asset, $resource, $options);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAssetResourceAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['asset'] = $asset;
                $methodArguments['resource'] = $resource;
                $methodArguments['options'] = $options;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('updateAssetResourceAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'updateAssetResourceAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAssetResourceAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAssetResourceAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @param AssetInterface $asset
     * @return void
     * @throws ForwardException
     */
    public function relatedNodesAction(\Neos\Media\Domain\Model\AssetInterface $asset) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['relatedNodesAction'])) {
            parent::relatedNodesAction($asset);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['relatedNodesAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['asset'] = $asset;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('relatedNodesAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'relatedNodesAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['relatedNodesAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['relatedNodesAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @param string $label
     * @return void
     * @throws ForwardException
     * @\Neos\Flow\Annotations\Validate(type="NotEmpty", argumentName="label")
     * @\Neos\Flow\Annotations\Validate(type="Label", argumentName="label")
     */
    public function createTagAction(string $label) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createTagAction'])) {
            parent::createTagAction($label);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['createTagAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['label'] = $label;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('createTagAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'createTagAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createTagAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createTagAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @param Tag $tag
     * @return void
     * @throws ForwardException
     */
    public function editTagAction(\Neos\Media\Domain\Model\Tag $tag) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['editTagAction'])) {
            parent::editTagAction($tag);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['editTagAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['tag'] = $tag;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('editTagAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'editTagAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['editTagAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['editTagAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @param Tag $tag
     * @return void
     * @throws ForwardException
     */
    public function updateTagAction(\Neos\Media\Domain\Model\Tag $tag) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateTagAction'])) {
            parent::updateTagAction($tag);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['updateTagAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['tag'] = $tag;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('updateTagAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'updateTagAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateTagAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateTagAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @param Tag $tag
     * @return void
     * @throws ForwardException
     */
    public function deleteTagAction(\Neos\Media\Domain\Model\Tag $tag) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['deleteTagAction'])) {
            parent::deleteTagAction($tag);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['deleteTagAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['tag'] = $tag;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('deleteTagAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'deleteTagAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['deleteTagAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['deleteTagAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @param string $title
     * @return void
     * @throws ForwardException
     * @\Neos\Flow\Annotations\Validate(type="NotEmpty", argumentName="title")
     * @\Neos\Flow\Annotations\Validate(type="Label", argumentName="title")
     */
    public function createAssetCollectionAction($title) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createAssetCollectionAction'])) {
            parent::createAssetCollectionAction($title);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['createAssetCollectionAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['title'] = $title;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('createAssetCollectionAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'createAssetCollectionAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createAssetCollectionAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createAssetCollectionAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @param AssetCollection $assetCollection
     * @return void
     * @throws ForwardException
     */
    public function editAssetCollectionAction(\Neos\Media\Domain\Model\AssetCollection $assetCollection) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['editAssetCollectionAction'])) {
            parent::editAssetCollectionAction($assetCollection);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['editAssetCollectionAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['assetCollection'] = $assetCollection;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('editAssetCollectionAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'editAssetCollectionAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['editAssetCollectionAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['editAssetCollectionAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @param AssetCollection $assetCollection
     * @return void
     * @throws ForwardException
     */
    public function updateAssetCollectionAction(\Neos\Media\Domain\Model\AssetCollection $assetCollection) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAssetCollectionAction'])) {
            parent::updateAssetCollectionAction($assetCollection);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAssetCollectionAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['assetCollection'] = $assetCollection;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('updateAssetCollectionAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'updateAssetCollectionAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAssetCollectionAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAssetCollectionAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @param AssetCollection $assetCollection
     * @return void
     * @throws ForwardException
     */
    public function deleteAssetCollectionAction(\Neos\Media\Domain\Model\AssetCollection $assetCollection) : void
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['deleteAssetCollectionAction'])) {
            parent::deleteAssetCollectionAction($assetCollection);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['deleteAssetCollectionAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['assetCollection'] = $assetCollection;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('deleteAssetCollectionAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'deleteAssetCollectionAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['deleteAssetCollectionAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['deleteAssetCollectionAction']);
        }
    }

    /**
     * Autogenerated Proxy Method
     * @param ImageVariant $imageVariant
     */
    public function updateImageVariantAction(\Neos\Media\Domain\Model\ImageVariant $imageVariant)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateImageVariantAction'])) {
            $result = parent::updateImageVariantAction($imageVariant);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['updateImageVariantAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['imageVariant'] = $imageVariant;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('updateImageVariantAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\AssetController', 'updateImageVariantAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateImageVariantAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateImageVariantAction']);
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
  'viewFormatToObjectNameMap' => 'array',
  'siteRepository' => 'Neos\\Neos\\Domain\\Repository\\SiteRepository',
  'domainRepository' => 'Neos\\Neos\\Domain\\Repository\\DomainRepository',
  'assetRepository' => 'Neos\\Media\\Domain\\Repository\\AssetRepository',
  'tagRepository' => 'Neos\\Media\\Domain\\Repository\\TagRepository',
  'assetCollectionRepository' => 'Neos\\Media\\Domain\\Repository\\AssetCollectionRepository',
  'packageManager' => 'Neos\\Flow\\Package\\PackageManagerInterface',
  'browserState' => 'Neos\\Media\\Browser\\Domain\\Session\\BrowserState',
  'assetService' => 'Neos\\Media\\Domain\\Service\\AssetService',
  'translator' => 'Neos\\Flow\\I18n\\Translator',
  'assetSourceService' => '\\Neos\\Media\\Domain\\Service\\AssetSourceService',
  'assetSources' => 'array<Neos\\Media\\Domain\\Model\\AssetSource\\AssetSourceInterface>',
  'imageProfilesConfiguration' => 'array',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'reflectionService' => 'Neos\\Flow\\Reflection\\ReflectionService',
  'mvcPropertyMappingConfigurationService' => 'Neos\\Flow\\Mvc\\Controller\\MvcPropertyMappingConfigurationService',
  'viewConfigurationManager' => 'Neos\\Flow\\Mvc\\ViewConfigurationManager',
  'view' => 'Neos\\Flow\\Mvc\\View\\ViewInterface',
  'viewObjectNamePattern' => 'string',
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
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'supportedMediaTypes' => 'array',
  '_contextFactory' => '\\Neos\\Neos\\Domain\\Service\\ContentContextFactory',
  '_siteRepository' => '\\Neos\\Neos\\Domain\\Repository\\SiteRepository',
  '_localizationService' => '\\Neos\\Flow\\I18n\\Service',
  '_userService' => '\\Neos\\Neos\\Service\\UserService',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Media.Browser'));
        $this->injectLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Psr\Log\LoggerInterface'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\SiteRepository', 'Neos\Neos\Domain\Repository\SiteRepository', 'siteRepository', '42785f5eca4dff104f1860b84f531a9f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\SiteRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\DomainRepository', 'Neos\Neos\Domain\Repository\DomainRepository', 'domainRepository', '37b1b7f7b2d5d92dae299591af3b7e10', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\DomainRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Repository\AssetRepository', 'Neos\Media\Domain\Repository\AssetRepository', 'assetRepository', '45191f771a429c7decedb6fc0abbcc74', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Repository\AssetRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Repository\TagRepository', 'Neos\Media\Domain\Repository\TagRepository', 'tagRepository', '4cf01dea3b6190efe49ffdcb9a0ab644', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Repository\TagRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Repository\AssetCollectionRepository', 'Neos\Media\Domain\Repository\AssetCollectionRepository', 'assetCollectionRepository', '75dd022c88117120d3ec81cf84770446', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Repository\AssetCollectionRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Package\PackageManagerInterface', 'Neos\Flow\Package\PackageManager', 'packageManager', 'b44be8eaae4695ec4f42edfbf6f8880a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Package\PackageManagerInterface'); });
        $this->browserState = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Browser\Domain\Session\BrowserState');
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Service\AssetService', 'Neos\Media\Domain\Service\AssetService', 'assetService', 'b8a3f9ba29596737396943e4de630328', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Service\AssetService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\I18n\Translator', 'Neos\Flow\I18n\Translator', 'translator', 'a1556ebf8488dcff234496272bb811f7', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\Translator'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Service\AssetSourceService', 'Neos\Media\Domain\Service\AssetSourceService', 'assetSourceService', 'b2da7e7ea7dc0a27a66e2eb5ce277399', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Service\AssetSourceService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Reflection\ReflectionService', 'Neos\Flow\Reflection\ReflectionService', 'reflectionService', '464c26aa94c66579c050985566cbfc1f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Reflection\ReflectionService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService', 'Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService', 'mvcPropertyMappingConfigurationService', '245f31ad31ca22b8c2b2255e0f65f847', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\ViewConfigurationManager', 'Neos\Flow\Mvc\ViewConfigurationManager', 'viewConfigurationManager', '40e27e95b530777b9b476762cf735a69', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\ViewConfigurationManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\SystemLoggerInterface', 'Neos\Flow\Log\Logger', 'systemLogger', '717e9de4d0309f4f47c821b9257eb5c2', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Validation\ValidatorResolver', 'Neos\Flow\Validation\ValidatorResolver', 'validatorResolver', 'e992f50de62d81bfe770d5c5f1242621', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Validation\ValidatorResolver'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\FlashMessageContainer', 'Neos\Flow\Mvc\FlashMessageContainer', 'flashMessageContainer', 'a5f5265657df54eb081324fb2ff5b8e1', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\FlashMessageContainer'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Service\ContentContextFactory', 'Neos\Neos\Domain\Service\ContentContextFactory', '_contextFactory', 'bf6447fb48e80589ca3a024bc3882005', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Service\ContentContextFactory'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\SiteRepository', 'Neos\Neos\Domain\Repository\SiteRepository', '_siteRepository', '42785f5eca4dff104f1860b84f531a9f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\SiteRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\I18n\Service', 'Neos\Flow\I18n\Service', '_localizationService', 'bdcad09aa1b6973b35f2987887987892', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\Service'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\UserService', 'Neos\Neos\Service\UserService', '_userService', '3af75a289d0337400c3d43d557f82c49', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\UserService'); });
        $this->imageProfilesConfiguration = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Media.imageProfiles');
        $this->defaultViewImplementation = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow.mvc.view.defaultImplementation');
        $this->Flow_Injected_Properties = array (
  0 => 'settings',
  1 => 'logger',
  2 => 'siteRepository',
  3 => 'domainRepository',
  4 => 'assetRepository',
  5 => 'tagRepository',
  6 => 'assetCollectionRepository',
  7 => 'packageManager',
  8 => 'browserState',
  9 => 'assetService',
  10 => 'translator',
  11 => 'assetSourceService',
  12 => 'objectManager',
  13 => 'reflectionService',
  14 => 'mvcPropertyMappingConfigurationService',
  15 => 'viewConfigurationManager',
  16 => 'systemLogger',
  17 => 'validatorResolver',
  18 => 'flashMessageContainer',
  19 => 'persistenceManager',
  20 => '_contextFactory',
  21 => '_siteRepository',
  22 => '_localizationService',
  23 => '_userService',
  24 => 'imageProfilesConfiguration',
  25 => 'defaultViewImplementation',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Media.Browser/Classes/Controller/AssetController.php
#