<?php 
namespace Neos\Flow\Security\Authorization;

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
use Neos\Flow\Security\Authorization\Privilege\PrivilegeInterface;
use Neos\Flow\Security\Context;
use Neos\Flow\Security\Policy\Role;

/**
 * An access decision voter manager
 *
 * @Flow\Scope("singleton")
 */
class PrivilegeManager_Original implements PrivilegeManagerInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Context
     */
    protected $securityContext;

    /**
     * If set to true access will be granted for objects where all voters abstain from decision.
     *
     * @Flow\InjectConfiguration("security.authorization.allowAccessIfAllVotersAbstain")
     * @var boolean
     */
    protected $allowAccessIfAllAbstain = false;

    /**
     * @param ObjectManagerInterface $objectManager The object manager
     * @param Context $securityContext The current security context
     */
    public function __construct(ObjectManagerInterface $objectManager, Context $securityContext)
    {
        $this->objectManager = $objectManager;
        $this->securityContext = $securityContext;
    }

    /**
     * Returns true, if the given privilege type is granted for the given subject based
     * on the current security context.
     *
     * @param string $privilegeType The type of privilege that should be evaluated
     * @param mixed $subject The subject to check privileges for
     * @param string $reason This variable will be filled by a message giving information about the reasons for the result of this method
     * @return boolean
     */
    public function isGranted($privilegeType, $subject, &$reason = '')
    {
        return $this->isGrantedForRoles($this->securityContext->getRoles(), $privilegeType, $subject, $reason);
    }

    /**
     * Returns true, if the given privilege type would be granted for the given roles and subject
     *
     * @param array<Role> $roles The roles that should be evaluated
     * @param string $privilegeType The type of privilege that should be evaluated
     * @param mixed $subject The subject to check privileges for
     * @param string $reason This variable will be filled by a message giving information about the reasons for the result of this method
     * @return boolean
     */
    public function isGrantedForRoles(array $roles, $privilegeType, $subject, &$reason = '')
    {
        $availablePrivileges = array_reduce($roles, $this->getPrivilegeByTypeReducer($privilegeType), []);
        $effectivePrivileges = array_filter($availablePrivileges, $this->getPrivilegeSubjectFilter($subject));
        /** @var PrivilegePermissionResult $result */
        $result = array_reduce($effectivePrivileges, [$this, 'applyPrivilegeToResult'], new PrivilegePermissionResult());

        $effectivePrivilegeIdentifiersWithPermission = $result->getEffectivePrivilegeIdentifiersWithPermission();
        if ($effectivePrivilegeIdentifiersWithPermission === []) {
            $reason = sprintf('No privilege of type "%s" matched.', $privilegeType);
            return true;
        }

        $reason = sprintf('Evaluated following %d privilege target(s):' . chr(10) . '%s' . chr(10) . '(%d granted, %d denied, %d abstained)',
            count($effectivePrivilegeIdentifiersWithPermission),
            implode(chr(10), $effectivePrivilegeIdentifiersWithPermission),
            $result->getGrants(),
            $result->getDenies(),
            $result->getAbstains()
        );

        if ($result->getDenies() > 0) {
            return false;
        }
        if ($result->getGrants() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if access is granted on the given privilege target in the current security context
     *
     * @param string $privilegeTargetIdentifier The identifier of the privilege target to decide on
     * @param array $privilegeParameters Optional array of privilege parameters (simple key => value array)
     * @return boolean true if access is granted, false otherwise
     */
    public function isPrivilegeTargetGranted($privilegeTargetIdentifier, array $privilegeParameters = [])
    {
        return $this->isPrivilegeTargetGrantedForRoles($this->securityContext->getRoles(), $privilegeTargetIdentifier, $privilegeParameters);
    }

    /**
     * Returns true if access is granted on the given privilege target in the current security context
     *
     * @param array<Role> $roles The roles that should be evaluated
     * @param string $privilegeTargetIdentifier The identifier of the privilege target to decide on
     * @param array $privilegeParameters Optional array of privilege parameters (simple key => value array)
     * @return boolean true if access is granted, false otherwise
     */
    public function isPrivilegeTargetGrantedForRoles(array $roles, $privilegeTargetIdentifier, array $privilegeParameters = [])
    {
        $privilegeMapper = function (Role $role) use ($privilegeTargetIdentifier, $privilegeParameters) {
            return $role->getPrivilegeForTarget($privilegeTargetIdentifier, $privilegeParameters);
        };

        $privileges = array_map($privilegeMapper, $roles);
        /** @var PrivilegePermissionResult $result */
        $result = array_reduce($privileges, [$this, 'applyPrivilegeToResult'], new PrivilegePermissionResult());

        if ($result->getDenies() === 0 && $result->getGrants() > 0) {
            return true;
        }

        $privilegeFound = $privileges !== [];
        if ($result->getDenies() === 0 && $result->getGrants() === 0 && $privilegeFound === true && $this->allowAccessIfAllAbstain === true) {
            return true;
        }

        return false;
    }

    /**
     * @param PrivilegePermissionResult $result
     * @param PrivilegeInterface|null $privilege
     * @return PrivilegePermissionResult
     */
    protected function applyPrivilegeToResult(PrivilegePermissionResult $result, PrivilegeInterface $privilege = null): PrivilegePermissionResult
    {
        return $result->withPrivilege($privilege);
    }

    /**
     * @param string $privilegeType
     * @return \Closure
     */
    protected function getPrivilegeByTypeReducer(string $privilegeType): \Closure
    {
        return function (array $availablePrivileges, Role $role) use ($privilegeType) {
            return array_merge($availablePrivileges, $role->getPrivilegesByType($privilegeType));
        };
    }

    /**
     * @param mixed $subject
     * @return \Closure
     */
    protected function getPrivilegeSubjectFilter($subject): \Closure
    {
        return function (PrivilegeInterface $privilege) use ($subject) {
            return $privilege->matchesSubject($subject);
        };
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Security\Authorization;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * An access decision voter manager
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class PrivilegeManager extends PrivilegeManager_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     * @param ObjectManagerInterface $objectManager The object manager
     * @param Context $securityContext The current security context
     */
    public function __construct()
    {
        $arguments = func_get_args();

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        if (get_class($this) === 'Neos\Flow\Security\Authorization\PrivilegeManager') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authorization\PrivilegeManager', $this);
        if (get_class($this) === 'Neos\Flow\Security\Authorization\PrivilegeManager') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authorization\PrivilegeManagerInterface', $this);

        if (!array_key_exists(0, $arguments)) $arguments[0] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface');
        if (!array_key_exists(1, $arguments)) $arguments[1] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Context');
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $objectManager in class ' . __CLASS__ . '. Please check your calling code and Dependency Injection configuration.', 1296143787);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $securityContext in class ' . __CLASS__ . '. Please check your calling code and Dependency Injection configuration.', 1296143787);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Flow\Security\Authorization\PrivilegeManager' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }
    }

    /**
     * Autogenerated Proxy Method
     */
    protected function Flow_Aop_Proxy_buildMethodsAndAdvicesArray()
    {
        if (method_exists(get_parent_class(), 'Flow_Aop_Proxy_buildMethodsAndAdvicesArray') && is_callable('parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray')) parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        $objectManager = \Neos\Flow\Core\Bootstrap::$staticObjectManager;
        $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array(
            'isPrivilegeTargetGranted' => array(
                'Neos\Flow\Aop\Advice\AfterAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterAdvice('Neos\Flow\Security\Aspect\LoggingAspect', 'logPrivilegeAccessDecisions', $objectManager, NULL),
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
        if (get_class($this) === 'Neos\Flow\Security\Authorization\PrivilegeManager') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authorization\PrivilegeManager', $this);
        if (get_class($this) === 'Neos\Flow\Security\Authorization\PrivilegeManager') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authorization\PrivilegeManagerInterface', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
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
     * @param string $privilegeTargetIdentifier The identifier of the privilege target to decide on
     * @param array $privilegeParameters Optional array of privilege parameters (simple key => value array)
     * @return boolean true if access is granted, false otherwise
     */
    public function isPrivilegeTargetGranted($privilegeTargetIdentifier, array $privilegeParameters = array())
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['isPrivilegeTargetGranted'])) {
            $result = parent::isPrivilegeTargetGranted($privilegeTargetIdentifier, $privilegeParameters);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['isPrivilegeTargetGranted'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['privilegeTargetIdentifier'] = $privilegeTargetIdentifier;
                $methodArguments['privilegeParameters'] = $privilegeParameters;
            
        $result = NULL;
        $afterAdviceInvoked = false;
        try {

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authorization\PrivilegeManager', 'isPrivilegeTargetGranted', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['isPrivilegeTargetGranted']['Neos\Flow\Aop\Advice\AfterAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['isPrivilegeTargetGranted']['Neos\Flow\Aop\Advice\AfterAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authorization\PrivilegeManager', 'isPrivilegeTargetGranted', $methodArguments, NULL, $result);
                    $afterAdviceInvoked = true;
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {

                if (!$afterAdviceInvoked && isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['isPrivilegeTargetGranted']['Neos\Flow\Aop\Advice\AfterAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['isPrivilegeTargetGranted']['Neos\Flow\Aop\Advice\AfterAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authorization\PrivilegeManager', 'isPrivilegeTargetGranted', $methodArguments, NULL, NULL, $exception);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }
                }

                throw $exception;
        }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['isPrivilegeTargetGranted']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['isPrivilegeTargetGranted']);
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
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'securityContext' => 'Neos\\Flow\\Security\\Context',
  'allowAccessIfAllAbstain' => 'boolean',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->allowAccessIfAllAbstain = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow.security.authorization.allowAccessIfAllVotersAbstain');
        $this->Flow_Injected_Properties = array (
  0 => 'allowAccessIfAllAbstain',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Framework/Neos.Flow/Classes/Security/Authorization/PrivilegeManager.php
#