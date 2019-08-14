<?php 
namespace Neos\Neos\Fusion;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\ThrowableStorageInterface;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Neos\Service\LinkingService;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Neos\Exception as NeosException;
use Psr\Log\LoggerInterface;

/**
 * Create a link to a node
 */
class NodeUriImplementation_Original extends AbstractFusionObject
{
    /**
     * @Flow\Inject
     * @var LinkingService
     */
    protected $linkingService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ThrowableStorageInterface
     */
    private $throwableStorage;

    /**
     * @param LoggerInterface $logger
     */
    public function injectLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ThrowableStorageInterface $throwableStorage
     */
    public function injectThrowableStorage(ThrowableStorageInterface $throwableStorage)
    {
        $this->throwableStorage = $throwableStorage;
    }

    /**
     * A node object or a string node path or NULL to resolve the current document node
     *
     * @return mixed
     */
    public function getNode()
    {
        return $this->fusionValue('node');
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
        return (string)$this->fusionValue('section');
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
    public function getAddQueryString()
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
     * The name of the base node inside the Fusion context to use for resolving relative paths.
     *
     * @return string
     */
    public function getBaseNodeName()
    {
        return $this->fusionValue('baseNodeName');
    }

    /**
     * Render the Uri.
     *
     * @return string The rendered URI or NULL if no URI could be resolved for the given node
     * @throws NeosException
     */
    public function evaluate()
    {
        $baseNode = null;
        $baseNodeName = $this->getBaseNodeName() ?: 'documentNode';
        $currentContext = $this->runtime->getCurrentContext();
        if (isset($currentContext[$baseNodeName])) {
            $baseNode = $currentContext[$baseNodeName];
        } else {
            throw new NeosException(sprintf('Could not find a node instance in Fusion context with name "%s" and no node instance was given to the node argument. Set a node instance in the Fusion context or pass a node object to resolve the URI.', $baseNodeName), 1373100400);
        }

        try {
            return $this->linkingService->createNodeUri(
                $this->runtime->getControllerContext(),
                $this->getNode(),
                $baseNode,
                $this->getFormat(),
                $this->isAbsolute(),
                $this->getAdditionalParams(),
                $this->getSection(),
                $this->getAddQueryString(),
                $this->getArgumentsToBeExcludedFromQueryString()
            );
        } catch (NeosException $exception) {
            // TODO: Revisit if we actually need to store a stack trace.
            $logMessage = $this->throwableStorage->logThrowable($exception);
            $this->logger->error($logMessage, LogEnvironment::fromMethodName(__METHOD__));
            return '';
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Fusion;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Create a link to a node
 */
class NodeUriImplementation extends NodeUriImplementation_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


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
        if ('Neos\Neos\Fusion\NodeUriImplementation' === get_class($this)) {
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
  'linkingService' => 'Neos\\Neos\\Service\\LinkingService',
  'logger' => 'Psr\\Log\\LoggerInterface',
  'throwableStorage' => 'Neos\\Flow\\Log\\ThrowableStorageInterface',
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
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Psr\Log\LoggerInterface'));
        $this->injectThrowableStorage(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\ThrowableStorageInterface'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\LinkingService', 'Neos\Neos\Service\LinkingService', 'linkingService', '4473b90cfba243c7f02dd86c13d56fd2', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\LinkingService'); });
        $this->Flow_Injected_Properties = array (
  0 => 'logger',
  1 => 'throwableStorage',
  2 => 'linkingService',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Fusion/NodeUriImplementation.php
#