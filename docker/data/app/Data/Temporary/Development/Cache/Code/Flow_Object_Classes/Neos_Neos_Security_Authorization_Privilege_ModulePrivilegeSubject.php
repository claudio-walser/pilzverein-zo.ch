<?php 
namespace Neos\Neos\Security\Authorization\Privilege;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Security\Authorization\Privilege\PrivilegeSubjectInterface;

/**
 * A subject for the ModulePrivilege
 */
class ModulePrivilegeSubject_Original implements PrivilegeSubjectInterface
{
    /**
     * @var string
     */
    private $modulePath;

    /**
     * @param string $modulePath
     */
    public function __construct($modulePath)
    {
        $this->modulePath = $modulePath;
    }

    /**
     * @return string
     */
    public function getModulePath()
    {
        return $this->modulePath;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Security\Authorization\Privilege;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A subject for the ModulePrivilege
 */
class ModulePrivilegeSubject extends ModulePrivilegeSubject_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param string $modulePath
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $modulePath in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
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
);
        $propertyVarTags = array (
  'modulePath' => 'string',
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
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Security/Authorization/Privilege/ModulePrivilegeSubject.php
#