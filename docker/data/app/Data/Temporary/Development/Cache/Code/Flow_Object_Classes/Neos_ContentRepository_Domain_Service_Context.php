<?php 
namespace Neos\ContentRepository\Domain\Service;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\PsrSystemLoggerInterface;
use Neos\ContentRepository\Domain\Factory\NodeFactory;
use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\Workspace;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\ContentRepository\Domain\Repository\WorkspaceRepository;
use Neos\ContentRepository\Domain\Service\Cache\FirstLevelNodeCache;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;

/**
 * Context
 *
 * @api
 */
class Context_Original
{
    /**
     * @Flow\Inject
     * @var WorkspaceRepository
     */
    protected $workspaceRepository;

    /**
     * @Flow\Inject
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;

    /**
     * @Flow\Inject
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var PsrSystemLoggerInterface
     */
    protected $systemLogger;

    /**
     * @var Workspace
     */
    protected $workspace;

    /**
     * @var string
     */
    protected $workspaceName;

    /**
     * @var \DateTime
     */
    protected $currentDateTime;

    /**
     * If true, invisible content elements will be shown.
     *
     * @var boolean
     */
    protected $invisibleContentShown = false;

    /**
     * If true, removed content elements will be shown, even though they are removed.
     *
     * @var boolean
     */
    protected $removedContentShown = false;

    /**
     * If true, even content elements will be shown which are not accessible by the currently logged in account.
     *
     * @var boolean
     */
    protected $inaccessibleContentShown = false;

    /**
     * @var array
     */
    protected $dimensions = [];

    /**
     * @var array
     */
    protected $targetDimensions = [];

    /**
     * @Flow\IgnoreValidation
     * @var FirstLevelNodeCache
     */
    protected $firstLevelNodeCache;

    /**
     * Creates a new Context object.
     *
     * NOTE: This is for internal use only, you should use the ContextFactory for creating Context instances.
     *
     * @param string $workspaceName Name of the current workspace
     * @param \DateTimeInterface $currentDateTime The current date and time
     * @param array $dimensions Array of dimensions with array of ordered values
     * @param array $targetDimensions Array of dimensions used when creating / modifying content
     * @param boolean $invisibleContentShown If invisible content should be returned in query results
     * @param boolean $removedContentShown If removed content should be returned in query results
     * @param boolean $inaccessibleContentShown If inaccessible content should be returned in query results
     * @see ContextFactoryInterface
     */
    public function __construct($workspaceName, \DateTimeInterface $currentDateTime, array $dimensions, array $targetDimensions, $invisibleContentShown, $removedContentShown, $inaccessibleContentShown)
    {
        $this->workspaceName = $workspaceName;
        $this->currentDateTime = $currentDateTime;
        $this->dimensions = $dimensions;
        $this->invisibleContentShown = $invisibleContentShown;
        $this->removedContentShown = $removedContentShown;
        $this->inaccessibleContentShown = $inaccessibleContentShown;
        $this->targetDimensions = $targetDimensions;

        $this->firstLevelNodeCache = new FirstLevelNodeCache();
    }

    /**
     * Returns the current workspace.
     *
     * @param boolean $createWorkspaceIfNecessary DEPRECATED: If enabled, creates a workspace with the configured name if it doesn't exist already. This option is DEPRECATED, create workspace explicitly instead.
     * @return Workspace The workspace or NULL
     * @api
     * @throws IllegalObjectTypeException
     */
    public function getWorkspace($createWorkspaceIfNecessary = true)
    {
        if ($this->workspace !== null) {
            return $this->workspace;
        }

        $this->workspace = $this->workspaceRepository->findByIdentifier($this->workspaceName);
        if ($this->workspace === null && $createWorkspaceIfNecessary) {
            $liveWorkspace = $this->workspaceRepository->findByIdentifier('live');
            $this->workspace = new Workspace($this->workspaceName, $liveWorkspace);
            $this->workspaceRepository->add($this->workspace);
            $this->systemLogger->notice(sprintf('Notice: %s::getWorkspace() implicitly created the new workspace "%s". This behaviour is discouraged and will be removed in future versions. Make sure to create workspaces explicitly by adding a new workspace to the Workspace Repository.', __CLASS__, $this->workspaceName), LogEnvironment::fromMethodName(__METHOD__));
        }

        if ($this->workspace !== null) {
            $this->validateWorkspace($this->workspace);
        }

        return $this->workspace;
    }

