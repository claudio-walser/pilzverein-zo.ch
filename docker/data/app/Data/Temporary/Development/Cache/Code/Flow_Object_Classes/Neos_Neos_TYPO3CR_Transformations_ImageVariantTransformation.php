<?php 
namespace Neos\Neos\TYPO3CR\Transformations;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\ORM\EntityManagerInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\Media\Domain\Model\ImageVariant;
use Neos\Media\Domain\Repository\AssetRepository;
use Neos\Media\TypeConverter\ProcessingInstructionsConverter;
use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\ContentRepository\Migration\Transformations\AbstractTransformation;

/**
 * Convert serialized (old resource management) ImageVariants to new ImageVariants.
 */
class ImageVariantTransformation_Original extends AbstractTransformation
{
    /**
     * @Flow\Inject
     * @var AssetRepository
     */
    protected $assetRepository;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var ProcessingInstructionsConverter
     */
    protected $processingInstructionsConverter;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * Doctrine's Entity Manager.
     *
     * @Flow\Inject
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @param NodeData $node
     * @return boolean
     */
    public function isTransformable(NodeData $node)
    {
        return true;
    }

    /**
     * Change the property on the given node.
     *
     * @param NodeData $node
     * @return void
     */
    public function execute(NodeData $node)
    {
        foreach ($node->getNodeType()->getProperties() as $propertyName => $propertyConfiguration) {
            if (isset($propertyConfiguration['type']) && ($propertyConfiguration['type'] === ImageInterface::class || preg_match('/array\<.*\>/', $propertyConfiguration['type']))) {
                if (!isset($nodeProperties)) {
                    $nodeRecordQuery = $this->entityManager->getConnection()->prepare('SELECT properties FROM typo3_typo3cr_domain_model_nodedata WHERE persistence_object_identifier=?');
                    $nodeRecordQuery->execute([$this->persistenceManager->getIdentifierByObject($node)]);
                    $nodeRecord = $nodeRecordQuery->fetch(\PDO::FETCH_ASSOC);
                    $nodeProperties = unserialize($nodeRecord['properties']);
                }

                if (!isset($nodeProperties[$propertyName]) || empty($nodeProperties[$propertyName])) {
                    continue;
                }

                if ($propertyConfiguration['type'] === ImageInterface::class) {
                    $adjustments = [];
                    $oldVariantConfiguration = $nodeProperties[$propertyName];
                    if (is_array($oldVariantConfiguration)) {
                        foreach ($oldVariantConfiguration as $variantPropertyName => $property) {
                            switch (substr($variantPropertyName, 3)) {
                                case 'originalImage':
                                    /**
                                     * @var $originalAsset \Neos\Media\Domain\Model\Image
                                     */
                                    $originalAsset = $this->assetRepository->findByIdentifier($this->persistenceManager->getIdentifierByObject($property));
                                    break;
                                case 'processingInstructions':
                                    $adjustments = $this->processingInstructionsConverter->convertFrom($property, 'array');
                                    break;
                            }
                        }

                        $nodeProperties[$propertyName] = null;
                        if (isset($originalAsset)) {
                            $stream = $originalAsset->getResource()->getStream();
                            if ($stream === false) {
                                continue;
                            }

                            fclose($stream);
                            $newImageVariant = new ImageVariant($originalAsset);
                            foreach ($adjustments as $adjustment) {
                                $newImageVariant->addAdjustment($adjustment);
                            }
                            $originalAsset->addVariant($newImageVariant);
                            $this->assetRepository->update($originalAsset);
                            $nodeProperties[$propertyName] = $this->persistenceManager->getIdentifierByObject($newImageVariant);
                        }
                    }
                } elseif (preg_match('/array\<.*\>/', $propertyConfiguration['type'])) {
                    if (is_array($nodeProperties[$propertyName])) {
                        $convertedValue = [];
                        foreach ($nodeProperties[$propertyName] as $entryValue) {
                            if (!is_object($entryValue)) {
                                continue;
                            }

                            $stream = $entryValue->getResource()->getStream();
                            if ($stream === false) {
                                continue;
                            }

                            fclose($stream);
                            $existingObjectIdentifier = null;
                            try {
                                $existingObjectIdentifier = $this->persistenceManager->getIdentifierByObject($entryValue);
                                if ($existingObjectIdentifier !== null) {
                                    $convertedValue[] = $existingObjectIdentifier;
                                }
                            } catch (\Exception $exception) {
                            }
                        }
                        $nodeProperties[$propertyName] = $convertedValue;
                    }
                }
            }
        }

        if (isset($nodeProperties)) {
            $nodeUpdateQuery = $this->entityManager->getConnection()->prepare('UPDATE typo3_typo3cr_domain_model_nodedata SET properties=? WHERE persistence_object_identifier=?');
            $nodeUpdateQuery->execute([serialize($nodeProperties), $this->persistenceManager->getIdentifierByObject($node)]);
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\TYPO3CR\Transformations;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Convert serialized (old resource management) ImageVariants to new ImageVariants.
 */
class ImageVariantTransformation extends ImageVariantTransformation_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\Neos\TYPO3CR\Transformations\ImageVariantTransformation' === get_class($this)) {
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
  'assetRepository' => 'Neos\\Media\\Domain\\Repository\\AssetRepository',
  'resourceManager' => 'Neos\\Flow\\ResourceManagement\\ResourceManager',
  'processingInstructionsConverter' => 'Neos\\Media\\TypeConverter\\ProcessingInstructionsConverter',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'entityManager' => 'Doctrine\\ORM\\EntityManagerInterface',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Repository\AssetRepository', 'Neos\Media\Domain\Repository\AssetRepository', 'assetRepository', '45191f771a429c7decedb6fc0abbcc74', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Repository\AssetRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ResourceManagement\ResourceManager', 'Neos\Flow\ResourceManagement\ResourceManager', 'resourceManager', '5c4c2fb284addde18c78849a54b02875', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ResourceManagement\ResourceManager'); });
        $this->processingInstructionsConverter = new \Neos\Media\TypeConverter\ProcessingInstructionsConverter();
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Doctrine\ORM\EntityManagerInterface', 'Doctrine\ORM\EntityManagerInterface', 'entityManager', '68dcc38bb5d1acad752c62baff04cd05', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Doctrine\ORM\EntityManagerInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'assetRepository',
  1 => 'resourceManager',
  2 => 'processingInstructionsConverter',
  3 => 'persistenceManager',
  4 => 'entityManager',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/TYPO3CR/Transformations/ImageVariantTransformation.php
#