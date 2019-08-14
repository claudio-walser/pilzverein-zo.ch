<?php 

namespace Neos\Seo\Fusion;

/*
 * This file is part of the Neos.Seo package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Exception\NodeException;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;

class XmlSitemapUrlsImplementation_Original extends AbstractFusionObject
{
    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @Flow\Inject
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var array
     */
    protected $assetPropertiesByNodeType;

    /**
     * @var bool
     */
    protected $renderHiddenInIndex;

    /**
     * @var bool
     */
    protected $includeImageUrls;

    /**
     * @var NodeInterface
     */
    protected $startingPoint;

    /**
     * @var array
     */
    protected $items;

    /**
     * @return bool
     */
    public function getIncludeImageUrls(): bool
    {
        if ($this->includeImageUrls === null) {
            return $this->fusionValue('includeImageUrls');
        }

        return $this->includeImageUrls;
    }

    /**
     * @return bool
     */
    public function getRenderHiddenInIndex(): bool
    {
        if ($this->renderHiddenInIndex === null) {
            $this->renderHiddenInIndex = (boolean)$this->fusionValue('renderHiddenInIndex');
        }

        return $this->renderHiddenInIndex;
    }

    /**
     * @return NodeInterface
     */
    public function getStartingPoint(): NodeInterface
    {
        if ($this->startingPoint === null) {
            return $this->fusionValue('startingPoint');
        }

        return $this->startingPoint;
    }

    /**
     * @return void
     */
    public function initializeObject()
    {
        if ($this->getIncludeImageUrls()) {
            $relevantPropertyTypes = [
                'array<Neos\Media\Domain\Model\Asset>' => true,
                'Neos\Media\Domain\Model\Asset' => true,
                'Neos\Media\Domain\Model\ImageInterface' => true
            ];

            foreach ($this->nodeTypeManager->getNodeTypes(false) as $nodeType) {
                /** @var NodeType $nodeType */
                foreach ($nodeType->getProperties() as $propertyName => $propertyConfiguration) {
                    if (isset($relevantPropertyTypes[$nodeType->getPropertyType($propertyName)])) {
                        $this->assetPropertiesByNodeType[$nodeType->getName()][] = $propertyName;
                    }
                }
            }
        }
    }

    /**
     * @param array & $items
     * @param NodeInterface $node
     * @return void
     * @throws NodeException
     */
    protected function appendItems(array &$items, NodeInterface $node)
    {
        if ($this->isDocumentNodeToBeIndexed($node)) {
            $item = [
                'node' => $node,
                'lastModificationDateTime' => $node->getNodeData()->getLastModificationDateTime(),
                'priority' => $node->getProperty('xmlSitemapPriority') ?: '',
                'images' => [],
            ];
            if ($node->getProperty('xmlSitemapChangeFrequency')) {
                $item['changeFrequency'] = $node->getProperty('xmlSitemapChangeFrequency');
            }
            if ($this->getIncludeImageUrls()) {
                $this->resolveImages($node, $item);
            }
            $items[] = $item;
        }
        foreach ($node->getChildNodes('Neos.Neos:Document') as $childDocumentNode) {
            $this->appendItems($items, $childDocumentNode);
        }
    }

    /**
     * @param NodeInterface $node
     * @param array & $item
     * @return void
     * @throws NodeException
     */
    protected function resolveImages(NodeInterface $node, array &$item)
    {
        if (isset($this->assetPropertiesByNodeType[$node->getNodeType()->getName()]) && !empty($this->assetPropertiesByNodeType[$node->getNodeType()->getName()])) {

            foreach ($this->assetPropertiesByNodeType[$node->getNodeType()->getName()] as $propertyName) {
                if (is_array($node->getProperty($propertyName)) && !empty($node->getProperty($propertyName))) {
                    foreach ($node->getProperty($propertyName) as $asset) {
                        if ($asset instanceof ImageInterface) {
                            $item['images'][$this->persistenceManager->getIdentifierByObject($asset)] = $asset;
                        }
                    }
                } elseif ($node->getProperty($propertyName) instanceof ImageInterface) {
                    $item['images'][$this->persistenceManager->getIdentifierByObject($node->getProperty($propertyName))] = $node->getProperty($propertyName);
                }
            }
        }

        foreach ($node->getChildNodes('Neos.Neos:ContentCollection,Neos.Neos:Content') as $childNode) {
            $this->resolveImages($childNode, $item);
        }
    }

    /**
     * Return TRUE/FALSE if the node is currently hidden; taking the "renderHiddenInIndex" configuration
     * of the Menu Fusion object into account.
     *
     * @param NodeInterface $node
     * @return bool
     * @throws NodeException
     */
    protected function isDocumentNodeToBeIndexed(NodeInterface $node): bool
    {
        return !$node->getNodeType()->isOfType('Neos.Seo:NoindexMixin') && $node->isVisible()
            && ($this->getRenderHiddenInIndex() || !$node->isHiddenInIndex()) && $node->isAccessible()
            && $node->getProperty('metaRobotsNoindex') !== true;
    }

    /**
     * Evaluate this Fusion object and return the result
     *
     * @return array
     */
    public function evaluate(): array
    {
        if ($this->items === null) {
            $items = [];

            try {
                $this->appendItems($items, $this->getStartingPoint());
            } catch (NodeException $e) {
            }
            $this->items = $items;
        }

        return $this->items;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Seo\Fusion;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 */
class XmlSitemapUrlsImplementation extends XmlSitemapUrlsImplementation_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param Runtime $runtime
     * @param string $path
     * @param string $fusionObjectName
     */
    public function __construct()
    {
        $arguments = func_get_args();

        if (!array_key_exists(0, $arguments)) $arguments[0] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Fusion\Core\Runtime');
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $runtime in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $path in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(2, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $fusionObjectName in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Seo\Fusion\XmlSitemapUrlsImplementation' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Seo\Fusion\XmlSitemapUrlsImplementation';
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
  'nodeTypeManager' => 'Neos\\ContentRepository\\Domain\\Service\\NodeTypeManager',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\Doctrine\\PersistenceManager',
  'assetPropertiesByNodeType' => 'array',
  'renderHiddenInIndex' => 'boolean',
  'includeImageUrls' => 'boolean',
  'startingPoint' => 'Neos\\ContentRepository\\Domain\\Model\\NodeInterface',
  'items' => 'array',
  'runtime' => 'Neos\\Fusion\\Core\\Runtime',
  'path' => 'string',
  'fusionObjectName' => 'string',
  'tsValueCache' => 'array',
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

        $isSameClass = get_class($this) === 'Neos\Seo\Fusion\XmlSitemapUrlsImplementation';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Seo\Fusion\XmlSitemapUrlsImplementation', $classParents) !== false && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== false;

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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\NodeTypeManager', 'Neos\ContentRepository\Domain\Service\NodeTypeManager', 'nodeTypeManager', 'e525e2ecb65f7f9731d6537ddecd16d4', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\NodeTypeManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\Doctrine\PersistenceManager', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '90135528ef7af4a013b4d45f90addf22', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\Doctrine\PersistenceManager'); });
        $this->Flow_Injected_Properties = array (
  0 => 'nodeTypeManager',
  1 => 'persistenceManager',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Seo/Classes/Fusion/XmlSitemapUrlsImplementation.php
#