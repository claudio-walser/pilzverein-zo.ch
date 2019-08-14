<?php 
namespace Neos\Flow\Security\Authorization\Interceptor;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Aop\JoinPointInterface;
use Neos\Flow\Security\Authorization\AfterInvocationManagerInterface;
use Neos\Flow\Security\Authorization\InterceptorInterface;
use Neos\Flow\Security\Context;

/**
 * This is the second main security interceptor, which enforces the current security policy for return values and is usually applied over AOP:
 *
 * 1. We call the AfterInvocationManager with the method's return value as parameter
 * 2. If we had a "run as" support, we would have to reset the security context
 * 3. If a PermissionDeniedException was thrown we look for any an authentication entry point in the active tokens to redirect to authentication
 * 4. Then the value is returned to the caller
 *
 */
class AfterInvocation_Original implements InterceptorInterface
{
    /**
     * @var AfterInvocationManagerInterface
     */
    protected $afterInvocationManager = null;

    /**
     * Result of the (probably intercepted) target method
     * @var mixed
     */
    protected $result;

    /**
     * Constructor.
     *
     * @param Context $securityContext The current security context
     * @param AfterInvocationManagerInterface $afterInvocationManager The after invocation manager
     */
    public function __construct(Context $securityContext, AfterInvocationManagerInterface $afterInvocationManager)
    {
    }

    /**
     * Sets the current joinpoint for this interception
     *
     * @param JoinPointInterface $joinPoint The current joinpoint
     * @return void
     */
    public function setJoinPoint(JoinPointInterface $joinPoint)
    {
    }

    /**
     * Sets the result (return object) of the intercepted method
     *
     * @param mixed $result The result of the intercepted method
     * @return void
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * Invokes the security interception
     *
     * @return boolean true if the security checks was passed
     * @todo Implement interception logic
     */
    public function invoke()
    {
        return $this->result;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Security\Authorization\Interceptor;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * This is the second main security interceptor, which enforces the current security policy for return values and is usually applied over AOP:
 * 
 * 1. We call the AfterInvocationManager with the method's return value as parameter
 * 2. If we had a "run as" support, we would have to reset the security context
 * 3. If a PermissionDeniedException was thrown we look for any an authentication entry point in the active tokens to redirect to authentication
 * 4. Then the value is returned to the caller
 */
class AfterInvocation extends AfterInvocation_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param Context $securityContext The current security context
     * @param AfterInvocationManagerInterface $afterInvocationManager The after invocation manager
     */
    public function __construct()
    {
        $arguments = func_get_args();

        if (!array_key_exists(0, $arguments)) $arguments[0] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Context');
        if (!array_key_exists(1, $arguments)) $arguments[1] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Authorization\AfterInvocationManagerInterface');
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $securityContext in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $afterInvocationManager in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
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
  'afterInvocationManager' => 'Neos\\Flow\\Security\\Authorization\\AfterInvocationManagerInterface',
  'result' => 'mixed',
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
# PathAndFilename: /var/www/html/neos/Packages/Framework/Neos.Flow/Classes/Security/Authorization/Interceptor/AfterInvocation.php
#