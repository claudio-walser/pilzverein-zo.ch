<?php 
namespace Neos\Flow\Http;

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
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Http\Component\ComponentChain;
use Neos\Flow\Http\Component\ComponentContext;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Package\PackageManager;
use Psr\Http\Message\ResponseInterface;

/**
 * A request handler which can handle HTTP requests.
 *
 * @Flow\Scope("singleton")
 */
class RequestHandler_Original implements HttpRequestHandlerInterface
{
    /**
     * @var Bootstrap
     */
    protected $bootstrap;

    /**
     * @var Component\ComponentChain
     */
    protected $baseComponentChain;

    /**
     * @var Component\ComponentContext
     */
    protected $componentContext;

    /**
     * Make exit() a closure so it can be manipulated during tests
     *
     * @var \Closure
     */
    public $exit;

    /**
     * @param Bootstrap $bootstrap
     */
    public function __construct(Bootstrap $bootstrap)
    {
        $this->bootstrap = $bootstrap;
        $this->exit = function () {
            exit();
        };
    }

    /**
     * This request handler can handle any web request.
     *
     * @return boolean If the request is a web request, true otherwise false
     * @api
     */
    public function canHandleRequest()
    {
        return (PHP_SAPI !== 'cli');
    }

    /**
     * Returns the priority - how eager the handler is to actually handle the
     * request.
     *
     * @return integer The priority of the request handler.
     * @api
     */
    public function getPriority()
    {
        return 100;
    }

    /**
     * Handles a HTTP request
     *
     * @return void
     */
    public function handleRequest()
    {
        // Create the request very early so the ResourceManagement has a chance to grab it:
        $request = Request::createFromEnvironment();
        $response = new Response();
        $this->componentContext = new ComponentContext($request, $response);

        $this->boot();
        $this->resolveDependencies();
        $response = $this->addPoweredByHeader($response);
        $this->componentContext->replaceHttpResponse($response);
        $baseUriSetting = $this->bootstrap->getObjectManager()->get(ConfigurationManager::class)->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'Neos.Flow.http.baseUri');
        if (!empty($baseUriSetting)) {
            $baseUri = new Uri($baseUriSetting);
            $request = $request->withAttribute(Request::ATTRIBUTE_BASE_URI, $baseUri);
            $this->componentContext->replaceHttpRequest($request);
        }

        $this->baseComponentChain->handle($this->componentContext);
        $response = $this->baseComponentChain->getResponse();

        $response->send();

        $this->bootstrap->shutdown(Bootstrap::RUNLEVEL_RUNTIME);
        $this->exit->__invoke();
    }

    /**
     * Returns the currently handled HTTP request
     *
     * @return Request
     * @api
     */
    public function getHttpRequest()
    {
        return $this->componentContext->getHttpRequest();
    }

    /**
     * Returns the HTTP response corresponding to the currently handled request
     *
     * @return Response
     * @api
     */
    public function getHttpResponse()
    {
        return $this->componentContext->getHttpResponse();
    }

    /**
     * Boots up Flow to runtime
     *
     * @return void
     */
    protected function boot()
    {
        $sequence = $this->bootstrap->buildRuntimeSequence();
        $sequence->invoke($this->bootstrap);
    }

    /**
     * Resolves a few dependencies of this request handler which can't be resolved
     * automatically due to the early stage of the boot process this request handler
     * is invoked at.
     *
     * @return void
     */
    protected function resolveDependencies()
    {
        $objectManager = $this->bootstrap->getObjectManager();
        $this->baseComponentChain = $objectManager->get(ComponentChain::class);
    }

    /**
     * Adds an HTTP header to the Response which indicates that the application is powered by Flow.
     *
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws \Neos\Flow\Exception
     */
    protected function addPoweredByHeader(ResponseInterface $response): ResponseInterface
    {
        $token = static::prepareApplicationToken($this->bootstrap->getObjectManager());
        if ($token === '') {
            return $response;
        }

        return $response->withAddedHeader('X-Flow-Powered', $token);
    }

    /**
     * Renders a major version out of a full version string
     *
     * @param string $version For example "2.3.7"
     * @return string For example "2"
     */
    protected static function renderMajorVersion($version)
    {
        preg_match('/^(\d+)/', $version, $versionMatches);
        return isset($versionMatches[1]) ? $versionMatches[1] : '';
    }

    /**
     * Renders a minor version out of a full version string
     *
     * @param string $version For example "2.3.7"
     * @return string For example "2.3"
     */
    protected static function renderMinorVersion($version)
    {
        preg_match('/^(\d+\.\d+)/', $version, $versionMatches);
        return isset($versionMatches[1]) ? $versionMatches[1] : '';
    }

    /**
     * Generate an application information header for the response based on settings and package versions.
     * Will statically compile in production for performance benefits.
     *
     * @param ObjectManagerInterface $objectManager
     * @return string
     * @throws \Neos\Flow\Configuration\Exception\InvalidConfigurationTypeException
     * @Flow\CompileStatic
     */
    public static function prepareApplicationToken(ObjectManagerInterface $objectManager): string
    {
        $configurationManager = $objectManager->get(ConfigurationManager::class);
        $tokenSetting = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'Neos.Flow.http.applicationToken');

        if (!in_array($tokenSetting, ['ApplicationName', 'MinorVersion', 'MajorVersion'])) {
            return '';
        }

        $applicationPackageKey = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'Neos.Flow.core.applicationPackageKey');
        $applicationName = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'Neos.Flow.core.applicationName');
        $applicationIsNotFlow = ($applicationPackageKey !== 'Neos.Flow');

        if ($tokenSetting === 'ApplicationName') {
            return 'Flow' . ($applicationIsNotFlow ? ' ' . $applicationName : '');
        }

        $packageManager = $objectManager->get(PackageManager::class);
        $flowPackage = $packageManager->getPackage('Neos.Flow');
        $applicationPackage = $applicationIsNotFlow ? $packageManager->getPackage($applicationPackageKey) : null;

        // At this point the $tokenSetting must be either "MinorVersion" or "MajorVersion" so lets use it.

        $versionRenderer = 'render' . $tokenSetting;
        $flowVersion = static::$versionRenderer($flowPackage->getInstalledVersion());
        $applicationVersion = $applicationIsNotFlow ? static::$versionRenderer($applicationPackage->getInstalledVersion()) : null;

        return 'Flow/' . ($flowVersion ?: 'dev') . ($applicationIsNotFlow ? ' ' . $applicationName . '/' . ($applicationVersion ?: 'dev') : '');
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Http;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A request handler which can handle HTTP requests.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class RequestHandler extends RequestHandler_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param Bootstrap $bootstrap
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (get_class($this) === 'Neos\Flow\Http\RequestHandler') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Http\RequestHandler', $this);

        if (!array_key_exists(0, $arguments)) $arguments[0] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Core\Bootstrap');
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $bootstrap in class ' . __CLASS__ . '. Please check your calling code and Dependency Injection configuration.', 1296143787);
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
  'bootstrap' => 'Neos\\Flow\\Core\\Bootstrap',
  'baseComponentChain' => 'Neos\\Flow\\Http\\Component\\ComponentChain',
  'componentContext' => 'Neos\\Flow\\Http\\Component\\ComponentContext',
  'exit' => '\\Closure',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Flow\Http\RequestHandler') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Http\RequestHandler', $this);

        $this->Flow_setRelatedEntities();
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Framework/Neos.Flow/Classes/Http/RequestHandler.php
#