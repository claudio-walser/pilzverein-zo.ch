<?php 
namespace Neos\Neos\Controller\Module\Management;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Diff\Diff;
use Neos\Diff\Renderer\Html\HtmlArrayRenderer;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Annotations as Flow;
use Neos\Error\Messages\Message;
use Neos\Flow\I18n\Translator;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Package\PackageManager;
use Neos\Flow\Property\PropertyMapper;
use Neos\Flow\Property\TypeConverter\PersistentObjectConverter;
use Neos\Flow\Security\Context;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Neos\Neos\Domain\Model\User;
use Neos\Neos\Domain\Repository\SiteRepository;
use Neos\Neos\Domain\Service\ContentContextFactory;
use Neos\Neos\Domain\Service\ContentDimensionPresetSourceInterface;
use Neos\Neos\Domain\Service\UserService;
use Neos\Neos\Domain\Service\SiteService;
use Neos\Neos\Service\PublishingService;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\Workspace;
use Neos\ContentRepository\Domain\Repository\WorkspaceRepository;
use Neos\ContentRepository\Exception\WorkspaceException;
use Neos\ContentRepository\TypeConverter\NodeConverter;
use Neos\ContentRepository\Utility;
use Neos\Neos\Utility\User as UserUtility;

/**
 * The Neos Workspaces module controller
 *
 * @Flow\Scope("singleton")
 */
class WorkspacesController_Original extends AbstractModuleController
{

    /**
     * @Flow\Inject
     * @var PublishingService
     */
    protected $publishingService;

    /**
     * @Flow\Inject
     * @var WorkspaceRepository
     */
    protected $workspaceRepository;

    /**
     * @Flow\Inject
     * @var SiteRepository
     */
    protected $siteRepository;

    /**
     * @Flow\Inject
     * @var PropertyMapper
     */
    protected $propertyMapper;

    /**
     * @Flow\Inject
     * @var Context
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var ContentContextFactory
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var UserService
     */
    protected $userService;

    /**
     * @Flow\Inject
     * @var Translator
     */
    protected $translator;

    /**
     * @var PackageManager
     * @Flow\Inject
     */
    protected $packageManager;

    /**
     * @var ContentDimensionPresetSourceInterface
     * @Flow\Inject
     */
    protected $contentDimensionPresetSource;

    /**
     * @return void
     */
    protected function initializeAction()
    {
        if ($this->arguments->hasArgument('node')) {
            $this->arguments->getArgument('node')->getPropertyMappingConfiguration()->setTypeConverterOption(NodeConverter::class, NodeConverter::REMOVED_CONTENT_SHOWN, true);
        }
        if ($this->arguments->hasArgument('nodes')) {
            $this->arguments->getArgument('nodes')->getPropertyMappingConfiguration()->forProperty('*')->setTypeConverterOption(NodeConverter::class, NodeConverter::REMOVED_CONTENT_SHOWN, true);
        }
        parent::initializeAction();
    }

    /**
     * Display a list of unpublished content
     *
     * @return void
     */
    public function indexAction()
    {
        $currentAccount = $this->securityContext->getAccount();
        $userWorkspace = $this->workspaceRepository->findOneByName(UserUtility::getPersonalWorkspaceNameForUsername($currentAccount->getAccountIdentifier()));
        /** @var Workspace $userWorkspace */

        $workspacesAndCounts = [
            $userWorkspace->getName() => [
                'workspace' => $userWorkspace,
                'changesCounts' => $this->computeChangesCount($userWorkspace),
                'canPublish' => false,
                'canManage' => false,
                'canDelete' => false
            ]
        ];

        foreach ($this->workspaceRepository->findAll() as $workspace) {
            /** @var Workspace $workspace */
            // FIXME: This check should be implemented through a specialized Workspace Privilege or something similar
            if (!$workspace->isPersonalWorkspace() && ($workspace->isInternalWorkspace() || $this->userService->currentUserCanManageWorkspace($workspace))) {
                $workspacesAndCounts[$workspace->getName()]['workspace'] = $workspace;
                $workspacesAndCounts[$workspace->getName()]['changesCounts'] = $this->computeChangesCount($workspace);
                $workspacesAndCounts[$workspace->getName()]['canPublish'] = $this->userService->currentUserCanPublishToWorkspace($workspace);
                $workspacesAndCounts[$workspace->getName()]['canManage'] = $this->userService->currentUserCanManageWorkspace($workspace);
                $workspacesAndCounts[$workspace->getName()]['dependentWorkspacesCount'] = count($this->workspaceRepository->findByBaseWorkspace($workspace));
            }
        }

        $this->view->assign('userWorkspace', $userWorkspace);
        $this->view->assign('workspacesAndChangeCounts', $workspacesAndCounts);
    }

    /**
     * @param Workspace $workspace
     * @return void
     */
    public function showAction(Workspace $workspace)
    {
        $this->view->assignMultiple([
            'selectedWorkspace' => $workspace,
            'selectedWorkspaceLabel' => $workspace->getTitle() ?: $workspace->getName(),
            'baseWorkspaceName' => $workspace->getBaseWorkspace()->getName(),
            'baseWorkspaceLabel' => $workspace->getBaseWorkspace()->getTitle() ?: $workspace->getBaseWorkspace()->getName(),
            'canPublishToBaseWorkspace' => $this->userService->currentUserCanPublishToWorkspace($workspace->getBaseWorkspace()),
            'siteChanges' => $this->computeSiteChanges($workspace),
            'contentDimensions' => $this->contentDimensionPresetSource->getAllPresets()
        ]);
    }

