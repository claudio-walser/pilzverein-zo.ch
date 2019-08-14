<?php 
namespace Neos\SiteKickstarter\Service;

/*
 * This file is part of the Neos.Kickstarterer package.
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
use Neos\ContentRepository\Domain\Repository\ContentDimensionRepository;
use Neos\ContentRepository\Utility;

/**
 * Service to generate site packages
 */
class GeneratorService_Original extends \Neos\Kickstarter\Service\GeneratorService
{
    /**
     * @Flow\Inject
     * @var PackageManagerInterface
     */
    protected $packageManager;

    /**
     * @Flow\Inject
     * @var ContentDimensionRepository
     */
    protected $contentDimensionRepository;

    /**
     * Generate a site package and fill it with boilerplate data.
     *
     * @param string $packageKey
     * @param string $siteName
     * @return array
     */
    public function generateSitePackage($packageKey, $siteName)
    {
        $this->packageManager->createPackage($packageKey, [
            'type' => 'neos-site',
            "require" => [
                "neos/neos" => "*",
                "neos/nodetypes" => "*"
            ],
            "suggest" => [
                "neos/seo" => "*"
            ]
        ]);

        $this->generateSitesXml($packageKey, $siteName);
        $this->generateSitesRootFusion($packageKey, $siteName);
        $this->generateSitesPageFusion($packageKey, $siteName);
        $this->generateDefaultTemplate($packageKey, $siteName);
        $this->generateNodeTypesConfiguration($packageKey);
        $this->generateAdditionalFolders($packageKey);

        return $this->generatedFiles;
    }

    /**
     * Generate a "Sites.xml" for the given package and name.
     *
     * @param string $packageKey
     * @param string $siteName
     * @return void
     */
    protected function generateSitesXml($packageKey, $siteName)
    {
        $templatePathAndFilename = 'resource://Neos.SiteKickstarter/Private/Generator/Content/Sites.xml';

        $contextVariables = [
            'packageKey' => $packageKey,
            'siteName' => htmlspecialchars($siteName),
            'siteNodeName' => $this->generateSiteNodeName($packageKey),
            'dimensions' => $this->contentDimensionRepository->findAll()
        ];

        $fileContent = $this->renderTemplate($templatePathAndFilename, $contextVariables);

        $sitesXmlPathAndFilename = $this->packageManager->getPackage($packageKey)->getResourcesPath() . 'Private/Content/Sites.xml';
        $this->generateFile($sitesXmlPathAndFilename, $fileContent);
    }

    /**
     * Generate basic root Fusion file.
     *
     * @param string $packageKey
     * @param string $siteName
     * @return void
     */
    protected function generateSitesRootFusion($packageKey, $siteName)
    {
        $templatePathAndFilename = 'resource://Neos.SiteKickstarter/Private/Generator/Fusion/Root.fusion';

        $contextVariables = [
            'packageKey' => $packageKey,
            'siteName' => $siteName,
            'siteNodeName' => $this->generateSiteNodeName($packageKey)
        ];

        $fileContent = $this->renderSimpleTemplate($templatePathAndFilename, $contextVariables);

        $sitesRootFusionPathAndFilename = $this->packageManager->getPackage($packageKey)->getResourcesPath() . 'Private/Fusion/Root.fusion';
        $this->generateFile($sitesRootFusionPathAndFilename, $fileContent);
    }

    /**
     * Generate basic Fusion documentNode file.
     *
     * @param string $packageKey
     * @param string $siteName
     * @return void
     */
    protected function generateSitesPageFusion($packageKey, $siteName)
    {
        $templatePathAndFilename = 'resource://Neos.SiteKickstarter/Private/Generator/Fusion/NodeTypes/Pages/Page.fusion';

        $contextVariables = [];
        $contextVariables['packageKey'] = $packageKey;
        $contextVariables['siteName'] = $siteName;
        $packageKeyDomainPart = substr(strrchr($packageKey, '.'), 1) ?: $packageKey;
        $contextVariables['siteNodeName'] = $packageKeyDomainPart;

        $fileContent = $this->renderSimpleTemplate($templatePathAndFilename, $contextVariables);

        $sitesPageFusionPathAndFilename = $this->packageManager->getPackage($packageKey)->getResourcesPath() . 'Private/Fusion/NodeTypes/Page.fusion';
        $this->generateFile($sitesPageFusionPathAndFilename, $fileContent);
    }