    /**
     * This method is called in order to check if a workspace is accessible.
     *
     * At the time of this writing, it is not possible in Flow to restrict access to Workspace through Entity Privileges
     * because Workspaces are used at a very early stage during routing where the security context is not yet initialized.
     * As a workaround, we use a Method Privilege which protects this validateWorkspace() method and thus prevents
     * unauthorized access to a workspace when calling this context's getWorkspace() method.
     *
     * Since some privilege definitions check the "owner" property of a Workspace, we need a real Workspace object and
     * not just the name - hence this method.
     *
     * @param Workspace $workspace The workspace to check
     * @return void
     */
    public function validateWorkspace(Workspace $workspace)
    {
        // if access to validateWorkspace() is granted, everything is fine, otherwise the security framework will
        // throw an exception
    }

    /**
     * Returns the name of the workspace.
     *
     * @return string
     * @api
     */
    public function getWorkspaceName()
    {
        return $this->workspaceName;
    }

    /**
     * Returns the current date and time in form of a \DateTime
     * object.
     *
     * If you use this method for getting the current date and time
     * everywhere in your code, it will be possible to simulate a certain
     * time in unit tests or in the actual application (for realizing previews etc).
     *
     * @return \DateTime The current date and time - or a simulated version of it
     * @api
     */
    public function getCurrentDateTime()
    {
        return $this->currentDateTime;
    }

    /**
     * Convenience method returns the root node for
     * this context workspace.
     *
     * @return NodeInterface
     * @api
     */
    public function getRootNode()
    {
        return $this->getNode('/');
    }

    /**
     * Returns a node specified by the given absolute path.
     *
     * @param string $path Absolute path specifying the node
     * @return NodeInterface The specified node or NULL if no such node exists
     * @throws \InvalidArgumentException
     * @api
     */
    public function getNode($path)
    {
        if (!is_string($path) || $path[0] !== '/') {
            throw new \InvalidArgumentException('Only absolute paths are allowed for Context::getNode()', 1284975105);
        }

        $path = strtolower($path);

        $workspaceRootNode = $this->getWorkspace()->getRootNodeData();
        $rootNode = $this->nodeFactory->createFromNodeData($workspaceRootNode, $this);
        if ($path !== '/') {
            $node = $this->firstLevelNodeCache->getByPath($path);
            if ($node === false) {
                $node = $rootNode->getNode(substr($path, 1));
                $this->firstLevelNodeCache->setByPath($path, $node);
            }
        } else {
            $node = $rootNode;
        }

        return $node;
    }

    /**
     * Get a node by identifier and this context
     *
     * @param string $identifier The identifier of a node
     * @return NodeInterface The node with the given identifier or NULL if no such node exists
     */
    public function getNodeByIdentifier($identifier)
    {
        $node = $this->firstLevelNodeCache->getByIdentifier($identifier);
        if ($node !== false) {
            return $node;
        }
        $nodeData = $this->nodeDataRepository->findOneByIdentifier($identifier, $this->getWorkspace(), $this->dimensions, $this->removedContentShown);
        if ($nodeData !== null) {
            $node = $this->nodeFactory->createFromNodeData($nodeData, $this);
        } else {
            $node = null;
        }
        $this->firstLevelNodeCache->setByIdentifier($identifier, $node);
        return $node;
    }

