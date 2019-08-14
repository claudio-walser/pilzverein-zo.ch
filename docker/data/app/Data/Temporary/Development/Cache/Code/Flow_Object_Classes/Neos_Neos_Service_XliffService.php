<?php 
namespace Neos\Neos\Service;

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
use Neos\Cache\Frontend\VariableFrontend;
use Neos\Error\Messages\Result;
use Neos\Flow\I18n\Exception;
use Neos\Flow\I18n\Xliff\Service\XliffFileProvider;
use Neos\Flow\I18n\Xliff\Service\XliffReader;
use Neos\Flow\Package\PackageInterface;
use Neos\Flow\Package\PackageManager;
use Neos\Utility\Files;
use Neos\Flow\I18n\Locale;
use Neos\Flow\I18n\Service as LocalizationService;
use Neos\Utility\Unicode\Functions as UnicodeFunctions;

/**
 * The XLIFF service provides methods to find XLIFF files and parse them to json
 *
 * @Flow\Scope("singleton")
 */
class XliffService_Original
{
    /**
     * A relative path for translations inside the package resources.
     *
     * @var string
     */
    protected $xliffBasePath = 'Private/Translations/';

    /**
     * @Flow\Inject
     * @var XliffReader
     */
    protected $xliffReader;

    /**
     * @Flow\Inject
     * @var LocalizationService
     */
    protected $localizationService;

    /**
     * @Flow\Inject
     * @var VariableFrontend
     */
    protected $xliffToJsonTranslationsCache;

    /**
     * @Flow\InjectConfiguration(path="userInterface.scrambleTranslatedLabels", package="Neos.Neos")
     * @var boolean
     */
    protected $scrambleTranslatedLabels = false;

    /**
     * @Flow\InjectConfiguration(path="userInterface.translation.autoInclude", package="Neos.Neos")
     * @var array
     */
    protected $packagesRegisteredForAutoInclusion = [];

    /**
     * @Flow\Inject
     * @var XliffFileProvider
     */
    protected $xliffFileProvider;

    /**
     * @Flow\Inject
     * @var PackageManager
     */
    protected $packageManager;

    /**
     * Return the json array for a given locale, sourceCatalog, xliffPath and package.
     * The json will be cached.
     *
     * @param Locale $locale The locale
     * @return Result
     * @throws Exception
     */
    public function getCachedJson(Locale $locale)
    {
        $cacheIdentifier = md5($locale);

        if ($this->xliffToJsonTranslationsCache->has($cacheIdentifier)) {
            $json = $this->xliffToJsonTranslationsCache->get($cacheIdentifier);
        } else {
            $labels = [];

            foreach ($this->packagesRegisteredForAutoInclusion as $packageKey => $sourcesToBeIncluded) {
                if (!is_array($sourcesToBeIncluded)) {
                    continue;
                }

                $package = $this->packageManager->getPackage($packageKey);
                $sources = $this->collectPackageSources($package, $sourcesToBeIncluded);

                //get the xliff files for those sources
                foreach ($sources as $sourceName) {
                    $fileId = $packageKey . ':' . $sourceName;
                    $file = $this->xliffFileProvider->getFile($fileId, $locale);

                    foreach ($file->getTranslationUnits() as $key => $value) {
                        $valueToStore = !empty($value[0]['target']) ? $value[0]['target'] : $value[0]['source'];
                        if ($this->scrambleTranslatedLabels) {
                            $valueToStore = str_repeat('#', UnicodeFunctions::strlen($valueToStore));
                        }
                        $this->setArrayDataValue($labels, str_replace('.', '_', $packageKey) . '.' . str_replace('/', '_', $sourceName) . '.' . str_replace('.', '_', $key), $valueToStore);
                    }
                }
            }

            $json = json_encode($labels);
            $this->xliffToJsonTranslationsCache->set($cacheIdentifier, $json);
        }

        return $json;
    }

    /**
     * @return integer The current cache version identifier
     */
    public function getCacheVersion()
    {
        $version = $this->xliffToJsonTranslationsCache->get('ConfigurationVersion');
        if ($version === false) {
            $version = time();
            $this->xliffToJsonTranslationsCache->set('ConfigurationVersion', (string)$version);
        }
        return $version;
    }

