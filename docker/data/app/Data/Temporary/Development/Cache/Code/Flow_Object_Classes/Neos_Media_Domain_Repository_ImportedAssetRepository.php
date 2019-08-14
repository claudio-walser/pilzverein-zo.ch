<?php 
namespace Neos\Media\Domain\Repository;

/*
 * This file is part of the Neos.Media package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
  *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Persistence\Repository;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class ImportedAssetRepository_Original extends Repository
{
    /**
     * @param string $assetSourceIdentifier
     * @param string $remoteAssetIdentifier
     * @return object
     */
    public function findOneByAssetSourceIdentifierAndRemoteAssetIdentifier(string $assetSourceIdentifier, string $remoteAssetIdentifier)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('assetSourceIdentifier', $assetSourceIdentifier),
                $query->equals('remoteAssetIdentifier', $remoteAssetIdentifier)
            )
        );
        return $query->execute()->getFirst();
    }

    /**
     * @param string $localAssetIdentifier
     * @return object
     */
    public function findOneByLocalAssetIdentifier(string $localAssetIdentifier)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->equals('localAssetIdentifier', $localAssetIdentifier)
        );
        return $query->execute()->getFirst();
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Media\Domain\Repository;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
final class ImportedAssetRepository extends ImportedAssetRepository_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Media\Domain\Repository\ImportedAssetRepository') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Media\Domain\Repository\ImportedAssetRepository', $this);
        parent::__construct();
        if ('Neos\Media\Domain\Repository\ImportedAssetRepository' === get_class($this)) {
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
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'entityClassName' => 'string',
  'defaultOrderings' => 'array',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Media\Domain\Repository\ImportedAssetRepository') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Media\Domain\Repository\ImportedAssetRepository', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'persistenceManager',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Media/Classes/Domain/Repository/ImportedAssetRepository.php
#