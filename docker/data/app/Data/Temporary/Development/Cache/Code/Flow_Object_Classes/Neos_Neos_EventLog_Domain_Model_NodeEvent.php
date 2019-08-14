<?php 
namespace Neos\Neos\EventLog\Domain\Model;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Utility\Arrays;
use Neos\Neos\Domain\Repository\SiteRepository;
use Neos\Neos\Domain\Service\ContentContext;
use Neos\Neos\Service\UserService;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;

/**
 * A specific event which is used for ContentRepository Nodes (i.e. content).
 *
 * @Flow\Entity
 *
 * The following annotation is not picked up by Doctrine migrations and thus included in the Event class as well.
 * See https://github.com/doctrine/doctrine2/issues/6248
 *
 * @ORM\Table(
 *    indexes={
 *      @ORM\Index(name="documentnodeidentifier", columns={"documentnodeidentifier"})
 *    }
 * )
 */
class NodeEvent_Original extends Event
{
    /**
     * the node identifier which was created/modified/...
     *
     * @var string
     */
    protected $nodeIdentifier;

    /**
     * the document node identifier on which the action took place. is equal to NodeIdentifier if the action happened on documentNodes
     *
     * @var string
     */
    protected $documentNodeIdentifier;

    /**
     * the workspace name where the action took place
     *
     * @var string
     */
    protected $workspaceName;

    /**
     * the dimension values for that event
     *
     * @var array
     */
    protected $dimension;

    /**
     * @Flow\Inject
     * @var UserService
     */
    protected $userService;

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var SiteRepository
     */
    protected $siteRepository;

    /**
     * Return name of the workspace where the node event happened
     *
     * @return string
     */
    public function getWorkspaceName()
    {
        return $this->workspaceName;
    }

    /**
     * @return bool
     */
    public function isDocumentEvent()
    {
        return $this->documentNodeIdentifier === $this->nodeIdentifier;
    }

    /**
     * Return the node identifier of the closest parent document node related to this event
     *
     * @return string
     */
    public function getDocumentNodeIdentifier()
    {
        return $this->documentNodeIdentifier;
    }

    /**
     * Return the node identifier of the node this event relates to
     *
     * @return string
     */
    public function getNodeIdentifier()
    {
        return $this->nodeIdentifier;
    }

    /**
     * Set the "context node" this operation was working on.
     *
     * @param NodeInterface $node
     * @return void
     */
    public function setNode(NodeInterface $node)
    {
        $this->nodeIdentifier = $node->getIdentifier();
        $this->workspaceName = $node->getContext()->getWorkspaceName();
        $this->dimension = $node->getContext()->getDimensions();

        $context = $node->getContext();
        if ($context instanceof ContentContext && $context->getCurrentSite() !== null) {
            $siteIdentifier = $this->persistenceManager->getIdentifierByObject($context->getCurrentSite());
        } else {
            $siteIdentifier = null;
        }
        $this->data = Arrays::arrayMergeRecursiveOverrule($this->data, [
            'nodeContextPath' => $node->getContextPath(),
            'nodeLabel' => $node->getLabel(),
            'nodeType' => $node->getNodeType()->getName(),
            'site' => $siteIdentifier
        ]);

        $node = self::getClosestAggregateNode($node);

        if ($node !== null) {
            $this->documentNodeIdentifier = $node->getIdentifier();
            $this->data = Arrays::arrayMergeRecursiveOverrule($this->data, [
                'documentNodeContextPath' => $node->getContextPath(),
                'documentNodeLabel' => $node->getLabel(),
                'documentNodeType' => $node->getNodeType()->getName()
            ]);
        }
    }

    /**
     * Override the workspace name. *MUST* be called after setNode(), else it won't have an effect.
     *
     * @param string $workspaceName
     * @return void
     */
    public function setWorkspaceName($workspaceName)
    {
        $this->workspaceName = $workspaceName;
    }

    /**
     * Returns the closest aggregate node of the given node
     *
     * @param NodeInterface $node
     * @return NodeInterface
     */
    public static function getClosestAggregateNode(NodeInterface $node)
    {
        while ($node !== null && !$node->getNodeType()->isAggregate()) {
            $node = $node->getParent();
        }
        return $node;
    }

