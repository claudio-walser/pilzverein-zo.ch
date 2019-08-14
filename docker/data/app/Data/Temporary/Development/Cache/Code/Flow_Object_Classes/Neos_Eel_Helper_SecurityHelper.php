<?php 
namespace Neos\Eel\Helper;

/*
 * This file is part of the Neos.Eel package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Security\Account;
use Neos\Flow\Security\Context as SecurityContext;
use Neos\Flow\Security\Authorization\PrivilegeManagerInterface;
use Neos\Flow\Security\Authentication\TokenInterface;

/**
 * Helper for security related information
 *
 */
class SecurityHelper_Original implements ProtectedContextAwareInterface
{
    /**
     * @Flow\Inject
     * @var SecurityContext
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var PrivilegeManagerInterface
     */
    protected $privilegeManager;

    /**
     * Get the account of the first authenticated token.
     *
     * @return Account|NULL
     */
    public function getAccount()
    {
        if ($this->securityContext->canBeInitialized()) {
            return $this->securityContext->getAccount();
        }

        return null;
    }

    /**
     * Returns CSRF token which is required for "unsafe" requests (e.g. POST, PUT, DELETE, ...)
     *
     * @return string
     */
    public function csrfToken(): string
    {
        return $this->securityContext->getCsrfProtectionToken();
    }

    /**
     * Returns true, if any account is currently authenticated
     *
     * @return boolean true if any account is authenticated
     */
    public function isAuthenticated(): bool
    {
        if (!$this->securityContext->canBeInitialized()) {
            return false;
        }

        return array_reduce($this->securityContext->getAuthenticationTokens(), function (bool $isAuthenticated, TokenInterface $token) {
            return $isAuthenticated || $token->isAuthenticated();
        }, false);
    }

    /**
     * Returns true, if access to the given privilege-target is granted
     *
     * @param string $privilegeTarget The identifier of the privilege target to decide on
     * @param array $parameters Optional array of privilege parameters (simple key => value array)
     * @return boolean true if access is granted, false otherwise
     */
    public function hasAccess(string $privilegeTarget, array $parameters = []): bool
    {
        if (!$this->securityContext->canBeInitialized()) {
            return false;
        }
        return $this->privilegeManager->isPrivilegeTargetGranted($privilegeTarget, $parameters);
    }

    /**
     * Returns true, if at least one of the currently authenticated accounts holds
     * a role with the given identifier, also recursively.
     *
     * @param string $roleIdentifier The string representation of the role to search for
     * @return boolean true, if a role with the given string representation was found
     */
    public function hasRole($roleIdentifier)
    {
        if ($roleIdentifier === 'Neos.Flow:Everybody') {
            return true;
        }

        if ($this->securityContext->canBeInitialized()) {
            return $this->securityContext->hasRole($roleIdentifier);
        }

        return false;
    }

    /**
     * All methods are considered safe
     *
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Eel\Helper;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Helper for security related information
 */
class SecurityHelper extends SecurityHelper_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\Eel\Helper\SecurityHelper' === get_class($this)) {
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
  'securityContext' => 'Neos\\Flow\\Security\\Context',
  'privilegeManager' => 'Neos\\Flow\\Security\\Authorization\\PrivilegeManagerInterface',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Context', 'Neos\Flow\Security\Context', 'securityContext', 'f7e2ddeaebd191e228b8c2e4dc7f1f83', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Context'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Authorization\PrivilegeManagerInterface', 'Neos\Flow\Security\Authorization\PrivilegeManager', 'privilegeManager', '68ada25ea2828278e185a684d1c86739', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Authorization\PrivilegeManagerInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'securityContext',
  1 => 'privilegeManager',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Framework/Neos.Eel/Classes/Helper/SecurityHelper.php
#