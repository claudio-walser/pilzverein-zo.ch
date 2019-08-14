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
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\Utility\Algorithms;
use Neos\Utility\Files;
use Neos\Media\Domain\Model\Adjustment\ResizeImageAdjustment;
use Neos\Media\Domain\Model\Thumbnail;
use Neos\Media\Domain\Service\ImageService;
use Neos\Media\Exception;

/**
 * A system-generated preview version of a font document.
 *
 * Format support depend on GD/FreeType 2 and your configuration (Settings.yaml)
 *
 * @see http://php.net/manual/en/function.imagefttext.php
 */
class FontDocumentThumbnailGenerator_Original extends AbstractThumbnailGenerator
{
    /**
     * The priority for this thumbnail generator.
     *
     * @var integer
     * @api
     */
    protected static $priority = 5;

    /**
     * @Flow\Inject
     * @var ImageService
     */
    protected $imageService;

    /**
     * @param Thumbnail $thumbnail
     * @return boolean
     */
    public function canRefresh(Thumbnail $thumbnail)
    {
        return (
            $this->isExtensionSupported($thumbnail) &&
            function_exists('imagefttext')
        );
    }

    /**
     * @param Thumbnail $thumbnail
     * @return void
     * @throws Exception\NoThumbnailAvailableException
     */
    public function refresh(Thumbnail $thumbnail)
    {
        $temporaryPathAndFilename = null;
        try {
            $filename = pathinfo($thumbnail->getOriginalAsset()->getResource()->getFilename(), PATHINFO_FILENAME);

            $temporaryLocalCopyFilename = $thumbnail->getOriginalAsset()->getResource()->createTemporaryLocalCopy();
            $temporaryPathAndFilename = $this->environment->getPathToTemporaryDirectory() . 'ProcessedFontThumbnail-' . Algorithms::generateRandomString(13) . '.' . $filename . '.jpg';

            $width = 1000;
            $height = 1000;
            $im = imagecreate($width, $height);
            $red = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
            $black = imagecolorallocate($im, 0x00, 0x00, 0x00);

            imagefilledrectangle($im, 0, 0, $width, $height, $red);
            imagefttext($im, 48, 0, 80, 150, $black, $temporaryLocalCopyFilename, 'Neos Font Preview');
            imagefttext($im, 32, 0, 80, 280, $black, $temporaryLocalCopyFilename, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
            imagefttext($im, 32, 0, 80, 360, $black, $temporaryLocalCopyFilename, 'abcdefghijklmopqrstuvwxyz');
            imagefttext($im, 32, 0, 80, 440, $black, $temporaryLocalCopyFilename, '1234567890');
            imagefttext($im, 32, 0, 80, 560, $black, $temporaryLocalCopyFilename, '+ " * ç % & / ( ) = ? @ €');

            imagejpeg($im, $temporaryPathAndFilename);

            $resource = $this->resourceManager->importResource($temporaryPathAndFilename);
            $processedImageInfo = $this->resize($thumbnail, $resource);

            $thumbnail->setResource($processedImageInfo['resource']);
            $thumbnail->setWidth($processedImageInfo['width']);
            $thumbnail->setHeight($processedImageInfo['height']);

            Files::unlink($temporaryPathAndFilename);
        } catch (\Exception $exception) {
            Files::unlink($temporaryPathAndFilename);
            $filename = $thumbnail->getOriginalAsset()->getResource()->getFilename();
            $sha1 = $thumbnail->getOriginalAsset()->getResource()->getSha1();
            $message = sprintf('Unable to generate thumbnail for the given font (filename: %s, SHA1: %s)', $filename, $sha1);
            throw new Exception\NoThumbnailAvailableException($message, 1433109653, $exception);
        }
    }

    /**
     * @param Thumbnail $thumbnail
     * @param PersistentResource $resource
     * @return array
     * @throws Exception\ImageFileException
     */
    protected function resize(Thumbnail $thumbnail, PersistentResource $resource)
    {
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
            )
        ];

        return $this->imageService->processImage($resource, $adjustments);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Media\Domain\Model\ThumbnailGenerator;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A system-generated preview version of a font document.
 * 
 * Format support depend on GD/FreeType 2 and your configuration (Settings.yaml)
 */
class FontDocumentThumbnailGenerator extends FontDocumentThumbnailGenerator_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\Media\Domain\Model\ThumbnailGenerator\FontDocumentThumbnailGenerator' === get_class($this)) {
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
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Media/Classes/Domain/Model/ThumbnailGenerator/FontDocumentThumbnailGenerator.php
#