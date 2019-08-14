<?php 
namespace Neos\ContentRepository\Migration\Command;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Cli\CommandController;
use Neos\Flow\Configuration\Source\YamlSource;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\ContentRepository\Migration\Domain\Factory\MigrationFactory;
use Neos\ContentRepository\Migration\Domain\Repository\MigrationStatusRepository;
use Neos\ContentRepository\Migration\Exception\MigrationException;
use Neos\Flow\Persistence\Doctrine\Exception\DatabaseException;
use Neos\ContentRepository\Migration\Service\NodeMigration;
use Neos\ContentRepository\Migration\Domain\Model\MigrationStatus;
use Neos\ContentRepository\Migration\Domain\Model\MigrationConfiguration;
use Neos\Flow\Annotations as Flow;

/**
 * Command controller for tasks related to node handling.
 *
 * @Flow\Scope("singleton")
 */
class NodeCommandController_Original extends CommandController
{
    /**
     * @Flow\Inject
     * @var YamlSource
     */
    protected $yamlSourceImporter;

    /**
     * @Flow\Inject
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;

    /**
     * @Flow\Inject
     * @var MigrationStatusRepository
     */
    protected $migrationStatusRepository;

    /**
     * @Flow\Inject
     * @var MigrationFactory
     */
    protected $migrationFactory;

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * Do the configured migrations in the given migration.
     *
     * By default the up direction is applied, using the direction parameter this can
     * be changed.
     *
     * @param string $version The version of the migration configuration you want to use.
     * @param boolean $confirmation Confirm application of this migration, only needed if the given migration contains any warnings.
     * @param string $direction The direction to work in, MigrationStatus::DIRECTION_UP or MigrationStatus::DIRECTION_DOWN
     * @return void
     * @see neos.contentrepository.migration:node:migrationstatus
     */
    public function migrateCommand($version, $confirmation = false, $direction = MigrationStatus::DIRECTION_UP)
    {
        try {
            $migrationConfiguration = $direction === MigrationStatus::DIRECTION_UP ?
                $this->migrationFactory->getMigrationForVersion($version)->getUpConfiguration() :
                $this->migrationFactory->getMigrationForVersion($version)->getDownConfiguration();

            $this->outputCommentsAndWarnings($migrationConfiguration);
            if ($migrationConfiguration->hasWarnings() && $confirmation === false) {
                $this->outputLine();
                $this->outputLine('Migration has warnings. You need to confirm execution by adding the "--confirmation true" option to the command.');
                $this->quit(1);
            }

            $nodeMigrationService = new NodeMigration($migrationConfiguration->getMigration());
            $nodeMigrationService->execute();
            $migrationStatus = new MigrationStatus($version, $direction, new \DateTime());
            $this->migrationStatusRepository->add($migrationStatus);
            $this->outputLine();
            $this->outputLine('Successfully applied migration.');
        } catch (MigrationException $e) {
            $this->outputLine();
            $this->outputLine('Error: ' . $e->getMessage());
            $this->quit(1);
        } catch (DatabaseException $exception) {
            $this->outputLine();
            $this->outputLine('An exception occurred during the migration, run a ./flow doctrine:migrate and run the migration again.');
            $this->quit(1);
        }
    }

    /**
     * List available and applied migrations
     *
     * @return void
     * @see neos.contentrepository.migration:node:migrate
     */
    public function migrationStatusCommand()
    {
        $this->outputLine();

        $availableMigrations = $this->migrationFactory->getAvailableMigrationsForCurrentConfigurationType();
        if (count($availableMigrations) === 0) {
            $this->outputLine('No migrations available.');
            $this->quit();
        }

        $appliedMigrations = $this->migrationStatusRepository->findAll();
        $appliedMigrationsDictionary = [];
        /** @var $appliedMigration MigrationStatus */
        foreach ($appliedMigrations as $appliedMigration) {
            $appliedMigrationsDictionary[$appliedMigration->getVersion()][] = $appliedMigration;
        }

        $tableRows = [];
        foreach ($availableMigrations as $version => $migration) {
            $migrationUpConfigurationComments = $this->migrationFactory->getMigrationForVersion($version)->getUpConfiguration()->getComments();

            if (isset($appliedMigrationsDictionary[$version])) {
                $applicationInformation = $this->phraseMigrationApplicationInformation($appliedMigrationsDictionary[$version]);
                if ($applicationInformation !== '') {
                    $migrationUpConfigurationComments .= PHP_EOL . '<b>Applied:</b>' . PHP_EOL . $applicationInformation;
                }
            }

            $tableRows[] = [
                $version,
                $migration['formattedVersionNumber'],
                $migration['package']->getPackageKey(),
                wordwrap($migrationUpConfigurationComments, 60)
            ];
        }

        $this->outputLine('<b>Available migrations</b>');
        $this->outputLine();
        $this->output->outputTable($tableRows, ['Version', 'Date', 'Package', 'Comments']);
    }

