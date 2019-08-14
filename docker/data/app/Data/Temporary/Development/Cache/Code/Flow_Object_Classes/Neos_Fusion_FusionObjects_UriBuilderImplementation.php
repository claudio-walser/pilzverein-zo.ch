<?php 
namespace Neos\Fusion\FusionObjects;

/*
 * This file is part of the Neos.Fusion package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */


/**
 * A Fusion UriBuilder object
 *
 * The following TS properties are evaluated:
 *  * package
 *  * subpackage
 *  * controller
 *  * action
 *  * arguments
 *  * format
 *  * section
 *  * additionalParams
 *  * addQueryString
 *  * argumentsToBeExcludedFromQueryString
 *  * absolute
 *
 * See respective getters for descriptions
 */
class UriBuilderImplementation_Original extends AbstractFusionObject
{
    /**
     * Key of the target package
     *
     * @return string
     */
    public function getPackage()
    {
        return $this->fusionValue('package');
    }

    /**
     * Key of the target sub package
     *
     * @return string
     */
    public function getSubpackage()
    {
        return $this->fusionValue('subpackage');
    }

    /**
     * Target controller name
     *
     * @return string
     */
    public function getController()
    {
        return $this->fusionValue('controller');
    }

    /**
     * Target controller action name
     *
     * @return string
     */
    public function getAction()
    {
        return $this->fusionValue('action');
    }

    /**
     * Controller arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->fusionValue('arguments');
    }

    /**
     * The requested format, for example "html"
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->fusionValue('format');
    }

    /**
     * The anchor to be appended to the URL
     *
     * @return string
     */
    public function getSection()
    {
        return $this->fusionValue('section');
    }

    /**
     * Additional query parameters that won't be prefixed like $arguments (overrule $arguments)
     *
     * @return array
     */
    public function getAdditionalParams()
    {
        return $this->fusionValue('additionalParams');
    }

    /**
     * Arguments to be removed from the URI. Only active if addQueryString = true
     *
     * @return array
     */
    public function getArgumentsToBeExcludedFromQueryString()
    {
        return $this->fusionValue('argumentsToBeExcludedFromQueryString');
    }

    /**
     * If true, the current query parameters will be kept in the URI
     *
     * @return boolean
     */
    public function isAddQueryString()
    {
        return (boolean)$this->fusionValue('addQueryString');
    }

    /**
     * If true, an absolute URI is rendered
     *
     * @return boolean
     */
    public function isAbsolute()
    {
        return (boolean)$this->fusionValue('absolute');
    }

    /**
     * @return string
     */
    public function evaluate()
    {
        $controllerContext = $this->runtime->getControllerContext();
        $uriBuilder = $controllerContext->getUriBuilder()->reset();

        $format = $this->getFormat();
        if ($format !== null) {
            $uriBuilder->setFormat($format);
        }

        $additionalParams = $this->getAdditionalParams();
        if ($additionalParams !== null) {
            $uriBuilder->setArguments($additionalParams);
        }

        $argumentsToBeExcludedFromQueryString = $this->getArgumentsToBeExcludedFromQueryString();
        if ($argumentsToBeExcludedFromQueryString !== null) {
            $uriBuilder->setArgumentsToBeExcludedFromQueryString($argumentsToBeExcludedFromQueryString);
        }

        $absolute = $this->isAbsolute();
        if ($absolute === true) {
            $uriBuilder->setCreateAbsoluteUri(true);
        }

        $section = $this->getSection();
        if ($section !== null) {
            $uriBuilder->setSection($section);
        }

        $addQueryString = $this->isAddQueryString();
        if ($addQueryString === true) {
            $uriBuilder->setAddQueryString(true);
        }

        try {
            return $uriBuilder->uriFor(
                $this->getAction(),
                $this->getArguments(),
                $this->getController(),
                $this->getPackage(),
                $this->getSubpackage()
            );
        } catch (\Exception $exception) {
            return $this->runtime->handleRenderingException($this->path, $exception);
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Fusion\FusionObjects;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A Fusion UriBuilder object
 * 
 * The following TS properties are evaluated:
 *  * package
 *  * subpackage
 *  * controller
 *  * action
 *  * arguments
 *  * format
 *  * section
 *  * additionalParams
 *  * addQueryString
 *  * argumentsToBeExcludedFromQueryString
 *  * absolute
 * 
 * See respective getters for descriptions
 */
class UriBuilderImplementation extends UriBuilderImplementation_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param Runtime $runtime
     * @param string $path
     * @param string $fusionObjectName
     */
    public function __construct()
    {
        $arguments = func_get_args();

        if (!array_key_exists(0, $arguments)) $arguments[0] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Fusion\Core\Runtime');
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $runtime in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $path in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(2, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $fusionObjectName in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
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
  'runtime' => 'Neos\\Fusion\\Core\\Runtime',
  'path' => 'string',
  'fusionObjectName' => 'string',
  'tsValueCache' => 'array',
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
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Fusion/Classes/FusionObjects/UriBuilderImplementation.php
#