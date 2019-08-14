<?php 
namespace Neos\Media\Domain\Model\AssetSource\Neos;

/*
 * This file is part of the Neos.Media package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
  *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\ORM\EntityManagerInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Persistence\Exception\InvalidQueryException;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Media\Domain\Model\AssetCollection;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\AssetSource\AssetNotFoundExceptionInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryResultInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyRepositoryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetTypeFilter;
use Neos\Media\Domain\Model\AssetSource\SupportsCollectionsInterface;
use Neos\Media\Domain\Model\AssetSource\SupportsSortingInterface;
use Neos\Media\Domain\Model\AssetSource\SupportsTaggingInterface;
use Neos\Media\Domain\Model\Tag;
use Neos\Media\Domain\Repository\AssetRepository;
use Neos\Media\Domain\Repository\AudioRepository;
use Neos\Media\Domain\Repository\DocumentRepository;
use Neos\Media\Domain\Repository\ImageRepository;
use Neos\Media\Domain\Repository\VideoRepository;

class NeosAssetProxyRepository_Original implements AssetProxyRepositoryInterface, SupportsSortingInterface, SupportsCollectionsInterface, SupportsTaggingInterface
{
    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\Inject
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var NeosAssetSource
     */
    private $assetSource;

    /**
     * @var AssetRepository
     */
    private $assetRepository;

    /**
     * @var string
     */
    private $assetTypeFilter = 'All';

    /**
     * @var AssetCollection
     */
    private $activeAssetCollection;

    /**
     * @var array
     */
    private $assetRepositoryClassNames = [
        'All' => AssetRepository::class,
        'Image' => ImageRepository::class,
        'Document' => DocumentRepository::class,
        'Video' => VideoRepository::class,
        'Audio' => AudioRepository::class
    ];

    /**
     * @param NeosAssetSource $assetSource
     */
    public function __construct(NeosAssetSource $assetSource)
    {
        $this->assetSource = $assetSource;
    }

    /**
     * @return void
     */
    public function initializeObject(): void
    {
        $this->assetRepository = $this->objectManager->get($this->assetRepositoryClassNames[$this->assetTypeFilter]);
    }

    /**
     * Sets the property names to order results by. Expected like this:
     * array(
     *  'foo' => \Neos\Flow\Persistence\QueryInterface::ORDER_ASCENDING,
     *  'bar' => \Neos\Flow\Persistence\QueryInterface::ORDER_DESCENDING
     * )
     *
     * @param array $orderings The property names to order by by default
     * @return void
     * @api
     */
    public function orderBy(array $orderings):void
    {
        $this->assetRepository->setDefaultOrderings($orderings);
    }

    /**
     * @param AssetTypeFilter $assetType
     */
    public function filterByType(AssetTypeFilter $assetType = null): void
    {
        $this->assetTypeFilter = (string)$assetType ?: 'All';
        $this->initializeObject();
    }

    /**
     * NOTE: This needs to be refactored to use an asset collection identifier instead of Media's domain model before
     *       it can become a public API for other asset sources.
     *
     * @param AssetCollection $assetCollection
     */
    public function filterByCollection(AssetCollection $assetCollection = null): void
    {
        $this->activeAssetCollection = $assetCollection;
    }

    /**
     * @param string $identifier
     * @return AssetProxyInterface
     * @throws AssetNotFoundExceptionInterface
     */
    public function getAssetProxy(string $identifier): AssetProxyInterface
    {
        $asset = $this->assetRepository->findByIdentifier($identifier);
        if ($asset === null || !$asset instanceof AssetInterface) {
            throw new NeosAssetNotFoundException('The specified asset was not found.', 1509632861);
        }
        return new NeosAssetProxy($asset, $this->assetSource);
    }

    /**
     * @return AssetProxyQueryResultInterface
     */
    public function findAll(): AssetProxyQueryResultInterface
    {
        $queryResult = $this->assetRepository->findAll($this->activeAssetCollection);
        $query = $this->filterOutImportedAssetsFromOtherAssetSources($queryResult->getQuery());
        $query = $this->filterOutImageVariants($query);
        return new NeosAssetProxyQueryResult($query->execute(), $this->assetSource);
    }

    /**
     * @param string $searchTerm
     * @return AssetProxyQueryResultInterface
     */
    public function findBySearchTerm(string $searchTerm): AssetProxyQueryResultInterface
    {
        $queryResult = $this->assetRepository->findBySearchTermOrTags($searchTerm, [], $this->activeAssetCollection);
        $query = $this->filterOutImportedAssetsFromOtherAssetSources($queryResult->getQuery());
        $query = $this->filterOutImageVariants($query);
        return new NeosAssetProxyQueryResult($query->execute(), $this->assetSource);
    }

    /**
     * @param Tag $tag
     * @return AssetProxyQueryResultInterface
     */
    public function findByTag(Tag $tag): AssetProxyQueryResultInterface
    {
        $queryResult = $this->assetRepository->findByTag($tag, $this->activeAssetCollection);
        $query = $this->filterOutImportedAssetsFromOtherAssetSources($queryResult->getQuery());
        $query = $this->filterOutImageVariants($query);
        return new NeosAssetProxyQueryResult($query->execute(), $this->assetSource);
    }

    /**
     * @return AssetProxyQueryResultInterface
     */
    public function findUntagged(): AssetProxyQueryResultInterface
    {
        $queryResult = $this->assetRepository->findUntagged($this->activeAssetCollection);
        $query = $this->filterOutImportedAssetsFromOtherAssetSources($queryResult->getQuery());
        $query = $this->filterOutImageVariants($query);
        return new NeosAssetProxyQueryResult($query->execute(), $this->assetSource);
    }

    /**
     * @return int
     */
    public function countAll(): int
    {
        $query = $this->filterOutImportedAssetsFromOtherAssetSources($this->assetRepository->createQuery());
        $query = $this->filterOutAssetsFromOtherAssetCollections($query);
        $query = $this->filterOutImageVariants($query);
        return $query->count();
    }

    /**
     * @return int
     */
    public function countUntagged(): int
    {
        $query = $this->assetRepository->createQuery();
        try {
            $query->matching($query->isEmpty('tags'));
        } catch (InvalidQueryException $e) {
        }

        $query = $this->filterOutImportedAssetsFromOtherAssetSources($query);
        $query = $this->filterOutAssetsFromOtherAssetCollections($query);
        $query = $this->filterOutImageVariants($query);
        return $query->count();
    }

    /**
     * @param QueryInterface $query
     * @return QueryInterface
     */
    private function filterOutImportedAssetsFromOtherAssetSources(QueryInterface $query): QueryInterface
    {
        $constraint = $query->getConstraint();
        $query->matching(
            $query->logicalAnd([
                $constraint,
                $query->equals('assetSourceIdentifier', 'neos')
            ])
        );
        return $query;
    }

    /**
     * @param QueryInterface $query
     * @return QueryInterface
     */
    private function filterOutImageVariants(QueryInterface $query): QueryInterface
    {
        $queryBuilder = $query->getQueryBuilder();
        $queryBuilder->andWhere('e NOT INSTANCE OF Neos\Media\Domain\Model\ImageVariant');
        return $query;
    }

    /**
     * @param QueryInterface $query
     * @return QueryInterface
     */
    private function filterOutAssetsFromOtherAssetCollections(QueryInterface $query): QueryInterface
    {
        if ($this->activeAssetCollection === null) {
            return $query;
        }

        $constraints = $query->getConstraint();
        try {
            $query->matching(
                $query->logicalAnd([
                    $constraints,
                    $query->contains('assetCollections', $this->activeAssetCollection)
                ])
            );
        } catch (InvalidQueryException $e) {
        }
        return $query;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Media\Domain\Model\AssetSource\Neos;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 */
final class NeosAssetProxyRepository extends NeosAssetProxyRepository_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param NeosAssetSource $assetSource
     */
    public function __construct()
    {
        $arguments = func_get_args();

        if (!array_key_exists(0, $arguments)) $arguments[0] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Model\AssetSource\Neos\NeosAssetSource');
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $assetSource in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Media\Domain\Model\AssetSource\Neos\NeosAssetProxyRepository' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Media\Domain\Model\AssetSource\Neos\NeosAssetProxyRepository';
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
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'entityManager' => 'Doctrine\\ORM\\EntityManagerInterface',
  'assetSource' => 'Neos\\Media\\Domain\\Model\\AssetSource\\Neos\\NeosAssetSource',
  'assetRepository' => 'Neos\\Media\\Domain\\Repository\\AssetRepository',
  'assetTypeFilter' => 'string',
  'activeAssetCollection' => 'Neos\\Media\\Domain\\Model\\AssetCollection',
  'assetRepositoryClassNames' => 'array',
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
            $result = NULL;

        $isSameClass = get_class($this) === 'Neos\Media\Domain\Model\AssetSource\Neos\NeosAssetProxyRepository';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Media\Domain\Model\AssetSource\Neos\NeosAssetProxyRepository', $classParents) !== false && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== false;

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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Doctrine\ORM\EntityManagerInterface', 'Doctrine\ORM\EntityManagerInterface', 'entityManager', '68dcc38bb5d1acad752c62baff04cd05', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Doctrine\ORM\EntityManagerInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'objectManager',
  1 => 'entityManager',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Media/Classes/Domain/Model/AssetSource/Neos/NeosAssetProxyRepository.php
#