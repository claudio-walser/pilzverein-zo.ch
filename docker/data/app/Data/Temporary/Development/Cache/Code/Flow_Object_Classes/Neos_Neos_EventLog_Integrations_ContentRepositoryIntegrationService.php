<?php 
namespace Neos\Neos\EventLog\Integrations;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Neos\EventLog\Domain\Model\NodeEvent;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\Workspace;
use Neos\ContentRepository\Domain\Service\Context;

/**
 * Monitors Neos.ContentRepository changes
 *
 * @Flow\Scope("singleton")
 */
class ContentRepositoryIntegrationService_Original extends AbstractIntegrationService
{
    const NODE_ADDED = 'Node.Added';
    const NODE_UPDATED = 'Node.Updated';
    const NODE_LABEL_CHANGED = 'Node.LabelChanged';
    const NODE_REMOVED = 'Node.Removed';
    const DOCUMENT_PUBLISHED = 'Node.Published';
    const NODE_COPY = 'Node.Copy';
    const NODE_MOVE = 'Node.Move';
    const NODE_ADOPT = 'Node.Adopt';

    /**
     * @Flow\Inject
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var array
     */
    protected $changedNodes = [];

    /**
     * @var array
     */
    protected $currentNodeAddEvents = [];

    /**
     * @var boolean
     */
    protected $currentlyCopying = false;

    /**
     * @var boolean
     */
    protected $currentlyMoving = 0;

    /**
     * @var integer
     */
    protected $currentlyAdopting = 0;

    /**
     * @var array
     */
    protected $scheduledNodeEventUpdates = [];

    /**
     * React on the Doctrine preFlush event and trigger the respective internal node events
     *
     * @return void
     */
    public function preFlush()
    {
        $this->generateNodeEvents();
    }

    /**
     * Emit a "Node Added" event
     *
     * @return void
     */
    public function beforeNodeCreate()
    {
        if (!$this->eventEmittingService->isEnabled()) {
            return;
        }

        /* @var $nodeEvent NodeEvent */
        $nodeEvent = $this->eventEmittingService->generate(self::NODE_ADDED, [], NodeEvent::class);
        $this->currentNodeAddEvents[] = $nodeEvent;
        $this->eventEmittingService->pushContext($nodeEvent);
    }

    /**
     * Add the created node to the previously created "Added Node" event
     *
     * @param NodeInterface $node
     * @return void
     */
    public function afterNodeCreate(NodeInterface $node)
    {
        if (!$this->eventEmittingService->isEnabled()) {
            return;
        }

        /* @var $nodeEvent NodeEvent */
        $nodeEvent = array_pop($this->currentNodeAddEvents);
        $nodeEvent->setNode($node);
        $this->eventEmittingService->popContext();
        $this->eventEmittingService->add($nodeEvent);
    }

    /**
     * Emit a "Node Updated" event
     *
     * @param NodeInterface $node
     * @return void
     */
    public function nodeUpdated(NodeInterface $node)
    {
        if (!$this->eventEmittingService->isEnabled()) {
            return;
        }

        if (!isset($this->changedNodes[$node->getContextPath()])) {
            $this->changedNodes[$node->getContextPath()] = ['node' => $node];
        }
    }

    /**
     * Emit an event when node properties have been changed
     *
     * @param NodeInterface $node
     * @param $propertyName
     * @param $oldValue
     * @param $value
     * @return void
     */
    public function beforeNodePropertyChange(NodeInterface $node, $propertyName, $oldValue, $value)
    {
        if (!$this->eventEmittingService->isEnabled()) {
            return;
        }

        if (count($this->currentNodeAddEvents) > 0) {
            // add is currently running, during that; we do not want any update events
            return;
        }
        if ($oldValue === $value) {
            return;
        }
        if (!isset($this->changedNodes[$node->getContextPath()])) {
            $this->changedNodes[$node->getContextPath()] = ['node' => $node];
        }
        if (!isset($this->changedNodes[$node->getContextPath()]['oldLabel'])) {
            $this->changedNodes[$node->getContextPath()]['oldLabel'] = $node->getLabel();
        }

        $this->changedNodes[$node->getContextPath()]['old'][$propertyName] = $oldValue;
        $this->changedNodes[$node->getContextPath()]['new'][$propertyName] = $value;
    }

    /**
     * Add the new label to a previously created node property changed event
     *
     * @param NodeInterface $node
     * @param $propertyName
     * @param $oldValue
     * @param $value
     * @return void
     */
    public function nodePropertyChanged(NodeInterface $node, $propertyName, $oldValue, $value)
    {
        if (!$this->eventEmittingService->isEnabled()) {
            return;
        }

        if ($oldValue === $value) {
            return;
        }

        $this->changedNodes[$node->getContextPath()]['newLabel'] = $node->getLabel();
        $this->changedNodes[$node->getContextPath()]['node'] = $node;
    }

    /**
     * Emits a "Node Removed" event
     *
     * @param NodeInterface $node
     * @return void
     */
    public function nodeRemoved(NodeInterface $node)
    {
        if (!$this->eventEmittingService->isEnabled()) {
            return;
        }

        /* @var $nodeEvent NodeEvent */
        $nodeEvent = $this->eventEmittingService->emit(self::NODE_REMOVED, [], NodeEvent::class);
        $nodeEvent->setNode($node);
    }

