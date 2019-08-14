<?php 

namespace Flowpack\Neos\FrontendLogin\Security;

/*
 * This file is part of the Flowpack.Neos.FrontendLogin package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\RequestInterface;
use Neos\Flow\Security\RequestPatternInterface;

/**
 * A request pattern that can detect and match "frontend" and "backend" mode
 */
class NeosRequestPattern_Original implements RequestPatternInterface
{

    /**
     * @var array
     */
    protected $options;

    /**
     * Expects options in the form array('matchFrontend' => TRUE/FALSE)
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * Matches a \Neos\Flow\Mvc\RequestInterface against its set pattern rules
     *
     * @param RequestInterface $request The request that should be matched
     * @return boolean TRUE if the pattern matched, FALSE otherwise
     */
    public function matchRequest(RequestInterface $request)
    {
        if (!$request instanceof ActionRequest) {
            return false;
        }
        $shouldMatchFrontend = isset($this->options['matchFrontend']) && $this->options['matchFrontend'] === true;
        $requestPath = $request->getHttpRequest()->getUri()->getPath();
        $requestPathMatchesBackend = substr($requestPath, 0, 5) === '/neos' || strpos($requestPath, '@') !== false;
        return $shouldMatchFrontend !== $requestPathMatchesBackend;
    }

}

#
# Start of Flow generated Proxy code
#
namespace Flowpack\Neos\FrontendLogin\Security;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A request pattern that can detect and match "frontend" and "backend" mode
 */
class NeosRequestPattern extends NeosRequestPattern_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param array $options
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $options in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
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
  'options' => 'array',
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
# PathAndFilename: /var/www/html/neos/Packages/Plugins/Flowpack.Neos.FrontendLogin/Classes/Flowpack/Neos/FrontendLogin/Security/NeosRequestPattern.php
#