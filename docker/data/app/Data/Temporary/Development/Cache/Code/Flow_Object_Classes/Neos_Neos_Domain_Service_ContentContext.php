<?php 
namespace Neos\Neos\Domain\Service;

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
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Flow\Security\Authorization\PrivilegeManagerInterface;
use Neos\Flow\Security\Exception;
use Neos\Neos\Domain\Model\Domain;
use Neos\Neos\Domain\Model\UserInterfaceMode;
use Neos\Neos\Domain\Model\Site;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\Context;
use Neos\ContentRepository\Domain\Utility\NodePaths;

/**
 * The Content Context
 *
 * @Flow\Scope("prototype")
 * @api
 */
class ContentContext_Original extends Context
{
    /**
     * @var Site
     */
    protected $currentSite;

    /**
     * @var Domain
     */
    protected $currentDomain;

    /**
     * @var NodeInterface
     */
    protected $currentSiteNode;

    /**
     * @Flow\Inject
     * @var PrivilegeManagerInterface
     */
    protected $privilegeManager;

    /**
     * @Flow\Inject
     * @var UserInterfaceModeService
     */
    protected $interfaceRenderModeService;

    /**
     * Creates a new Content Context object.
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
     * @param Site $currentSite The current Site object
     * @param Domain $currentDomain The current Domain object
     * @see ContextFactoryInterface
     */
    public function __construct($workspaceName, \DateTimeInterface $currentDateTime, array $dimensions, array $targetDimensions, $invisibleContentShown, $removedContentShown, $inaccessibleContentShown, Site $currentSite = null, Domain $currentDomain = null)
    {
        parent::__construct($workspaceName, $currentDateTime, $dimensions, $targetDimensions, $invisibleContentShown, $removedContentShown, $inaccessibleContentShown);
        $this->currentSite = $currentSite;
        $this->currentDomain = $currentDomain;
        $this->targetDimensions = $targetDimensions;
    }

    /**
     * Returns the current site from this frontend context
     *
     * @return Site The current site
     */
    public function getCurrentSite()
    {
        return $this->currentSite;
    }

    /**
     * Returns the current domain from this frontend context
     *
     * @return Domain The current domain
     * @api
     */
    public function getCurrentDomain()
    {
        return $this->currentDomain;
    }

    /**
     * Returns the node of the current site.
     *
     * @return NodeInterface
     */
    public function getCurrentSiteNode()
    {
        if ($this->currentSite !== null && $this->currentSiteNode === null) {
            $siteNodePath = NodePaths::addNodePathSegment(SiteService::SITES_ROOT_PATH, $this->currentSite->getNodeName());
            $this->currentSiteNode = $this->getNode($siteNodePath);
            if (!($this->currentSiteNode instanceof NodeInterface)) {
                $this->systemLogger->warning(sprintf('Couldn\'t load the site node for path "%s" in workspace "%s". This is probably due to a missing baseworkspace for the workspace of the current user.', $siteNodePath, $this->workspaceName), LogEnvironment::fromMethodName(__METHOD__));
            }
        }
        return $this->currentSiteNode;
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
            'inaccessibleContentShown' => $this->inaccessibleContentShown,
            'currentSite' => $this->currentSite,
            'currentDomain' => $this->currentDomain
        ];
    }

    /**
     * Returns true if current context is live workspace, false otherwise
     *
     * @return bool
     * @throws IllegalObjectTypeException
     */
    public function isLive(): bool
    {
        return ($this->getWorkspace()->getBaseWorkspace() === null);
    }

    /**
     * Returns true while rendering backend (not live workspace and access to backend granted), false otherwise
     *
     * @return boolean
     * @throws IllegalObjectTypeException
     */
    public function isInBackend(): bool
    {
        return (!$this->isLive() && $this->hasAccessToBackend());
    }

    /**
     * @return UserInterfaceMode
     */
    public function getCurrentRenderingMode(): UserInterfaceMode
    {
        return $this->interfaceRenderModeService->findModeByCurrentUser();
    }

    /**
     * Is access to the neos backend granted by current authentications.
     *
     * @return bool
     */
    protected function hasAccessToBackend(): bool
    {
        try {
            return $this->privilegeManager->isPrivilegeTargetGranted('Neos.Neos:Backend.GeneralAccess');
        } catch (Exception $exception) {
            return false;
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Domain\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * The Content Context
 * @\Neos\Flow\Annotations\Scope("prototype")
 */
class ContentContext extends ContentContext_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
     * @param Site $currentSite The current Site object
     * @param Domain $currentDomain The current Domain object
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
        if ('Neos\Neos\Domain\Service\ContentContext' === get_class($this)) {
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
            
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Domain\Service\ContentContext', 'emitBeforeAdoptNode', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitBeforeAdoptNode']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitBeforeAdoptNode']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Domain\Service\ContentContext', 'emitBeforeAdoptNode', $methodArguments, NULL, $result);
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
            
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Domain\Service\ContentContext', 'emitAfterAdoptNode', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAfterAdoptNode']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAfterAdoptNode']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Domain\Service\ContentContext', 'emitAfterAdoptNode', $methodArguments, NULL, $result);
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
  'currentSite' => 'Neos\\Neos\\Domain\\Model\\Site',
  'currentDomain' => 'Neos\\Neos\\Domain\\Model\\Domain',
  'currentSiteNode' => 'Neos\\ContentRepository\\Domain\\Model\\NodeInterface',
  'privilegeManager' => 'Neos\\Flow\\Security\\Authorization\\PrivilegeManagerInterface',
  'interfaceRenderModeService' => 'Neos\\Neos\\Domain\\Service\\UserInterfaceModeService',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Authorization\PrivilegeManagerInterface', 'Neos\Flow\Security\Authorization\PrivilegeManager', 'privilegeManager', '68ada25ea2828278e185a684d1c86739', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Authorization\PrivilegeManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Service\UserInterfaceModeService', 'Neos\Neos\Domain\Service\UserInterfaceModeService', 'interfaceRenderModeService', 'd506ed8bff86306e76666d08f297140a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Service\UserInterfaceModeService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'workspaceRepository', '9cacb5dd2ad57e06d6f8c82dd5707855', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\WorkspaceRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\NodeDataRepository', 'Neos\ContentRepository\Domain\Repository\NodeDataRepository', 'nodeDataRepository', '6d07985e92d364413ac81acd8f47b11b', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\NodeDataRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Factory\NodeFactory', 'Neos\ContentRepository\Domain\Factory\NodeFactory', 'nodeFactory', 'dd541629b8e42562866a1bf47375f14d', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Factory\NodeFactory'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\ContextFactoryInterface', 'Neos\Neos\Domain\Service\ContentContextFactory', 'contextFactory', '98dca7b1f95a25ec173662fc4e785341', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\ContextFactoryInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\PsrSystemLoggerInterface', '', 'systemLogger', '35a47897f04aa042c1ab70dc21ff014f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\PsrSystemLoggerInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'privilegeManager',
  1 => 'interfaceRenderModeService',
  2 => 'workspaceRepository',
  3 => 'nodeDataRepository',
  4 => 'nodeFactory',
  5 => 'contextFactory',
  6 => 'systemLogger',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Domain/Service/ContentContext.php
#