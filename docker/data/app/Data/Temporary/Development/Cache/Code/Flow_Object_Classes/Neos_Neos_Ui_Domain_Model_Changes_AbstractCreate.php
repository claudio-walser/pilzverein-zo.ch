<?php 
namespace Neos\Neos\Ui\Domain\Model\Changes;

/*
 * This file is part of the Neos.Neos.Ui package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Service\NodeServiceInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Ui\Exception\InvalidNodeCreationHandlerException;
use Neos\Neos\Ui\NodeCreationHandler\NodeCreationHandlerInterface;

abstract class AbstractCreate_Original extends AbstractStructuralChange
{
    /**
     * The type of the node that will be created
     *
     * @var NodeType
     */
    protected $nodeType;

    /**
     * @var NodeTypeManager
     * @Flow\Inject
     */
    protected $nodeTypeManager;

    /**
     * Incoming data from creationDialog
     *
     * @var array
     */
    protected $data = [];

    /**
     * An (optional) name that will be used for the new node path
     *
     * @var string|null
     */
    protected $name = null;

    /**
     * @Flow\Inject
     * @var NodeServiceInterface
     */
    protected $nodeService;

    /**
     * Set the node type
     *
     * @param string $nodeType
     */
    public function setNodeType($nodeType)
    {
        if (is_string($nodeType)) {
            $nodeType = $this->nodeTypeManager->getNodeType($nodeType);
        }

        if (!$nodeType instanceof NodeType) {
            throw new \InvalidArgumentException('nodeType needs to be of type string or NodeType', 1452100970);
        }

        $this->nodeType = $nodeType;
    }

    /**
     * Get the node type
     *
     * @return NodeType
     */
    public function getNodeType()
    {
        return $this->nodeType;
    }

    /**
     * Set the data
     *
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get the name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Creates a new node beneath $parent
     *
     * @param  NodeInterface $parent
     * @return NodeInterface
     */
    protected function createNode(NodeInterface $parent)
    {
        $nodeType = $this->getNodeType();
        $name = $this->getName() ?: $this->nodeService->generateUniqueNodeName($parent->getPath());

        $node = $parent->createNode($name, $nodeType);

        $this->applyNodeCreationHandlers($node);

        $this->finish($node);
        // NOTE: we need to run "finish" before "addNodeCreatedFeedback" to ensure the new node already exists when the last feedback is processed
        $this->addNodeCreatedFeedback($node);

        return $node;
    }

    /**
     * Apply nodeCreationHandlers
     *
     * @param NodeInterface $node
     * @throws InvalidNodeCreationHandlerException
     * @return void
     */
    protected function applyNodeCreationHandlers(NodeInterface $node)
    {
        $data = $this->getData() ?: [];
        $nodeType = $node->getNodeType();
        if (isset($nodeType->getOptions()['nodeCreationHandlers'])) {
            $nodeCreationHandlers = $nodeType->getOptions()['nodeCreationHandlers'];
            if (is_array($nodeCreationHandlers)) {
                foreach ($nodeCreationHandlers as $nodeCreationHandlerConfiguration) {
                    $nodeCreationHandler = new $nodeCreationHandlerConfiguration['nodeCreationHandler']();
                    if (!$nodeCreationHandler instanceof NodeCreationHandlerInterface) {
                        throw new InvalidNodeCreationHandlerException(sprintf('Expected NodeCreationHandlerInterface but got "%s"', get_class($nodeCreationHandler)), 1364759956);
                    }
                    $nodeCreationHandler->handle($node, $data);
                }
            }
        }

        $this->emitNodeCreationHandlersApplied($node);
    }

    /**
     * Signals, that all changes by node creation handlers are applied
     *
     * @Flow\Signal
     *
     * @param NodeInterface $node The node, the node creation handlers are applied to
     * @return void
     */
    public function emitNodeCreationHandlersApplied(NodeInterface $node)
    {
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Ui\Domain\Model\Changes;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 */
abstract class AbstractCreate extends AbstractCreate_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\Aop\AdvicesTrait;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
    }

    /**
     * Autogenerated Proxy Method
     */
    protected function Flow_Aop_Proxy_buildMethodsAndAdvicesArray()
    {
        if (method_exists(get_parent_class(), 'Flow_Aop_Proxy_buildMethodsAndAdvicesArray') && is_callable('parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray')) parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        $objectManager = \Neos\Flow\Core\Bootstrap::$staticObjectManager;
        $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array(
            'emitNodeCreationHandlersApplied' => array(
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
     * @param NodeInterface $node The node, the node creation handlers are applied to
     * @return void
     * @\Neos\Flow\Annotations\Signal
     */
    public function emitNodeCreationHandlersApplied(\Neos\ContentRepository\Domain\Model\NodeInterface $node)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitNodeCreationHandlersApplied'])) {
            $result = parent::emitNodeCreationHandlersApplied($node);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitNodeCreationHandlersApplied'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
            
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Domain\Model\Changes\AbstractCreate', 'emitNodeCreationHandlersApplied', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitNodeCreationHandlersApplied']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitNodeCreationHandlersApplied']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Domain\Model\Changes\AbstractCreate', 'emitNodeCreationHandlersApplied', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitNodeCreationHandlersApplied']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitNodeCreationHandlersApplied']);
        }
        return $result;
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos.Ui/Classes/Domain/Model/Changes/AbstractCreate.php
#