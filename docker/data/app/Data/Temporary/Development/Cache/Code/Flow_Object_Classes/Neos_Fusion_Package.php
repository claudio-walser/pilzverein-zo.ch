<?php 
namespace Neos\Fusion;

/*
 * This file is part of the Neos.Fusion package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Cache\CacheManager;
use Neos\Flow\Core\Booting\Sequence;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Monitor\FileMonitor;
use Neos\Flow\Package\Package as BasePackage;
use Neos\Flow\Package\PackageManager;
use Neos\Flow\Package\PackageManagerInterface;
use Neos\Fusion\Core\Cache\FileMonitorListener;

/**
 * The Neos Fusion Package
 */
class Package_Original extends BasePackage
{
    /**
     * Invokes custom PHP code directly after the package manager has been initialized.
     *
     * @param Bootstrap $bootstrap The current bootstrap
     * @return void
     */
    public function boot(Bootstrap $bootstrap)
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();

        $context = $bootstrap->getContext();
        if (!$context->isProduction()) {
            $dispatcher->connect(Sequence::class, 'afterInvokeStep', function ($step) use ($bootstrap, $dispatcher) {
                if ($step->getIdentifier() === 'neos.flow:systemfilemonitor') {
                    $fusionFileMonitor = FileMonitor::createFileMonitorAtBoot('Fusion_Files', $bootstrap);
                    /** @var PackageManager $packageManager */
                    $packageManager = $bootstrap->getEarlyInstance(PackageManagerInterface::class);
                    foreach ($packageManager->getFlowPackages() as $packageKey => $package) {
                        if ($packageManager->isPackageFrozen($packageKey)) {
                            continue;
                        }

                        $fusionPaths = [
                            $package->getResourcesPath() . 'Private/Fusion'
                        ];
                        foreach ($fusionPaths as $fusionPath) {
                            if (is_dir($fusionPath)) {
                                $fusionFileMonitor->monitorDirectory($fusionPath);
                            }
                        }
                    }

                    $fusionFileMonitor->detectChanges();
                    $fusionFileMonitor->shutdownObject();
                }

                if ($step->getIdentifier() === 'neos.flow:cachemanagement') {
                    $cacheManager = $bootstrap->getEarlyInstance(CacheManager::class);
                    $listener = new FileMonitorListener($cacheManager);
                    $dispatcher->connect(FileMonitor::class, 'filesHaveChanged', $listener, 'flushContentCacheOnFileChanges');
                }
            });
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Fusion;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * The Neos Fusion Package
 */
class Package extends Package_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param string $packageKey Key of this package
     * @param string $composerName
     * @param string $packagePath Absolute path to the location of the package's composer manifest
     * @param array $autoloadConfiguration
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $packageKey in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $composerName in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(2, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $packagePath in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
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
  'packageKey' => 'string',
  'composerName' => 'string',
  'packagePath' => 'string',
  'namespaces' => 'array<string>',
  'autoloadTypes' => 'array<string>',
  'autoloadConfiguration' => 'array',
  'flattenedAutoloadConfiguration' => 'array',
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
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Fusion/Classes/Package.php
#