<?php 
namespace Neos\ContentRepository\Security\Authorization\Privilege\Node;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Eel\CompilingEvaluator;
use Neos\Eel\Context;
use Neos\Flow\Security\Authorization\Privilege\Entity\Doctrine\EntityPrivilege;
use Neos\Flow\Security\Authorization\Privilege\PrivilegeSubjectInterface;
use Neos\Flow\Security\Exception\InvalidPrivilegeTypeException;
use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\ContentRepository\Security\Authorization\Privilege\Node\Doctrine\ConditionGenerator;

/**
 * A node privilege to restricting reading of nodes.
 * Nodes not granted for reading will be filtered via SQL.
 *
 * Currently only doctrine persistence is supported as we use
 * the doctrine filter api, to rewrite SQL queries.
 */
class ReadNodePrivilege_Original extends EntityPrivilege
{
    /**
     * @param string $entityType
     * @return boolean
     */
    public function matchesEntityType($entityType)
    {
        return $entityType === NodeData::class;
    }

    /**
     * @return ConditionGenerator
     */
    protected function getConditionGenerator()
    {
        return new ConditionGenerator();
    }

    /**
     * @param PrivilegeSubjectInterface $subject
     * @return boolean
     * @throws InvalidPrivilegeTypeException
     */
    public function matchesSubject(PrivilegeSubjectInterface $subject)
    {
        if (!$subject instanceof NodePrivilegeSubject) {
            throw new InvalidPrivilegeTypeException(sprintf('Privileges of type "%s" only support subjects of type "%s", but we got a subject of type: "%s".', static::class, NodePrivilegeSubject::class, get_class($subject)), 1465979693);
        }
        $nodeContext = new NodePrivilegeContext($subject->getNode());
        $eelContext = new Context($nodeContext);
        $eelCompilingEvaluator = $this->objectManager->get(CompilingEvaluator::class);
        return $eelCompilingEvaluator->evaluate($this->getParsedMatcher(), $eelContext);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\ContentRepository\Security\Authorization\Privilege\Node;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A node privilege to restricting reading of nodes.
 * Nodes not granted for reading will be filtered via SQL.
 * 
 * Currently only doctrine persistence is supported as we use
 * the doctrine filter api, to rewrite SQL queries.
 */
class ReadNodePrivilege extends ReadNodePrivilege_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param PrivilegeTarget $privilegeTarget
     * @param string $matcher
     * @param string $permission One of the constants GRANT, DENY or ABSTAIN
     * @param PrivilegeParameterInterface[] $parameters
     */
    public function __construct()
    {
        $arguments = func_get_args();

        if (!array_key_exists(0, $arguments)) $arguments[0] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Authorization\Privilege\PrivilegeTarget');
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $privilegeTarget in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $matcher in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(2, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $permission in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(3, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $parameters in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\ContentRepository\Security\Authorization\Privilege\Node\ReadNodePrivilege' === get_class($this)) {
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
  'isEvaluated' => 'boolean',
  'entityType' => 'string',
  'conditionGenerator' => 'Neos\\Flow\\Security\\Authorization\\Privilege\\Entity\\Doctrine\\SqlGeneratorInterface',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'cacheEntryIdentifier' => 'string',
  'privilegeTarget' => 'Neos\\Flow\\Security\\Authorization\\Privilege\\PrivilegeTarget',
  'parameters' => 'array<Neos\\Flow\\Security\\Authorization\\Privilege\\Parameter\\PrivilegeParameterInterface>',
  'matcher' => 'string',
  'parsedMatcher' => 'string',
  'permission' => 'integer One of the constants ABSTAIN, GRANT or DENY',
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
        $this->injectObjectManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'));
        $this->Flow_Injected_Properties = array (
  0 => 'objectManager',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.ContentRepository/Classes/Security/Authorization/Privilege/Node/ReadNodePrivilege.php
#