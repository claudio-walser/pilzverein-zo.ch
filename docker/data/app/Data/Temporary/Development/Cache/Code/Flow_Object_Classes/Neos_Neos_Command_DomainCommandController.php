<?php 
namespace Neos\Neos\Command;

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
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Validation\ValidatorResolver;
use Neos\Neos\Domain\Model\Domain;
use Neos\Neos\Domain\Model\Site;
use Neos\Neos\Domain\Repository\DomainRepository;
use Neos\Neos\Domain\Repository\SiteRepository;

/**
 * Domain command controller for the Neos.Neos package
 *
 * @Flow\Scope("singleton")
 */
class DomainCommandController_Original extends CommandController
{
    /**
     * @var DomainRepository
     * @Flow\Inject
     */
    protected $domainRepository;

    /**
     * @var SiteRepository
     * @Flow\Inject
     */
    protected $siteRepository;

    /**
     * @var ValidatorResolver
     * @Flow\Inject
     */
    protected $validatorResolver;

    /**
     * Add a domain record
     *
     * @param string $siteNodeName The nodeName of the site rootNode, e.g. "flowneosio"
     * @param string $hostname The hostname to match on, e.g. "flow.neos.io"
     * @param string $scheme The scheme for linking (http/https)
     * @param integer $port The port for linking (0-49151)
     * @return void
     */
    public function addCommand($siteNodeName, $hostname, $scheme = null, $port = null)
    {
        $site = $this->siteRepository->findOneByNodeName($siteNodeName);
        if (!$site instanceof Site) {
            $this->outputLine('<error>No site found with nodeName "%s".</error>', [$siteNodeName]);
            $this->quit(1);
        }

        $domains = $this->domainRepository->findByHostname($hostname);
        if ($domains->count() > 0) {
            $this->outputLine('<error>The host name "%s" is not unique.</error>', [$hostname]);
            $this->quit(1);
        }

        $domain = new Domain();
        if ($scheme !== null) {
            $domain->setScheme($scheme);
        }
        if ($port !== null) {
            $domain->setPort($port);
        }
        $domain->setSite($site);
        $domain->setHostname($hostname);

        $domainValidator = $this->validatorResolver->getBaseValidatorConjunction(Domain::class);
        $result = $domainValidator->validate($domain);
        if ($result->hasErrors()) {
            foreach ($result->getFlattenedErrors() as $propertyName => $errors) {
                $firstError = array_pop($errors);
                $this->outputLine('<error>Validation failed for "' . $propertyName . '": ' . $firstError . '</error>');
                $this->quit(1);
            }
        }

        $this->domainRepository->add($domain);

        $this->outputLine('Domain entry created.');
    }

    /**
     * Display a list of available domain records
     *
     * @param string $hostname An optional hostname to search for
     * @return void
     */
    public function listCommand($hostname = null)
    {
        if ($hostname === null) {
            $domains = $this->domainRepository->findAll();
        } else {
            $domains = $this->domainRepository->findByHostname($hostname);
        }

        if (count($domains) === 0) {
            $this->outputLine('No domain entries available.');
            $this->quit(0);
        }

        $availableDomains = [];
        foreach ($domains as $domain) {
            /** @var \Neos\Neos\Domain\Model\Domain $domain */
            $availableDomains[] = [
                'nodeName' => $domain->getSite()->getNodeName(),
                'hostname' => (string)$domain,
                'active' => $domain->getActive() ? 'active' : 'inactive'
            ];
        }

        $this->output->outputTable($availableDomains, ['Node name', 'Domain (Scheme/Host/Port)', 'State']);
    }

    /**
     * Delete a domain record by hostname (with globbing)
     *
     * @param string $hostname The hostname to remove (globbing is supported)
     * @return void
     */
    public function deleteCommand($hostname)
    {
        $domains = $this->findDomainsByHostnamePattern($hostname);
        if (empty($domains)) {
            $this->outputLine('<error>No domain found for hostname-pattern "%s".</error>', [$hostname]);
            $this->quit(1);
        }
        foreach ($domains as $domain) {
            $site = $domain->getSite();
            if ($site->getPrimaryDomain() === $domain) {
                $site->setPrimaryDomain(null);
                $this->siteRepository->update($site);
            }
            $this->domainRepository->remove($domain);
            $this->outputLine('Domain entry "%s" deleted.', [$domain->getHostname()]);
        }
    }

    /**
     * Activate a domain record by hostname (with globbing)
     *
     * @param string $hostname The hostname to activate (globbing is supported)
     * @return void
     */
    public function activateCommand($hostname)
    {
        $domains = $this->findDomainsByHostnamePattern($hostname);
        if (empty($domains)) {
            $this->outputLine('<error>No domain found for hostname-pattern "%s".</error>', [$hostname]);
            $this->quit(1);
        }
        foreach ($domains as $domain) {
            $domain->setActive(true);
            $this->domainRepository->update($domain);
            $this->outputLine('Domain entry "%s" was activated.', [$domain->getHostname()]);
        }
    }

    /**
     * Deactivate a domain record by hostname (with globbing)
     *
     * @param string $hostname The hostname to deactivate (globbing is supported)
     * @return void
     */
    public function deactivateCommand($hostname)
    {
        $domains = $this->findDomainsByHostnamePattern($hostname);
        if (empty($domains)) {
            $this->outputLine('<error>No domain found for hostname-pattern "%s".</error>', [$hostname]);
            $this->quit(1);
        }
        foreach ($domains as $domain) {
            $domain->setActive(false);
            $this->domainRepository->update($domain);
            $this->outputLine('Domain entry "%s" was deactivated.', [$domain->getHostname()]);
        }
    }

    /**
     * Find domains that match the given hostname with globbing support
     *
     * @param string $hostnamePattern pattern for the hostname of the domains
     * @return array<Domain>
     */
    protected function findDomainsByHostnamePattern($hostnamePattern)
    {
        return array_filter(
            $this->domainRepository->findAll()->toArray(),
            function ($domain) use ($hostnamePattern) {
                return fnmatch($hostnamePattern, $domain->getHostname());
            }
        );
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Command;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Domain command controller for the Neos.Neos package
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class DomainCommandController extends DomainCommandController_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Neos\Command\DomainCommandController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Command\DomainCommandController', $this);
        parent::__construct();
        if ('Neos\Neos\Command\DomainCommandController' === get_class($this)) {
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
  'domainRepository' => 'Neos\\Neos\\Domain\\Repository\\DomainRepository',
  'siteRepository' => 'Neos\\Neos\\Domain\\Repository\\SiteRepository',
  'validatorResolver' => 'Neos\\Flow\\Validation\\ValidatorResolver',
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
        if (get_class($this) === 'Neos\Neos\Command\DomainCommandController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Command\DomainCommandController', $this);

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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\DomainRepository', 'Neos\Neos\Domain\Repository\DomainRepository', 'domainRepository', '37b1b7f7b2d5d92dae299591af3b7e10', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\DomainRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\SiteRepository', 'Neos\Neos\Domain\Repository\SiteRepository', 'siteRepository', '42785f5eca4dff104f1860b84f531a9f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\SiteRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Validation\ValidatorResolver', 'Neos\Flow\Validation\ValidatorResolver', 'validatorResolver', 'e992f50de62d81bfe770d5c5f1242621', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Validation\ValidatorResolver'); });
        $this->Flow_Injected_Properties = array (
  0 => 'commandManager',
  1 => 'objectManager',
  2 => 'domainRepository',
  3 => 'siteRepository',
  4 => 'validatorResolver',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Command/DomainCommandController.php
#