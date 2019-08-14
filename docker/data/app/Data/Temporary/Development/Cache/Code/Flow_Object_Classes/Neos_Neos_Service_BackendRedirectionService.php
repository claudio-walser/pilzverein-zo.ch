<?php 
namespace Neos\Neos\Service;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Routing\UriBuilder;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Property\PropertyMapper;
use Neos\Flow\Session\SessionInterface;
use Neos\Neos\Domain\Repository\DomainRepository;
use Neos\Neos\Domain\Repository\SiteRepository;
use Neos\Neos\Domain\Service\ContentContext;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\Workspace;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\ContentRepository\Domain\Repository\WorkspaceRepository;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;

/**
 * @Flow\Scope("singleton")
 */
class BackendRedirectionService_Original
{
    /**
     * @Flow\Inject
     * @var SessionInterface
     */
    protected $session;

    /**
     * @Flow\Inject
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;

    /**
     * @Flow\Inject
     * @var WorkspaceRepository
     */
    protected $workspaceRepository;

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var DomainRepository
     */
    protected $domainRepository;

    /**
     * @Flow\Inject
     * @var SiteRepository
     */
    protected $siteRepository;

    /**
     * @Flow\Inject
     * @var UserService
     */
    protected $userService;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var PropertyMapper
     */
    protected $propertyMapper;

    /**
     * @Flow\InjectConfiguration(package="Neos.Neos", path="userInterface.routeAfterLogin.values")
     * @var bool
     */
    protected $routingValuesAfterLogin;

    /**
     * Returns a specific URI string to redirect to after the login; or NULL if there is none.
     *
     * @param ActionRequest $actionRequest
     * @return string
     */
    public function getAfterLoginRedirectionUri(ActionRequest $actionRequest)
    {
        $user = $this->userService->getBackendUser();
        if ($user === null) {
            return null;
        }

        $workspaceName = $this->userService->getPersonalWorkspaceName();
        $this->createWorkspaceAndRootNodeIfNecessary($workspaceName);

        $uriBuilder = new UriBuilder();
        $uriBuilder->setRequest($actionRequest);
        $uriBuilder->setFormat('html');
        $uriBuilder->setCreateAbsoluteUri(true);

        $nodeToEdit = $this->getLastVisitedNode($workspaceName);
        if ($nodeToEdit === null) {
            $contentContext = $this->createContext($workspaceName);
            $nodeToEdit = $contentContext->getCurrentSiteNode();
        }

        $arguments = array_merge(['node' => $nodeToEdit], $this->routingValuesAfterLogin);
        return $uriBuilder->uriFor($this->routingValuesAfterLogin['@action'], $arguments, $this->routingValuesAfterLogin['@controller'], $this->routingValuesAfterLogin['@package']);
    }

    /**
     * Returns a specific URI string to redirect to after the logout; or NULL if there is none.
     * In case of NULL, it's the responsibility of the AuthenticationController where to redirect,
     * most likely to the LoginController's index action.
     *
     * @param ActionRequest $actionRequest
     * @return string A possible redirection URI, if any
     */
    public function getAfterLogoutRedirectionUri(ActionRequest $actionRequest)
    {
        $lastVisitedNode = $this->getLastVisitedNode('live');
        if ($lastVisitedNode === null) {
            return null;
        }
        $uriBuilder = new UriBuilder();
        $uriBuilder->setRequest($actionRequest);
        $uriBuilder->setFormat('html');
        $uriBuilder->setCreateAbsoluteUri(true);
        return $uriBuilder->uriFor('show', ['node' => $lastVisitedNode], 'Frontend\\Node', 'Neos.Neos');
    }

