<?php 
namespace Neos\Media\Domain\Model\ThumbnailGenerator;

/*
 * This file is part of the Neos.Media package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Media\Domain\Model\Adjustment\QualityImageAdjustment;
use Neos\Media\Domain\Model\Adjustment\ResizeImageAdjustment;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\Media\Domain\Model\Thumbnail;
use Neos\Media\Domain\Service\ImageService;
use Neos\Media\Exception;

/**
 * A system-generated preview version of an Image
 */
class ImageThumbnailGenerator_Original extends AbstractThumbnailGenerator
{
    /**
     * The priority for this thumbnail generator.
     *
     * @var integer
     * @api
     */
    protected static $priority = 5;

    /**
     * @var ImageService
     * @Flow\Inject
     */
    protected $imageService;

    /**
     * @param Thumbnail $thumbnail
     * @return boolean
     */
    public function canRefresh(Thumbnail $thumbnail)
    {
        return (
            $thumbnail->getOriginalAsset() instanceof ImageInterface
        );
    }

    /**
     * @param Thumbnail $thumbnail
     * @return void
     * @throws Exception\NoThumbnailAvailableException
     */
    public function refresh(Thumbnail $thumbnail)
    {
        try {
            $adjustments = [
                new ResizeImageAdjustment(
                    [
                        'width' => $thumbnail->getConfigurationValue('width'),
                        'maximumWidth' => $thumbnail->getConfigurationValue('maximumWidth'),
                        'height' => $thumbnail->getConfigurationValue('height'),
                        'maximumHeight' => $thumbnail->getConfigurationValue('maximumHeight'),
                        'ratioMode' => $thumbnail->getConfigurationValue('ratioMode'),
                        'allowUpScaling' => $thumbnail->getConfigurationValue('allowUpScaling'),
                    ]
                ),
                new QualityImageAdjustment(
                    [
                        'quality' => $thumbnail->getConfigurationValue('quality')
                    ]
                )
            ];

            $targetFormat = $thumbnail->getConfigurationValue('format');
            $processedImageInfo = $this->imageService->processImage($thumbnail->getOriginalAsset()->getResource(), $adjustments, $targetFormat);

            $thumbnail->setResource($processedImageInfo['resource']);
            $thumbnail->setWidth($processedImageInfo['width']);
            $thumbnail->setHeight($processedImageInfo['height']);
            $thumbnail->setQuality($processedImageInfo['quality']);
        } catch (\Exception $exception) {
            $message = sprintf('Unable to generate thumbnail for the given image (filename: %s, SHA1: %s)', $thumbnail->getOriginalAsset()->getResource()->getFilename(), $thumbnail->getOriginalAsset()->getResource()->getSha1());
            throw new Exception\NoThumbnailAvailableException($message, 1433109654, $exception);
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Media\Domain\Model\ThumbnailGenerator;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A system-generated preview version of an Image
 */
class ImageThumbnailGenerator extends ImageThumbnailGenerator_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\Media\Domain\Model\ThumbnailGenerator\ImageThumbnailGenerator' === get_class($this)) {
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
  'priority' => 'integer',
  'imageService' => 'Neos\\Media\\Domain\\Service\\ImageService',
  'environment' => 'Neos\\Flow\\Utility\\Environment',
  'imagineService' => 'Imagine\\Image\\ImagineInterface',
  'resourceManager' => 'Neos\\Flow\\ResourceManagement\\ResourceManager',
  'options' => 'array',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Media\Domain\Service\ImageService', 'Neos\Media\Domain\Service\ImageService', 'imageService', '7b342e21f2438a00b80abb708ce6db88', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Media\Domain\Service\ImageService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Utility\Environment', 'Neos\Flow\Utility\Environment', 'environment', 'cce2af5ed9f80b598c497d98c35a5eb3', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Utility\Environment'); });
        $this->imagineService = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Imagine\Image\ImagineInterface');
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ResourceManagement\ResourceManager', 'Neos\Flow\ResourceManagement\ResourceManager', 'resourceManager', '5c4c2fb284addde18c78849a54b02875', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ResourceManagement\ResourceManager'); });
        $this->options = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Media.thumbnailGenerators');
        $this->Flow_Injected_Properties = array (
  0 => 'imageService',
  1 => 'environment',
  2 => 'imagineService',
  3 => 'resourceManager',
  4 => 'options',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Media/Classes/Domain/Model/ThumbnailGenerator/ImageThumbnailGenerator.php
#