<?php 
namespace Neos\Flow\Security\Policy;

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
use Neos\Flow\Security\Authorization\Privilege\PrivilegeInterface;

/**
 * A role. These roles can be structured in a tree.
 */
class Role_Original
{
    const ROLE_IDENTIFIER_PATTERN = '/^(\w+(?:\.\w+)*)\:(\w+)$/';   // Vendor(.Package)?:RoleName

    /**
     * The identifier of this role
     *
     * @var string
     */
    protected $identifier;

    /**
     * The name of this role (without package key)
     *
     * @var string
     */
    protected $name;

    /**
     * The package key this role belongs to (extracted from the identifier)
     *
     * @var string
     */
    protected $packageKey;

    /**
     * Whether or not the role is "abstract", meaning it can't be assigned to accounts directly but only serves as a "template role" for other roles to inherit from
     *
     * @var boolean
     */
    protected $abstract = false;

    /**
     * @Flow\Transient
     * @var Role[]
     */
    protected $parentRoles;

    /**
     * @var PrivilegeInterface[]
     */
    protected $privileges = [];

    /**
     * @param string $identifier The fully qualified identifier of this role (Vendor.Package:Role)
     * @param Role[] $parentRoles
     * @throws \InvalidArgumentException
     */
    public function __construct($identifier, array $parentRoles = [])
    {
        if (!is_string($identifier)) {
            throw new \InvalidArgumentException('The role identifier must be a string, "' . gettype($identifier) . '" given. Please check the code or policy configuration creating or defining this role.', 1296509556);
        }
        if (preg_match(self::ROLE_IDENTIFIER_PATTERN, $identifier, $matches) !== 1) {
            throw new \InvalidArgumentException('The role identifier must follow the pattern "Vendor.Package:RoleName", but "' . $identifier . '" was given. Please check the code or policy configuration creating or defining this role.', 1365446549);
        }
        $this->identifier = $identifier;
        $this->packageKey = $matches[1];
        $this->name = $matches[2];
        $this->parentRoles = $parentRoles;
    }

    /**
     * Returns the fully qualified identifier of this role
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * The key of the package that defines this role.
     *
     * @return string
     */
    public function getPackageKey()
    {
        return $this->packageKey;
    }

    /**
     * The name of this role, being the identifier without the package key.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param boolean $abstract
     * @return void
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;
    }

    /**
     * Whether or not this role is "abstract", meaning it can't be assigned to accounts directly but only serves as a "template role" for other roles to inherit from
     *
     * @return boolean
     */
    public function isAbstract()
    {
        return $this->abstract;
    }

    /**
     * Assign parent roles to this role.
     *
     * @param Role[] $parentRoles indexed by role identifier
     * @return void
     */
    public function setParentRoles(array $parentRoles)
    {
        $this->parentRoles = [];
        foreach ($parentRoles as $parentRole) {
            $this->addParentRole($parentRole);
        }
    }

    /**
     * Returns an array of all directly assigned parent roles.
     *
     * @return Role[] Array of direct parent roles, indexed by role identifier
     */
    public function getParentRoles()
    {
        return $this->parentRoles;
    }

    /**
     * Returns all (directly and indirectly reachable) parent roles for the given role.
     *
     * @return Role[] Array of parent roles, indexed by role identifier
     */
    public function getAllParentRoles()
    {
        $reducer = function (array $result, Role $role) {
            $result[$role->getIdentifier()] = $role;
            return array_merge($result, $role->getAllParentRoles());
        };

        return array_reduce($this->parentRoles, $reducer, []);
    }

    /**
     * Add a (direct) parent role to this role.
     *
     * @param Role $parentRole
     * @return void
     */
    public function addParentRole(Role $parentRole)
    {
        if (!$this->hasParentRole($parentRole)) {
            $parentRoleIdentifier = $parentRole->getIdentifier();
            $this->parentRoles[$parentRoleIdentifier] = $parentRole;
        }
    }

    /**
     * Returns true if the given role is a directly assigned parent of this role.
     *
     * @param Role $role
     * @return boolean
     */
    public function hasParentRole(Role $role)
    {
        return isset($this->parentRoles[$role->getIdentifier()]);
    }

    /**
     * Assign privileges to this role.
     *
     * @param PrivilegeInterface[] $privileges
     * @return void
     */
    public function setPrivileges(array $privileges)
    {
        foreach ($privileges as $privilege) {
            $this->privileges[$privilege->getCacheEntryIdentifier()] = $privilege;
        }
    }

    /**
     * @return PrivilegeInterface[] Array of privileges assigned to this role
     */
    public function getPrivileges()
    {
        return $this->privileges;
    }

    /**
     * @param string $className Fully qualified name of the Privilege class to filter for
     * @return PrivilegeInterface[]
     */
    public function getPrivilegesByType($className)
    {
        $privileges = [];
        foreach ($this->privileges as $privilege) {
            if ($privilege instanceof $className) {
                $privileges[] = $privilege;
            }
        }
        return $privileges;
    }

    /**
     * @param string $privilegeTargetIdentifier
     * @param array $privilegeParameters
     * @return PrivilegeInterface the matching privilege or NULL if no privilege exists for the given constraints
     */
    public function getPrivilegeForTarget($privilegeTargetIdentifier, array $privilegeParameters = [])
    {
        foreach ($this->privileges as $privilege) {
            if ($privilege->getPrivilegeTargetIdentifier() !== $privilegeTargetIdentifier) {
                continue;
            }
            if (array_diff_assoc($privilege->getParameters(), $privilegeParameters) !== []) {
                continue;
            }
            return $privilege;
        }
        return null;
    }

    /**
     * Add a privilege to this role.
     *
     * @param PrivilegeInterface $privilege
     * @return void
     */
    public function addPrivilege($privilege)
    {
        $this->privileges[$privilege->getCacheEntryIdentifier()] = $privilege;
    }

    /**
     * Returns the string representation of this role (the identifier)
     *
     * @return string the string representation of this role
     */
    public function __toString()
    {
        return $this->identifier;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Security\Policy;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A role. These roles can be structured in a tree.
 */
class Role extends Role_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param string $identifier The fully qualified identifier of this role (Vendor.Package:Role)
     * @param Role[] $parentRoles
     * @throws \InvalidArgumentException
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $identifier in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __sleep()
    {
            $result = NULL;
        $this->Flow_Object_PropertiesToSerialize = array();

        $transientProperties = array (
  0 => 'parentRoles',
);
        $propertyVarTags = array (
  'identifier' => 'string',
  'name' => 'string',
  'packageKey' => 'string',
  'abstract' => 'boolean',
  'parentRoles' => 'array<Neos\\Flow\\Security\\Policy\\Role>',
  'privileges' => 'array<Neos\\Flow\\Security\\Authorization\\Privilege\\PrivilegeInterface>',
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
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Framework/Neos.Flow/Classes/Security/Policy/Role.php
#