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
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Package\Exception\InvalidPackageStateException;
use Neos\Flow\Package\Exception\UnknownPackageException;
use Neos\Flow\Package\PackageManagerInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Reflection\ReflectionService;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\ImageVariant;
use Neos\Neos\Domain\Model\Site;
use Neos\Neos\Domain\Repository\SiteRepository;
use Neos\Neos\EventLog\Domain\Service\EventEmittingService;
use Neos\Neos\Exception as NeosException;
use Neos\ContentRepository\Domain\Model\Workspace;
use Neos\ContentRepository\Domain\Repository\WorkspaceRepository;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\ContentRepository\Domain\Service\ImportExport\NodeImportService;
use Neos\ContentRepository\Domain\Utility\NodePaths;

/**
 * The Site Import Service
 *
 * @Flow\Scope("singleton")
 * @api
 */
class SiteImportService_Original
{
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
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var NodeImportService
     */
    protected $nodeImportService;

    /**
     * @Flow\Inject
     * @var WorkspaceRepository
     */
    protected $workspaceRepository;

    /**
     * @Flow\Inject
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var EventEmittingService
     */
    protected $eventEmittingService;

    /**
     * @var string
     */
    protected $resourcesPath = null;

    /**
     * An array that contains all fully qualified class names that extend ImageVariant including ImageVariant itself
     *
     * @var array<string>
     */
    protected $imageVariantClassNames = [];

    /**
     * An array that contains all fully qualified class names that implement AssetInterface
     *
     * @var array<string>
     */
    protected $assetClassNames = [];

    /**
     * An array that contains all fully qualified class names that extend \DateTime including \DateTime itself
     *
     * @var array<string>
     */
    protected $dateTimeClassNames = [];

    /**
     * @return void
     */
    public function initializeObject()
    {
        $this->imageVariantClassNames = $this->reflectionService->getAllSubClassNamesForClass(ImageVariant::class);
        array_unshift($this->imageVariantClassNames, ImageVariant::class);

        $this->assetClassNames = $this->reflectionService->getAllImplementationClassNamesForInterface(AssetInterface::class);

        $this->dateTimeClassNames = $this->reflectionService->getAllSubClassNamesForClass('DateTime');
        array_unshift($this->dateTimeClassNames, 'DateTime');
    }

    /**
     * Checks for the presence of Sites.xml in the given package and imports it if found.
     *
     * @param string $packageKey
     * @return Site the imported site
     * @throws NeosException
     */
    public function importFromPackage($packageKey)
    {
        if (!$this->packageManager->isPackageAvailable($packageKey)) {
            throw new NeosException(sprintf('Error: Package "%s" is not active.', $packageKey), 1384192950);
        }
        $contentPathAndFilename = sprintf('resource://%s/Private/Content/Sites.xml', $packageKey);
        if (!file_exists($contentPathAndFilename)) {
            throw new NeosException(sprintf('Error: No content found in package "%s".', $packageKey), 1384192955);
        }
        try {
            return $this->importFromFile($contentPathAndFilename);
        } catch (\Exception $exception) {
            throw new NeosException(sprintf('Error: During import an exception occurred: "%s".', $exception->getMessage()), 1300360480, $exception);
        }
    }

    /**
     * Imports one or multiple sites from the XML file at $pathAndFilename
     *
     * @param string $pathAndFilename
     * @return Site The imported site
     * @throws UnknownPackageException|InvalidPackageStateException|NeosException
     */
    public function importFromFile($pathAndFilename)
    {
        if (!file_exists($pathAndFilename)) {
            throw new NeosException(sprintf('Error: File "%s" does not exist.', $pathAndFilename), 1540934412);
        }

        /** @var Site $importedSite */
        $site = null;
        $xmlReader = new \XMLReader();
        if ($xmlReader->open($pathAndFilename, null, LIBXML_PARSEHUGE) === false) {
            throw new NeosException(sprintf('Error: XMLReader could not open "%s".', $pathAndFilename), 1540934199);
        }

        if ($this->workspaceRepository->findOneByName('live') === null) {
            $this->workspaceRepository->add(new Workspace('live'));
            $this->persistenceManager->persistAll();
        }

        while ($xmlReader->read()) {
            if ($xmlReader->nodeType != \XMLReader::ELEMENT || $xmlReader->name !== 'site') {
                continue;
            }

            $site = $this->getSiteByNodeName($xmlReader->getAttribute('siteNodeName'));
            $site->setName($xmlReader->getAttribute('name'));
            $site->setState((integer)$xmlReader->getAttribute('state'));

            $siteResourcesPackageKey = $xmlReader->getAttribute('siteResourcesPackageKey');
            if (!$this->packageManager->isPackageAvailable($siteResourcesPackageKey)) {
                throw new UnknownPackageException(sprintf('Package "%s" specified in the XML as site resources package does not exist.', $siteResourcesPackageKey), 1303891443);
            }
            if (!$this->packageManager->isPackageAvailable($siteResourcesPackageKey)) {
                throw new InvalidPackageStateException(sprintf('Package "%s" specified in the XML as site resources package is not active.', $siteResourcesPackageKey), 1303898135);
            }
            $site->setSiteResourcesPackageKey($siteResourcesPackageKey);

            $rootNode = $this->contextFactory->create()->getRootNode();
            // We fetch the workspace to be sure it's known to the persistence manager and persist all
            // so the workspace and site node are persisted before we import any nodes to it.
            $rootNode->getContext()->getWorkspace();
            $this->persistenceManager->persistAll();

            $sitesNode = $rootNode->getNode(SiteService::SITES_ROOT_PATH);
            if ($sitesNode === null) {
                $sitesNode = $rootNode->createNode(NodePaths::getNodeNameFromPath(SiteService::SITES_ROOT_PATH));
            }

            $this->nodeImportService->import($xmlReader, $sitesNode->getPath(), dirname($pathAndFilename) . '/Resources');
        }

        if ($site === null) {
            throw new NeosException(sprintf('The XML file did not contain a valid site node.'), 1418999522);
        }
        $this->emitSiteImported($site);
        return $site;
    }