    /**
     * @return void
     */
    public function newAction()
    {
        $this->view->assign('baseWorkspaceOptions', $this->prepareBaseWorkspaceOptions());
    }

    /**
     * Create a workspace
     *
     * @Flow\Validate(argumentName="title", type="\Neos\Flow\Validation\Validator\NotEmptyValidator")
     * @param string $title Human friendly title of the workspace, for example "Christmas Campaign"
     * @param Workspace $baseWorkspace Workspace the new workspace should be based on
     * @param string $visibility Visibility of the new workspace, must be either "internal" or "shared"
     * @param string $description A description explaining the purpose of the new workspace
     * @return void
     */
    public function createAction($title, Workspace $baseWorkspace, $visibility, $description = '')
    {
        $workspace = $this->workspaceRepository->findOneByTitle($title);
        if ($workspace instanceof Workspace) {
            $this->addFlashMessage($this->translator->translateById('workspaces.workspaceWithThisTitleAlreadyExists', [], null, null, 'Modules', 'Neos.Neos'), '', Message::SEVERITY_WARNING);
            $this->redirect('new');
        }

        $workspaceName = Utility::renderValidNodeName($title) . '-' . substr(base_convert(microtime(false), 10, 36), -5, 5);
        while ($this->workspaceRepository->findOneByName($workspaceName) instanceof Workspace) {
            $workspaceName = Utility::renderValidNodeName($title) . '-' . substr(base_convert(microtime(false), 10, 36), -5, 5);
        }

        if ($visibility === 'private') {
            $owner = $this->userService->getCurrentUser();
        } else {
            $owner = null;
        }

        $workspace = new Workspace($workspaceName, $baseWorkspace, $owner);
        $workspace->setTitle($title);
        $workspace->setDescription($description);

        $this->workspaceRepository->add($workspace);
        $this->redirect('index');
    }

    /**
     * Edit a workspace
     *
     * @param Workspace $workspace
     * @return void
     */
    public function editAction(Workspace $workspace)
    {
        $this->view->assign('workspace', $workspace);
        $this->view->assign('baseWorkspaceOptions', $this->prepareBaseWorkspaceOptions($workspace));
        $this->view->assign('disableBaseWorkspaceSelector', $this->publishingService->getUnpublishedNodesCount($workspace) > 0);
        $this->view->assign('showOwnerSelector', $this->userService->currentUserCanTransferOwnershipOfWorkspace($workspace));
        $this->view->assign('ownerOptions', $this->prepareOwnerOptions());
    }