    /**
     * Returns the closest document node, if it can be resolved.
     *
     * It might happen that, if this event refers to a node contained in a site which is not available anymore,
     * Doctrine's proxy class of the Site domain model will fail with an EntityNotFoundException. We catch this
     * case and return NULL.
     *
     * @return NodeInterface
     */
    public function getDocumentNode()
    {
        try {
            $context = $this->contextFactory->create([
                'workspaceName' => $this->userService->getPersonalWorkspaceName(),
                'dimensions' => $this->dimension,
                'currentSite' => $this->getCurrentSite(),
                'invisibleContentShown' => true
            ]);
            return $context->getNodeByIdentifier($this->documentNodeIdentifier);
        } catch (EntityNotFoundException $e) {
            return null;
        }
    }

    /**
     * Returns the node this even refers to, if it can be resolved.
     *
     * It might happen that, if this event refers to a node contained in a site which is not available anymore,
     * Doctrine's proxy class of the Site domain model will fail with an EntityNotFoundException. We catch this
     * case and return NULL.
     *
     * @return NodeInterface
     */
    public function getNode()
    {
        try {
            $context = $this->contextFactory->create([
                'workspaceName' => $this->userService->getPersonalWorkspaceName(),
                'dimensions' => $this->dimension,
                'currentSite' => $this->getCurrentSite(),
                'invisibleContentShown' => true
            ]);
            return $context->getNodeByIdentifier($this->nodeIdentifier);
        } catch (EntityNotFoundException $e) {
            return null;
        }
    }

    /**
     * Prevents invalid calls to the site repository in case the site data property is not available.
     *
     * @return null|object
     */
    protected function getCurrentSite()
    {
        if (!isset($this->data['site']) || $this->data['site'] === null) {
            return null;
        }

        return $this->siteRepository->findByIdentifier($this->data['site']);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('NodeEvent[%s, %s]', $this->eventType, $this->nodeIdentifier);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\EventLog\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A specific event which is used for ContentRepository Nodes (i.e. content).
 * @\Neos\Flow\Annotations\Entity
 * @\Doctrine\ORM\Mapping\Table(indexes={ @\Doctrine\ORM\Mapping\Index(name="documentnodeidentifier", columns={ "documentnodeidentifier" }) })
 */
class NodeEvent extends NodeEvent_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface, \Neos\Flow\Persistence\Aspect\PersistenceMagicInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     * @param string $eventType
     * @param array $data
     * @param string $user
     * @param Event $parentEvent
     */
    public function __construct()
    {
        $arguments = func_get_args();

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $eventType in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $data in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Neos\EventLog\Domain\Model\NodeEvent' === get_class($this)) {
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
            '__clone' => array(
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', 'cloneObject', $objectManager, NULL),
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

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone'])) {
            $result = NULL;

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone'] = true;
            try {
            
                $methodArguments = [];

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\EventLog\Domain\Model\NodeEvent', '__clone', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\EventLog\Domain\Model\NodeEvent', '__clone', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone']);
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
  'nodeIdentifier' => 'string',
  'documentNodeIdentifier' => 'string',
  'workspaceName' => 'string',
  'dimension' => 'array',
  'userService' => 'Neos\\Neos\\Service\\UserService',
  'contextFactory' => 'Neos\\ContentRepository\\Domain\\Service\\ContextFactoryInterface',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'siteRepository' => 'Neos\\Neos\\Domain\\Repository\\SiteRepository',
  'timestamp' => '\\DateTime',
  'uid' => 'integer',
  'eventType' => 'string',
  'accountIdentifier' => 'string',
  'data' => 'array',
  'parentEvent' => 'Neos\\Neos\\EventLog\\Domain\\Model\\Event',
  'childEvents' => 'Doctrine\\Common\\Collections\\ArrayCollection<Neos\\Neos\\EventLog\\Domain\\Model\\Event>',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\UserService', 'Neos\Neos\Service\UserService', 'userService', '3af75a289d0337400c3d43d557f82c49', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\UserService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\ContextFactoryInterface', 'Neos\Neos\Domain\Service\ContentContextFactory', 'contextFactory', '98dca7b1f95a25ec173662fc4e785341', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\ContextFactoryInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\SiteRepository', 'Neos\Neos\Domain\Repository\SiteRepository', 'siteRepository', '42785f5eca4dff104f1860b84f531a9f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\SiteRepository'); });
        $this->Flow_Injected_Properties = array (
  0 => 'userService',
  1 => 'contextFactory',
  2 => 'persistenceManager',
  3 => 'siteRepository',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/EventLog/Domain/Model/NodeEvent.php
#