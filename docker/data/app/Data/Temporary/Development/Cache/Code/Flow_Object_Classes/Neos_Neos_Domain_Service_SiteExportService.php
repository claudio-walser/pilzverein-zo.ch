<?php 
namespace Neos\Neos\Domain\Service;

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
use Neos\Flow\Package\PackageManagerInterface;
use Neos\Utility\Files;
use Neos\Neos\Domain\Model\Site;
use Neos\Neos\Domain\Exception as NeosException;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\ContentRepository\Domain\Service\ImportExport\NodeExportService;

/**
 * The Site Export Service
 *
 * @Flow\Scope("singleton")
 */
class SiteExportService_Original
{
    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     *
     * @var NodeExportService
     */
    protected $nodeExportService;

    /**
     * @Flow\Inject
     * @var PackageManagerInterface
     */
    protected $packageManager;

    /**
     * Absolute path to exported resources, or NULL if resources should be inlined in the exported XML
     *
     * @var string
     */
    protected $resourcesPath = null;

    /**
     * The XMLWriter that is used to construct the export.
     *
     * @var \XMLWriter
     */
    protected $xmlWriter;

    /**
     * Fetches the site with the given name and exports it into XML.
     *
     * @param array<Site> $sites
     * @param boolean $tidy Whether to export formatted XML
     * @param string $nodeTypeFilter Filter the node type of the nodes, allows complex expressions (e.g. "Neos.Neos:Page", "!Neos.Neos:Page,Neos.Neos:Text")
     * @return string
     */
    public function export(array $sites, $tidy = false, $nodeTypeFilter = null)
    {
        $this->xmlWriter = new \XMLWriter();
        $this->xmlWriter->openMemory();
        $this->xmlWriter->setIndent($tidy);

        $this->exportSites($sites, $nodeTypeFilter);

        return $this->xmlWriter->outputMemory(true);
    }

    /**
     * Fetches the site with the given name and exports it into XML in the given package.
     *
     * @param array<Site> $sites
     * @param boolean $tidy Whether to export formatted XML
     * @param string $packageKey Package key where the export output should be saved to
     * @param string $nodeTypeFilter Filter the node type of the nodes, allows complex expressions (e.g. "Neos.Neos:Page", "!Neos.Neos:Page,Neos.Neos:Text")
     * @return void
     * @throws NeosException
     */
    public function exportToPackage(array $sites, $tidy = false, $packageKey, $nodeTypeFilter = null)
    {
        if (!$this->packageManager->isPackageAvailable($packageKey)) {
            throw new NeosException(sprintf('Error: Package "%s" is not active.', $packageKey), 1404375719);
        }
        $contentPathAndFilename = sprintf('resource://%s/Private/Content/Sites.xml', $packageKey);

        $this->resourcesPath = Files::concatenatePaths([dirname($contentPathAndFilename), 'Resources']);
        Files::createDirectoryRecursively($this->resourcesPath);

        $this->xmlWriter = new \XMLWriter();
        $this->xmlWriter->openUri($contentPathAndFilename);
        $this->xmlWriter->setIndent($tidy);

        $this->exportSites($sites, $nodeTypeFilter);

        $this->xmlWriter->flush();
    }

    /**
     * Fetches the site with the given name and exports it as XML into the given file.
     *
     * @param array<Site> $sites
     * @param boolean $tidy Whether to export formatted XML
     * @param string $pathAndFilename Path to where the export output should be saved to
     * @param string $nodeTypeFilter Filter the node type of the nodes, allows complex expressions (e.g. "Neos.Neos:Page", "!Neos.Neos:Page,Neos.Neos:Text")
     * @return void
     */
    public function exportToFile(array $sites, $tidy = false, $pathAndFilename, $nodeTypeFilter = null)
    {
        $this->resourcesPath = Files::concatenatePaths([dirname($pathAndFilename), 'Resources']);
        Files::createDirectoryRecursively($this->resourcesPath);

        $this->xmlWriter = new \XMLWriter();
        $this->xmlWriter->openUri($pathAndFilename);
        $this->xmlWriter->setIndent($tidy);

        $this->exportSites($sites, $nodeTypeFilter);

        $this->xmlWriter->flush();
    }

    /**
     * Exports the given sites to the XMLWriter
     *
     * @param array<Site> $sites
     * @param string $nodeTypeFilter
     * @return void
     */
    protected function exportSites(array $sites, $nodeTypeFilter)
    {
        $this->xmlWriter->startDocument('1.0', 'UTF-8');
        $this->xmlWriter->startElement('root');

        foreach ($sites as $site) {
            $this->exportSite($site, $nodeTypeFilter);
        }

        $this->xmlWriter->endElement();
        $this->xmlWriter->endDocument();
    }

    /**
     * Export the given $site to the XMLWriter
     *
     * @param Site $site
     * @param string $nodeTypeFilter
     * @return void
     */
    protected function exportSite(Site $site, $nodeTypeFilter)
    {
        /** @var ContentContext $contentContext */
        $contentContext = $this->contextFactory->create([
            'currentSite' => $site,
            'invisibleContentShown' => true,
            'inaccessibleContentShown' => true
        ]);

        $siteNode = $contentContext->getCurrentSiteNode();

        $this->xmlWriter->startElement('site');
        $this->xmlWriter->writeAttribute('name', $site->getName());
        $this->xmlWriter->writeAttribute('state', $site->getState());
        $this->xmlWriter->writeAttribute('siteResourcesPackageKey', $site->getSiteResourcesPackageKey());
        $this->xmlWriter->writeAttribute('siteNodeName', $siteNode->getName());

        $this->nodeExportService->export($siteNode->getPath(), $contentContext->getWorkspaceName(), $this->xmlWriter, false, false, $this->resourcesPath, $nodeTypeFilter);

        $this->xmlWriter->endElement();
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Domain\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * The Site Export Service
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class SiteExportService extends SiteExportService_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Neos\Domain\Service\SiteExportService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Domain\Service\SiteExportService', $this);
        if ('Neos\Neos\Domain\Service\SiteExportService' === get_class($this)) {
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
  'contextFactory' => 'Neos\\ContentRepository\\Domain\\Service\\ContextFactoryInterface',
  'nodeExportService' => 'Neos\\ContentRepository\\Domain\\Service\\ImportExport\\NodeExportService',
  'packageManager' => 'Neos\\Flow\\Package\\PackageManagerInterface',
  'resourcesPath' => 'string',
  'xmlWriter' => '\\XMLWriter',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Neos\Domain\Service\SiteExportService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Domain\Service\SiteExportService', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\ContextFactoryInterface', 'Neos\Neos\Domain\Service\ContentContextFactory', 'contextFactory', '98dca7b1f95a25ec173662fc4e785341', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\ContextFactoryInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\ImportExport\NodeExportService', 'Neos\ContentRepository\Domain\Service\ImportExport\NodeExportService', 'nodeExportService', 'ea4a8bd37fdbb0b1ceacd0cc7379f3a9', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\ImportExport\NodeExportService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Package\PackageManagerInterface', 'Neos\Flow\Package\PackageManager', 'packageManager', 'b44be8eaae4695ec4f42edfbf6f8880a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Package\PackageManagerInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'contextFactory',
  1 => 'nodeExportService',
  2 => 'packageManager',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Domain/Service/SiteExportService.php
#