    /**
     * Get all node variants for the given identifier
     *
     * A variant of a node can have different dimension values and path (for non-aggregate nodes).
     * The resulting node instances might belong to a different context.
     *
     * @param string $identifier The identifier of a node
     * @return array<\Neos\ContentRepository\Domain\Model\NodeInterface>
     */
    public function getNodeVariantsByIdentifier($identifier)
    {
        $nodeVariants = [];
        $nodeDataElements = $this->nodeDataRepository->findByIdentifierWithoutReduce($identifier, $this->getWorkspace());
        /** @var NodeData $nodeData */
        foreach ($nodeDataElements as $nodeData) {
            $contextProperties = $this->getProperties();
            $contextProperties['dimensions'] = $nodeData->getDimensionValues();
            unset($contextProperties['targetDimensions']);
            $adjustedContext = $this->contextFactory->create($contextProperties);
            $nodeVariant = $this->nodeFactory->createFromNodeData($nodeData, $adjustedContext);
            $nodeVariants[] = $nodeVariant;
        }
        return $nodeVariants;
    }

    /**
     * Finds all nodes lying on the path specified by (and including) the given
     * starting point and end point.
     *
     * @param mixed $startingPoint Either an absolute path or an actual node specifying the starting point, for example /sites/mysitecom
     * @param mixed $endPoint Either an absolute path or an actual node specifying the end point, for example /sites/mysitecom/homepage/subpage
     * @return array<\Neos\ContentRepository\Domain\Model\NodeInterface> The nodes found between and including the given paths or an empty array of none were found
     * @api
     */
    public function getNodesOnPath($startingPoint, $endPoint)
    {
        $startingPointPath = ($startingPoint instanceof NodeInterface) ? $startingPoint->getPath() : $startingPoint;
        $endPointPath = ($endPoint instanceof NodeInterface) ? $endPoint->getPath() : $endPoint;

        $nodeDataElements = $this->nodeDataRepository->findOnPath($startingPointPath, $endPointPath, $this->getWorkspace(), $this->getDimensions(), $this->isRemovedContentShown());
        $nodes = [];
        foreach ($nodeDataElements as $nodeData) {
            $node = $this->nodeFactory->createFromNodeData($nodeData, $this);
            if ($node !== null) {
                $nodes[] = $node;
                $this->firstLevelNodeCache->setByPath($node->getPath(), $node);
            }
        }

        return $nodes;
    }

    /**
     * Adopts a node from a (possibly) different context to this context
     *
     * Checks if a node variant matching the exact dimensions already exists for this context and
     * return it if found. Otherwise a new node variant for this context is created.
     *
     * In case the node already exists in the context but does not match the target dimensions a
     * new, more specific node is created and returned.
     *
     * @param NodeInterface $node The node with a different context. If the context of the given node is the same as this context the operation will have no effect.
     * @param boolean $recursive If true also adopt all descendant nodes which are non-aggregate
     * @return NodeInterface A new or existing node that matches this context
     */
    public function adoptNode(NodeInterface $node, $recursive = false)
    {
        if ($node->getContext() === $this && $node->dimensionsAreMatchingTargetDimensionValues()) {
            return $node;
        }

        $this->emitBeforeAdoptNode($node, $this, $recursive);

        $existingNode = $this->getNodeByIdentifier($node->getIdentifier());
        if ($existingNode !== null) {
            if ($existingNode->dimensionsAreMatchingTargetDimensionValues()) {
                $adoptedNode = $existingNode;
            } else {
                $adoptedNode = $existingNode->createVariantForContext($this);
            }
        } else {
            $adoptedNode = $node->createVariantForContext($this);
        }
        $this->firstLevelNodeCache->setByIdentifier($adoptedNode->getIdentifier(), $adoptedNode);

        if ($recursive) {
            $childNodes = $node->getChildNodes();
            /** @var NodeInterface $childNode */
            foreach ($childNodes as $childNode) {
                if (!$childNode->getNodeType()->isAggregate()) {
                    $this->adoptNode($childNode, true);
                }
            }
        }

        $this->emitAfterAdoptNode($node, $this, $recursive);

        return $adoptedNode;
    }

