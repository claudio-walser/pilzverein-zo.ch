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

use Neos\Flow\Aop\Pointcut\PointcutFilterInterface;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Security\Authorization\Privilege\AbstractPrivilege;
use Neos\Flow\Security\Authorization\Privilege\Method\MethodPrivilege;
use Neos\Flow\Security\Authorization\Privilege\Method\MethodPrivilegeInterface;
use Neos\Flow\Security\Authorization\Privilege\Method\MethodPrivilegeSubject;
use Neos\Flow\Security\Authorization\Privilege\PrivilegeSubjectInterface;
use Neos\Flow\Security\Authorization\Privilege\PrivilegeTarget;
use Neos\Flow\Security\Exception\InvalidPolicyException;
use Neos\Flow\Security\Exception\InvalidPrivilegeTypeException;

/**
 * A privilege covering general access to Neos Backend Modules
 *
 * It matches if the matcher is equal to the (sub)module path ("<module>/<submodule>") in question
 */
class ModulePrivilege_Original extends AbstractPrivilege implements MethodPrivilegeInterface
{
    /**
     * @var MethodPrivilegeInterface
     */
    private $methodPrivilege;

    /**
     * @var boolean
     */
    private $initialized = false;

    /**
     * @return void
     * @throws InvalidPolicyException
     */
    public function initialize()
    {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        $moduleSettings = $this->objectManager->get(ConfigurationManager::class)->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'Neos.Neos.modules');
        $targetModulePath = $this->getParsedMatcher();
        list($moduleName, $subModuleName) = array_pad(explode('/', $targetModulePath, 2), 2, null);
        if (!isset($moduleSettings[$moduleName])) {
            throw new InvalidPolicyException(sprintf('The module "%s" specified in privilege target "%s" is not configured', $moduleName, $this->getPrivilegeTargetIdentifier()), 1493206188);
        }
        if ($subModuleName !== null && !isset($moduleSettings[$moduleName]['submodules'][$subModuleName])) {
            throw new InvalidPolicyException(sprintf('The module "%s" specified in privilege target "%s" is not configured', $targetModulePath, $this->getPrivilegeTargetIdentifier()), 1493206192);
        }
        $targetModuleConfiguration = $subModuleName !== null ? $moduleSettings[$moduleName]['submodules'][$subModuleName] : $moduleSettings[$moduleName];
        if (!isset($targetModuleConfiguration['controller'])) {
            throw new \RuntimeException(sprintf('The module "%s" specified in privilege target "%s" doesn\'t have a "controller" configured', $targetModulePath, $this->getPrivilegeTargetIdentifier()), 1493206825);
        }

        $methodPrivilegeMatcher = 'method(public ' . ltrim($targetModuleConfiguration['controller'], '\\') . '->(?!initialize).*Action())';
        $methodPrivilegeTarget = new PrivilegeTarget($this->privilegeTarget->getIdentifier() . '__methodPrivilege', MethodPrivilege::class, $methodPrivilegeMatcher);
        $methodPrivilegeTarget->injectObjectManager($this->objectManager);
        $this->methodPrivilege = $methodPrivilegeTarget->createPrivilege($this->getPermission(), $this->getParameters());
    }

    /**
     * Returns a string which distinctly identifies this object and thus can be used as an identifier for cache entries
     * related to this object.
     *
     * @return string
     */
    public function getCacheEntryIdentifier(): string
    {
        $this->initialize();
        return $this->methodPrivilege->getCacheEntryIdentifier();
    }

    /**
     * Returns true, if this privilege covers the given subject
     *
     * @param PrivilegeSubjectInterface $subject
     * @return boolean
     * @throws InvalidPrivilegeTypeException if the given $subject is not supported by the privilege
     */
    public function matchesSubject(PrivilegeSubjectInterface $subject)
    {
        if (!($subject instanceof ModulePrivilegeSubject) && !($subject instanceof MethodPrivilegeSubject)) {
            throw new InvalidPrivilegeTypeException(
                sprintf(
                    'Privileges of type "%s" only support subjects of type "%s" or "%s", but we got a subject of type: "%s".',
                    self::class,
                    ModulePrivilegeSubject::class,
                    MethodPrivilegeSubject::class,
                    get_class($subject)
                ), 1493130646);
        }
        $this->initialize();
        if ($subject instanceof MethodPrivilegeSubject) {
            return $this->methodPrivilege->matchesSubject($subject);
        }
        return $subject->getModulePath() === $this->getParsedMatcher();
    }

    /**
     * @param string $className
     * @param string $methodName
     * @return boolean
     */
    public function matchesMethod($className, $methodName)
    {
        $this->initialize();
        return $this->methodPrivilege->matchesMethod($className, $methodName);
    }

    /**
     * @return PointcutFilterInterface
     */
    public function getPointcutFilterComposite()
    {
        $this->initialize();
        return $this->methodPrivilege->getPointcutFilterComposite();
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Security\Authorization\Privilege;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A privilege covering general access to Neos Backend Modules
 * 
 * It matches if the matcher is equal to the (sub)module path ("<module>/<submodule>") in question
 */
class ModulePrivilege extends ModulePrivilege_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
        if ('Neos\Neos\Security\Authorization\Privilege\ModulePrivilege' === get_class($this)) {
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
  'methodPrivilege' => 'Neos\\Flow\\Security\\Authorization\\Privilege\\Method\\MethodPrivilegeInterface',
  'initialized' => 'boolean',
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
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Security/Authorization/Privilege/ModulePrivilege.php
#