    /**
     * @param NodeInterface $node
     * @param Workspace $targetWorkspace
     * @return void
     */
    public function beforeNodePublishing(NodeInterface $node, Workspace $targetWorkspace)
    {
    }

    /**
     * Emits a "Node Copy" event
     *
     * @param NodeInterface $sourceNode
     * @param NodeInterface $targetParentNode
     * @return void
     * @throws \Exception
     */
    public function beforeNodeCopy(NodeInterface $sourceNode, NodeInterface $targetParentNode)
    {
        if (!$this->eventEmittingService->isEnabled()) {
            return;
        }

        if ($this->currentlyCopying) {
            throw new \Exception('TODO: already copying...');
        }

        $this->currentlyCopying = true;

        /* @var $nodeEvent NodeEvent */
        $nodeEvent = $this->eventEmittingService->emit(self::NODE_COPY, [
            'copiedInto' => $targetParentNode->getContextPath()
        ], NodeEvent::class);
        $nodeEvent->setNode($sourceNode);
        $this->eventEmittingService->pushContext();
    }

    /**
     * @param NodeInterface $copiedNode
     * @param NodeInterface $targetParentNode
     * @return void
     * @throws \Exception
     */
    public function afterNodeCopy(NodeInterface $copiedNode, NodeInterface $targetParentNode)
    {
        if (!$this->eventEmittingService->isEnabled()) {
            return;
        }

        if ($this->currentlyCopying === false) {
            throw new \Exception('TODO: copying not started');
        }
        $this->currentlyCopying = false;
        $this->eventEmittingService->popContext();
    }

    /**
     * Emits a "Node Move" event
     *
     * @param NodeInterface $movedNode
     * @param NodeInterface $referenceNode
     * @param integer $moveOperation
     */
    public function beforeNodeMove(NodeInterface $movedNode, NodeInterface $referenceNode, $moveOperation)
    {
        if (!$this->eventEmittingService->isEnabled()) {
            return;
        }

        $this->currentlyMoving += 1;

        /* @var $nodeEvent NodeEvent */
        $nodeEvent = $this->eventEmittingService->emit(self::NODE_MOVE, [
            'referenceNode' => $referenceNode->getContextPath(),
            'moveOperation' => $moveOperation
        ], NodeEvent::class);
        $nodeEvent->setNode($movedNode);
        $this->eventEmittingService->pushContext();
    }

    /**
     * @param NodeInterface $movedNode
     * @param NodeInterface $referenceNode
     * @param integer $moveOperation
     * @return void
     * @throws \Exception
     */
    public function afterNodeMove(NodeInterface $movedNode, NodeInterface $referenceNode, $moveOperation)
    {
        if (!$this->eventEmittingService->isEnabled()) {
            return;
        }

        if ($this->currentlyMoving === 0) {
            throw new \Exception('TODO: moving not started');
        }

        $this->currentlyMoving -= 1;
        $this->eventEmittingService->popContext();
    }

    /**
     * Emits a "Node Adopt" event
     *
     * @param NodeInterface $node
     * @param Context $context
     * @param $recursive
     * @return void
     */
    public function beforeAdoptNode(NodeInterface $node, Context $context, $recursive)
    {
        if (!$this->eventEmittingService->isEnabled()) {
            return;
        }

        if ($this->currentlyAdopting === 0) {
            /* @var $nodeEvent NodeEvent */
            $nodeEvent = $this->eventEmittingService->emit(self::NODE_ADOPT, [
                'targetWorkspace' => $context->getWorkspaceName(),
                'targetDimensions' => $context->getTargetDimensions(),
                'recursive' => $recursive
            ], NodeEvent::class);
            $nodeEvent->setNode($node);
            $this->eventEmittingService->pushContext();
        }

        $this->currentlyAdopting++;
    }

    /**
     * @param NodeInterface $node
     * @param Context $context
     * @param $recursive
     * @return void
     */
    public function afterAdoptNode(NodeInterface $node, Context $context, $recursive)
    {
        if (!$this->eventEmittingService->isEnabled()) {
            return;
        }

        $this->currentlyAdopting--;
        if ($this->currentlyAdopting === 0) {
            $this->eventEmittingService->popContext();
        }
    }

    /**
     * @return void
     */
    public function generateNodeEvents()
    {
        if (!$this->eventEmittingService->isEnabled()) {
            return;
        }

        if (count($this->currentNodeAddEvents) > 0) {
            return;
        }

        foreach ($this->changedNodes as $nodePath => $data) {
            $node = $data['node'];
            unset($data['node']);
            /* @var $nodeEvent NodeEvent */

            if (isset($data['oldLabel']) && isset($data['newLabel'])) {
                if ($data['oldLabel'] !== $data['newLabel']) {
                    $nodeEvent = $this->eventEmittingService->emit(self::NODE_LABEL_CHANGED, ['oldLabel' => $data['oldLabel'], 'newLabel' => $data['newLabel']], NodeEvent::class);
                    $nodeEvent->setNode($node);
                }
                unset($data['oldLabel']);
                unset($data['newLabel']);
            }

            if (!empty($data)) {
                $nodeEvent = $this->eventEmittingService->emit(self::NODE_UPDATED, $data, NodeEvent::class);
                $nodeEvent->setNode($node);
            }
        }

        $this->changedNodes = [];
    }

