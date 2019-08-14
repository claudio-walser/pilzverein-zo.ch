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

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\Workspace;
use Neos\ContentRepository\Domain\Repository\WorkspaceRepository;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\ContentRepository\Exception\NodeTypeNotFoundException;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Service\AssetService;
use Neos\Neos\Controller\CreateContentContextTrait;
use Neos\Neos\Domain\Model\Dto\AssetUsageInNodeProperties;
use Neos\Neos\Domain\Repository\SiteRepository;
use Neos\Neos\Domain\Service\ContentDimensionPresetSourceInterface;
use Neos\Neos\Service\UserService;
use Neos\Neos\Domain\Service\UserService as DomainUserService;

/**
 * Controller for asset usage handling
 *
 * @Flow\Scope("singleton")
 */
class UsageController_Original extends ActionController
{
    use CreateContentContextTrait;

    /**
     * @Flow\Inject
     * @var AssetService
     */
    protected $assetService;

    /**
     * @Flow\Inject
     * @var ContentDimensionPresetSourceInterface
     */
    protected $contentDimensionPresetSource;

    /**
     * @Flow\Inject
     * @var UserService
     */
    protected $userService;

    /**
     * @Flow\Inject
     * @var SiteRepository
     */
    protected $siteRepository;

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @Flow\Inject
     * @var WorkspaceRepository
     */
    protected $workspaceRepository;

    /**
     * @Flow\Inject
     * @var DomainUserService
     */
    protected $domainUserService;

    /**
     * Get Related Nodes for an asset
     *
     * @param AssetInterface $asset
     * @return void
     */
    public function relatedNodesAction(AssetInterface $asset)
    {
        $userWorkspace = $this->userService->getPersonalWorkspace();

        $usageReferences = $this->assetService->getUsageReferences($asset);
        $relatedNodes = [];
        $inaccessibleRelations = [];

        $existingSites = $this->siteRepository->findAll();

        foreach ($usageReferences as $usage) {
            $inaccessibleRelation = [
                'type' => get_class($usage),
                'usage' => $usage
            ];

            if (!$usage instanceof AssetUsageInNodeProperties) {
                $inaccessibleRelations[] = $inaccessibleRelation;
                continue;
            }

            try {
                $nodeType = $this->nodeTypeManager->getNodeType($usage->getNodeTypeName());
            } catch (NodeTypeNotFoundException $e) {
                $nodeType = null;
            }
            /** @var Workspace $workspace */
            $workspace = $this->workspaceRepository->findByIdentifier($usage->getWorkspaceName());
            $accessible = $this->domainUserService->currentUserCanReadWorkspace($workspace);

            $inaccessibleRelation['nodeIdentifier'] = $usage->getNodeIdentifier();
            $inaccessibleRelation['workspaceName'] = $usage->getWorkspaceName();
            $inaccessibleRelation['workspace'] = $workspace;
            $inaccessibleRelation['nodeType'] = $nodeType;
            $inaccessibleRelation['accessible'] = $accessible;

            if (!$accessible) {
                $inaccessibleRelations[] = $inaccessibleRelation;
                continue;
            }

            $node = $this->getNodeFrom($usage);
            // this should actually never happen.
            if (!$node) {
                $inaccessibleRelations[] = $inaccessibleRelation;
                continue;
            }

            $flowQuery = new FlowQuery([$node]);
            $documentNode = $flowQuery->closest('[instanceof Neos.Neos:Document]')->get(0);
            // this should actually never happen, too.
            if (!$documentNode) {
                $inaccessibleRelations[] = $inaccessibleRelation;
                continue;
            }

            $site = $node->getContext()->getCurrentSite();
            foreach ($existingSites as $existingSite) {
                /** @var Site $existingSite * */
                $siteNodePath = '/sites/' . $existingSite->getNodeName();
                if ($siteNodePath === $node->getPAth() || strpos($node->getPath(), $siteNodePath . '/') === 0) {
                    $site = $existingSite;
                }
            }

            $relatedNodes[$site->getNodeName()]['site'] = $site;
            $relatedNodes[$site->getNodeName()]['nodes'][] = [
                'node' => $node,
                'documentNode' => $documentNode
            ];
        }

        $this->view->assignMultiple([
            'totalUsageCount' => count($usageReferences),
            'nodeUsageClass' => AssetUsageInNodeProperties::class,
            'asset' => $asset,
            'inaccessibleRelations' => $inaccessibleRelations,
            'relatedNodes' => $relatedNodes,
            'contentDimensions' => $this->contentDimensionPresetSource->getAllPresets(),
            'userWorkspace' => $userWorkspace
        ]);
    }

