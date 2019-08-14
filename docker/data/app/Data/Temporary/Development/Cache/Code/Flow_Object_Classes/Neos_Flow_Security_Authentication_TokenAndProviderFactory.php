<?php 
namespace Neos\Flow\Security\Authentication;

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
use Neos\Flow\Security\Exception;
use Neos\Flow\Security\RequestPatternInterface;
use Neos\Flow\Security\RequestPatternResolver;

/**
 * Default factory for providers and tokens.
 *
 * @Flow\Scope("singleton")
 */
class TokenAndProviderFactory_Original implements TokenAndProviderFactoryInterface
{
    /**
     * @var bool
     */
    protected $isInitialized = false;

    /**
     * @var AuthenticationProviderInterface[]
     */
    protected $providers = [];

    /**
     * @var TokenInterface[]
     */
    protected $tokens = [];

    /**
     * @var array
     */
    protected $providerConfigurations = [];

    /**
     * @var AuthenticationProviderResolver
     */
    protected $providerResolver;

    /**
     * @var RequestPatternResolver
     */
    protected $requestPatternResolver;

    /**
     * @param AuthenticationProviderResolver $providerResolver The provider resolver
     * @param RequestPatternResolver $requestPatternResolver The request pattern resolver
     */
    public function __construct(AuthenticationProviderResolver $providerResolver, RequestPatternResolver $requestPatternResolver)
    {
        $this->providerResolver = $providerResolver;
        $this->requestPatternResolver = $requestPatternResolver;
    }

    /**
     * Returns clean tokens this manager is responsible for.
     * Note: The order of the tokens in the array is important, as the tokens will be authenticated in the given order.
     *
     * @return TokenInterface[]
     * @throws Exception\InvalidAuthenticationProviderException
     * @throws Exception\InvalidRequestPatternException
     * @throws Exception\NoAuthenticationProviderFoundException
     * @throws Exception\NoEntryPointFoundException
     * @throws Exception\NoRequestPatternFoundException
     */
    public function getTokens(): array
    {
        $this->buildProvidersAndTokensFromConfiguration();
        return $this->tokens;
    }

    /**
     * Returns all configured authentication providers
     *
     * @return AuthenticationProviderInterface[]
     * @throws Exception\InvalidAuthenticationProviderException
     * @throws Exception\InvalidRequestPatternException
     * @throws Exception\NoAuthenticationProviderFoundException
     * @throws Exception\NoEntryPointFoundException
     * @throws Exception\NoRequestPatternFoundException
     */
    public function getProviders(): array
    {
        $this->buildProvidersAndTokensFromConfiguration();
        return $this->providers;
    }

    /**
     * Inject the settings and does a fresh build of tokens based on the injected settings
     *
     * @param array $settings The settings
     * @return void
     * @throws Exception
     */
    public function injectSettings(array $settings)
    {
        if (!isset($settings['security']['authentication']['providers']) || !is_array($settings['security']['authentication']['providers'])) {
            return;
        }

        $this->providerConfigurations = $settings['security']['authentication']['providers'];
    }

