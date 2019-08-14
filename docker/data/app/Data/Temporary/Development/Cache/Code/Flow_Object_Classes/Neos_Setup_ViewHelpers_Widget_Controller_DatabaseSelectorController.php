<?php 
namespace Neos\Setup\ViewHelpers\Widget\Controller;

/*
 * This file is part of the Neos.Setup package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\ConfigurationManager;

/**
 * Controller for the DatabaseSelector Fluid Widget
 */
class DatabaseSelectorController_Original extends \Neos\FluidAdaptor\Core\Widget\AbstractWidgetController
{
    /**
     * @Flow\Inject
     * @var ConfigurationManager
     */
    protected $configurationManager;

    const MINIMUM_MYSQL_VERSION = '5.7';
    const MINIMUM_MARIA_DB_VERSION = '10.2.2';

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->view->assign('driverDropdownFieldId', $this->widgetConfiguration['driverDropdownFieldId']);
        $this->view->assign('userFieldId', $this->widgetConfiguration['userFieldId']);
        $this->view->assign('passwordFieldId', $this->widgetConfiguration['passwordFieldId']);
        $this->view->assign('hostFieldId', $this->widgetConfiguration['hostFieldId']);
        $this->view->assign('dbNameTextFieldId', $this->widgetConfiguration['dbNameTextFieldId']);
        $this->view->assign('dbNameDropdownFieldId', $this->widgetConfiguration['dbNameDropdownFieldId']);
        $this->view->assign('statusContainerId', $this->widgetConfiguration['statusContainerId']);
        $this->view->assign('metadataStatusContainerId', $this->widgetConfiguration['metadataStatusContainerId']);
    }

    /**
     * @param string $driver
     * @param string $user
     * @param string $password
     * @param string $host
     * @return string
     */
    public function checkConnectionAction($driver, $user, $password, $host)
    {
        $this->response->setHeader('Content-Type', 'application/json');
        $connectionSettings = $this->buildConnectionSettingsArray($driver, $user, $password, $host);
        try {
            $connection = $this->getConnectionAndConnect($connectionSettings);
            $databases = $connection->getSchemaManager()->listDatabases();
            $result = ['success' => true, 'databases' => $databases];
        } catch (\PDOException $exception) {
            $result = ['success' => false, 'errorMessage' => $exception->getMessage(), 'errorCode' => $exception->getCode()];
        } catch (\Doctrine\DBAL\DBALException $exception) {
            $result = ['success' => false, 'errorMessage' => $exception->getMessage(), 'errorCode' => $exception->getCode()];
        } catch (\Exception $exception) {
            $result = ['success' => false, 'errorMessage' => 'Unexpected exception (check logs)', 'errorCode' => $exception->getCode()];
        }

        return json_encode($result);
    }

    /**
     * This fetches information about the database provided, in particular the charset being used.
     * Depending on whether it is utf8 or not, the (JSON-) response is layed out accordingly.
     *
     * @param string $driver
     * @param string $user
     * @param string $password
     * @param string $host
     * @param string $databaseName
     * @return string
     */
    public function getMetadataAction($driver, $user, $password, $host, $databaseName)
    {
        $this->response->setHeader('Content-Type', 'application/json');
        $connectionSettings = $this->buildConnectionSettingsArray($driver, $user, $password, $host);
        $connectionSettings['dbname'] = $databaseName;
        $result = [];
        try {
            $connection = $this->getConnectionAndConnect($connectionSettings);
            $databasePlatform = $connection->getDatabasePlatform();
            if ($databasePlatform instanceof MySqlPlatform) {
                $databaseVersionQueryResult = $connection->executeQuery('SELECT VERSION()')->fetch();
                $databaseVersion = isset($databaseVersionQueryResult['VERSION()']) ? $databaseVersionQueryResult['VERSION()'] : null;
                if (isset($databaseVersion) && $this->databaseSupportsUtf8Mb4($databaseVersion) === false) {
                    $result[] = [
                        'level' => 'error',
                        'message' => sprintf('The minimum required version for MySQL is "%s" or "%s" for MariaDB.', self::MINIMUM_MYSQL_VERSION, self::MINIMUM_MARIA_DB_VERSION)
                    ];
                }

                $charsetQueryResult = $connection->executeQuery('SHOW VARIABLES LIKE \'character_set_database\'')->fetch();
                $databaseCharacterSet = strtolower($charsetQueryResult['Value']);
                if (isset($databaseCharacterSet)) {
                    if ($databaseCharacterSet === 'utf8mb4') {
                        $result[] = ['level' => 'notice', 'message' => 'The selected database\'s character set is set to "utf8mb4" which is the recommended setting for MySQL/MariaDB databases.'];
                    } else {
                        $result[] = [
                            'level' => 'warning',
                            'message' => sprintf('The selected database\'s character set is "%s", however changing it to "utf8mb4" is urgently recommended. This setup tool won\'t do this for you.', $databaseCharacterSet)
                        ];
                    }
                }
            } elseif ($databasePlatform instanceof PostgreSqlPlatform) {
                $charsetQueryResult = $connection->executeQuery('SELECT pg_encoding_to_char(encoding) FROM pg_database WHERE datname = ?', [$databaseName])->fetch();
                $databaseCharacterSet = strtolower($charsetQueryResult['pg_encoding_to_char']);
                if (isset($databaseCharacterSet)) {
                    if ($databaseCharacterSet === 'utf8') {
                        $result[] = ['level' => 'notice', 'message' => 'The selected database\'s character set is set to "utf8" which is the recommended setting for PostgreSQL databases.'];
                    } else {
                        $result[] = [
                            'level' => 'warning',
                            'message' => sprintf('The selected database\'s character set is "%s", however changing it to "utf8" is urgently recommended. This setup tool won\'t do this for you.', $databaseCharacterSet)
                        ];
                    }
                }
            } else {
                $result[] = ['level' => 'error', 'message' => sprintf('Only MySQL/MariaDB and PostgreSQL are supported, the selected database is "%s".', $databasePlatform->getName())];
            }
        } catch (\PDOException $exception) {
            $result = ['level' => 'error', 'message' => $exception->getMessage(), 'errorCode' => $exception->getCode()];
        } catch (\Doctrine\DBAL\DBALException $exception) {
            $result = ['level' => 'error', 'message' => $exception->getMessage(), 'errorCode' => $exception->getCode()];
        } catch (\Exception $exception) {
            $result = ['level' => 'error', 'message' => 'Unexpected exception', 'errorCode' => $exception->getCode()];
        }

        return json_encode($result);
    }

    /**
     * @param string $driver
     * @param string $user
     * @param string $password
     * @param string $host
     * @return array
     */
    protected function buildConnectionSettingsArray($driver, $user, $password, $host)
    {
        $settings = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'Neos.Flow');
        $connectionSettings = $settings['persistence']['backendOptions'];
        $connectionSettings['driver'] = $driver;
        $connectionSettings['user'] = $user;
        $connectionSettings['password'] = $password;
        $connectionSettings['host'] = $host;
        if ($connectionSettings['driver'] === 'pdo_pgsql') {
            $connectionSettings['dbname'] = 'template1';
            // Postgres natively supports multibyte-UTF8. It does not know utf8mb4
            $connectionSettings['charset'] = 'utf8';

            return $connectionSettings;
        } else {
            unset($connectionSettings['dbname']);

            return $connectionSettings;
        }
    }

    /**
     * @param array $connectionSettings
     * @return \Doctrine\DBAL\Connection
     */
    protected function getConnectionAndConnect(array $connectionSettings)
    {
        $connection = \Doctrine\DBAL\DriverManager::getConnection($connectionSettings);
        $connection->connect();

        return $connection;
    }

    /**
     * Check if MySQL based database supports utf8mb4 character set.
     *
     * @param string $databaseVersion
     * @return bool
     */
    protected function databaseSupportsUtf8Mb4(string $databaseVersion): bool
    {
        if (strpos($databaseVersion, '-MariaDB') !== false &&
            version_compare($databaseVersion, self::MINIMUM_MARIA_DB_VERSION) === -1
        ) {
            return false;
        }

        if (preg_match('([a-zA-Z])', $databaseVersion) === 0 &&
            version_compare($databaseVersion, self::MINIMUM_MYSQL_VERSION) === -1
        ) {
            return false;
        }

        return true;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Setup\ViewHelpers\Widget\Controller;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Controller for the DatabaseSelector Fluid Widget
 */
class DatabaseSelectorController extends DatabaseSelectorController_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        if ('Neos\Setup\ViewHelpers\Widget\Controller\DatabaseSelectorController' === get_class($this)) {
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
            'indexAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'checkConnectionAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
                ),
            ),
            'getMetadataAction' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Security\Aspect\PolicyEnforcementAspect', 'enforcePolicy', $objectManager, NULL),
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
     * @return void
     */
    public function indexAction()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction'])) {
            $result = parent::indexAction();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction'] = true;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('indexAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Setup\ViewHelpers\Widget\Controller\DatabaseSelectorController', 'indexAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['indexAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param string $driver
     * @param string $user
     * @param string $password
     * @param string $host
     * @return string
     */
    public function checkConnectionAction($driver, $user, $password, $host)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['checkConnectionAction'])) {
            $result = parent::checkConnectionAction($driver, $user, $password, $host);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['checkConnectionAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['driver'] = $driver;
                $methodArguments['user'] = $user;
                $methodArguments['password'] = $password;
                $methodArguments['host'] = $host;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('checkConnectionAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Setup\ViewHelpers\Widget\Controller\DatabaseSelectorController', 'checkConnectionAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['checkConnectionAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['checkConnectionAction']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param string $driver
     * @param string $user
     * @param string $password
     * @param string $host
     * @param string $databaseName
     * @return string
     */
    public function getMetadataAction($driver, $user, $password, $host, $databaseName)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getMetadataAction'])) {
            $result = parent::getMetadataAction($driver, $user, $password, $host, $databaseName);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getMetadataAction'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['driver'] = $driver;
                $methodArguments['user'] = $user;
                $methodArguments['password'] = $password;
                $methodArguments['host'] = $host;
                $methodArguments['databaseName'] = $databaseName;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getMetadataAction');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Setup\ViewHelpers\Widget\Controller\DatabaseSelectorController', 'getMetadataAction', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getMetadataAction']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getMetadataAction']);
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
  'configurationManager' => 'Neos\\Flow\\Configuration\\ConfigurationManager',
  'widgetConfiguration' => 'array',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'reflectionService' => 'Neos\\Flow\\Reflection\\ReflectionService',
  'mvcPropertyMappingConfigurationService' => 'Neos\\Flow\\Mvc\\Controller\\MvcPropertyMappingConfigurationService',
  'viewConfigurationManager' => 'Neos\\Flow\\Mvc\\ViewConfigurationManager',
  'view' => 'Neos\\Flow\\Mvc\\View\\ViewInterface',
  'viewObjectNamePattern' => 'string',
  'viewFormatToObjectNameMap' => 'array',
  'defaultViewObjectName' => 'string',
  'defaultViewImplementation' => 'string',
  'actionMethodName' => 'string',
  'errorMethodName' => 'string',
  'settings' => 'array',
  'systemLogger' => 'Neos\\Flow\\Log\\SystemLoggerInterface',
  'logger' => 'Psr\\Log\\LoggerInterface',
  'uriBuilder' => 'Neos\\Flow\\Mvc\\Routing\\UriBuilder',
  'validatorResolver' => 'Neos\\Flow\\Validation\\ValidatorResolver',
  'request' => 'Neos\\Flow\\Mvc\\ActionRequest',
  'response' => 'Neos\\Flow\\Mvc\\ActionResponse',
  'arguments' => 'Neos\\Flow\\Mvc\\Controller\\Arguments',
  'controllerContext' => 'Neos\\Flow\\Mvc\\Controller\\ControllerContext',
  'flashMessageContainer' => 'Neos\\Flow\\Mvc\\FlashMessageContainer',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'supportedMediaTypes' => 'array',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Setup'));
        $this->injectLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Psr\Log\LoggerInterface'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Configuration\ConfigurationManager', 'Neos\Flow\Configuration\ConfigurationManager', 'configurationManager', 'f559bc775c41b957515dc1c69b91d8b1', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Configuration\ConfigurationManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Reflection\ReflectionService', 'Neos\Flow\Reflection\ReflectionService', 'reflectionService', '464c26aa94c66579c050985566cbfc1f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Reflection\ReflectionService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService', 'Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService', 'mvcPropertyMappingConfigurationService', '245f31ad31ca22b8c2b2255e0f65f847', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\ViewConfigurationManager', 'Neos\Flow\Mvc\ViewConfigurationManager', 'viewConfigurationManager', '40e27e95b530777b9b476762cf735a69', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\ViewConfigurationManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\SystemLoggerInterface', 'Neos\Flow\Log\Logger', 'systemLogger', '717e9de4d0309f4f47c821b9257eb5c2', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Validation\ValidatorResolver', 'Neos\Flow\Validation\ValidatorResolver', 'validatorResolver', 'e992f50de62d81bfe770d5c5f1242621', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Validation\ValidatorResolver'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\FlashMessageContainer', 'Neos\Flow\Mvc\FlashMessageContainer', 'flashMessageContainer', 'a5f5265657df54eb081324fb2ff5b8e1', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\FlashMessageContainer'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->defaultViewImplementation = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow.mvc.view.defaultImplementation');
        $this->Flow_Injected_Properties = array (
  0 => 'settings',
  1 => 'logger',
  2 => 'configurationManager',
  3 => 'objectManager',
  4 => 'reflectionService',
  5 => 'mvcPropertyMappingConfigurationService',
  6 => 'viewConfigurationManager',
  7 => 'systemLogger',
  8 => 'validatorResolver',
  9 => 'flashMessageContainer',
  10 => 'persistenceManager',
  11 => 'defaultViewImplementation',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Setup/Classes/ViewHelpers/Widget/Controller/DatabaseSelectorController.php
#