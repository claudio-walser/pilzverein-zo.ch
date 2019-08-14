<?php 
namespace Neos\Flow\Log;

/**
 * Helps to streamline the configuration for the default logger.
 */
class LoggerBackendConfigurationHelper_Original
{
    /**
     * @var array
     */
    protected $legacyConfiguration;

    /**
     * LoggerBackendConfigurationHelper constructor.
     *
     * @param array $logConfiguration
     */
    public function __construct(array $logConfiguration)
    {
        $this->legacyConfiguration = $logConfiguration;
    }

    /**
     * Normalize a backend configuration to a unified format.
     *
     * @return array
     */
    public function getNormalizedLegacyConfiguration(): array
    {
        $normalizedConfiguration = [];
        foreach ($this->legacyConfiguration as $logIdentifier => $configuration) {
            // Skip everything that is not an actual log configuration.
            if (!isset($configuration['backend'])) {
                continue;
            }

            $backendObjectNames = $configuration['backend'];
            $backendOptions = $configuration['backendOptions'] ?? [];
            $normalizedConfiguration[$logIdentifier] = $this->mapLoggerConfiguration($backendObjectNames, $backendOptions);
        }

        return $normalizedConfiguration;
    }

    /**
     * @param mixed $backendObjectNames
     * @param array $backendOptions
     * @return array
     */
    protected function mapLoggerConfiguration($backendObjectNames, array $backendOptions): array
    {
        if (!is_array($backendObjectNames)) {
            return [$this->mapBackendInformation($backendObjectNames, $backendOptions)];
        }

        $backends = [];
        foreach ($backendObjectNames as $i => $backendObjectName) {
            if (isset($backendOptions[$i])) {
                $backends[] = $this->mapBackendInformation($backendObjectName, $backendOptions[$i]);
            }
        }

        return $backends;
    }

    /**
     * Map a backend object name and it's options into an array with defined keys.
     *
     * @param string $backendObjectName
     * @param array $backendOptions
     * @return array
     */
    protected function mapBackendInformation(string $backendObjectName, array $backendOptions): array
    {
        return [
            'class' => $backendObjectName,
            'options' => $backendOptions
        ];
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Log;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Helps to streamline the configuration for the default logger.
 */
class LoggerBackendConfigurationHelper extends LoggerBackendConfigurationHelper_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param array $logConfiguration
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $logConfiguration in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
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
  'legacyConfiguration' => 'array',
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
# PathAndFilename: /var/www/html/neos/Packages/Framework/Neos.Flow/Classes/Log/LoggerBackendConfigurationHelper.php
#