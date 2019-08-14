<?php 
namespace Neos\Neos\Ui\Service;

/*
 * This file is part of the Neos.Neos.Ui package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeInterface;

/**
 * This is a container for clipboard state that needs to be persisted server side
 *
 * @Flow\Scope("session")
 */
class NodeClipboard_Original
{
    const MODE_COPY = 'Copy';
    const MODE_MOVE = 'Move';

    /**
     * @var string
     */
    protected $nodeContextPath = '';

    /**
     * @var string one of the NodeClipboard::MODE_*  constants
     */
    protected $mode = '';

    /**
     * Save copied node to clipboard.
     *
     * @param NodeInterface $node
     * @return void
     * @Flow\Session(autoStart=true)
     */
    public function copyNode(NodeInterface $node)
    {
        $this->nodeContextPath = $node->getContextPath();
        $this->mode = self::MODE_COPY;
    }

    /**
     * Save cut node to clipboard.
     *
     * @param NodeInterface $node
     * @return void
     * @Flow\Session(autoStart=true)
     */
    public function cutNode(NodeInterface $node)
    {
        $this->nodeContextPath = $node->getContextPath();
        $this->mode = self::MODE_MOVE;
    }

    /**
     * Reset clipboard.
     *
     * @return void
     * @Flow\Session(autoStart=true)
     */
    public function clear()
    {
        $this->nodeContextPath = '';
        $this->mode = '';
    }

    /**
     * Get clipboard node.
     *
     * @return string $nodeContextPath
     */
    public function getNodeContextPath()
    {
        return $this->nodeContextPath ? $this->nodeContextPath : '';
    }

    /**
     * Get clipboard mode.
     *
     * @return string $mode
     */
    public function getMode()
    {
        return $this->mode;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Ui\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * This is a container for clipboard state that needs to be persisted server side
 * @\Neos\Flow\Annotations\Scope("session")
 */
class NodeClipboard extends NodeClipboard_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        if (get_class($this) === 'Neos\Neos\Ui\Service\NodeClipboard') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Ui\Service\NodeClipboard', $this);
    }

    /**
     * Autogenerated Proxy Method
     */
    protected function Flow_Aop_Proxy_buildMethodsAndAdvicesArray()
    {
        if (method_exists(get_parent_class(), 'Flow_Aop_Proxy_buildMethodsAndAdvicesArray') && is_callable('parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray')) parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        $objectManager = \Neos\Flow\Core\Bootstrap::$staticObjectManager;
        $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array(
            'copyNode' => array(
                'Neos\Flow\Aop\Advice\BeforeAdvice' => array(
                    new \Neos\Flow\Aop\Advice\BeforeAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'initializeSession', $objectManager, NULL),
                ),
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'cutNode' => array(
                'Neos\Flow\Aop\Advice\BeforeAdvice' => array(
                    new \Neos\Flow\Aop\Advice\BeforeAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'initializeSession', $objectManager, NULL),
                ),
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'clear' => array(
                'Neos\Flow\Aop\Advice\BeforeAdvice' => array(
                    new \Neos\Flow\Aop\Advice\BeforeAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'initializeSession', $objectManager, NULL),
                ),
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'getNodeContextPath' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'getMode' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
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
        if (get_class($this) === 'Neos\Neos\Ui\Service\NodeClipboard') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Ui\Service\NodeClipboard', $this);

        $this->Flow_setRelatedEntities();
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
     * @return void
     * @\Neos\Flow\Annotations\Session(autoStart=true)
     */
    public function copyNode(\Neos\ContentRepository\Domain\Model\NodeInterface $node)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['copyNode'])) {
            $result = parent::copyNode($node);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['copyNode'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
            
                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['copyNode']['Neos\Flow\Aop\Advice\BeforeAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['copyNode']['Neos\Flow\Aop\Advice\BeforeAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Service\NodeClipboard', 'copyNode', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('copyNode');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Service\NodeClipboard', 'copyNode', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['copyNode']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['copyNode']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param NodeInterface $node
     * @return void
     * @\Neos\Flow\Annotations\Session(autoStart=true)
     */
    public function cutNode(\Neos\ContentRepository\Domain\Model\NodeInterface $node)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['cutNode'])) {
            $result = parent::cutNode($node);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['cutNode'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['node'] = $node;
            
                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['cutNode']['Neos\Flow\Aop\Advice\BeforeAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['cutNode']['Neos\Flow\Aop\Advice\BeforeAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Service\NodeClipboard', 'cutNode', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('cutNode');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Service\NodeClipboard', 'cutNode', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['cutNode']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['cutNode']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     * @\Neos\Flow\Annotations\Session(autoStart=true)
     */
    public function clear()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['clear'])) {
            $result = parent::clear();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['clear'] = true;
            try {
            
                $methodArguments = [];

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['clear']['Neos\Flow\Aop\Advice\BeforeAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['clear']['Neos\Flow\Aop\Advice\BeforeAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Service\NodeClipboard', 'clear', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('clear');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Service\NodeClipboard', 'clear', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['clear']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['clear']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return string $nodeContextPath
     */
    public function getNodeContextPath()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getNodeContextPath'])) {
            $result = parent::getNodeContextPath();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getNodeContextPath'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getNodeContextPath');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Service\NodeClipboard', 'getNodeContextPath', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getNodeContextPath']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getNodeContextPath']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return string $mode
     */
    public function getMode()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getMode'])) {
            $result = parent::getMode();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getMode'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getMode');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Ui\Service\NodeClipboard', 'getMode', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getMode']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getMode']);
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
  'nodeContextPath' => 'string',
  'mode' => 'string one of the NodeClipboard::MODE_*  constants',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos.Ui/Classes/Service/NodeClipboard.php
#