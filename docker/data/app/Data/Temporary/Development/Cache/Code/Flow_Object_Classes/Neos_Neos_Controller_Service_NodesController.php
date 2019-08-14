<?php 
namespace Neos\Neos\Controller\Service;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Property\PropertyMapper;
use Neos\FluidAdaptor\View\TemplateView;
use Neos\Neos\Controller\BackendUserTranslationTrait;
use Neos\Neos\Controller\CreateContentContextTrait;
use Neos\Neos\Domain\Service\ContentContext;
use Neos\Neos\Domain\Service\NodeSearchServiceInterface;
use Neos\Neos\Domain\Service\SiteService;
use Neos\Neos\View\Service\NodeJsonView;
use Neos\Neos\Service\Mapping\NodePropertyConverterService;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\ContentRepository\Domain\Utility\NodePaths;

/**
 * Rudimentary REST service for nodes
 *
 * @Flow\Scope("singleton")
 */
class NodesController_Original extends ActionController
{
    use BackendUserTranslationTrait;
    use CreateContentContextTrait;

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @Flow\Inject
     * @var NodeSearchServiceInterface
     */
    protected $nodeSearchService;

    /**
     * @Flow\Inject
     * @var PropertyMapper
     */
    protected $propertyMapper;

    /**
     * @Flow\Inject
     * @var NodePropertyConverterService
     */
    protected $nodePropertyConverterService;

    /**
     * @var array
     */
    protected $viewFormatToObjectNameMap = [
        'html' => TemplateView::class,
        'json' => NodeJsonView::class
    ];

    /**
     * A list of IANA media types which are supported by this controller
     *
     * @var array
     * @see http://www.iana.org/assignments/media-types/index.html
     */
    protected $supportedMediaTypes = [
        'text/html',
        'application/json'
    ];

    /**
     * Shows a list of nodes
     *
     * @param string $searchTerm An optional search term used for filtering the list of nodes
     * @param array $nodeIdentifiers An optional list of node identifiers
     * @param string $workspaceName Name of the workspace to search in, "live" by default
     * @param array $dimensions Optional list of dimensions and their values which should be used for querying
     * @param array $nodeTypes A list of node types the list should be filtered by
     * @param NodeInterface $contextNode a node to use as context for the search
     * @return string
     */
    public function indexAction($searchTerm = '', array $nodeIdentifiers = [], $workspaceName = 'live', array $dimensions = [], array $nodeTypes = ['Neos.Neos:Document'], NodeInterface $contextNode = null)
    {
        $searchableNodeTypeNames = [];
        foreach ($nodeTypes as $nodeTypeName) {
            if (!$this->nodeTypeManager->hasNodeType($nodeTypeName)) {
                $this->throwStatus(400, sprintf('Unknown node type "%s"', $nodeTypeName));
            }

            $searchableNodeTypeNames[$nodeTypeName] = $nodeTypeName;
            /** @var NodeType $subNodeType */
            foreach ($this->nodeTypeManager->getSubNodeTypes($nodeTypeName, false) as $subNodeTypeName => $subNodeType) {
                $searchableNodeTypeNames[$subNodeTypeName] = $subNodeTypeName;
            }
        }

        $contentContext = $this->createContentContext($workspaceName, $dimensions);
        if ($nodeIdentifiers === []) {
            $nodes = $this->nodeSearchService->findByProperties($searchTerm, $searchableNodeTypeNames, $contentContext, $contextNode);
        } else {
            $nodes = array_filter(
                array_map(function ($identifier) use ($contentContext) {
                    return $contentContext->getNodeByIdentifier($identifier);
                }, $nodeIdentifiers)
            );
        }

        $this->view->assign('nodes', $nodes);
    }

