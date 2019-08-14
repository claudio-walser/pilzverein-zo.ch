<?php 
namespace Neos\Flow\Command;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Mvc\Exception\StopActionException;

/**
 * Command controller for tasks related to database handling
 *
 * @Flow\Scope("singleton")
 */
class DatabaseCommandController_Original extends CommandController
{
    /**
     * @Flow\InjectConfiguration(path="persistence")
     * @var array
     */
    protected $persistenceSettings = [];

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * Create a Doctrine DBAL Connection with the configured settings.
     *
     * @return void
     * @throws DBALException
     */
    protected function initializeConnection()
    {
        $this->connection = DriverManager::getConnection($this->persistenceSettings['backendOptions']);
    }

    /**
     * Convert the database schema to use the given character set and collation (defaults to utf8mb4 and utf8mb4_unicode_ci).
     *
     * This command can be used to convert the database configured in the Flow settings to the utf8mb4 character
     * set and the utf8mb4_unicode_ci collation (by default, a custom collation can be given). It will only
     * work when using the pdo_mysql driver.
     *
     * <b>Make a backup</b> before using it, to be on the safe side. If you want to inspect the statements used
     * for conversion, you can use the $output parameter to write them into a file. This file can be used to do
     * the conversion manually.
     *
     * For background information on this, see:
     *
     * - http://stackoverflow.com/questions/766809/
     * - http://dev.mysql.com/doc/refman/5.5/en/alter-table.html
     * - https://medium.com/@adamhooper/in-mysql-never-use-utf8-use-utf8mb4-11761243e434
     * - https://mathiasbynens.be/notes/mysql-utf8mb4
     * - https://florian.ec/articles/mysql-doctrine-utf8/
     *
     * The main purpose of this is to fix setups that were created with Flow before version 5.0. In those cases,
     * the tables will have a collation that does not match the default collation of later Flow versions, potentially
     * leading to problems when creating foreign key constraints (among others, potentially).
     *
     * If you have special needs regarding the charset and collation, you <i>can</i> override the defaults with
     * different ones.
     *
     * Note: This command <b>is not a general purpose conversion tool</b>. It will specifically not fix cases
     * of actual utf8 stored in latin1 columns. For this a conversion to BLOB followed by a conversion to the
     * proper type, charset and collation is needed instead.
     *
     * @param string $characterSet Character set, defaults to utf8mb4
     * @param string $collation Collation to use, defaults to utf8mb4_unicode_ci
     * @param string $output A file to write SQL to, instead of executing it
     * @param boolean $verbose If set, the statements will be shown as they are executed
     * @throws ConnectionException
     * @throws DBALException
     * @throws StopActionException
     */
    public function setCharsetCommand(string $characterSet = 'utf8mb4', string $collation = 'utf8mb4_unicode_ci', string $output = null, bool $verbose = false)
    {
        if (!in_array($this->persistenceSettings['backendOptions']['driver'], ['pdo_mysql', 'mysqli'])) {
            $this->outputLine('Database charset/collation fixing is only supported on MySQL.');
            $this->quit(1);
        }

        if ($this->persistenceSettings['backendOptions']['host'] === null) {
            $this->outputLine('Database charset/collation fixing has been SKIPPED, the host backend option is not set in the configuration.');
            $this->quit(1);
        }

        $this->initializeConnection();
        $this->convertToCharacterSetAndCollation($characterSet, $collation, $output, $verbose);

        if ($output === null) {
            $this->outputLine('Database charset/collation was converted.');
        } else {
            $this->outputLine('Wrote SQL for converting database charset/collation to file "' . $output . '".');
        }
    }

    /**
     * Convert the tables in the current database to use given character set and collation.
     *
     * @param string $characterSet Character set to convert to
     * @param string $collation Collation to set, must be compatible with the character set
     * @param string $outputPathAndFilename
     * @param boolean $verbose
     * @throws ConnectionException
     * @throws DBALException
     */
    protected function convertToCharacterSetAndCollation(string $characterSet, string $collation, string $outputPathAndFilename = null, bool $verbose = false)
    {
        $statements = ['SET foreign_key_checks = 0'];

        $statements[] = 'ALTER DATABASE ' . $this->connection->quoteIdentifier($this->persistenceSettings['backendOptions']['dbname']) . ' CHARACTER SET ' . $characterSet . ' COLLATE ' . $collation;

        $tableNames = $this->connection->getSchemaManager()->listTableNames();
        foreach ($tableNames as $tableName) {
            $statements[] = 'ALTER TABLE ' . $this->connection->quoteIdentifier($tableName) . ' DEFAULT CHARACTER SET ' . $characterSet . ' COLLATE ' . $collation;
            $statements[] = 'ALTER TABLE ' . $this->connection->quoteIdentifier($tableName) . ' CONVERT TO CHARACTER SET ' . $characterSet . ' COLLATE ' . $collation;
        }

        $statements[] = 'SET foreign_key_checks = 1';

        if ($outputPathAndFilename === null) {
            try {
                $this->connection->beginTransaction();
                foreach ($statements as $statement) {
                    if ($verbose) {
                        $this->outputLine($statement);
                    }
                    $this->connection->exec($statement);
                }
                $this->connection->commit();
            } catch (\Exception $exception) {
                $this->connection->rollBack();
                $this->outputLine($exception->getMessage());
                $this->outputLine('[ERROR] The transaction was rolled back.');
            }
        } else {
            file_put_contents($outputPathAndFilename, implode(';' . PHP_EOL, $statements) . ';');
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Command;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Command controller for tasks related to database handling
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class DatabaseCommandController extends DatabaseCommandController_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Flow\Command\DatabaseCommandController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Command\DatabaseCommandController', $this);
        parent::__construct();
        if ('Neos\Flow\Command\DatabaseCommandController' === get_class($this)) {
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
  'persistenceSettings' => 'array',
  'connection' => 'Doctrine\\DBAL\\Connection',
  'request' => 'Neos\\Flow\\Cli\\Request',
  'response' => 'Neos\\Flow\\Cli\\Response',
  'arguments' => 'Neos\\Flow\\Mvc\\Controller\\Arguments',
  'commandMethodName' => 'string',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'commandManager' => 'Neos\\Flow\\Cli\\CommandManager',
  'output' => 'Neos\\Flow\\Cli\\ConsoleOutput',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Flow\Command\DatabaseCommandController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Command\DatabaseCommandController', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectCommandManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Cli\CommandManager'));
        $this->injectObjectManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'));
        $this->persistenceSettings = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow.persistence');
        $this->Flow_Injected_Properties = array (
  0 => 'commandManager',
  1 => 'objectManager',
  2 => 'persistenceSettings',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Framework/Neos.Flow/Classes/Command/DatabaseCommandController.php
#