    /**
     * @return void
     */
    protected function initializeUpdateAction()
    {
        $converter = new PersistentObjectConverter();
        $this->arguments->getArgument('workspace')->getPropertyMappingConfiguration()
            ->forProperty('owner')
            ->setTypeConverter($converter)
            ->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_TARGET_TYPE, User::class);
        parent::initializeAction();
    }

    /**
     * Update a workspace
     *
     * @param Workspace $workspace A workspace to update
     * @return void
     */
    public function updateAction(Workspace $workspace)
    {
        if ($workspace->getTitle() === '') {
            $workspace->setTitle($workspace->getName());
        }

        $this->workspaceRepository->update($workspace);
        $this->addFlashMessage($this->translator->translateById('workspaces.workspaceHasBeenUpdated', [$workspace->getTitle()], null, null, 'Modules', 'Neos.Neos'));
        $this->redirect('index');
    }

    /**
     * Delete a workspace
     *
     * @param Workspace $workspace A workspace to delete
     * @return void
     */
    public function deleteAction(Workspace $workspace)
    {
        if ($workspace->isPersonalWorkspace()) {
            $this->redirect('index');
        }

        $dependentWorkspaces = $this->workspaceRepository->findByBaseWorkspace($workspace);
        if (count($dependentWorkspaces) > 0) {
            $dependentWorkspaceTitles = [];
            /** @var Workspace $dependentWorkspace */
            foreach ($dependentWorkspaces as $dependentWorkspace) {
                $dependentWorkspaceTitles[] = $dependentWorkspace->getTitle();
            }

            $message = $this->translator->translateById('workspaces.workspaceCannotBeDeletedBecauseOfDependencies', [$workspace->getTitle(), implode(', ', $dependentWorkspaceTitles)], null, null, 'Modules', 'Neos.Neos');
            $this->addFlashMessage($message, '', Message::SEVERITY_WARNING);
            $this->redirect('index');
        }

        $nodesCount = 0;
        try {
            $nodesCount = $this->publishingService->getUnpublishedNodesCount($workspace);
        } catch (\Exception $exception) {
            $message = $this->translator->translateById('workspaces.notDeletedErrorWhileFetchingUnpublishedNodes', [$workspace->getTitle()], null, null, 'Modules', 'Neos.Neos');
            $this->addFlashMessage($message, '', Message::SEVERITY_WARNING);
            $this->redirect('index');
        }
        if ($nodesCount > 0) {
            $message = $this->translator->translateById('workspaces.workspaceCannotBeDeletedBecauseOfUnpublishedNodes', [$workspace->getTitle(), $nodesCount], $nodesCount, null, 'Modules', 'Neos.Neos');
            $this->addFlashMessage($message, '', Message::SEVERITY_WARNING);
            $this->redirect('index');
        }

        $this->workspaceRepository->remove($workspace);
        $this->addFlashMessage($message = $this->translator->translateById('workspaces.workspaceHasBeenRemoved', [$workspace->getTitle()], null, null, 'Modules', 'Neos.Neos'));
        $this->redirect('index');
    }

    /**
     * Rebase the current users personal workspace onto the given $targetWorkspace and then
     * redirects to the $targetNode in the content module.
     *
     * @param NodeInterface $targetNode
     * @param Workspace $targetWorkspace
     * @return void
     */
    public function rebaseAndRedirectAction(NodeInterface $targetNode, Workspace $targetWorkspace)
    {
        $currentAccount = $this->securityContext->getAccount();
        $personalWorkspace = $this->workspaceRepository->findOneByName(UserUtility::getPersonalWorkspaceNameForUsername($currentAccount->getAccountIdentifier()));
        /** @var Workspace $personalWorkspace */

        if ($personalWorkspace !== $targetWorkspace) {
            if ($this->publishingService->getUnpublishedNodesCount($personalWorkspace) > 0) {
                $message = $this->translator->translateById('workspaces.cantEditBecauseWorkspaceContainsChanges', [], null, null, 'Modules', 'Neos.Neos');
                $this->addFlashMessage($message, '', Message::SEVERITY_WARNING, [], 1437833387);
                $this->redirect('show', null, null, ['workspace' => $targetWorkspace]);
            }

            $personalWorkspace->setBaseWorkspace($targetWorkspace);
            $this->workspaceRepository->update($personalWorkspace);
        }

        $contextProperties = $targetNode->getContext()->getProperties();
        $contextProperties['workspaceName'] = $personalWorkspace->getName();
        $context = $this->contextFactory->create($contextProperties);

        $mainRequest = $this->controllerContext->getRequest()->getMainRequest();
        /** @var ActionRequest $mainRequest */
        $this->uriBuilder->setRequest($mainRequest);

        if ($this->packageManager->isPackageAvailable('Neos.Neos.Ui')) {
            $this->redirect('index', 'Backend', 'Neos.Neos.Ui', ['node' => $context->getNode($targetNode->getPath())]);
        }

        $this->redirect('show', 'Frontend\\Node', 'Neos.Neos', ['node' => $context->getNode($targetNode->getPath())]);
    }

    /**
     * Publish a single node
     *
     * @param NodeInterface $node
     * @param Workspace $selectedWorkspace
     */
    public function publishNodeAction(NodeInterface $node, Workspace $selectedWorkspace)
    {
        $this->publishingService->publishNode($node);
        $this->addFlashMessage($this->translator->translateById('workspaces.selectedChangeHasBeenPublished', [], null, null, 'Modules', 'Neos.Neos'));
        $this->redirect('show', null, null, ['workspace' => $selectedWorkspace]);
    }

    /**
     * Discard a a single node
     *
     * @param NodeInterface $node
     * @param Workspace $selectedWorkspace
     * @throws WorkspaceException
     */
    public function discardNodeAction(NodeInterface $node, Workspace $selectedWorkspace)
    {
        // Hint: we cannot use $node->remove() here, as this removes the node recursively (but we just want to *discard changes*)
        $this->publishingService->discardNode($node);
        $this->addFlashMessage($this->translator->translateById('workspaces.selectedChangeHasBeenDiscarded', [], null, null, 'Modules', 'Neos.Neos'));
        $this->redirect('show', null, null, ['workspace' => $selectedWorkspace]);
    }

    /**
     * Publishes or discards the given nodes
     *
     * @param array $nodes <\Neos\ContentRepository\Domain\Model\NodeInterface> $nodes
     * @param string $action
     * @param Workspace $selectedWorkspace
     * @throws \Exception
     * @throws \Neos\Flow\Property\Exception
     * @throws \Neos\Flow\Security\Exception
     */
    public function publishOrDiscardNodesAction(array $nodes, $action, Workspace $selectedWorkspace = null)
    {
        $propertyMappingConfiguration = $this->propertyMapper->buildPropertyMappingConfiguration();
        $propertyMappingConfiguration->setTypeConverterOption(NodeConverter::class, NodeConverter::REMOVED_CONTENT_SHOWN, true);
        foreach ($nodes as $key => $node) {
            $nodes[$key] = $this->propertyMapper->convert($node, NodeInterface::class, $propertyMappingConfiguration);
        }
        switch ($action) {
            case 'publish':
                foreach ($nodes as $node) {
                    $this->publishingService->publishNode($node);
                }
                $this->addFlashMessage($this->translator->translateById('workspaces.selectedChangesHaveBeenPublished', [], null, null, 'Modules', 'Neos.Neos'));
            break;
            case 'discard':
                $this->publishingService->discardNodes($nodes);
                $this->addFlashMessage($this->translator->translateById('workspaces.selectedChangesHaveBeenDiscarded', [], null, null, 'Modules', 'Neos.Neos'));
            break;
            default:
                throw new \RuntimeException('Invalid action "' . htmlspecialchars($action) . '" given.', 1346167441);
        }

        $this->redirect('show', null, null, ['workspace' => $selectedWorkspace]);
    }

    /**
     * Publishes the whole workspace
     *
     * @param Workspace $workspace
     * @return void
     */
    public function publishWorkspaceAction(Workspace $workspace)
    {
        if (($targetWorkspace = $workspace->getBaseWorkspace()) === null) {
            $targetWorkspace = $this->workspaceRepository->findOneByName('live');
        }
        $this->publishingService->publishNodes($this->publishingService->getUnpublishedNodes($workspace), $targetWorkspace);
        $this->addFlashMessage($this->translator->translateById('workspaces.allChangesInWorkspaceHaveBeenPublished', [htmlspecialchars($workspace->getTitle()), htmlspecialchars($targetWorkspace->getTitle())], null, null, 'Modules', 'Neos.Neos'));
        $this->redirect('index');
    }

    /**
     * Discards content of the whole workspace
     *
     * @param Workspace $workspace
     * @return void
     */
    public function discardWorkspaceAction(Workspace $workspace)
    {
        $unpublishedNodes = $this->publishingService->getUnpublishedNodes($workspace);
        $this->publishingService->discardNodes($unpublishedNodes);
        $this->addFlashMessage($this->translator->translateById('workspaces.allChangesInWorkspaceHaveBeenDiscarded', [htmlspecialchars($workspace->getTitle())], null, null, 'Modules', 'Neos.Neos'));
        $this->redirect('index');
    }

    /**
     * Computes the number of added, changed and removed nodes for the given workspace
     *
     * @param Workspace $selectedWorkspace
     * @return array
     */
    protected function computeChangesCount(Workspace $selectedWorkspace)
    {
        $changesCount = ['new' => 0, 'changed' => 0, 'removed' => 0, 'total' => 0];
        foreach ($this->computeSiteChanges($selectedWorkspace) as $siteChanges) {
            foreach ($siteChanges['documents'] as $documentChanges) {
                foreach ($documentChanges['changes'] as $change) {
                    if ($change['node']->isRemoved()) {
                        $changesCount['removed']++;
                    } elseif ($change['isNew']) {
                        $changesCount['new']++;
                    } else {
                        $changesCount['changed']++;
                    };
                    $changesCount['total']++;
                }
            }
        }

        return $changesCount;
    }

    /**
     * Builds an array of changes for sites in the given workspace
     *
     * @param Workspace $selectedWorkspace
     * @return array
     */
    protected function computeSiteChanges(Workspace $selectedWorkspace)
    {
        $siteChanges = [];
        foreach ($this->publishingService->getUnpublishedNodes($selectedWorkspace) as $node) {
            /** @var NodeInterface $node */
            $skipCollectionChanges = $node->getNodeType()->isOfType('Neos.Neos:ContentCollection') && !$node->getNodeType()->isOfType('Neos.Neos:Content');
            if (!$skipCollectionChanges) {
                $pathParts = explode('/', $node->getPath());
                if (count($pathParts) > 2) {
                    $siteNodeName = $pathParts[2];
                    $q = new FlowQuery([$node]);
                    $document = $q->closest('[instanceof Neos.Neos:Document]')->get(0);

                    // $document will be null if we have a broken root line for this node. This actually should never happen, but currently can in some scenarios.
                    if ($document !== null) {
                        $documentPath = implode('/', array_slice(explode('/', $document->getPath()), 3));
                        $relativePath = str_replace(sprintf(SiteService::SITES_ROOT_PATH . '/%s/%s', $siteNodeName, $documentPath), '', $node->getPath());
                        if (!isset($siteChanges[$siteNodeName]['siteNode'])) {
                            $siteChanges[$siteNodeName]['siteNode'] = $this->siteRepository->findOneByNodeName($siteNodeName);
                        }
                        $siteChanges[$siteNodeName]['documents'][$documentPath]['documentNode'] = $document;

                        $change = [
                            'node' => $node,
                            'contentChanges' => $this->renderContentChanges($node)
                        ];
                        if ($node->getNodeType()->isOfType('Neos.Neos:Node')) {
                            $change['configuration'] = $node->getNodeType()->getFullConfiguration();
                        }
                        $siteChanges[$siteNodeName]['documents'][$documentPath]['changes'][$relativePath] = $change;
                    }
                }
            }
        }

        $liveContext = $this->contextFactory->create([
            'workspaceName' => 'live'
        ]);

        ksort($siteChanges);
        foreach ($siteChanges as $siteKey => $site) {
            foreach ($site['documents'] as $documentKey => $document) {
                $liveDocumentNode = $liveContext->getNodeByIdentifier($document['documentNode']->getIdentifier());
                $siteChanges[$siteKey]['documents'][$documentKey]['isMoved'] = $liveDocumentNode && $document['documentNode']->getPath() !== $liveDocumentNode->getPath();
                $siteChanges[$siteKey]['documents'][$documentKey]['isNew'] = $liveDocumentNode === null;
                foreach ($document['changes'] as $changeKey => $change) {
                    $liveNode = $liveContext->getNodeByIdentifier($change['node']->getIdentifier());
                    $siteChanges[$siteKey]['documents'][$documentKey]['changes'][$changeKey]['isNew'] = is_null($liveNode);
                    $siteChanges[$siteKey]['documents'][$documentKey]['changes'][$changeKey]['isMoved'] = $liveNode && $change['node']->getPath() !== $liveNode->getPath();
                }
            }
            ksort($siteChanges[$siteKey]['documents']);
        }
        return $siteChanges;
    }

    /**
     * Retrieves the given node's corresponding node in the base workspace (that is, which would be overwritten if the
     * given node would be published)
     *
     * @param NodeInterface $modifiedNode
     * @return NodeInterface
     */
    protected function getOriginalNode(NodeInterface $modifiedNode)
    {
        $baseWorkspaceName = $modifiedNode->getWorkspace()->getBaseWorkspace()->getName();
        $contextProperties = $modifiedNode->getContext()->getProperties();
        $contextProperties['workspaceName'] = $baseWorkspaceName;
        $contentContext = $this->contextFactory->create($contextProperties);

        return $contentContext->getNodeByIdentifier($modifiedNode->getIdentifier());
    }

    /**
     * Renders the difference between the original and the changed content of the given node and returns it, along
     * with meta information, in an array.
     *
     * @param NodeInterface $changedNode
     * @return array
     */
    protected function renderContentChanges(NodeInterface $changedNode)
    {
        $contentChanges = [];
        $originalNode = $this->getOriginalNode($changedNode);
        $changeNodePropertiesDefaults = $changedNode->getNodeType()->getDefaultValuesForProperties($changedNode);

        $renderer = new HtmlArrayRenderer();
        foreach ($changedNode->getProperties() as $propertyName => $changedPropertyValue) {
            if ($originalNode === null && empty($changedPropertyValue) || (isset($changeNodePropertiesDefaults[$propertyName]) && $changedPropertyValue === $changeNodePropertiesDefaults[$propertyName])) {
                continue;
            }

            $originalPropertyValue = ($originalNode === null ? null : $originalNode->getProperty($propertyName));

            if ($changedPropertyValue === $originalPropertyValue && !$changedNode->isRemoved()) {
                continue;
            }

            if (!is_object($originalPropertyValue) && !is_object($changedPropertyValue)) {
                $originalSlimmedDownContent = $this->renderSlimmedDownContent($originalPropertyValue);
                $changedSlimmedDownContent = $changedNode->isRemoved() ? '' : $this->renderSlimmedDownContent($changedPropertyValue);

                $diff = new Diff(explode("\n", $originalSlimmedDownContent), explode("\n", $changedSlimmedDownContent), ['context' => 1]);
                $diffArray = $diff->render($renderer);
                $this->postProcessDiffArray($diffArray);

                if (count($diffArray) > 0) {
                    $contentChanges[$propertyName] = [
                        'type' => 'text',
                        'propertyLabel' => $this->getPropertyLabel($propertyName, $changedNode),
                        'diff' => $diffArray
                    ];
                }
                // The && in belows condition is on purpose as creating a thumbnail for comparison only works if actually
                // BOTH are ImageInterface (or NULL).
            } elseif (
                ($originalPropertyValue instanceof ImageInterface || $originalPropertyValue === null)
                && ($changedPropertyValue instanceof ImageInterface || $changedPropertyValue === null)
            ) {
                $contentChanges[$propertyName] = [
                    'type' => 'image',
                    'propertyLabel' => $this->getPropertyLabel($propertyName, $changedNode),
                    'original' => $originalPropertyValue,
                    'changed' => $changedPropertyValue
                ];
            } elseif ($originalPropertyValue instanceof AssetInterface || $changedPropertyValue instanceof AssetInterface) {
                $contentChanges[$propertyName] = [
                    'type' => 'asset',
                    'propertyLabel' => $this->getPropertyLabel($propertyName, $changedNode),
                    'original' => $originalPropertyValue,
                    'changed' => $changedPropertyValue
                ];
            } elseif ($originalPropertyValue instanceof \DateTime || $changedPropertyValue instanceof \DateTime) {
                $changed = false;
                if (!$changedPropertyValue instanceof \DateTime || !$originalPropertyValue instanceof \DateTime) {
                    $changed = true;
                } elseif ($changedPropertyValue->getTimestamp() !== $originalPropertyValue->getTimestamp()) {
                    $changed = true;
                }
                if ($changed) {
                    $contentChanges[$propertyName] = [
                        'type' => 'datetime',
                        'propertyLabel' => $this->getPropertyLabel($propertyName, $changedNode),
                        'original' => $originalPropertyValue,
                        'changed' => $changedPropertyValue
                    ];
                }
            }
        }
        return $contentChanges;
    }

    /**
     * Renders a slimmed down representation of a property of the given node. The output will be HTML, but does not
     * contain any markup from the original content.
     *
     * Note: It's clear that this method needs to be extracted and moved to a more universal service at some point.
     * However, since we only implemented diff-view support for this particular controller at the moment, it stays
     * here for the time being. Once we start displaying diffs elsewhere, we should refactor the diff rendering part.
     *
     * @param mixed $propertyValue
     * @return string
     */
    protected function renderSlimmedDownContent($propertyValue)
    {
        $content = '';
        if (is_string($propertyValue)) {
            $contentSnippet = preg_replace('/<br[^>]*>/', "\n", $propertyValue);
            $contentSnippet = preg_replace('/<[^>]*>/', ' ', $contentSnippet);
            $contentSnippet = str_replace('&nbsp;', ' ', $contentSnippet);
            $content = trim(preg_replace('/ {2,}/', ' ', $contentSnippet));
        }
        return $content;
    }

    /**
     * Tries to determine a label for the specified property
     *
     * @param string $propertyName
     * @param NodeInterface $changedNode
     * @return string
     */
    protected function getPropertyLabel($propertyName, NodeInterface $changedNode)
    {
        $properties = $changedNode->getNodeType()->getProperties();
        if (!isset($properties[$propertyName]) ||
            !isset($properties[$propertyName]['ui']['label'])
        ) {
            return $propertyName;
        }
        return $properties[$propertyName]['ui']['label'];
    }

    /**
     * A workaround for some missing functionality in the Diff Renderer:
     *
     * This method will check if content in the given diff array is either completely new or has been completely
     * removed and wraps the respective part in <ins> or <del> tags, because the Diff Renderer currently does not
     * do that in these cases.
     *
     * @param array $diffArray
     * @return void
     */
    protected function postProcessDiffArray(array &$diffArray)
    {
        foreach ($diffArray as $index => $blocks) {
            foreach ($blocks as $blockIndex => $block) {
                $baseLines = trim(implode('', $block['base']['lines']), " \t\n\r\0\xC2\xA0");
                $changedLines = trim(implode('', $block['changed']['lines']), " \t\n\r\0\xC2\xA0");
                if ($baseLines === '') {
                    foreach ($block['changed']['lines'] as $lineIndex => $line) {
                        $diffArray[$index][$blockIndex]['changed']['lines'][$lineIndex] = '<ins>' . $line . '</ins>';
                    }
                }
                if ($changedLines === '') {
                    foreach ($block['base']['lines'] as $lineIndex => $line) {
                        $diffArray[$index][$blockIndex]['base']['lines'][$lineIndex] = '<del>' . $line . '</del>';
                    }
                }
            }
        }
    }

    /**
     * Creates an array of workspace names and their respective titles which are possible base workspaces for other
     * workspaces.
     *
     * @param Workspace $excludedWorkspace If set, this workspace will be excluded from the list of returned workspaces
     * @return array
     */
    protected function prepareBaseWorkspaceOptions(Workspace $excludedWorkspace = null)
    {
        $baseWorkspaceOptions = [];
        foreach ($this->workspaceRepository->findAll() as $workspace) {
            /** @var Workspace $workspace */
            if (!$workspace->isPersonalWorkspace() && $workspace !== $excludedWorkspace && ($workspace->isPublicWorkspace() || $workspace->isInternalWorkspace() || $this->userService->currentUserCanManageWorkspace($workspace))) {
                $baseWorkspaceOptions[$workspace->getName()] = $workspace->getTitle();
            }
        }

        return $baseWorkspaceOptions;
    }

    /**
     * Creates an array of user names and their respective labels which are possible owners for a workspace.
     *
     * @return array
     */
    protected function prepareOwnerOptions()
    {
        $ownerOptions = ['' => '-'];
        foreach ($this->userService->getUsers() as $user) {
            /** @var User $user */
            $ownerOptions[$this->persistenceManager->getIdentifierByObject($user)] = $user->getLabel();
        }

        return $ownerOptions;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Controller\Module\Management;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * The Neos Workspaces module controller
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class WorkspacesController extends WorkspacesController_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
        if (get_class($this) === 'Neos\Neos\Controller\Module\Management\WorkspacesController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Controller\Module\Management\WorkspacesController', $this);
        if ('Neos\Neos\Controller\Module\Management\WorkspacesController' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Neos\Controller\Module\Management\WorkspacesController';
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
            'showAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'newAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'createAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'editAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'updateAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'deleteAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'rebaseAndRedirectAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'publishNodeAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'discardNodeAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'publishOrDiscardNodesAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'publishWorkspaceAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'discardWorkspaceAction' => array(
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
        if (get_class($this) === 'Neos\Neos\Controller\Module\Management\WorkspacesController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Controller\Module\Management\WorkspacesController', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();

        $isSameClass = get_class($this) === 'Neos\Neos\Controller\Module\Management\WorkspacesController';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Neos\Controller\Module\Management\WorkspacesController', $classParents) !== false && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== false;

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
     * @return void
     */
    public function indexAction()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction'])) {
            $result = parent::indexAction();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('indexAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Module\Management\WorkspacesController', 'indexAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Workspace $workspace
     * @return void
     */
    public function showAction(\Neos\ContentRepository\Domain\Model\Workspace $workspace)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['showAction'])) {
            $result = parent::showAction($workspace);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['showAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['workspace'] = $workspace;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('showAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Module\Management\WorkspacesController', 'showAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['showAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['showAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     */
    public function newAction()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['newAction'])) {
            $result = parent::newAction();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['newAction'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('newAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Module\Management\WorkspacesController', 'newAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['newAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['newAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param string $title Human friendly title of the workspace, for example "Christmas Campaign
     * @param Workspace $baseWorkspace Workspace the new workspace should be based on
     * @param string $visibility Visibility of the new workspace, must be either "internal" or "shared
     * @param string $description A description explaining the purpose of the new workspace
     * @return void
     * @\Neos\Flow\Annotations\Validate(type="\Neos\Flow\Validation\Validator\NotEmptyValidator", argumentName="title")
     */
    public function createAction($title, \Neos\ContentRepository\Domain\Model\Workspace $baseWorkspace, $visibility, $description = '')
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createAction'])) {
            $result = parent::createAction($title, $baseWorkspace, $visibility, $description);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['createAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['title'] = $title;
                $methodArguments['baseWorkspace'] = $baseWorkspace;
                $methodArguments['visibility'] = $visibility;
                $methodArguments['description'] = $description;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('createAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Module\Management\WorkspacesController', 'createAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Workspace $workspace
     * @return void
     */
    public function editAction(\Neos\ContentRepository\Domain\Model\Workspace $workspace)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['editAction'])) {
            $result = parent::editAction($workspace);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['editAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['workspace'] = $workspace;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('editAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Module\Management\WorkspacesController', 'editAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['editAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['editAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Workspace $workspace A workspace to update
     * @return void
     */
    public function updateAction(\Neos\ContentRepository\Domain\Model\Workspace $workspace)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAction'])) {
            $result = parent::updateAction($workspace);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['workspace'] = $workspace;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('updateAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Module\Management\WorkspacesController', 'updateAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Workspace $workspace A workspace to delete
     * @return void
     */
    public function deleteAction(\Neos\ContentRepository\Domain\Model\Workspace $workspace)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['deleteAction'])) {
            $result = parent::deleteAction($workspace);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['deleteAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['workspace'] = $workspace;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('deleteAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Module\Management\WorkspacesController', 'deleteAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['deleteAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['deleteAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param NodeInterface $targetNode
     * @param Workspace $targetWorkspace
     * @return void
     */
    public function rebaseAndRedirectAction(\Neos\ContentRepository\Domain\Model\NodeInterface $targetNode, \Neos\ContentRepository\Domain\Model\Workspace $targetWorkspace)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['rebaseAndRedirectAction'])) {
            $result = parent::rebaseAndRedirectAction($targetNode, $targetWorkspace);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['rebaseAndRedirectAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['targetNode'] = $targetNode;
                $methodArguments['targetWorkspace'] = $targetWorkspace;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('rebaseAndRedirectAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Module\Management\WorkspacesController', 'rebaseAndRedirectAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['rebaseAndRedirectAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['rebaseAndRedirectAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param NodeInterface $node
     * @param Workspace $selectedWorkspace
     */
    public function publishNodeAction(\Neos\ContentRepository\Domain\Model\NodeInterface $node, \Neos\ContentRepository\Domain\Model\Workspace $selectedWorkspace)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishNodeAction'])) {
            $result = parent::publishNodeAction($node, $selectedWorkspace);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['publishNodeAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
                $methodArguments['selectedWorkspace'] = $selectedWorkspace;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('publishNodeAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Module\Management\WorkspacesController', 'publishNodeAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishNodeAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishNodeAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param NodeInterface $node
     * @param Workspace $selectedWorkspace
     * @throws WorkspaceException
     */
    public function discardNodeAction(\Neos\ContentRepository\Domain\Model\NodeInterface $node, \Neos\ContentRepository\Domain\Model\Workspace $selectedWorkspace)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['discardNodeAction'])) {
            $result = parent::discardNodeAction($node, $selectedWorkspace);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['discardNodeAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
                $methodArguments['selectedWorkspace'] = $selectedWorkspace;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('discardNodeAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Module\Management\WorkspacesController', 'discardNodeAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['discardNodeAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['discardNodeAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param array $nodes <\Neos\ContentRepository\Domain\Model\NodeInterface> $nodes
     * @param string $action
     * @param Workspace $selectedWorkspace
     * @throws \Exception
     * @throws \Neos\Flow\Property\Exception
     * @throws \Neos\Flow\Security\Exception
     */
    public function publishOrDiscardNodesAction(array $nodes, $action, ?\Neos\ContentRepository\Domain\Model\Workspace $selectedWorkspace = NULL)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishOrDiscardNodesAction'])) {
            $result = parent::publishOrDiscardNodesAction($nodes, $action, $selectedWorkspace);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['publishOrDiscardNodesAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['nodes'] = $nodes;
                $methodArguments['action'] = $action;
                $methodArguments['selectedWorkspace'] = $selectedWorkspace;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('publishOrDiscardNodesAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Module\Management\WorkspacesController', 'publishOrDiscardNodesAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishOrDiscardNodesAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishOrDiscardNodesAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Workspace $workspace
     * @return void
     */
    public function publishWorkspaceAction(\Neos\ContentRepository\Domain\Model\Workspace $workspace)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishWorkspaceAction'])) {
            $result = parent::publishWorkspaceAction($workspace);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['publishWorkspaceAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['workspace'] = $workspace;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('publishWorkspaceAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Module\Management\WorkspacesController', 'publishWorkspaceAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishWorkspaceAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['publishWorkspaceAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Workspace $workspace
     * @return void
     */
    public function discardWorkspaceAction(\Neos\ContentRepository\Domain\Model\Workspace $workspace)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['discardWorkspaceAction'])) {
            $result = parent::discardWorkspaceAction($workspace);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['discardWorkspaceAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['workspace'] = $workspace;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('discardWorkspaceAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Module\Management\WorkspacesController', 'discardWorkspaceAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['discardWorkspaceAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['discardWorkspaceAction']);
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
  'publishingService' => 'Neos\\Neos\\Service\\PublishingService',
  'workspaceRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\WorkspaceRepository',
  'siteRepository' => 'Neos\\Neos\\Domain\\Repository\\SiteRepository',
  'propertyMapper' => 'Neos\\Flow\\Property\\PropertyMapper',
  'securityContext' => 'Neos\\Flow\\Security\\Context',
  'contextFactory' => 'Neos\\Neos\\Domain\\Service\\ContentContextFactory',
  'userService' => 'Neos\\Neos\\Domain\\Service\\UserService',
  'translator' => 'Neos\\Flow\\I18n\\Translator',
  'packageManager' => 'Neos\\Flow\\Package\\PackageManager',
  'contentDimensionPresetSource' => 'Neos\\Neos\\Domain\\Service\\ContentDimensionPresetSourceInterface',
  'moduleConfiguration' => 'array',
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
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'supportedMediaTypes' => 'array',
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
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Neos'));
        $this->injectLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Psr\Log\LoggerInterface'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\PublishingService', 'Neos\Neos\Service\PublishingService', 'publishingService', '790a6e9f9a23baf9242545af9512e2e0', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\PublishingService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'workspaceRepository', '9cacb5dd2ad57e06d6f8c82dd5707855', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\WorkspaceRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\SiteRepository', 'Neos\Neos\Domain\Repository\SiteRepository', 'siteRepository', '42785f5eca4dff104f1860b84f531a9f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\SiteRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Property\PropertyMapper', 'Neos\Flow\Property\PropertyMapper', 'propertyMapper', '2ab4a1ce2ee31715713d0f207f0ac637', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Property\PropertyMapper'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Context', 'Neos\Flow\Security\Context', 'securityContext', 'f7e2ddeaebd191e228b8c2e4dc7f1f83', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Context'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Service\ContentContextFactory', 'Neos\Neos\Domain\Service\ContentContextFactory', 'contextFactory', 'bf6447fb48e80589ca3a024bc3882005', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Service\ContentContextFactory'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Service\UserService', 'Neos\Neos\Domain\Service\UserService', 'userService', '187743c7a02891374827e34e9a230cc7', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Service\UserService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\I18n\Translator', 'Neos\Flow\I18n\Translator', 'translator', 'a1556ebf8488dcff234496272bb811f7', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\Translator'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Package\PackageManager', 'Neos\Flow\Package\PackageManager', 'packageManager', '5969f0154592264b520c05738a0c9f97', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Package\PackageManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Service\ContentDimensionPresetSourceInterface', 'Neos\Neos\Domain\Service\ConfigurationContentDimensionPresetSource', 'contentDimensionPresetSource', '96bb2f02eb23939468e8a031d3fe4c1a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Service\ContentDimensionPresetSourceInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Reflection\ReflectionService', 'Neos\Flow\Reflection\ReflectionService', 'reflectionService', '464c26aa94c66579c050985566cbfc1f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Reflection\ReflectionService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService', 'Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService', 'mvcPropertyMappingConfigurationService', '245f31ad31ca22b8c2b2255e0f65f847', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\ViewConfigurationManager', 'Neos\Flow\Mvc\ViewConfigurationManager', 'viewConfigurationManager', '40e27e95b530777b9b476762cf735a69', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\ViewConfigurationManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\SystemLoggerInterface', 'Neos\Flow\Log\Logger', 'systemLogger', '717e9de4d0309f4f47c821b9257eb5c2', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Validation\ValidatorResolver', 'Neos\Flow\Validation\ValidatorResolver', 'validatorResolver', 'e992f50de62d81bfe770d5c5f1242621', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Validation\ValidatorResolver'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\FlashMessageContainer', 'Neos\Flow\Mvc\FlashMessageContainer', 'flashMessageContainer', 'a5f5265657df54eb081324fb2ff5b8e1', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\FlashMessageContainer'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\I18n\Service', 'Neos\Flow\I18n\Service', '_localizationService', 'bdcad09aa1b6973b35f2987887987892', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\Service'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\UserService', 'Neos\Neos\Service\UserService', '_userService', '3af75a289d0337400c3d43d557f82c49', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\UserService'); });
        $this->defaultViewImplementation = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow.mvc.view.defaultImplementation');
        $this->Flow_Injected_Properties = array (
  0 => 'settings',
  1 => 'logger',
  2 => 'publishingService',
  3 => 'workspaceRepository',
  4 => 'siteRepository',
  5 => 'propertyMapper',
  6 => 'securityContext',
  7 => 'contextFactory',
  8 => 'userService',
  9 => 'translator',
  10 => 'packageManager',
  11 => 'contentDimensionPresetSource',
  12 => 'objectManager',
  13 => 'reflectionService',
  14 => 'mvcPropertyMappingConfigurationService',
  15 => 'viewConfigurationManager',
  16 => 'systemLogger',
  17 => 'validatorResolver',
  18 => 'flashMessageContainer',
  19 => 'persistenceManager',
  20 => '_localizationService',
  21 => '_userService',
  22 => 'defaultViewImplementation',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Controller/Module/Management/WorkspacesController.php
#