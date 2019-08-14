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

use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\ContentRepository\Security\Authorization\Privilege\Node\CreateNodePrivilege;
use Neos\ContentRepository\Security\Authorization\Privilege\Node\CreateNodePrivilegeSubject;
use Neos\ContentRepository\Security\Authorization\Privilege\Node\EditNodePrivilege;
use Neos\ContentRepository\Security\Authorization\Privilege\Node\EditNodePropertyPrivilege;
use Neos\ContentRepository\Security\Authorization\Privilege\Node\NodePrivilegeSubject;
use Neos\ContentRepository\Security\Authorization\Privilege\Node\PropertyAwareNodePrivilegeSubject;
use Neos\ContentRepository\Security\Authorization\Privilege\Node\RemoveNodePrivilege;
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Security\Authorization\Privilege\PrivilegeInterface;
use Neos\Flow\Security\Authorization\PrivilegeManagerInterface;
use Neos\Flow\Security\Policy\PolicyService;
use Neos\Neos\Security\Authorization\Privilege\NodeTreePrivilege;

/**
 * @Flow\Scope("singleton")
 */
class NodePolicyService_Original
{

    /**
     * @Flow\Inject
     * @var PrivilegeManagerInterface
     */
    protected $privilegeManager;

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @return array the key is a Privilege class name; the value is "true" if privileges are configured for this class name.
     * @Flow\CompileStatic
     */
    public static function getUsedPrivilegeClassNames(ObjectManagerInterface $objectManager): array
    {
        $policyService = $objectManager->get(PolicyService::class);
        $usedPrivilegeClassNames = [];
        foreach ($policyService->getPrivilegeTargets() as $privilegeTarget) {
            $usedPrivilegeClassNames[$privilegeTarget->getPrivilegeClassName()] = true;
            foreach (class_parents($privilegeTarget->getPrivilegeClassName()) as $parentPrivilege) {
                if (is_a($parentPrivilege, PrivilegeInterface::class, true)) {
                    $usedPrivilegeClassNames[$parentPrivilege] = true;
                }
            }
        }

        return $usedPrivilegeClassNames;
    }

    /**
     * @param NodeInterface $node
     * @return array
     */
    public function getNodePolicyInformation(NodeInterface $node): array
    {
        return [
            'disallowedNodeTypes' => $this->getDisallowedNodeTypes($node),
            'canRemove' => $this->canRemoveNode($node),
            'canEdit' => $this->canEditNode($node),
            'disallowedProperties' => $this->getDisallowedProperties($node)
        ];
    }

    /**
     * @param NodeInterface $node
     * @return bool
     */
    public function isNodeTreePrivilegeGranted(NodeInterface $node): bool
    {
        if (!isset(self::getUsedPrivilegeClassNames($this->objectManager)[NodeTreePrivilege::class])) {
            return true;
        }

        return $this->privilegeManager->isGranted(
            NodeTreePrivilege::class,
            new NodePrivilegeSubject($node)
        );
    }

    /**
     * @param NodeInterface $node
     * @return array
     */
    public function getDisallowedNodeTypes(NodeInterface $node): array
    {
        $disallowedNodeTypes = [];

        if (!isset(self::getUsedPrivilegeClassNames($this->objectManager)[CreateNodePrivilege::class])) {
            return $disallowedNodeTypes;
        }

        $filter = function ($nodeType) use ($node) {
            return !$this->privilegeManager->isGranted(
                CreateNodePrivilege::class,
                new CreateNodePrivilegeSubject($node, $nodeType)
            );
        };

        $disallowedNodeTypeObjects = array_filter($this->nodeTypeManager->getNodeTypes(), $filter);

        $mapper = function ($nodeType) {
            return $nodeType->getName();
        };

        return array_values(array_map($mapper, $disallowedNodeTypeObjects));
    }

    /**
     * @param NodeInterface $node
     * @return bool
     */
    public function canRemoveNode(NodeInterface $node): bool
    {
        $canRemove = true;
        if (isset(self::getUsedPrivilegeClassNames($this->objectManager)[RemoveNodePrivilege::class])) {
            $canRemove = $this->privilegeManager->isGranted(RemoveNodePrivilege::class, new NodePrivilegeSubject($node));
        }

        return $canRemove;
    }

    /**
     * @param NodeInterface $node
     * @return bool
     */
    public function canEditNode(NodeInterface $node): bool
    {
        $canEdit = true;
        if (isset(self::getUsedPrivilegeClassNames($this->objectManager)[EditNodePrivilege::class])) {
            $canEdit = $this->privilegeManager->isGranted(EditNodePrivilege::class, new NodePrivilegeSubject($node));
        }

        return $canEdit;
    }

    /**
     * @param NodeInterface $node
     * @return array
     */
    public function getDisallowedProperties(NodeInterface $node): array
    {
        $disallowedProperties = [];

        if (!isset(self::getUsedPrivilegeClassNames($this->objectManager)[EditNodePropertyPrivilege::class])) {
            return $disallowedProperties;
        }

        $filter = function ($propertyName) use ($node) {
            return !$this->privilegeManager->isGranted(
                EditNodePropertyPrivilege::class,
                new PropertyAwareNodePrivilegeSubject($node, null, $propertyName)
            );
        };

        $disallowedProperties = array_filter(array_keys($node->getNodeType()->getProperties()), $filter);
        return $disallowedProperties;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Ui\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class NodePolicyService extends NodePolicyService_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Neos\Ui\Service\NodePolicyService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Ui\Service\NodePolicyService', $this);
        if ('Neos\Neos\Ui\Service\NodePolicyService' === get_class($this)) {
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
  'privilegeManager' => 'Neos\\Flow\\Security\\Authorization\\PrivilegeManagerInterface',
  'nodeTypeManager' => 'Neos\\ContentRepository\\Domain\\Service\\NodeTypeManager',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Neos\Ui\Service\NodePolicyService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Ui\Service\NodePolicyService', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Authorization\PrivilegeManagerInterface', 'Neos\Flow\Security\Authorization\PrivilegeManager', 'privilegeManager', '68ada25ea2828278e185a684d1c86739', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Authorization\PrivilegeManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\NodeTypeManager', 'Neos\ContentRepository\Domain\Service\NodeTypeManager', 'nodeTypeManager', 'e525e2ecb65f7f9731d6537ddecd16d4', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\NodeTypeManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'privilegeManager',
  1 => 'nodeTypeManager',
  2 => 'objectManager',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos.Ui/Classes/Service/NodePolicyService.php
#