    /**
     * Builds the provider and token objects based on the given configuration
     *
     * @return void
     * @throws Exception\InvalidAuthenticationProviderException
     * @throws Exception\InvalidRequestPatternException
     * @throws Exception\NoAuthenticationProviderFoundException
     * @throws Exception\NoEntryPointFoundException
     * @throws Exception\NoRequestPatternFoundException
     */
    protected function buildProvidersAndTokensFromConfiguration()
    {
        if ($this->isInitialized) {
            return;
        }

        $this->tokens = [];
        $this->providers = [];

        foreach ($this->providerConfigurations as $providerName => $providerConfiguration) {
            if (!is_array($providerConfiguration) || !isset($providerConfiguration['provider'])) {
                throw new Exception\InvalidAuthenticationProviderException('The configured authentication provider "' . $providerName . '" needs a "provider" option!', 1248209521);
            }

            $providerObjectName = $this->providerResolver->resolveProviderClass((string)$providerConfiguration['provider']);
            if ($providerObjectName === null) {
                throw new Exception\InvalidAuthenticationProviderException('The configured authentication provider "' . $providerConfiguration['provider'] . '" could not be found!', 1237330453);
            }
            $providerOptions = [];
            if (isset($providerConfiguration['providerOptions']) && is_array($providerConfiguration['providerOptions'])) {
                $providerOptions = $providerConfiguration['providerOptions'];
            }

            /** @var $providerInstance AuthenticationProviderInterface */
            $providerInstance = $providerObjectName::create($providerName, $providerOptions);
            $this->providers[$providerName] = $providerInstance;

            /** @var $tokenInstance TokenInterface */
            $tokenInstance = null;
            foreach ($providerInstance->getTokenClassNames() as $tokenClassName) {
                if (isset($providerConfiguration['token']) && $providerConfiguration['token'] !== $tokenClassName) {
                    continue;
                }

                $tokenInstance = new $tokenClassName();
                $tokenInstance->setAuthenticationProviderName($providerName);
                $this->tokens[] = $tokenInstance;
                break;
            }

            if (isset($providerConfiguration['requestPatterns']) && is_array($providerConfiguration['requestPatterns'])) {
                $requestPatterns = [];
                foreach ($providerConfiguration['requestPatterns'] as $patternName => $patternConfiguration) {
                    // skip request patterns that are set to NULL (i.e. `somePattern: ~` in a YAML file)
                    if ($patternConfiguration === null) {
                        continue;
                    }

                    $patternType = $patternConfiguration['pattern'];
                    $patternOptions = isset($patternConfiguration['patternOptions']) ? $patternConfiguration['patternOptions'] : [];
                    $patternClassName = $this->requestPatternResolver->resolveRequestPatternClass($patternType);
                    $requestPattern = new $patternClassName($patternOptions);
                    if (!$requestPattern instanceof RequestPatternInterface) {
                        throw new Exception\InvalidRequestPatternException(sprintf('Invalid request pattern configuration in setting "Neos:Flow:security:authentication:providers:%s": Class "%s" does not implement RequestPatternInterface', $providerName, $patternClassName), 1446222774);
                    }

                    $requestPatterns[] = $requestPattern;
                }
                if ($tokenInstance !== null) {
                    $tokenInstance->setRequestPatterns($requestPatterns);
                }
            }

            if (isset($providerConfiguration['entryPoint'])) {
                if (is_array($providerConfiguration['entryPoint'])) {
                    $message = 'Invalid entry point configuration in setting "Neos:Flow:security:authentication:providers:' . $providerName . '. Check your settings and make sure to specify only one entry point for each provider.';
                    throw new Exception\InvalidAuthenticationProviderException($message, 1327671458);
                }
                $entryPointName = $providerConfiguration['entryPoint'];
                $entryPointClassName = $entryPointName;
                if (!class_exists($entryPointClassName)) {
                    $entryPointClassName = 'Neos\Flow\Security\Authentication\EntryPoint\\' . $entryPointClassName;
                }
                if (!class_exists($entryPointClassName)) {
                    throw new Exception\NoEntryPointFoundException('An entry point with the name: "' . $entryPointName . '" could not be resolved. Make sure it is a valid class name, either fully qualified or relative to Neos\Flow\Security\Authentication\EntryPoint!', 1236767282);
                }

                /** @var $entryPoint EntryPointInterface */
                $entryPoint = new $entryPointClassName();
                if (isset($providerConfiguration['entryPointOptions'])) {
                    $entryPoint->setOptions($providerConfiguration['entryPointOptions']);
                }

                $tokenInstance->setAuthenticationEntryPoint($entryPoint);
            }
        }

        $this->isInitialized = true;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Security\Authentication;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Default factory for providers and tokens.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class TokenAndProviderFactory extends TokenAndProviderFactory_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param AuthenticationProviderResolver $providerResolver The provider resolver
     * @param RequestPatternResolver $requestPatternResolver The request pattern resolver
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (get_class($this) === 'Neos\Flow\Security\Authentication\TokenAndProviderFactory') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authentication\TokenAndProviderFactory', $this);
        if (get_class($this) === 'Neos\Flow\Security\Authentication\TokenAndProviderFactory') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authentication\TokenAndProviderFactoryInterface', $this);

        if (!array_key_exists(0, $arguments)) $arguments[0] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Authentication\AuthenticationProviderResolver');
        if (!array_key_exists(1, $arguments)) $arguments[1] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\RequestPatternResolver');
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $providerResolver in class ' . __CLASS__ . '. Please check your calling code and Dependency Injection configuration.', 1296143787);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $requestPatternResolver in class ' . __CLASS__ . '. Please check your calling code and Dependency Injection configuration.', 1296143787);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Flow\Security\Authentication\TokenAndProviderFactory' === get_class($this)) {
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
  'isInitialized' => 'boolean',
  'providers' => 'array<Neos\\Flow\\Security\\Authentication\\AuthenticationProviderInterface>',
  'tokens' => 'array<Neos\\Flow\\Security\\Authentication\\TokenInterface>',
  'providerConfigurations' => 'array',
  'providerResolver' => 'Neos\\Flow\\Security\\Authentication\\AuthenticationProviderResolver',
  'requestPatternResolver' => 'Neos\\Flow\\Security\\RequestPatternResolver',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Flow\Security\Authentication\TokenAndProviderFactory') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authentication\TokenAndProviderFactory', $this);
        if (get_class($this) === 'Neos\Flow\Security\Authentication\TokenAndProviderFactory') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authentication\TokenAndProviderFactoryInterface', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow'));
        $this->Flow_Injected_Properties = array (
  0 => 'settings',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Framework/Neos.Flow/Classes/Security/Authentication/TokenAndProviderFactory.php
#