    /**
     * Updates or creates a site with the given $siteNodeName
     *
     * @param string $siteNodeName
     * @return Site
     */
    protected function getSiteByNodeName($siteNodeName)
    {
        $site = $this->siteRepository->findOneByNodeName($siteNodeName);

        if ($site === null) {
            $site = new Site($siteNodeName);
            $this->siteRepository->add($site);
        } else {
            $this->siteRepository->update($site);
        }

        return $site;
    }


    /**
     * Signal that is triggered when a site has been imported successfully
     *
     * @Flow\Signal
     * @param Site $site The site that has been imported
     * @return void
     */
    protected function emitSiteImported(Site $site)
    {
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Domain\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * The Site Import Service
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class SiteImportService extends SiteImportService_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
        if (get_class($this) === 'Neos\Neos\Domain\Service\SiteImportService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Domain\Service\SiteImportService', $this);
        if ('Neos\Neos\Domain\Service\SiteImportService' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Neos\Domain\Service\SiteImportService';
        if ($isSameClass) {
            $this->initializeObject(1);
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
            'emitSiteImported' => array(
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\SignalSlot\SignalAspect', 'forwardSignalToDispatcher', $objectManager, NULL),
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
        if (get_class($this) === 'Neos\Neos\Domain\Service\SiteImportService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Domain\Service\SiteImportService', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();

        $isSameClass = get_class($this) === 'Neos\Neos\Domain\Service\SiteImportService';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Neos\Domain\Service\SiteImportService', $classParents) !== false && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== false;

        if ($isSameClass || $isClassProxy) {
            $this->initializeObject(2);
        }
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
     * @param Site $site The site that has been imported
     * @return void
     * @\Neos\Flow\Annotations\Signal
     */
    protected function emitSiteImported(\Neos\Neos\Domain\Model\Site $site)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitSiteImported'])) {
            $result = parent::emitSiteImported($site);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitSiteImported'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['site'] = $site;
            
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Domain\Service\SiteImportService', 'emitSiteImported', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitSiteImported']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitSiteImported']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Domain\Service\SiteImportService', 'emitSiteImported', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitSiteImported']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitSiteImported']);
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
  'packageManager' => 'Neos\\Flow\\Package\\PackageManagerInterface',
  'siteRepository' => 'Neos\\Neos\\Domain\\Repository\\SiteRepository',
  'contextFactory' => 'Neos\\ContentRepository\\Domain\\Service\\ContextFactoryInterface',
  'nodeImportService' => 'Neos\\ContentRepository\\Domain\\Service\\ImportExport\\NodeImportService',
  'workspaceRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\WorkspaceRepository',
  'reflectionService' => 'Neos\\Flow\\Reflection\\ReflectionService',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'eventEmittingService' => 'Neos\\Neos\\EventLog\\Domain\\Service\\EventEmittingService',
  'resourcesPath' => 'string',
  'imageVariantClassNames' => 'array<string>',
  'assetClassNames' => 'array<string>',
  'dateTimeClassNames' => 'array<string>',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Package\PackageManagerInterface', 'Neos\Flow\Package\PackageManager', 'packageManager', 'b44be8eaae4695ec4f42edfbf6f8880a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Package\PackageManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\SiteRepository', 'Neos\Neos\Domain\Repository\SiteRepository', 'siteRepository', '42785f5eca4dff104f1860b84f531a9f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\SiteRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\ContextFactoryInterface', 'Neos\Neos\Domain\Service\ContentContextFactory', 'contextFactory', '98dca7b1f95a25ec173662fc4e785341', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\ContextFactoryInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\ImportExport\NodeImportService', 'Neos\ContentRepository\Domain\Service\ImportExport\NodeImportService', 'nodeImportService', '20304a9d1fdf572ebb5c8638868c0514', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\ImportExport\NodeImportService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'Neos\ContentRepository\Domain\Repository\WorkspaceRepository', 'workspaceRepository', '9cacb5dd2ad57e06d6f8c82dd5707855', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\WorkspaceRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Reflection\ReflectionService', 'Neos\Flow\Reflection\ReflectionService', 'reflectionService', '464c26aa94c66579c050985566cbfc1f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Reflection\ReflectionService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\EventLog\Domain\Service\EventEmittingService', 'Neos\Neos\EventLog\Domain\Service\EventEmittingService', 'eventEmittingService', '5c51fbaaf43008ad76d5e80e333d9fcb', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\EventLog\Domain\Service\EventEmittingService'); });
        $this->Flow_Injected_Properties = array (
  0 => 'packageManager',
  1 => 'siteRepository',
  2 => 'contextFactory',
  3 => 'nodeImportService',
  4 => 'workspaceRepository',
  5 => 'reflectionService',
  6 => 'objectManager',
  7 => 'persistenceManager',
  8 => 'eventEmittingService',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Domain/Service/SiteImportService.php
#