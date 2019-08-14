<?php 
namespace Neos\Neos\Setup\Step;

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
use Neos\Flow\Mvc\FlashMessageContainer;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Package\PackageInterface;
use Neos\Flow\Package\PackageManagerInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Validation\Validator\NotEmptyValidator;
use Neos\Form\Core\Model\FinisherContext;
use Neos\Form\Core\Model\FormDefinition;
use Neos\Form\Finishers\ClosureFinisher;
use Neos\Form\FormElements\Section;
use Neos\Neos\Domain\Repository\DomainRepository;
use Neos\Neos\Domain\Repository\SiteRepository;
use Neos\Neos\Domain\Service\SiteImportService;
use Neos\Neos\Validation\Validator\PackageKeyValidator;
use Neos\Setup\Exception as SetupException;
use Neos\Setup\Exception;
use Neos\Setup\Step\AbstractStep;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\ContentRepository\Domain\Repository\WorkspaceRepository;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\SiteKickstarter\Service\GeneratorService;
use Psr\Log\LoggerInterface;

/**
 * @Flow\Scope("singleton")
 */
class SiteImportStep_Original extends AbstractStep
{
    /**
     * @var boolean
     */
    protected $optional = true;

    /**
     * @Flow\Inject
     * @var PackageManagerInterface
     */
    protected $packageManager;

    /**
     * @Flow\Inject
     * @var SiteRepository
     */
    protected $siteRepository;

    /**
     * @Flow\Inject
     * @var SiteImportService
     */
    protected $siteImportService;

    /**
     * @Flow\Inject
     * @var DomainRepository
     */
    protected $domainRepository;

    /**
     * @Flow\Inject
     * @var FlashMessageContainer
     */
    protected $flashMessageContainer;

    /**
     * @Flow\Inject
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;

    /**
     * @Flow\Inject
     * @var WorkspaceRepository
     */
    protected $workspaceRepository;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ClosureFinisher
     */
    protected $closureFinisher;

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

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
     * Returns the form definitions for the step
     *
     * @param FormDefinition $formDefinition
     * @return void
     */
    protected function buildForm(FormDefinition $formDefinition)
    {
        $page1 = $formDefinition->createPage('page1');
        $page1->setRenderingOption('header', 'Create a new site');

        $introduction = $page1->createElement('introduction', 'Neos.Form:StaticText');
        $introduction->setProperty('text', 'There are two ways of creating a site. Choose between the following:');

        $importSection = $page1->createElement('import', 'Neos.Form:Section');
        /** @var Section $importSection */
        $importSection->setLabel('Import a site from an existing site package');

        $sitePackages = [];
        foreach ($this->packageManager->getFilteredPackages('available', null, 'neos-site') as $package) {
            /** @var PackageInterface $package */
            $sitePackages[$package->getPackageKey()] = $package->getPackageKey();
        }

        if (count($sitePackages) > 0) {
            $site = $importSection->createElement('site', 'Neos.Form:SingleSelectDropdown');
            $site->setLabel('Select a site package');
            $site->setProperty('options', $sitePackages);
            $site->addValidator(new NotEmptyValidator());

            $sites = $this->siteRepository->findAll();
            if ($sites->count() > 0) {
                $prune = $importSection->createElement('prune', 'Neos.Form:Checkbox');
                $prune->setLabel('Delete existing sites');
            }
        } else {
            $error = $importSection->createElement('noSitePackagesError', 'Neos.Form:StaticText');
            $error->setProperty('text', 'No site packages were available, make sure you have an active site package');
            $error->setProperty('elementClassAttribute', 'alert alert-warning');
        }

        if ($this->packageManager->isPackageAvailable('Neos.SiteKickstarter')) {
            $separator = $page1->createElement('separator', 'Neos.Form:StaticText');
            $separator->setProperty('elementClassAttribute', 'section-separator');

            $newPackageSection = $page1->createElement('newPackageSection', 'Neos.Form:Section');
            /** @var Section $newPackageSection */
            $newPackageSection->setLabel('Create a new site package with a dummy site');
            $packageName = $newPackageSection->createElement('packageKey', 'Neos.Form:SingleLineText');
            $packageName->setLabel('Package Name (in form "Vendor.DomainCom")');
            $packageName->addValidator(new PackageKeyValidator());

            $siteName = $newPackageSection->createElement('siteName', 'Neos.Form:SingleLineText');
            $siteName->setLabel('Site Name (e.g. "domain.com")');
        } else {
            $error = $importSection->createElement('neosKickstarterUnavailableError', 'Neos.Form:StaticText');
            $error->setProperty('text', 'The Neos Kickstarter package (Neos.SiteKickstarter) is not installed, install it for kickstarting new sites (using "composer require typo3/neos-kickstarter")');
            $error->setProperty('elementClassAttribute', 'alert alert-warning');
        }

        $sitePackageExplanation = $page1->createElement('sitePackageExplanation', 'Neos.Form:StaticText');
        $sitePackageExplanation->setProperty('text', 'Notice the difference between a site package and a site. A site package is a Flow package that can be used for creating multiple site instances.');
        $sitePackageExplanation->setProperty('elementClassAttribute', 'alert alert-info');

        if (count($sitePackages) > 0) {
            $sitePackageAlreadyAvailableExplanation = $page1->createElement('sitePackageAlreadyAvailableExplanation', 'Neos.Form:StaticText');
            $sitePackageAlreadyAvailableExplanation->setProperty('text', sprintf('There are already other site packages available (%s). Some configuration like dimensions and node type configurations are shared between all sites packages. Make sure you remove the site packages you don\'t want to interfere with your newly created package.', implode(array_keys($sitePackages))));
            $sitePackageAlreadyAvailableExplanation->setProperty('elementClassAttribute', 'alert alert-info');
        }

        $step = $this;
        $callback = function (FinisherContext $finisherContext) use ($step) {
            $step->importSite($finisherContext);
        };
        $this->closureFinisher = new ClosureFinisher();
        $this->closureFinisher->setOption('closure', $callback);
        $formDefinition->addFinisher($this->closureFinisher);

        $formDefinition->setRenderingOption('skipStepNotice', 'You can always import a site using the site:import command');
    }

