<?php 
namespace Neos\RedirectHandler\DatabaseStorage;

/*
 * This file is part of the Neos.RedirectHandler.DatabaseStorage package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\RedirectHandler\DatabaseStorage\Domain\Model\Redirect;
use Neos\RedirectHandler\DatabaseStorage\Domain\Repository\RedirectRepository;
use Neos\RedirectHandler\Exception;
use Neos\RedirectHandler\Redirect as RedirectDto;
use Neos\RedirectHandler\RedirectInterface;
use Neos\RedirectHandler\Storage\RedirectStorageInterface;
use Neos\RedirectHandler\Traits\RedirectSignalTrait;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Routing\RouterCachingService;
use Neos\Flow\Persistence\PersistenceManagerInterface;

/**
 * Database Storage for the Redirects.
 *
 * @Flow\Scope("singleton")
 */
class RedirectStorage_Original implements RedirectStorageInterface
{
    use RedirectSignalTrait;

    /**
     * @Flow\Inject
     *
     * @var RedirectRepository
     */
    protected $redirectRepository;

    /**
     * @Flow\Inject
     *
     * @var RouterCachingService
     */
    protected $routerCachingService;

    /**
     * @Flow\InjectConfiguration(path="statusCode", package="Neos.RedirectHandler")
     *
     * @var array
     */
    protected $defaultStatusCode;