    /**
     * Helper to output comments and warnings for the given configuration.
     *
     * @param MigrationConfiguration $migrationConfiguration
     * @return void
     */
    protected function outputCommentsAndWarnings(MigrationConfiguration $migrationConfiguration)
    {
        if ($migrationConfiguration->hasComments()) {
            $this->outputLine();
            $this->outputLine('<b>Comments</b>');
            $this->outputFormatted($migrationConfiguration->getComments(), [], 2);
        }

        if ($migrationConfiguration->hasWarnings()) {
            $this->outputLine();
            $this->outputLine('<b><u>Warnings</u></b>');
            $this->outputFormatted($migrationConfiguration->getWarnings(), [], 2);
        }
    }

    /**
     * @param array $migrationsInVersion
     * @return string
     */
    protected function phraseMigrationApplicationInformation($migrationsInVersion)
    {
        usort($migrationsInVersion, function (MigrationStatus $migrationA, MigrationStatus $migrationB) {
            return $migrationA->getApplicationTimeStamp() > $migrationB->getApplicationTimeStamp();
        });

        $applied = [];
        /** @var MigrationStatus $migrationStatus */
        foreach ($migrationsInVersion as $migrationStatus) {
            $applied[] = sprintf(
                '%s applied on %s',
                str_pad(strtoupper($migrationStatus->getDirection()), 5, ' ', STR_PAD_LEFT),
                $migrationStatus->getApplicationTimeStamp()->format('Y-m-d H:i:s')
            );
        }
        return implode(PHP_EOL, $applied);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\ContentRepository\Migration\Command;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Command controller for tasks related to node handling.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class NodeCommandController extends NodeCommandController_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\ContentRepository\Migration\Command\NodeCommandController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\ContentRepository\Migration\Command\NodeCommandController', $this);
        parent::__construct();
        if ('Neos\ContentRepository\Migration\Command\NodeCommandController' === get_class($this)) {
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
  'yamlSourceImporter' => 'Neos\\Flow\\Configuration\\Source\\YamlSource',
  'nodeDataRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\NodeDataRepository',
  'migrationStatusRepository' => 'Neos\\ContentRepository\\Migration\\Domain\\Repository\\MigrationStatusRepository',
  'migrationFactory' => 'Neos\\ContentRepository\\Migration\\Domain\\Factory\\MigrationFactory',
  'contextFactory' => 'Neos\\ContentRepository\\Domain\\Service\\ContextFactoryInterface',
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
        if (get_class($this) === 'Neos\ContentRepository\Migration\Command\NodeCommandController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\ContentRepository\Migration\Command\NodeCommandController', $this);

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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Configuration\Source\YamlSource', 'Neos\Flow\Configuration\Source\YamlSource', 'yamlSourceImporter', '4e81d02eaab2f307379618613fe4e33a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Configuration\Source\YamlSource'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\NodeDataRepository', 'Neos\ContentRepository\Domain\Repository\NodeDataRepository', 'nodeDataRepository', '6d07985e92d364413ac81acd8f47b11b', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\NodeDataRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Migration\Domain\Repository\MigrationStatusRepository', 'Neos\ContentRepository\Migration\Domain\Repository\MigrationStatusRepository', 'migrationStatusRepository', '8a9ae1529c96850b5e7e19a0d97e5f12', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Migration\Domain\Repository\MigrationStatusRepository'); });
        $this->migrationFactory = new \Neos\ContentRepository\Migration\Domain\Factory\MigrationFactory();
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\ContextFactoryInterface', 'Neos\Neos\Domain\Service\ContentContextFactory', 'contextFactory', '98dca7b1f95a25ec173662fc4e785341', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\ContextFactoryInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'commandManager',
  1 => 'objectManager',
  2 => 'yamlSourceImporter',
  3 => 'nodeDataRepository',
  4 => 'migrationStatusRepository',
  5 => 'migrationFactory',
  6 => 'contextFactory',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.ContentRepository/Classes/Migration/Command/NodeCommandController.php
#