    /**
     * @Flow\Signal
     * @param NodeInterface $node
     * @param Context $context
     * @param $recursive
     */
    protected function emitBeforeAdoptNode(NodeInterface $node, Context $context, $recursive)
    {
    }

    /**
     * @Flow\Signal
     * @param NodeInterface $node
     * @param Context $context
     * @param $recursive
     */
    protected function emitAfterAdoptNode(NodeInterface $node, Context $context, $recursive)
    {
    }

    /**
     * Tells if nodes which are usually invisible should be accessible through the Node API and queries
     *
     * @return boolean
     * @see NodeFactory->filterNodeByContext()
     * @api
     */
    public function isInvisibleContentShown()
    {
        return $this->invisibleContentShown;
    }

    /**
     * Tells if nodes which have their "removed" flag set should be accessible through
     * the Node API and queries
     *
     * @return boolean
     * @see Node->filterNodeByContext()
     * @api
     */
    public function isRemovedContentShown()
    {
        return $this->removedContentShown;
    }

    /**
     * Tells if nodes which have access restrictions should be accessible through
     * the Node API and queries even without the necessary roles / rights
     *
     * @return boolean
     * @api
     */
    public function isInaccessibleContentShown()
    {
        return $this->inaccessibleContentShown;
    }

    /**
     * An indexed array of dimensions with ordered list of values for matching nodes by content dimensions
     *
     * @return array
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * An indexed array of dimensions with a set of values that should be applied when updating or creating
     *
     * @return array
     */
    public function getTargetDimensions()
    {
        return $this->targetDimensions;
    }

    /**
     * An indexed array of dimensions with a set of values that should be applied when updating or creating
     *
     * @return array
     */
    public function getTargetDimensionValues()
    {
        return array_map(function ($value) {
            return $value === null ? [] : [ $value ];
        }, $this->getTargetDimensions());
    }

    /**
     * Returns the properties of this context.
     *
     * @return array
     */
    public function getProperties()
    {
        return [
            'workspaceName' => $this->workspaceName,
            'currentDateTime' => $this->currentDateTime,
            'dimensions' => $this->dimensions,
            'targetDimensions' => $this->targetDimensions,
            'invisibleContentShown' => $this->invisibleContentShown,
            'removedContentShown' => $this->removedContentShown,
            'inaccessibleContentShown' => $this->inaccessibleContentShown
        ];
    }