    /**
     *
     * @param string $workspaceName
     * @return NodeInterface
     */
    protected function getLastVisitedNode($workspaceName)
    {
        if (!$this->session->isStarted() || !$this->session->hasKey('lastVisitedNode')) {
            return null;
        }
        try {
            $lastVisitedNode = $this->propertyMapper->convert($this->session->getData('lastVisitedNode'), NodeInterface::class);
            $q = new FlowQuery([$lastVisitedNode]);
            $lastVisitedNodeUserWorkspace = $q->context(['workspaceName' => $workspaceName])->get(0);
            return $lastVisitedNodeUserWorkspace;
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * Create a ContentContext to be used for the backend redirects.
     *
     * @param string $workspaceName
     * @return ContentContext
     */
    protected function createContext($workspaceName)
    {
        $contextProperties = [
            'workspaceName' => $workspaceName,
            'invisibleContentShown' => true,
            'inaccessibleContentShown' => true
        ];

        return $this->contextFactory->create($contextProperties);
    }

    /**
     * If the specified workspace or its root node does not exist yet, the workspace and root node will be created.
     *
     * This method is basically a safeguard for legacy and potentially broken websites where users might not have
     * their own workspace yet. In a normal setup, the Domain User Service is responsible for creating and deleting
     * user workspaces.
     *
     * @param string $workspaceName Name of the workspace
     * @return void
     */
    protected function createWorkspaceAndRootNodeIfNecessary($workspaceName)
    {
        $workspace = $this->workspaceRepository->findOneByName($workspaceName);
        if ($workspace === null) {
            $liveWorkspace = $this->workspaceRepository->findOneByName('live');
            $owner = $this->userService->getBackendUser();
            $workspace = new Workspace($workspaceName, $liveWorkspace, $owner);
            $this->workspaceRepository->add($workspace);
            $this->persistenceManager->whitelistObject($workspace);
        }

        $contentContext = $this->createContext($workspaceName);
        $rootNode = $contentContext->getRootNode();
        $this->persistenceManager->whitelistObject($rootNode);
        $this->persistenceManager->whitelistObject($rootNode->getNodeData());
        $this->persistenceManager->persistAll(true);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class BackendRedirectionService extends BackendRedirectionService_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Neos\Service\BackendRedirectionService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Service\BackendRedirectionService', $this);
        if ('Neos\Neos\Service\BackendRedirectionService' === get_class($this)) {
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
  'session' => 'Neos\\Flow\\Session\\SessionInterface',
  'nodeDataRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\NodeDataRepository',
  'workspaceRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\WorkspaceRepository',
  'contextFactory' => 'Neos\\ContentRepository\\Domain\\Service\\ContextFactoryInterface',
  'domainRepository' => 'Neos\\Neos\\Domain\\Repository\\DomainRepository',
  'siteRepository' => 'Neos\\Neos\\Domain\\Repository\\SiteRepository',
  'userService' => 'Neos\\Neos\\Service\\UserService',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'propertyMapper' => 'Neos\\Flow\\Property\\PropertyMapper',
  'routingValuesAfterLogin' => 'boolean',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Neos\Service\BackendRedirectionService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Service\BackendRedirectionService', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->session = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Session\SessionInterface');
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\NodeDataRepository', 'Neos\ContentRepository\Domain\Repository\NodeDataRepository', 'nodeDataRepository', '6d07985e92d364413ac81acd8f47b11b', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\NodeDataRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'workspaceRepository', '9cacb5dd2ad57e06d6f8c82dd5707855', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\WorkspaceRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\ContextFactoryInterface', 'Neos\Neos\Domain\Service\ContentContextFactory', 'contextFactory', '98dca7b1f95a25ec173662fc4e785341', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\ContextFactoryInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\DomainRepository', 'Neos\Neos\Domain\Repository\DomainRepository', 'domainRepository', '37b1b7f7b2d5d92dae299591af3b7e10', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\DomainRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\SiteRepository', 'Neos\Neos\Domain\Repository\SiteRepository', 'siteRepository', '42785f5eca4dff104f1860b84f531a9f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\SiteRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\UserService', 'Neos\Neos\Service\UserService', 'userService', '3af75a289d0337400c3d43d557f82c49', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\UserService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Property\PropertyMapper', 'Neos\Flow\Property\PropertyMapper', 'propertyMapper', '2ab4a1ce2ee31715713d0f207f0ac637', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Property\PropertyMapper'); });
        $this->routingValuesAfterLogin = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Neos.userInterface.routeAfterLogin.values');
        $this->Flow_Injected_Properties = array (
  0 => 'session',
  1 => 'nodeDataRepository',
  2 => 'workspaceRepository',
  3 => 'contextFactory',
  4 => 'domainRepository',
  5 => 'siteRepository',
  6 => 'userService',
  7 => 'persistenceManager',
  8 => 'propertyMapper',
  9 => 'routingValuesAfterLogin',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Service/BackendRedirectionService.php
#