    /**
     * @param FinisherContext $finisherContext
     * @return void
     * @throws Exception
     */
    public function importSite(FinisherContext $finisherContext)
    {
        $formValues = $finisherContext->getFormRuntime()->getFormState()->getFormValues();

        if (isset($formValues['prune']) && intval($formValues['prune']) === 1) {
            $this->nodeDataRepository->removeAll();
            $this->workspaceRepository->removeAll();
            $this->domainRepository->removeAll();
            $this->siteRepository->removeAll();
            $this->persistenceManager->persistAll();
        }

        if (!empty($formValues['packageKey'])) {
            if ($this->packageManager->isPackageAvailable($formValues['packageKey'])) {
                throw new Exception(sprintf('The package key "%s" already exists.', $formValues['packageKey']), 1346759486);
            }
            $packageKey = $formValues['packageKey'];
            $siteName = $formValues['siteName'];

            $generatorService = $this->objectManager->get(GeneratorService::class);
            $generatorService->generateSitePackage($packageKey, $siteName);
        } elseif (!empty($formValues['site'])) {
            $packageKey = $formValues['site'];
        }

        if (!empty($packageKey)) {
            try {
                $this->siteImportService->importFromPackage($packageKey);
            } catch (\Exception $exception) {
                $finisherContext->cancel();
                $logMessage = $this->throwableStorage->logThrowable($exception);
                $this->logger->error($logMessage, LogEnvironment::fromMethodName(__METHOD__));
                throw new SetupException(sprintf('Error: During the import of the "Sites.xml" from the package "%s" an exception occurred: %s', $packageKey, $exception->getMessage()), 1351000864);
            }
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Setup\Step;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class SiteImportStep extends SiteImportStep_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Neos\Setup\Step\SiteImportStep') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Setup\Step\SiteImportStep', $this);
        if ('Neos\Neos\Setup\Step\SiteImportStep' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Neos\Setup\Step\SiteImportStep';
        if ($isSameClass) {
            $this->initializeObject(1);
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
  'optional' => 'boolean',
  'packageManager' => 'Neos\\Flow\\Package\\PackageManagerInterface',
  'siteRepository' => 'Neos\\Neos\\Domain\\Repository\\SiteRepository',
  'siteImportService' => 'Neos\\Neos\\Domain\\Service\\SiteImportService',
  'domainRepository' => 'Neos\\Neos\\Domain\\Repository\\DomainRepository',
  'flashMessageContainer' => 'Neos\\Flow\\Mvc\\FlashMessageContainer',
  'nodeDataRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\NodeDataRepository',
  'workspaceRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\WorkspaceRepository',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'closureFinisher' => 'Neos\\Form\\Finishers\\ClosureFinisher',
  'contextFactory' => 'Neos\\ContentRepository\\Domain\\Service\\ContextFactoryInterface',
  'logger' => 'Psr\\Log\\LoggerInterface',
  'throwableStorage' => 'Neos\\Flow\\Log\\ThrowableStorageInterface',
  'formSettings' => 'array',
  'configurationManager' => '\\Neos\\Flow\\Configuration\\ConfigurationManager',
  'options' => 'array',
  'distributionSettings' => 'array',
  'presetName' => 'string',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Neos\Setup\Step\SiteImportStep') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Setup\Step\SiteImportStep', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;

        $isSameClass = get_class($this) === 'Neos\Neos\Setup\Step\SiteImportStep';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Neos\Setup\Step\SiteImportStep', $classParents) !== false && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== false;

        if ($isSameClass || $isClassProxy) {
            $this->initializeObject(2);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Psr\Log\LoggerInterface'));
        $this->injectThrowableStorage(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\ThrowableStorageInterface'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Package\PackageManagerInterface', 'Neos\Flow\Package\PackageManager', 'packageManager', 'b44be8eaae4695ec4f42edfbf6f8880a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Package\PackageManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\SiteRepository', 'Neos\Neos\Domain\Repository\SiteRepository', 'siteRepository', '42785f5eca4dff104f1860b84f531a9f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\SiteRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Service\SiteImportService', 'Neos\Neos\Domain\Service\SiteImportService', 'siteImportService', '138d916c995844499bbda23ab8e78dc0', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Service\SiteImportService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\DomainRepository', 'Neos\Neos\Domain\Repository\DomainRepository', 'domainRepository', '37b1b7f7b2d5d92dae299591af3b7e10', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\DomainRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\FlashMessageContainer', 'Neos\Flow\Mvc\FlashMessageContainer', 'flashMessageContainer', 'a5f5265657df54eb081324fb2ff5b8e1', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\FlashMessageContainer'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\NodeDataRepository', 'Neos\ContentRepository\Domain\Repository\NodeDataRepository', 'nodeDataRepository', '6d07985e92d364413ac81acd8f47b11b', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\NodeDataRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'workspaceRepository', '9cacb5dd2ad57e06d6f8c82dd5707855', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\WorkspaceRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\ContextFactoryInterface', 'Neos\Neos\Domain\Service\ContentContextFactory', 'contextFactory', '98dca7b1f95a25ec173662fc4e785341', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\ContextFactoryInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Configuration\ConfigurationManager', 'Neos\Flow\Configuration\ConfigurationManager', 'configurationManager', 'f559bc775c41b957515dc1c69b91d8b1', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Configuration\ConfigurationManager'); });
        $this->Flow_Injected_Properties = array (
  0 => 'logger',
  1 => 'throwableStorage',
  2 => 'packageManager',
  3 => 'siteRepository',
  4 => 'siteImportService',
  5 => 'domainRepository',
  6 => 'flashMessageContainer',
  7 => 'nodeDataRepository',
  8 => 'workspaceRepository',
  9 => 'persistenceManager',
  10 => 'objectManager',
  11 => 'contextFactory',
  12 => 'configurationManager',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Setup/Step/SiteImportStep.php
#