    /**
     * Shows a specific node
     *
     * @param string $identifier Specifies the node to look up
     * @param string $workspaceName Name of the workspace to use for querying the node
     * @param array $dimensions Optional list of dimensions and their values which should be used for querying the specified node
     * @return string
     */
    public function showAction($identifier, $workspaceName = 'live', array $dimensions = [])
    {
        $contentContext = $this->createContentContext($workspaceName, $dimensions);
        /** @var $node NodeInterface */
        $node = $contentContext->getNodeByIdentifier($identifier);

        if ($node === null) {
            $this->addExistingNodeVariantInformationToResponse($identifier, $contentContext);
            $this->throwStatus(404);
        }

        $convertedNodeProperties = $this->nodePropertyConverterService->getPropertiesArray($node);
        array_walk($convertedNodeProperties, function (&$value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
        });

        $this->view->assignMultiple([
            'node' => $node,
            'convertedNodeProperties' => $convertedNodeProperties
        ]);
    }

    /**
     * Create a new node from an existing one
     *
     * The "mode" property defines the basic mode of operation. Currently supported modes:
     *
     * 'adoptFromAnotherDimension': Adopts the single node from another dimension
     *   - $identifier, $workspaceName and $sourceDimensions specify the source node
     *   - $identifier, $workspaceName and $dimensions specify the target node
     *
     * @param string $mode
     * @param string $identifier Specifies the identifier of the node to be created; if source
     * @param string $workspaceName Name of the workspace where to create the node in
     * @param array $dimensions Optional list of dimensions and their values in which the node should be created
     * @param array $sourceDimensions
     * @return string
     */
    public function createAction($mode, $identifier, $workspaceName = 'live', array $dimensions = [], array $sourceDimensions = [])
    {
        if ($mode === 'adoptFromAnotherDimension' || $mode === 'adoptFromAnotherDimensionAndCopyContent') {
            $originalContentContext = $this->createContentContext($workspaceName, $sourceDimensions);
            $node = $originalContentContext->getNodeByIdentifier($identifier);

            if ($node === null) {
                $this->throwStatus(404, 'Original node was not found.');
            }

            $contentContext = $this->createContentContext($workspaceName, $dimensions);

            $this->adoptNodeAndParents($node, $contentContext, $mode === 'adoptFromAnotherDimensionAndCopyContent');

            $this->redirect('show', null, null, [
                'identifier' => $identifier,
                'workspaceName' => $workspaceName,
                'dimensions' => $dimensions
            ]);
        } else {
            $this->throwStatus(400, sprintf('The create mode "%s" is not supported.', $mode));
        }
    }

    /**
     * If the node is not found, we *first* want to figure out whether the node exists in other dimensions or is really non-existent
     *
     * @param $identifier
     * @param ContentContext $context
     * @return void
     */
    protected function addExistingNodeVariantInformationToResponse($identifier, ContentContext $context)
    {
        $nodeVariants = $context->getNodeVariantsByIdentifier($identifier);
        if (count($nodeVariants) > 0) {
            $this->response->setHeader('X-Neos-Node-Exists-In-Other-Dimensions', true);

            // If the node exists in another dimension, we want to know how many nodes in the rootline are also missing for the target
            // dimension. This is needed in the UI to tell the user if nodes will be materialized recursively upwards in the rootline.
            // To find the node path for the given identifier, we just use the first result. This is a safe assumption at least for
            // "Document" nodes (aggregate=true), because they are always moved in-sync.
            $node = reset($nodeVariants);
            /** @var NodeInterface $node */
            if ($node->getNodeType()->isAggregate()) {
                $pathSegmentsToSites = NodePaths::getPathDepth(SiteService::SITES_ROOT_PATH);
                $pathSegmentsToNodeVariant = NodePaths::getPathDepth($node->getPath());
                // Segments between the sites root "/sites" and the node variant (minimum 1)
                $pathSegments = $pathSegmentsToNodeVariant - $pathSegmentsToSites;
                // Nodes between (and including) the site root node and the node variant (minimum 1)
                $siteNodePath = NodePaths::addNodePathSegment(SiteService::SITES_ROOT_PATH, $context->getCurrentSite()->getNodeName());
                $nodes = $context->getNodesOnPath($siteNodePath, $node->getPath());
                $missingNodesOnRootline = $pathSegments - count($nodes);
                if ($missingNodesOnRootline > 0) {
                    $this->response->setHeader('X-Neos-Nodes-Missing-On-Rootline', $missingNodesOnRootline);
                }
            }
        }
    }