    /**
     * @param NodeInterface $node
     * @param Workspace $targetWorkspace
     * @return void
     */
    public function afterNodePublishing(NodeInterface $node, Workspace $targetWorkspace)
    {
        if (!$this->eventEmittingService->isEnabled()) {
            return;
        }

        $documentNode = NodeEvent::getClosestAggregateNode($node);

        if ($documentNode === null) {
            return;
        }

        $this->scheduledNodeEventUpdates[$documentNode->getContextPath()] = [
            'workspaceName' => $node->getContext()->getWorkspaceName(),
            'nestedNodeIdentifiersWhichArePublished' => [],
            'targetWorkspace' => $targetWorkspace->getName(),
            'documentNode' => $documentNode
        ];

        $this->scheduledNodeEventUpdates[$documentNode->getContextPath()]['nestedNodeIdentifiersWhichArePublished'][] = $node->getIdentifier();
    }

    /**
     * Binds events to a Node.Published event for each document node published
     *
     * @return void
     */
    public function updateEventsAfterPublish()
    {
        if (!$this->eventEmittingService->isEnabled()) {
            return;
        }

        /** @var $entityManager EntityManager */
        $entityManager = $this->entityManager;

        foreach ($this->scheduledNodeEventUpdates as $documentPublish) {
            /* @var $nodeEvent NodeEvent */
            $nodeEvent = $this->eventEmittingService->emit(self::DOCUMENT_PUBLISHED, [], NodeEvent::class);
            $nodeEvent->setNode($documentPublish['documentNode']);
            $nodeEvent->setWorkspaceName($documentPublish['targetWorkspace']);
            $this->persistenceManager->whitelistObject($nodeEvent);
            $this->persistenceManager->persistAll(true);

            $parentEventIdentifier = $this->persistenceManager->getIdentifierByObject($nodeEvent);

            $qb = $entityManager->createQueryBuilder();
            $qb->update(NodeEvent::class, 'e')
                ->set('e.parentEvent', ':parentEventIdentifier')
                ->setParameter('parentEventIdentifier', $parentEventIdentifier)
                ->where('e.parentEvent IS NULL')
                ->andWhere('e.workspaceName = :workspaceName')
                ->setParameter('workspaceName', $documentPublish['workspaceName'])
                ->andWhere('e.documentNodeIdentifier = :documentNodeIdentifier')
                ->setParameter('documentNodeIdentifier', $documentPublish['documentNode']->getIdentifier())
                ->andWhere('e.eventType != :publishedEventType')
                ->setParameter('publishedEventType', self::DOCUMENT_PUBLISHED)
                ->getQuery()->execute();
        }

        $this->scheduledNodeEventUpdates = [];
    }

    /**
     * @return void
     */
    public function reset()
    {
        $this->changedNodes = [];
        $this->scheduledNodeEventUpdates = [];
        $this->currentlyAdopting = false;
        $this->currentlyCopying = false;
        $this->currentNodeAddEvents = [];
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\EventLog\Integrations;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Monitors Neos.ContentRepository changes
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class ContentRepositoryIntegrationService extends ContentRepositoryIntegrationService_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Neos\EventLog\Integrations\ContentRepositoryIntegrationService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\EventLog\Integrations\ContentRepositoryIntegrationService', $this);
        if ('Neos\Neos\EventLog\Integrations\ContentRepositoryIntegrationService' === get_class($this)) {
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
  'entityManager' => 'Doctrine\\ORM\\EntityManagerInterface',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'changedNodes' => 'array',
  'currentNodeAddEvents' => 'array',
  'currentlyCopying' => 'boolean',
  'currentlyMoving' => 'boolean',
  'currentlyAdopting' => 'integer',
  'scheduledNodeEventUpdates' => 'array',
  'eventEmittingService' => 'Neos\\Neos\\EventLog\\Domain\\Service\\EventEmittingService',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Neos\EventLog\Integrations\ContentRepositoryIntegrationService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\EventLog\Integrations\ContentRepositoryIntegrationService', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Doctrine\ORM\EntityManagerInterface', 'Doctrine\ORM\EntityManagerInterface', 'entityManager', '68dcc38bb5d1acad752c62baff04cd05', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Doctrine\ORM\EntityManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\EventLog\Domain\Service\EventEmittingService', 'Neos\Neos\EventLog\Domain\Service\EventEmittingService', 'eventEmittingService', '5c51fbaaf43008ad76d5e80e333d9fcb', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\EventLog\Domain\Service\EventEmittingService'); });
        $this->Flow_Injected_Properties = array (
  0 => 'entityManager',
  1 => 'persistenceManager',
  2 => 'eventEmittingService',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/EventLog/Integrations/ContentRepositoryIntegrationService.php
#