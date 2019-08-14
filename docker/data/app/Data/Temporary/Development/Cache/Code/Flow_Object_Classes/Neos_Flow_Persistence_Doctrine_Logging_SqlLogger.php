<?php 
namespace Neos\Flow\Persistence\Doctrine\Logging;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Log\LoggerInterface;

/**
 * A SQL logger that logs to a Flow logger.
 *
 */
class SqlLogger_Original implements \Doctrine\DBAL\Logging\SQLLogger
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Logs a SQL statement to the system logger (DEBUG priority).
     *
     * @param string $sql The SQL to be executed
     * @param array $params The SQL parameters
     * @param array $types The SQL parameter types.
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        // this is a safeguard for when no logger might be available...
        if ($this->logger !== null) {
            $this->logger->log($sql, LOG_DEBUG, ['params' => $params, 'types' => $types]);
        }
    }

    /**
     * @return void
     */
    public function stopQuery()
    {
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Persistence\Doctrine\Logging;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A SQL logger that logs to a Flow logger.
 */
class SqlLogger extends SqlLogger_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\Flow\Persistence\Doctrine\Logging\SqlLogger' === get_class($this)) {
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
  'logger' => 'Neos\\Flow\\Log\\LoggerInterface',
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
        $this->Flow_Proxy_LazyPropertyInjection('', '', 'logger', 'ff45195ee1d1ff2bc452fdd42a1e07e8', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\LoggerFactory')->create('Sql_Queries', 'Neos\\Flow\\Log\\Logger', 'Neos\\Flow\\Log\\Backend\\FileBackend', \Neos\Flow\Core\Bootstrap::$staticObjectManager->getSettingsByPath(explode('.', 'Neos.Flow.log.sqlLogger.backendOptions'))); });
        $this->Flow_Injected_Properties = array (
  0 => 'logger',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Framework/Neos.Flow/Classes/Persistence/Doctrine/Logging/SqlLogger.php
#