<?php 
namespace Neos\ContentRepository\Security\Authorization\Privilege\Node\Doctrine;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Validation\Validator\NodeIdentifierValidator;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Authorization\Privilege\Entity\Doctrine\FalseConditionGenerator;
use Neos\Flow\Security\Context;
use Neos\Flow\Security\Authorization\Privilege\Entity\Doctrine\ConditionGenerator as EntityConditionGenerator;
use Neos\Flow\Security\Authorization\Privilege\Entity\Doctrine\DisjunctionGenerator;
use Neos\Flow\Security\Authorization\Privilege\Entity\Doctrine\PropertyConditionGenerator;
use Neos\Flow\Security\Exception\InvalidPrivilegeException;
use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\ContextFactory;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;

/**
 * A SQL condition generator, supporting special SQL constraints
 * for nodes.
 */
class ConditionGenerator_Original extends EntityConditionGenerator
{
    /**
     * @Flow\Inject
     * @var ContextFactory
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var Context
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @var string
     */
    protected $entityType = NodeData::class;

    /**
     * @param string $entityType
     * @return boolean
     * @throws InvalidPrivilegeException
     */
    public function isType($entityType)
    {
        throw new InvalidPrivilegeException('The isType() operator must not be used in Node privilege matchers!', 1417083500);
    }

    /**
     * @param string $nodePathOrIdentifier
     * @return PropertyConditionGenerator
     */
    public function isDescendantNodeOf($nodePathOrIdentifier)
    {
        $propertyConditionGenerator1 = new PropertyConditionGenerator('path');
        $propertyConditionGenerator2 = new PropertyConditionGenerator('path');

        if (preg_match(NodeIdentifierValidator::PATTERN_MATCH_NODE_IDENTIFIER, $nodePathOrIdentifier) === 1) {
            $node = $this->getNodeByIdentifier($nodePathOrIdentifier);
            if ($node === null) {
                return new FalseConditionGenerator();
            }
            $nodePath = $node->getPath();
        } else {
            $nodePathOrIdentifier = $propertyConditionGenerator1->getValueForOperand($nodePathOrIdentifier);
            $nodePath = rtrim($nodePathOrIdentifier, '/');
        }

        return new DisjunctionGenerator([$propertyConditionGenerator1->like($nodePath . '/%'), $propertyConditionGenerator2->equals($nodePath)]);
    }

    /**
     * @param string|array $nodeTypes
     * @return PropertyConditionGenerator
     */
    public function nodeIsOfType($nodeTypes)
    {
        $propertyConditionGenerator = new PropertyConditionGenerator('nodeType');
        $nodeTypes = $propertyConditionGenerator->getValueForOperand($nodeTypes);
        if (!is_array($nodeTypes)) {
            $nodeTypes = [$nodeTypes];
        }
        $expandedNodeTypeNames = [];
        foreach ($nodeTypes as $nodeTypeName) {
            $subNodeTypes = $this->nodeTypeManager->getSubNodeTypes($nodeTypeName, false);
            $expandedNodeTypeNames = array_merge($expandedNodeTypeNames, [$nodeTypeName], array_keys($subNodeTypes));
        }
        return $propertyConditionGenerator->in(array_unique($expandedNodeTypeNames));
    }

    /**
     * @param string|array $workspaceNames
     * @return PropertyConditionGenerator
     */
    public function isInWorkspace($workspaceNames)
    {
        $propertyConditionGenerator = new PropertyConditionGenerator('workspace');
        $workspaceNames = $propertyConditionGenerator->getValueForOperand($workspaceNames);
        if (!is_array($workspaceNames)) {
            $workspaceNames = [$workspaceNames];
        }
        return $propertyConditionGenerator->in($workspaceNames);
    }

    /**
     * @param string $nodeIdentifier
     * @return NodeInterface
     */
    protected function getNodeByIdentifier($nodeIdentifier)
    {
        $context = $this->contextFactory->create();
        $node = null;
        $this->securityContext->withoutAuthorizationChecks(function () use ($nodeIdentifier, $context, &$node) {
            $node = $context->getNodeByIdentifier($nodeIdentifier);
        });
        $context->getFirstLevelNodeCache()->setByIdentifier($nodeIdentifier, null);
        return $node;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\ContentRepository\Security\Authorization\Privilege\Node\Doctrine;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A SQL condition generator, supporting special SQL constraints
 * for nodes.
 */
class ConditionGenerator extends ConditionGenerator_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\ContentRepository\Security\Authorization\Privilege\Node\Doctrine\ConditionGenerator' === get_class($this)) {
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
  'contextFactory' => 'Neos\\ContentRepository\\Domain\\Service\\ContextFactory',
  'securityContext' => 'Neos\\Flow\\Security\\Context',
  'nodeTypeManager' => 'Neos\\ContentRepository\\Domain\\Service\\NodeTypeManager',
  'entityType' => 'string',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\ContextFactory', 'Neos\ContentRepository\Domain\Service\ContextFactory', 'contextFactory', 'fe29cc43adf119dd42e0028ba09ce06b', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\ContextFactory'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Context', 'Neos\Flow\Security\Context', 'securityContext', 'f7e2ddeaebd191e228b8c2e4dc7f1f83', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Context'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\NodeTypeManager', 'Neos\ContentRepository\Domain\Service\NodeTypeManager', 'nodeTypeManager', 'e525e2ecb65f7f9731d6537ddecd16d4', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\NodeTypeManager'); });
        $this->Flow_Injected_Properties = array (
  0 => 'contextFactory',
  1 => 'securityContext',
  2 => 'nodeTypeManager',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.ContentRepository/Classes/Security/Authorization/Privilege/Node/Doctrine/ConditionGenerator.php
#