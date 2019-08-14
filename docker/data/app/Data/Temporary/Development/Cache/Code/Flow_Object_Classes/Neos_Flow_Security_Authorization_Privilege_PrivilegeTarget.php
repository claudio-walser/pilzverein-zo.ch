<?php 
namespace Neos\Flow\Security\Authorization\Privilege;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Security\Authorization\Privilege\Parameter\PrivilegeParameterDefinition;
use Neos\Flow\Security\Authorization\Privilege\Parameter\PrivilegeParameterInterface;
use Neos\Flow\Security\Exception as SecurityException;

/**
 * A privilege target
 */
class PrivilegeTarget_Original
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $privilegeClassName;

    /**
     * @var string
     */
    protected $matcher;

    /**
     * @var Parameter\PrivilegeParameterDefinition[]
     */
    protected $parameterDefinitions;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param string $identifier
     * @param string $privilegeClassName
     * @param string $matcher
     * @param Parameter\PrivilegeParameterDefinition[] $parameterDefinitions
     */
    public function __construct($identifier, $privilegeClassName, $matcher, array $parameterDefinitions = [])
    {
        $this->identifier = $identifier;
        $this->privilegeClassName = $privilegeClassName;
        $this->matcher = $matcher;
        $this->parameterDefinitions = $parameterDefinitions;
    }

    /**
     * This object is created very early so we can't rely on AOP for the property injection
     *
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }


    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getPrivilegeClassName()
    {
        return $this->privilegeClassName;
    }

    /**
     * @return string
     */
    public function getMatcher()
    {
        return $this->matcher;
    }

    /**
     * @return Parameter\PrivilegeParameterDefinition[]
     */
    public function getParameterDefinitions()
    {
        return $this->parameterDefinitions;
    }

    /**
     * @return boolean
     */
    public function hasParameters()
    {
        return $this->parameterDefinitions !== [];
    }

    /**
     * @param string $permission one of "GRANT", "DENY" or "ABSTAIN"
     * @param array $parameters Optional key/value array with parameter names and -values
     * @return PrivilegeInterface
     * @throws SecurityException
     */
    public function createPrivilege($permission, array $parameters = [])
    {
        $permission = strtolower($permission);
        if ($permission !== PrivilegeInterface::GRANT && $permission !== PrivilegeInterface::DENY && $permission !== PrivilegeInterface::ABSTAIN) {
            throw new SecurityException(sprintf('permission must be either "GRANT", "DENY" or "ABSTAIN", given: "%s"', $permission), 1401878462);
        }

        $privilegeParameters = array_map($this->createParameterMapper($parameters), $this->parameterDefinitions);
        $privilege = new $this->privilegeClassName($this, $this->matcher, $permission, $privilegeParameters);
        if (!$privilege instanceof PrivilegeInterface) {
            throw new SecurityException(sprintf('Expected instance of PrivilegeInterface, got "%s"', get_class($privilege)), 1395869340);
        }
        $privilege->injectObjectManager($this->objectManager);

        return $privilege;
    }

    /**
     * @param array $parameters
     * @return \Closure
     */
    protected function createParameterMapper(array $parameters): \Closure
    {
        return function (PrivilegeParameterDefinition $parameterDefinition) use ($parameters) {
            return $this->createParameter($parameterDefinition, $parameters);
        };
    }

    /**
     * @param PrivilegeParameterDefinition $parameterDefinition
     * @param array $parameters
     * @return PrivilegeParameterInterface
     * @throws SecurityException
     */
    protected function createParameter(PrivilegeParameterDefinition $parameterDefinition, array $parameters): PrivilegeParameterInterface
    {
        $parameterName = $parameterDefinition->getName();
        if (!isset($parameters[$parameterName])) {
            throw new SecurityException(sprintf('The parameter "%s" is not specified', $parameterName), 1401794982);
        }

        $privilegeParameterClassName = $parameterDefinition->getParameterClassName();
        return new $privilegeParameterClassName($parameterName, $parameters[$parameterName]);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Security\Authorization\Privilege;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A privilege target
 */
class PrivilegeTarget extends PrivilegeTarget_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param string $identifier
     * @param string $privilegeClassName
     * @param string $matcher
     * @param Parameter\PrivilegeParameterDefinition[] $parameterDefinitions
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $identifier in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $privilegeClassName in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(2, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $matcher in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Flow\Security\Authorization\Privilege\PrivilegeTarget' === get_class($this)) {
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
  'identifier' => 'string',
  'privilegeClassName' => 'string',
  'matcher' => 'string',
  'parameterDefinitions' => 'array<Neos\\Flow\\Security\\Authorization\\Privilege\\Parameter\\PrivilegeParameterDefinition>',
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
# PathAndFilename: /var/www/html/neos/Packages/Framework/Neos.Flow/Classes/Security/Authorization/Privilege/PrivilegeTarget.php
#