    /**
     * Generate basic template file.
     *
     * @param string $packageKey
     * @param string $siteName
     * @return void
     */
    protected function generateDefaultTemplate($packageKey, $siteName)
    {
        $templatePathAndFilename = 'resource://Neos.SiteKickstarter/Private/Generator/Template/SiteTemplate.html';

        $contextVariables = [
            'siteName' => $siteName,
            'neosViewHelper' => '{namespace neos=Neos\Neos\ViewHelpers}',
            'fusionViewHelper' => '{namespace fusion=Neos\Fusion\ViewHelpers}',
            'siteNodeName' => $this->generateSiteNodeName($packageKey)
        ];

        $fileContent = $this->renderTemplate($templatePathAndFilename, $contextVariables);

        $defaultTemplatePathAndFilename = $this->packageManager->getPackage($packageKey)->getResourcesPath() . 'Private/Templates/Page/Default.html';
        $this->generateFile($defaultTemplatePathAndFilename, $fileContent);
    }

    /**
     * Generate site node name based on the given package key
     *
     * @param string $packageKey
     * @return string
     */
    protected function generateSiteNodeName($packageKey)
    {
        return Utility::renderValidNodeName($packageKey);
    }

    /**
     * Generate a example NodeTypes.yaml
     *
     * @param string $packageKey
     * @throws \Neos\FluidAdaptor\Core\Exception
     */
    protected function generateNodeTypesConfiguration($packageKey)
    {
        $templatePathAndFilename = 'resource://Neos.SiteKickstarter/Private/Generator/Configuration/NodeTypes.Document.Page.yaml';

        $contextVariables = [
            'packageKey' => $packageKey
        ];

        $fileContent = $this->renderSimpleTemplate($templatePathAndFilename, $contextVariables);

        $sitesNodeTypesPathAndFilename = $this->packageManager->getPackage($packageKey)->getConfigurationPath() . 'NodeTypes.Document.Page.yaml';
        $this->generateFile($sitesNodeTypesPathAndFilename, $fileContent);
    }

    /**
     * Generate additional folders for site packages.
     *
     * @param string $packageKey
     */
    protected function generateAdditionalFolders($packageKey)
    {
        $resourcesPath = $this->packageManager->getPackage($packageKey)->getResourcesPath();
        $publicResourcesPath = Files::concatenatePaths([$resourcesPath, 'Public']);

        foreach (['Images', 'JavaScript', 'Styles'] as $publicResourceFolder) {
            Files::createDirectoryRecursively(Files::concatenatePaths([$publicResourcesPath, $publicResourceFolder]));
        }
    }

    /**
     * Simplified template rendering
     *
     * @param string $templatePathAndFilename
     * @param array $contextVariables
     * @return string
     */
    protected function renderSimpleTemplate($templatePathAndFilename, array $contextVariables)
    {
        $content = file_get_contents($templatePathAndFilename);
        foreach ($contextVariables as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
        }
        return $content;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\SiteKickstarter\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Service to generate site packages
 */
class GeneratorService extends GeneratorService_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\SiteKickstarter\Service\GeneratorService' === get_class($this)) {
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
  'packageManager' => 'Neos\\Flow\\Package\\PackageManagerInterface',
  'contentDimensionRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\ContentDimensionRepository',
  'objectManager' => '\\Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'inflector' => '\\Neos\\Kickstarter\\Utility\\Inflector',
  'reflectionService' => '\\Neos\\Flow\\Reflection\\ReflectionService',
  'generatedFiles' => 'array',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Package\PackageManagerInterface', 'Neos\Flow\Package\PackageManager', 'packageManager', 'b44be8eaae4695ec4f42edfbf6f8880a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Package\PackageManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\ContentDimensionRepository', 'Neos\ContentRepository\Domain\Repository\ContentDimensionRepository', 'contentDimensionRepository', '0e2f039d4a6a71ad5a12a908317ff91a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\ContentDimensionRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->inflector = new \Neos\Kickstarter\Utility\Inflector();
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Reflection\ReflectionService', 'Neos\Flow\Reflection\ReflectionService', 'reflectionService', '464c26aa94c66579c050985566cbfc1f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Reflection\ReflectionService'); });
        $this->Flow_Injected_Properties = array (
  0 => 'packageManager',
  1 => 'contentDimensionRepository',
  2 => 'objectManager',
  3 => 'inflector',
  4 => 'reflectionService',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.SiteKickstarter/Classes/Service/GeneratorService.php
#