    /**
     * Not public API!
     *
     * @return FirstLevelNodeCache
     */
    public function getFirstLevelNodeCache()
    {
        return $this->firstLevelNodeCache;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\ContentRepository\Domain\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Context
 */
class Context extends Context_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     * @param string $workspaceName Name of the current workspace
     * @param \DateTimeInterface $currentDateTime The current date and time
     * @param array $dimensions Array of dimensions with array of ordered values
     * @param array $targetDimensions Array of dimensions used when creating / modifying content
     * @param boolean $invisibleContentShown If invisible content should be returned in query results
     * @param boolean $removedContentShown If removed content should be returned in query results
     * @param boolean $inaccessibleContentShown If inaccessible content should be returned in query results
     */
    public function __construct()
    {
        $arguments = func_get_args();

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $workspaceName in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $currentDateTime in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(2, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $dimensions in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(3, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $targetDimensions in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(4, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $invisibleContentShown in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(5, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $removedContentShown in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(6, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $inaccessibleContentShown in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\ContentRepository\Domain\Service\Context' === get_class($this)) {
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
            'validateWorkspace' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'emitBeforeAdoptNode' => array(
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\SignalSlot\SignalAspect', 'forwardSignalToDispatcher', $objectManager, NULL),
                ),
            ),
            'emitAfterAdoptNode' => array(
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
     * @param Workspace $workspace The workspace to check
     * @return void
     */
    public function validateWorkspace(\Neos\ContentRepository\Domain\Model\Workspace $workspace)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['validateWorkspace'])) {
            $result = parent::validateWorkspace($workspace);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['validateWorkspace'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['workspace'] = $workspace;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('validateWorkspace');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\ContentRepository\Domain\Service\Context', 'validateWorkspace', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['validateWorkspace']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['validateWorkspace']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param NodeInterface $node
     * @param Context $context
     * @param $recursive
     * @\Neos\Flow\Annotations\Signal
     */
    protected function emitBeforeAdoptNode(\Neos\ContentRepository\Domain\Model\NodeInterface $node, \Neos\ContentRepository\Domain\Service\Context $context, $recursive)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitBeforeAdoptNode'])) {
            $result = parent::emitBeforeAdoptNode($node, $context, $recursive);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitBeforeAdoptNode'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
                $methodArguments['context'] = $context;
                $methodArguments['recursive'] = $recursive;
            
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\ContentRepository\Domain\Service\Context', 'emitBeforeAdoptNode', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitBeforeAdoptNode']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitBeforeAdoptNode']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\ContentRepository\Domain\Service\Context', 'emitBeforeAdoptNode', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitBeforeAdoptNode']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitBeforeAdoptNode']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param NodeInterface $node
     * @param Context $context
     * @param $recursive
     * @\Neos\Flow\Annotations\Signal
     */
    protected function emitAfterAdoptNode(\Neos\ContentRepository\Domain\Model\NodeInterface $node, \Neos\ContentRepository\Domain\Service\Context $context, $recursive)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAfterAdoptNode'])) {
            $result = parent::emitAfterAdoptNode($node, $context, $recursive);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAfterAdoptNode'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
                $methodArguments['context'] = $context;
                $methodArguments['recursive'] = $recursive;
            
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\ContentRepository\Domain\Service\Context', 'emitAfterAdoptNode', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAfterAdoptNode']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAfterAdoptNode']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\ContentRepository\Domain\Service\Context', 'emitAfterAdoptNode', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAfterAdoptNode']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAfterAdoptNode']);
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
  'workspaceRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\WorkspaceRepository',
  'nodeDataRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\NodeDataRepository',
  'nodeFactory' => 'Neos\\ContentRepository\\Domain\\Factory\\NodeFactory',
  'contextFactory' => 'Neos\\ContentRepository\\Domain\\Service\\ContextFactoryInterface',
  'systemLogger' => 'Neos\\Flow\\Log\\PsrSystemLoggerInterface',
  'workspace' => 'Neos\\ContentRepository\\Domain\\Model\\Workspace',
  'workspaceName' => 'string',
  'currentDateTime' => '\\DateTime',
  'invisibleContentShown' => 'boolean',
  'removedContentShown' => 'boolean',
  'inaccessibleContentShown' => 'boolean',
  'dimensions' => 'array',
  'targetDimensions' => 'array',
  'firstLevelNodeCache' => 'Neos\\ContentRepository\\Domain\\Service\\Cache\\FirstLevelNodeCache',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'workspaceRepository', '9cacb5dd2ad57e06d6f8c82dd5707855', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\WorkspaceRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\NodeDataRepository', 'Neos\ContentRepository\Domain\Repository\NodeDataRepository', 'nodeDataRepository', '6d07985e92d364413ac81acd8f47b11b', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\NodeDataRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Factory\NodeFactory', 'Neos\ContentRepository\Domain\Factory\NodeFactory', 'nodeFactory', 'dd541629b8e42562866a1bf47375f14d', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Factory\NodeFactory'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\ContextFactoryInterface', 'Neos\Neos\Domain\Service\ContentContextFactory', 'contextFactory', '98dca7b1f95a25ec173662fc4e785341', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\ContextFactoryInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\PsrSystemLoggerInterface', '', 'systemLogger', '35a47897f04aa042c1ab70dc21ff014f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\PsrSystemLoggerInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'workspaceRepository',
  1 => 'nodeDataRepository',
  2 => 'nodeFactory',
  3 => 'contextFactory',
  4 => 'systemLogger',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.ContentRepository/Classes/Domain/Service/Context.php
#