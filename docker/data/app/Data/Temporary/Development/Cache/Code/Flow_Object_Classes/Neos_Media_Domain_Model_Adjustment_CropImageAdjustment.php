<?php 
namespace Neos\Media\Domain\Model\Adjustment;

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
use Doctrine\ORM\Mapping as ORM;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface as ImagineImageInterface;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\Media\Domain\ValueObject\Configuration\AspectRatio;

/**
 * An adjustment for cropping an image
 *
 * @Flow\Entity
 */
class CropImageAdjustment_Original extends AbstractImageAdjustment
{
    /**
     * @var integer
     */
    protected $position = 10;

    /**
     * @var integer
     * @ORM\Column(nullable = true)
     */
    protected $x;

    /**
     * @var integer
     * @ORM\Column(nullable = true)
     */
    protected $y;
    /**
     * @var integer
     * @ORM\Column(nullable = true)
     */
    protected $width;

    /**
     * @var integer
     * @ORM\Column(nullable = true)
     */
    protected $height;

    /**
     * @var string
     * @ORM\Column(nullable = true)
     */
    protected $aspectRatioAsString;

    /**
     * Sets height
     *
     * @param integer $height
     * @return void
     * @api
     */
    public function setHeight(int $height = null): void
    {
        $this->height = $height;
        $this->aspectRatioAsString = null;
    }

    /**
     * Returns height
     *
     * @return integer|null
     * @api
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * Sets width
     *
     * @param integer $width
     * @return void
     * @api
     */
    public function setWidth(int $width = null): void
    {
        $this->width = $width;
        $this->aspectRatioAsString = null;
    }

    /**
     * Returns width
     *
     * @return integer
     * @api
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * Sets x
     *
     * @param integer $x
     * @return void
     * @api
     */
    public function setX(int $x = null): void
    {
        $this->x = $x;
        $this->aspectRatioAsString = null;
    }

    /**
     * Returns x
     *
     * @return integer
     * @api
     */
    public function getX(): ?int
    {
        return $this->x;
    }

    /**
     * Sets y
     *
     * @param integer $y
     * @return void
     * @api
     */
    public function setY(int $y = null): void
    {
        $this->y = $y;
        $this->aspectRatioAsString = null;
    }

    /**
     * Returns y
     *
     * @return integer
     * @api
     */
    public function getY(): ?int
    {
        return $this->y;
    }

    /**
     * This setter accepts strings in order to make configuration / mapping of settings easier.
     *
     * @param AspectRatio | string | null $aspectRatio
     */
    public function setAspectRatio($aspectRatio = null): void
    {
        if ($aspectRatio === null) {
            $this->aspectRatioAsString = null;
            return;
        }
        if (!$aspectRatio instanceof AspectRatio && !is_string($aspectRatio)) {
            throw new \InvalidArgumentException(sprintf('Aspect ratio must be either AspectRatio or string, %s given.', gettype($aspectRatio)), 1552652570);
        }
        if (is_string($aspectRatio)) {
            $aspectRatio = AspectRatio::fromString($aspectRatio);
        }

        $this->aspectRatioAsString = (string)$aspectRatio;
        $this->x = null;
        $this->y = null;
        $this->width = null;
        $this->height = null;
    }

    /**
     * @return AspectRatio|null
     */
    public function getAspectRatio(): ?AspectRatio
    {
        return $this->aspectRatioAsString ? AspectRatio::fromString($this->aspectRatioAsString) : null;
    }

    /**
     * Calculates the actual position and dimensions of the cropping area based on the given image
     * dimensions and desired aspect ratio.
     *
     * @param int $originalWidth Width of the original image
     * @param int $originalHeight Height of the original image
     * @param AspectRatio $desiredAspectRatio The desired aspect ratio
     * @return array Returns an array containing x, y, width and height
     */
    public static function calculateDimensionsByAspectRatio(int $originalWidth, int $originalHeight, AspectRatio $desiredAspectRatio): array
    {
        $newWidth = $originalWidth;
        $newHeight = round($originalWidth / $desiredAspectRatio->getRatio());
        $newX = 0;
        $newY = round(($originalHeight - $newHeight) / 2);

        if ($newHeight > $originalHeight) {
            $newHeight = $originalHeight;
            $newY = 0;
            $newWidth = round($originalHeight * $desiredAspectRatio->getRatio());
            $newX = round(($originalWidth - $newWidth) / 2);
        }

        return [$newX, $newY, $newWidth, $newHeight];
    }