    /**
     * Adopt (translate) the given node and parents that are not yet visible to the given context
     *
     * @param NodeInterface $node
     * @param ContentContext $contentContext
     * @param boolean $copyContent true if the content from the nodes that are translated should be copied
     * @return void
     */
    protected function adoptNodeAndParents(NodeInterface $node, ContentContext $contentContext, $copyContent)
    {
        $contentContext->adoptNode($node, $copyContent);

        $parentNode = $node;
        while ($parentNode = $parentNode->getParent()) {
            $visibleInContext = $contentContext->getNodeByIdentifier($parentNode->getIdentifier()) !== null;
            if ($parentNode->getPath() !== '/' && $parentNode->getPath() !== SiteService::SITES_ROOT_PATH && !$visibleInContext) {
                $contentContext->adoptNode($parentNode, $copyContent);
            }
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Controller\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Rudimentary REST service for nodes
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class NodesController extends NodesController_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
        if (get_class($this) === 'Neos\Neos\Controller\Service\NodesController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Controller\Service\NodesController', $this);
        if ('Neos\Neos\Controller\Service\NodesController' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Neos\Controller\Service\NodesController';
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
            'createAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'errorAction' => array(
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
        if (get_class($this) === 'Neos\Neos\Controller\Service\NodesController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Controller\Service\NodesController', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();

        $isSameClass = get_class($this) === 'Neos\Neos\Controller\Service\NodesController';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Neos\Controller\Service\NodesController', $classParents) !== false && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== false;

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
     * @param string $searchTerm An optional search term used for filtering the list of nodes
     * @param array $nodeIdentifiers An optional list of node identifiers
     * @param string $workspaceName Name of the workspace to search in, "live" by default
     * @param array $dimensions Optional list of dimensions and their values which should be used for querying
     * @param array $nodeTypes A list of node types the list should be filtered by
     * @param NodeInterface $contextNode a node to use as context for the search
     * @return string
     */
    public function indexAction($searchTerm = '', array $nodeIdentifiers = array(), $workspaceName = 'live', array $dimensions = array(), array $nodeTypes = array(0 => 'Neos.Neos:Document'), ?\Neos\ContentRepository\Domain\Model\NodeInterface $contextNode = NULL)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction'])) {
            $result = parent::indexAction($searchTerm, $nodeIdentifiers, $workspaceName, $dimensions, $nodeTypes, $contextNode);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['searchTerm'] = $searchTerm;
                $methodArguments['nodeIdentifiers'] = $nodeIdentifiers;
                $methodArguments['workspaceName'] = $workspaceName;
                $methodArguments['dimensions'] = $dimensions;
                $methodArguments['nodeTypes'] = $nodeTypes;
                $methodArguments['contextNode'] = $contextNode;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('indexAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Service\NodesController', 'indexAction', $methodArguments, $adviceChain);
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
     * @param string $identifier Specifies the node to look up
     * @param string $workspaceName Name of the workspace to use for querying the node
     * @param array $dimensions Optional list of dimensions and their values which should be used for querying the specified node
     * @return string
     */
    public function showAction($identifier, $workspaceName = 'live', array $dimensions = array())
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['showAction'])) {
            $result = parent::showAction($identifier, $workspaceName, $dimensions);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['showAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['identifier'] = $identifier;
                $methodArguments['workspaceName'] = $workspaceName;
                $methodArguments['dimensions'] = $dimensions;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('showAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Service\NodesController', 'showAction', $methodArguments, $adviceChain);
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
     * @param string $mode
     * @param string $identifier Specifies the identifier of the node to be created; if source
     * @param string $workspaceName Name of the workspace where to create the node in
     * @param array $dimensions Optional list of dimensions and their values in which the node should be created
     * @param array $sourceDimensions
     * @return string
     */
    public function createAction($mode, $identifier, $workspaceName = 'live', array $dimensions = array(), array $sourceDimensions = array())
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['createAction'])) {
            $result = parent::createAction($mode, $identifier, $workspaceName, $dimensions, $sourceDimensions);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['createAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['mode'] = $mode;
                $methodArguments['identifier'] = $identifier;
                $methodArguments['workspaceName'] = $workspaceName;
                $methodArguments['dimensions'] = $dimensions;
                $methodArguments['sourceDimensions'] = $sourceDimensions;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('createAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Service\NodesController', 'createAction', $methodArguments, $adviceChain);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Controller\Service\NodesController', 'errorAction', $methodArguments, $adviceChain);
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
     */
    public function __sleep()
    {
            $result = NULL;
        $this->Flow_Object_PropertiesToSerialize = array();

        $transientProperties = array (
);
        $propertyVarTags = array (
  'nodeTypeManager' => 'Neos\\ContentRepository\\Domain\\Service\\NodeTypeManager',
  'nodeSearchService' => 'Neos\\Neos\\Domain\\Service\\NodeSearchServiceInterface',
  'propertyMapper' => 'Neos\\Flow\\Property\\PropertyMapper',
  'nodePropertyConverterService' => 'Neos\\Neos\\Service\\Mapping\\NodePropertyConverterService',
  'viewFormatToObjectNameMap' => 'array',
  'supportedMediaTypes' => 'array',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\NodeTypeManager', 'Neos\ContentRepository\Domain\Service\NodeTypeManager', 'nodeTypeManager', 'e525e2ecb65f7f9731d6537ddecd16d4', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\NodeTypeManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Service\NodeSearchServiceInterface', 'Neos\Neos\Domain\Service\NodeSearchService', 'nodeSearchService', 'e161d65eb2ddc7a4461eb3fc98cdaf5a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Service\NodeSearchServiceInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Property\PropertyMapper', 'Neos\Flow\Property\PropertyMapper', 'propertyMapper', '2ab4a1ce2ee31715713d0f207f0ac637', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Property\PropertyMapper'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\Mapping\NodePropertyConverterService', 'Neos\Neos\Service\Mapping\NodePropertyConverterService', 'nodePropertyConverterService', 'fcc7b444cbe0af01c7f7bd7f2fe01850', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\Mapping\NodePropertyConverterService'); });
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Service\ContentContextFactory', 'Neos\Neos\Domain\Service\ContentContextFactory', '_contextFactory', 'bf6447fb48e80589ca3a024bc3882005', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Service\ContentContextFactory'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\SiteRepository', 'Neos\Neos\Domain\Repository\SiteRepository', '_siteRepository', '42785f5eca4dff104f1860b84f531a9f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\SiteRepository'); });
        $this->defaultViewImplementation = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow.mvc.view.defaultImplementation');
        $this->Flow_Injected_Properties = array (
  0 => 'settings',
  1 => 'logger',
  2 => 'nodeTypeManager',
  3 => 'nodeSearchService',
  4 => 'propertyMapper',
  5 => 'nodePropertyConverterService',
  6 => 'objectManager',
  7 => 'reflectionService',
  8 => 'mvcPropertyMappingConfigurationService',
  9 => 'viewConfigurationManager',
  10 => 'systemLogger',
  11 => 'validatorResolver',
  12 => 'flashMessageContainer',
  13 => 'persistenceManager',
  14 => '_localizationService',
  15 => '_userService',
  16 => '_contextFactory',
  17 => '_siteRepository',
  18 => 'defaultViewImplementation',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Controller/Service/NodesController.php
#