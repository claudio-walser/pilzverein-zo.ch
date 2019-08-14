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

use Neos\Neos\Ui\Domain\Model\Feedback\Operations\UpdateNodeInfo;

class MoveBefore_Original extends AbstractMove
{

    /**
     * "Subject" is the to-be-moved node; the "sibling" node is the node after which the "Subject" should be copied.
     *
     * @return boolean
     */
    public function canApply()
    {
        $nodeType = $this->getSubject()->getNodeType();

        return $this->getSiblingNode()->getParent()->isNodeTypeAllowedAsChildNode($nodeType);
    }

    public function getMode()
    {
        return 'before';
    }

    /**
     * Applies this change
     *
     * @return void
     */
    public function apply()
    {
        if ($this->canApply()) {
            $before = self::cloneNodeWithNodeData($this->getSubject());
            $parent = $before->getParent();

            if ($this->nodeNameAvailableBelowNode($this->getSiblingNode()->getParent(), $this->getSubject())) {
                $this->getSubject()->moveBefore($this->getSiblingNode());
            } else {
                $nodeName = $this->generateUniqueNodeName($this->getSiblingNode()->getParent());
                $this->getSubject()->moveBefore($this->getSiblingNode(), $nodeName);
            }

            $updateParentNodeInfo = new UpdateNodeInfo();
            $updateParentNodeInfo->setNode($parent);

            $this->feedbackCollection->add($updateParentNodeInfo);

            $this->finish($before);
        }
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
class MoveBefore extends MoveBefore_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\Neos\Ui\Domain\Model\Changes\MoveBefore' === get_class($this)) {
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
  'contentRepositoryNodeService' => 'Neos\\ContentRepository\\Domain\\Service\\NodeServiceInterface',
  'parentDomAddress' => 'Neos\\Neos\\Ui\\Domain\\Model\\RenderedNodeDomAddress',
  'siblingDomAddress' => 'Neos\\Neos\\Ui\\Domain\\Model\\RenderedNodeDomAddress',
  'nodeService' => 'Neos\\Neos\\Ui\\ContentRepository\\Service\\NodeService',
  'cachedSiblingNode' => 'Neos\\ContentRepository\\Domain\\Model\\NodeInterface',
  'subject' => 'Neos\\ContentRepository\\Domain\\Model\\NodeInterface',
  'feedbackCollection' => 'Neos\\Neos\\Ui\\Domain\\Model\\FeedbackCollection',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectPersistenceManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\NodeServiceInterface', 'Neos\Neos\TYPO3CR\NeosNodeService', 'contentRepositoryNodeService', 'eb555e5f05a6142820b0894069a20195', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\NodeServiceInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Ui\ContentRepository\Service\NodeService', 'Neos\Neos\Ui\ContentRepository\Service\NodeService', 'nodeService', 'c1132e56328e2286433a0639d659934e', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Ui\ContentRepository\Service\NodeService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Ui\Domain\Model\FeedbackCollection', 'Neos\Neos\Ui\Domain\Model\FeedbackCollection', 'feedbackCollection', '159b5a4040d2f09b39bc0359dd53a19b', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Ui\Domain\Model\FeedbackCollection'); });
        $this->Flow_Injected_Properties = array (
  0 => 'persistenceManager',
  1 => 'contentRepositoryNodeService',
  2 => 'nodeService',
  3 => 'feedbackCollection',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos.Ui/Classes/Domain/Model/Changes/MoveBefore.php
#