    /**
     * Check if this Adjustment can or should be applied to its ImageVariant.
     *
     * @param ImagineImageInterface $image
     * @return bool
     */
    public function canBeApplied(ImagineImageInterface $image): bool
    {
        if ($this->aspectRatioAsString !== null) {
            $desiredAspectRatio = AspectRatio::fromString($this->aspectRatioAsString);
            $originalAspectRatio = new AspectRatio($image->getSize()->getWidth(), $image->getSize()->getHeight());
            return $originalAspectRatio->getRatio() !== $desiredAspectRatio->getRatio();
        }

        return !(
            $this->x === 0 &&
            $this->y === 0 &&
            $image->getSize()->getWidth() === $this->width &&
            $image->getSize()->getHeight() === $this->height
        );
    }

    /**
     * Applies this adjustment to the given Imagine Image object
     *
     * @param ImagineImageInterface $image
     * @return ImagineImageInterface
     * @internal Should never be used outside of the media package. Rely on the ImageService to apply your adjustments.
     */
    public function applyToImage(ImagineImageInterface $image): ImagineImageInterface
    {
        $desiredAspectRatio = $this->getAspectRatio();
        if ($desiredAspectRatio !== null) {
            $originalWidth = $image->getSize()->getWidth();
            $originalHeight = $image->getSize()->getHeight();

            [$newX, $newY, $newWidth, $newHeight] = self::calculateDimensionsByAspectRatio($originalWidth, $originalHeight, $desiredAspectRatio);

            $point = new Point($newX, $newY);
            $box = new Box($newWidth, $newHeight);
        } else {
            $point = new Point($this->x, $this->y);
            $box = new Box($this->width, $this->height);
        }
        return $image->crop($point, $box);
    }

    /**
     * Refits the crop proportions to be the maximum size within the image boundaries.
     *
     * @param ImageInterface $image
     * @return void
     */
    public function refit(ImageInterface $image): void
    {
        $this->x = 0;
        $this->y = 0;

        $ratio = $this->getWidth() / $image->getWidth();
        $this->setWidth($image->getWidth());
        $this->setHeight($this->getHeight() / $ratio);

        if ($this->getHeight() > $image->getHeight()) {
            $ratio = $this->getHeight() / $image->getHeight();
            $this->setWidth($this->getWidth() / $ratio);
            $this->setHeight($image->getHeight());
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Media\Domain\Model\Adjustment;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * An adjustment for cropping an image
 * @\Neos\Flow\Annotations\Entity
 */
class CropImageAdjustment extends CropImageAdjustment_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface, \Neos\Flow\Persistence\Aspect\PersistenceMagicInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(length=40)
     * introduced by Neos\Flow\Persistence\Aspect\PersistenceMagicAspect
     */
    protected $Persistence_Object_Identifier = NULL;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     * @param array $options configuration options - depends on the actual adjustment
     * @throws \InvalidArgumentException
     */
    public function __construct()
    {
        $arguments = func_get_args();

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'])) {
        call_user_func_array('parent::__construct', $arguments);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'] = true;
            try {
            
                $methodArguments = [];

                if (array_key_exists(0, $arguments)) $methodArguments['options'] = $arguments[0];
            
                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['Neos\Flow\Aop\Advice\BeforeAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['Neos\Flow\Aop\Advice\BeforeAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Adjustment\CropImageAdjustment', '__construct', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Adjustment\CropImageAdjustment', '__construct', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
            return;
        }
    }

    /**
     * Autogenerated Proxy Method
     */
    protected function Flow_Aop_Proxy_buildMethodsAndAdvicesArray()
    {
        if (method_exists(get_parent_class(), 'Flow_Aop_Proxy_buildMethodsAndAdvicesArray') && is_callable('parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray')) parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        $objectManager = \Neos\Flow\Core\Bootstrap::$staticObjectManager;
        $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array(
            '__clone' => array(
                'Neos\Flow\Aop\Advice\BeforeAdvice' => array(
                    new \Neos\Flow\Aop\Advice\BeforeAdvice('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', 'generateUuid', $objectManager, NULL),
                ),
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', 'cloneObject', $objectManager, NULL),
                ),
            ),
            '__construct' => array(
                'Neos\Flow\Aop\Advice\BeforeAdvice' => array(
                    new \Neos\Flow\Aop\Advice\BeforeAdvice('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', 'generateUuid', $objectManager, NULL),
                ),
            ),
        );
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        $this->Flow_setRelatedEntities();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __clone()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone'])) {
            $result = NULL;

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone'] = true;
            try {
            
                $methodArguments = [];

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\BeforeAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\BeforeAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Adjustment\CropImageAdjustment', '__clone', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Adjustment\CropImageAdjustment', '__clone', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Adjustment\CropImageAdjustment', '__clone', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone']);
        }
        return $result;
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
  'position' => 'integer',
  'x' => 'integer',
  'y' => 'integer',
  'width' => 'integer',
  'height' => 'integer',
  'aspectRatioAsString' => 'string',
  'imageVariant' => '\\Neos\\Media\\Domain\\Model\\ImageVariant',
  'Persistence_Object_Identifier' => 'string',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Media/Classes/Domain/Model/Adjustment/CropImageAdjustment.php
#