    /**
     * @param AssetUsageInNodeProperties $assetUsage
     * @return NodeInterface
     */
    private function getNodeFrom(AssetUsageInNodeProperties $assetUsage)
    {
        $context = $this->_contextFactory->create([
            'workspaceName' => $assetUsage->getWorkspaceName(),
            'dimensions' => $assetUsage->getDimensionValues(),
            'invisibleContentShown' => true,
            'removedContentShown' => true]
        );
        return $context->getNodeByIdentifier($assetUsage->getNodeIdentifier());
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Media\Browser\Controller;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Controller for asset usage handling
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class UsageController extends UsageController_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
        if (get_class($this) === 'Neos\Media\Browser\Controller\UsageController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Media\Browser\Controller\UsageController', $this);
        if ('Neos\Media\Browser\Controller\UsageController' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
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
            'relatedNodesAction' => array(
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
        if (get_class($this) === 'Neos\Media\Browser\Controller\UsageController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Media\Browser\Controller\UsageController', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();
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
     * @param AssetInterface $asset
     * @return void
     */
    public function relatedNodesAction(\Neos\Media\Domain\Model\AssetInterface $asset)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['relatedNodesAction'])) {
            $result = parent::relatedNodesAction($asset);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['relatedNodesAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['asset'] = $asset;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('relatedNodesAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Browser\Controller\UsageController', 'relatedNodesAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['relatedNodesAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['relatedNodesAction']);
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
  'assetService' => 'Neos\\Media\\Domain\\Service\\AssetService',
  'contentDimensionPresetSource' => 'Neos\\Neos\\Domain\\Service\\ContentDimensionPresetSourceInterface',
  'userService' => 'Neos\\Neos\\Service\\UserService',
  'siteRepository' => 'Neos\\Neos\\Domain\\Repository\\SiteRepository',
  'nodeTypeManager' => 'Neos\\ContentRepository\\Domain\\Service\\NodeTypeManager',
  'workspaceRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\WorkspaceRepository',
  'domainUserService' => 'Neos\\Neos\\Domain\\Service\\UserService',
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
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Media.Browser'));
        $this->injectLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Psr\Log\LoggerInterface'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Service\AssetService', 'Neos\Media\Domain\Service\AssetService', 'assetService', 'b8a3f9ba29596737396943e4de630328', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Service\AssetService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Service\ContentDimensionPresetSourceInterface', 'Neos\Neos\Domain\Service\ConfigurationContentDimensionPresetSource', 'contentDimensionPresetSource', '96bb2f02eb23939468e8a031d3fe4c1a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Service\ContentDimensionPresetSourceInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\UserService', 'Neos\Neos\Service\UserService', 'userService', '3af75a289d0337400c3d43d557f82c49', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\UserService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\SiteRepository', 'Neos\Neos\Domain\Repository\SiteRepository', 'siteRepository', '42785f5eca4dff104f1860b84f531a9f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\SiteRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\NodeTypeManager', 'Neos\ContentRepository\Domain\Service\NodeTypeManager', 'nodeTypeManager', 'e525e2ecb65f7f9731d6537ddecd16d4', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\NodeTypeManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'workspaceRepository', '9cacb5dd2ad57e06d6f8c82dd5707855', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\WorkspaceRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Service\UserService', 'Neos\Neos\Domain\Service\UserService', 'domainUserService', '187743c7a02891374827e34e9a230cc7', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Service\UserService'); });
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
        $this->defaultViewImplementation = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow.mvc.view.defaultImplementation');
        $this->Flow_Injected_Properties = array (
  0 => 'settings',
  1 => 'logger',
  2 => 'assetService',
  3 => 'contentDimensionPresetSource',
  4 => 'userService',
  5 => 'siteRepository',
  6 => 'nodeTypeManager',
  7 => 'workspaceRepository',
  8 => 'domainUserService',
  9 => 'objectManager',
  10 => 'reflectionService',
  11 => 'mvcPropertyMappingConfigurationService',
  12 => 'viewConfigurationManager',
  13 => 'systemLogger',
  14 => 'validatorResolver',
  15 => 'flashMessageContainer',
  16 => 'persistenceManager',
  17 => '_contextFactory',
  18 => '_siteRepository',
  19 => 'defaultViewImplementation',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Media.Browser/Classes/Controller/UsageController.php
#