    /**
     * @Flow\Inject
     *
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * {@inheritdoc}
     */
    public function getOneBySourceUriPathAndHost($sourceUriPath, $host = null, $fallback = true)
    {
        $redirect = $this->redirectRepository->findOneBySourceUriPathAndHost($sourceUriPath, $host, $fallback);
        if ($redirect !== null) {
            return RedirectDto::create($redirect);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAll($host = null)
    {
        foreach ($this->redirectRepository->findAll($host) as $redirect) {
            yield RedirectDto::create($redirect);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDistinctHosts()
    {
        return $this->redirectRepository->findDistinctHosts();
    }

    /**
     * {@inheritdoc}
     */
    public function removeOneBySourceUriPathAndHost($sourceUriPath, $host = null)
    {
        $redirect = $this->redirectRepository->findOneBySourceUriPathAndHost($sourceUriPath, $host);
        if ($redirect === null) {
            return;
        }
        $this->redirectRepository->remove($redirect);
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll()
    {
        $this->redirectRepository->removeAll();
    }

    /**
     * {@inheritdoc}
     */
    public function removeByHost($host = null)
    {
        $this->redirectRepository->removeByHost($host);
    }

    /**
     * {@inheritdoc}
     */
    public function addRedirect($sourceUriPath, $targetUriPath, $statusCode = null, array $hosts = [])
    {
        $statusCode = $statusCode ?: (int) $this->defaultStatusCode['redirect'];
        $redirects = [];
        if ($hosts !== []) {
            array_map(function ($host) use ($sourceUriPath, $targetUriPath, $statusCode, &$redirects) {
                $redirects[] = $this->addRedirectByHost($sourceUriPath, $targetUriPath, $statusCode, $host);
            }, $hosts);
        } else {
            $redirects[] = $this->addRedirectByHost($sourceUriPath, $targetUriPath, $statusCode);
        }
        $this->emitRedirectCreated($redirects);

        return $redirects;
    }

    /**
     * Adds a redirect to the repository and updates related redirects accordingly.
     *
     * @param string $sourceUriPath the relative URI path that should trigger a redirect
     * @param string $targetUriPath the relative URI path the redirect should point to
     * @param int $statusCode the status code of the redirect header
     * @param string $host the host for the current redirect
     * @return Redirect the freshly generated redirect DTO instance
     * @api
     */
    protected function addRedirectByHost($sourceUriPath, $targetUriPath, $statusCode, $host = null)
    {
        $redirect = new Redirect($sourceUriPath, $targetUriPath, $statusCode, $host);
        $this->updateDependingRedirects($redirect);
        $this->persistenceManager->persistAll();
        $this->redirectRepository->add($redirect);
        $this->routerCachingService->flushCachesForUriPath($sourceUriPath);

        return RedirectDto::create($redirect);
    }

    /**
     * Updates affected redirects in order to avoid redundant or circular redirections.
     *
     * @param RedirectInterface $newRedirect
     * @throws Exception if creating the redirect would cause conflicts
     * @return void
     */
    protected function updateDependingRedirects(RedirectInterface $newRedirect)
    {
        /** @var $existingRedirectForSourceUriPath Redirect */
        $existingRedirectForSourceUriPath = $this->redirectRepository->findOneBySourceUriPathAndHost($newRedirect->getSourceUriPath(), $newRedirect->getHost(), false);
        if ($existingRedirectForSourceUriPath !== null) {
            $this->removeAndLog($existingRedirectForSourceUriPath, sprintf('Existing redirect for the source URI path "%s" removed.', $newRedirect->getSourceUriPath()));
            $this->routerCachingService->flushCachesForUriPath($existingRedirectForSourceUriPath->getSourceUriPath());
        }

        /** @var $existingRedirectForTargetUriPath Redirect */
        $existingRedirectForTargetUriPath = $this->redirectRepository->findOneBySourceUriPathAndHost($newRedirect->getTargetUriPath(), $newRedirect->getHost(), false);
        if ($existingRedirectForTargetUriPath !== null) {
            $this->removeAndLog($existingRedirectForTargetUriPath, sprintf('Existing redirect for the target URI path "%s" removed.', $newRedirect->getTargetUriPath()));
            $this->routerCachingService->flushCachesForUriPath($existingRedirectForTargetUriPath->getSourceUriPath());
        }

        $obsoleteRedirectInstances = $this->redirectRepository->findByTargetUriPathAndHost($newRedirect->getSourceUriPath(), $newRedirect->getHost());
        /** @var $obsoleteRedirect Redirect */
        foreach ($obsoleteRedirectInstances as $obsoleteRedirect) {
            if ($obsoleteRedirect->getSourceUriPath() === $newRedirect->getTargetUriPath()) {
                $this->redirectRepository->remove($obsoleteRedirect);
            } else {
                $obsoleteRedirect->setTargetUriPath($newRedirect->getTargetUriPath());
                $this->redirectRepository->update($obsoleteRedirect);
            }
        }
    }

    /**
     * @param RedirectInterface $redirect
     * @param string $message
     * @return void
     */
    protected function removeAndLog(RedirectInterface $redirect, $message)
    {
        $this->redirectRepository->remove($redirect);
        $this->redirectRepository->persistEntities();
        $this->_logger->log($message, LOG_NOTICE);
    }

    /**
     * Increment the hit counter for the given redirect.
     *
     * @param RedirectInterface $redirect
     * @return void
     * @api
     */
    public function incrementHitCount(RedirectInterface $redirect)
    {
        try {
            $this->redirectRepository->incrementHitCount($redirect);
        } catch (\Exception $exception) {
            $this->_logger->logException($exception);
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\RedirectHandler\DatabaseStorage;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Database Storage for the Redirects.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class RedirectStorage extends RedirectStorage_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\RedirectHandler\DatabaseStorage\RedirectStorage') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\RedirectHandler\DatabaseStorage\RedirectStorage', $this);
        if (get_class($this) === 'Neos\RedirectHandler\DatabaseStorage\RedirectStorage') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\RedirectHandler\Storage\RedirectStorageInterface', $this);
        if ('Neos\RedirectHandler\DatabaseStorage\RedirectStorage' === get_class($this)) {
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
  'redirectRepository' => 'Neos\\RedirectHandler\\DatabaseStorage\\Domain\\Repository\\RedirectRepository',
  'routerCachingService' => 'Neos\\Flow\\Mvc\\Routing\\RouterCachingService',
  'defaultStatusCode' => 'array',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  '_redirectService' => '\\Neos\\RedirectHandler\\RedirectService',
  '_logger' => '\\Neos\\Flow\\Log\\SystemLoggerInterface',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\RedirectHandler\DatabaseStorage\RedirectStorage') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\RedirectHandler\DatabaseStorage\RedirectStorage', $this);
        if (get_class($this) === 'Neos\RedirectHandler\DatabaseStorage\RedirectStorage') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\RedirectHandler\Storage\RedirectStorageInterface', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\RedirectHandler\DatabaseStorage\Domain\Repository\RedirectRepository', 'Neos\RedirectHandler\DatabaseStorage\Domain\Repository\RedirectRepository', 'redirectRepository', '3ff3b86554ec445d9d88e98aec11ead6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\RedirectHandler\DatabaseStorage\Domain\Repository\RedirectRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Mvc\Routing\RouterCachingService', 'Neos\Flow\Mvc\Routing\RouterCachingService', 'routerCachingService', '8fc40685a308919d1842ba4fb253c576', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\Routing\RouterCachingService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\RedirectHandler\RedirectService', 'Neos\RedirectHandler\RedirectService', '_redirectService', '6ce3055b0540d3b933b293d119f092b4', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\RedirectHandler\RedirectService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\SystemLoggerInterface', 'Neos\Flow\Log\Logger', '_logger', '717e9de4d0309f4f47c821b9257eb5c2', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'); });
        $this->defaultStatusCode = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.RedirectHandler.statusCode');
        $this->Flow_Injected_Properties = array (
  0 => 'redirectRepository',
  1 => 'routerCachingService',
  2 => 'persistenceManager',
  3 => '_redirectService',
  4 => '_logger',
  5 => 'defaultStatusCode',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.RedirectHandler.DatabaseStorage/Classes/RedirectStorage.php
#