    /**
     * Collect all sources found in the given package as array (key = source, value = true)
     * If sourcesToBeIncluded is an array, only those sources are returned what match the wildcard-patterns in the
     * array-values
     *
     * @param PackageInterface $package
     * @param array $sourcesToBeIncluded optional array of wildcard-patterns to filter the sources
     * @return array
     */
    protected function collectPackageSources(PackageInterface $package, $sourcesToBeIncluded = null): array
    {
        $packageKey = $package->getPackageKey();
        $sources = [];
        $translationPath = $package->getResourcesPath() . $this->xliffBasePath;

        if (!is_dir($translationPath)) {
            return [];
        }

        foreach (Files::readDirectoryRecursively($translationPath, '.xlf') as $filePath) {
            //remove translation path from path
            $source = trim(str_replace($translationPath, '', $filePath), '/');
            //remove language part from path
            $source = trim(substr($source, strpos($source, '/')), '/');
            //remove file extension
            $source = substr($source, 0, strrpos($source, '.'));

            $this->xliffReader->readFiles($filePath,
                function (\XMLReader $file, $offset, $version) use ($packageKey, &$sources, $source, $sourcesToBeIncluded) {
                    $targetPackageKey = $packageKey;
                    if ($version === '1.2') {
                        //in xliff v1.2 the packageKey or source can be overwritten via attributes
                        $targetPackageKey = $file->getAttribute('product-name') ?: $packageKey;
                        $source = $file->getAttribute('original') ?: $source;
                    }
                    if ($packageKey !== $targetPackageKey) {
                        return;
                    }
                    if (is_array($sourcesToBeIncluded)) {
                        $addSource = false;
                        foreach ($sourcesToBeIncluded as $sourcePattern) {
                            if (fnmatch($sourcePattern, $source)) {
                                $addSource = true;
                                break;
                            }
                        }
                        if (!$addSource) {
                            return;
                        }
                    }
                    $sources[$source] = true;
                }
            );
        }
        return array_keys($sources);
    }

    /**
     * Helper method to create the needed json array from a dotted xliff id
     *
     * @param array $arrayPointer
     * @param string $key
     * @param string $value
     * @return void
     */
    protected function setArrayDataValue(array &$arrayPointer, $key, $value)
    {
        $keys = explode('.', $key);

        // Extract the last key
        $lastKey = array_pop($keys);

        // Walk/build the array to the specified key
        while ($arrayKey = array_shift($keys)) {
            if (!array_key_exists($arrayKey, $arrayPointer)) {
                $arrayPointer[$arrayKey] = [];
            }
            $arrayPointer = &$arrayPointer[$arrayKey];
        }

        // Set the final key
        $arrayPointer[$lastKey] = $value;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * The XLIFF service provides methods to find XLIFF files and parse them to json
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class XliffService extends XliffService_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Neos\Service\XliffService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Service\XliffService', $this);
        if ('Neos\Neos\Service\XliffService' === get_class($this)) {
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
  'xliffBasePath' => 'string',
  'xliffReader' => 'Neos\\Flow\\I18n\\Xliff\\Service\\XliffReader',
  'localizationService' => 'Neos\\Flow\\I18n\\Service',
  'xliffToJsonTranslationsCache' => 'Neos\\Cache\\Frontend\\VariableFrontend',
  'scrambleTranslatedLabels' => 'boolean',
  'packagesRegisteredForAutoInclusion' => 'array',
  'xliffFileProvider' => 'Neos\\Flow\\I18n\\Xliff\\Service\\XliffFileProvider',
  'packageManager' => 'Neos\\Flow\\Package\\PackageManager',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Neos\Service\XliffService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Service\XliffService', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('', '', 'xliffToJsonTranslationsCache', '86b977cedd44a265ea918d396900f211', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Cache\CacheManager')->getCache('Neos_Neos_XliffToJsonTranslations'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\I18n\Xliff\Service\XliffReader', 'Neos\Flow\I18n\Xliff\Service\XliffReader', 'xliffReader', '4990bc445fecb613109ff648adcc7dd3', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\Xliff\Service\XliffReader'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\I18n\Service', 'Neos\Flow\I18n\Service', 'localizationService', 'bdcad09aa1b6973b35f2987887987892', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\Service'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\I18n\Xliff\Service\XliffFileProvider', 'Neos\Flow\I18n\Xliff\Service\XliffFileProvider', 'xliffFileProvider', '3b9cf3b2efb1cd7013c26486cb86a339', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\Xliff\Service\XliffFileProvider'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Package\PackageManager', 'Neos\Flow\Package\PackageManager', 'packageManager', '5969f0154592264b520c05738a0c9f97', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Package\PackageManager'); });
        $this->scrambleTranslatedLabels = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Neos.userInterface.scrambleTranslatedLabels');
        $this->packagesRegisteredForAutoInclusion = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Neos.userInterface.translation.autoInclude');
        $this->Flow_Injected_Properties = array (
  0 => 'xliffToJsonTranslationsCache',
  1 => 'xliffReader',
  2 => 'localizationService',
  3 => 'xliffFileProvider',
  4 => 'packageManager',
  5 => 'scrambleTranslatedLabels',
  6 => 'packagesRegisteredForAutoInclusion',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Service/XliffService.php
#