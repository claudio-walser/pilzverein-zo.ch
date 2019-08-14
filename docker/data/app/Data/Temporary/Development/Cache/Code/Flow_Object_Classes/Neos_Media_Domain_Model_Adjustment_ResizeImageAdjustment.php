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

use Doctrine\ORM\Mapping as ORM;
use Neos\Media\Imagine\Box;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface as ImagineImageInterface;
use Imagine\Image\Point;
use Neos\Flow\Annotations as Flow;
use Neos\Media\Domain\Model\ImageInterface;

/**
 * An adjustment for resizing an image
 *
 * @Flow\Entity
 */
class ResizeImageAdjustment_Original extends AbstractImageAdjustment
{
    /**
     * @var integer
     */
    protected $position = 20;

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
     * @var integer
     * @ORM\Column(nullable = true)
     */
    protected $maximumWidth;

    /**
     * @var integer
     * @ORM\Column(nullable = true)
     */
    protected $maximumHeight;

    /**
     * @var integer
     * @ORM\Column(nullable = true)
     */
    protected $minimumWidth;

    /**
     * @var integer
     * @ORM\Column(nullable = true)
     */
    protected $minimumHeight;

    /**
     * One of the ImagineImageInterface::RATIOMODE_* constants
     *
     * @var string
     * @ORM\Column(nullable = true)
     */
    protected $ratioMode;

    /**
     * @var boolean
     * @ORM\Column(nullable = true)
     */
    protected $allowUpScaling;

    /**
     * @Flow\InjectConfiguration(package="Neos.Media", path="image.defaultOptions.resizeFilter")
     * @var string
     */
    protected $filter;

    /**
     * Sets maximumHeight
     *
     * @param integer $maximumHeight
     * @return void
     * @api
     */
    public function setMaximumHeight($maximumHeight)
    {
        $this->maximumHeight = $maximumHeight;
    }

    /**
     * Returns maximumHeight
     *
     * @return integer
     * @api
     */
    public function getMaximumHeight()
    {
        return $this->maximumHeight;
    }

    /**
     * Sets maximumWidth
     *
     * @param integer $maximumWidth
     * @return void
     * @api
     */
    public function setMaximumWidth($maximumWidth)
    {
        $this->maximumWidth = $maximumWidth;
    }

    /**
     * Returns maximumWidth
     *
     * @return integer
     * @api
     */
    public function getMaximumWidth()
    {
        return $this->maximumWidth;
    }

    /**
     * Sets height
     *
     * @param integer $height
     * @return void
     * @api
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * Returns height
     *
     * @return integer
     * @api
     */
    public function getHeight()
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
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * Returns width
     *
     * @return integer
     * @api
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Sets minimumHeight
     *
     * @param integer $minimumHeight
     * @return void
     * @api
     */
    public function setMinimumHeight($minimumHeight)
    {
        $this->minimumHeight = $minimumHeight;
    }

    /**
     * Returns minimumHeight
     *
     * @return integer
     * @api
     */
    public function getMinimumHeight()
    {
        return $this->minimumHeight;
    }

    /**
     * Sets minimumWidth
     *
     * @param integer $minimumWidth
     * @return void
     * @api
     */
    public function setMinimumWidth($minimumWidth)
    {
        $this->minimumWidth = $minimumWidth;
    }

    /**
     * Returns minimumWidth
     *
     * @return integer
     * @api
     */
    public function getMinimumWidth()
    {
        return $this->minimumWidth;
    }

    /**
     * Sets ratioMode
     *
     * @param string $ratioMode One of the \Neos\Media\Domain\Model\ImageInterface::RATIOMODE_* constants
     * @return void
     * @api
     */
    public function setRatioMode($ratioMode)
    {
        $this->ratioMode = $ratioMode;
    }

    /**
     * Returns ratioMode
     *
     * @return string
     * @api
     */
    public function getRatioMode()
    {
        return $this->ratioMode;
    }

    /**
     * Returns allowUpScaling
     *
     * @return boolean
     */
    public function getAllowUpScaling()
    {
        return (boolean)$this->allowUpScaling;
    }

    /**
     * Sets allowUpScaling
     *
     * @param boolean $allowUpScaling
     * @return void
     */
    public function setAllowUpScaling($allowUpScaling)
    {
        $this->allowUpScaling = $allowUpScaling;
    }

    /**
     * Check if this Adjustment can or should be applied to its ImageVariant.
     *
     * @param ImagineImageInterface $image
     * @return boolean
     */
    public function canBeApplied(ImagineImageInterface $image)
    {
        $expectedDimensions = $this->calculateDimensions($image->getSize());

        return ((string)$expectedDimensions !== (string)$image->getSize());
    }

    /**
     * Applies this adjustment to the given Imagine Image object
     *
     * @param ImagineImageInterface $image
     * @return ImagineImageInterface
     * @internal Should never be used outside of the media package. Rely on the ImageService to apply your adjustments.
     */
    public function applyToImage(ImagineImageInterface $image)
    {
        $ratioMode = $this->ratioMode ?: ImageInterface::RATIOMODE_INSET;
        return $this->resize($image, $ratioMode, $this->filter);
    }

    /**
     * Calculates and returns the dimensions the image should have according all parameters set
     * in this adjustment.
     *
     * @param BoxInterface $originalDimensions Dimensions of the unadjusted image
     * @return BoxInterface
     */
    protected function calculateDimensions(BoxInterface $originalDimensions)
    {
        $newDimensions = clone $originalDimensions;

        switch (true) {
            // height and width are set explicitly:
            case ($this->width !== null && $this->height !== null):
                $newDimensions = $this->calculateWithFixedDimensions($originalDimensions, $this->width, $this->height);
                break;
            // only width is set explicitly:
            case ($this->width !== null):
                $newDimensions = $this->calculateScalingToWidth($originalDimensions, $this->width);
                break;
            // only height is set explicitly:
            case ($this->height !== null):
                $newDimensions = $this->calculateScalingToHeight($originalDimensions, $this->height);
                break;
        }

        // We apply maximum dimensions and scale the new dimensions proportionally down to fit into the maximum.
        if ($this->maximumWidth !== null && $newDimensions->getWidth() > $this->maximumWidth) {
            $newDimensions = $newDimensions->widen($this->maximumWidth);
        }

        if ($this->maximumHeight !== null && $newDimensions->getHeight() > $this->maximumHeight) {
            $newDimensions = $newDimensions->heighten($this->maximumHeight);
        }

        return $newDimensions;
    }

    /**
     * @param BoxInterface $originalDimensions
     * @param integer $requestedWidth
     * @param integer $requestedHeight
     * @return BoxInterface
     */
    protected function calculateWithFixedDimensions(BoxInterface $originalDimensions, $requestedWidth, $requestedHeight)
    {
        $ratioMode = $this->ratioMode ?: ImageInterface::RATIOMODE_INSET;

        if ($ratioMode === ImageInterface::RATIOMODE_OUTBOUND) {
            return $this->calculateOutboundBox($originalDimensions, $requestedWidth, $requestedHeight);
        }

        $newDimensions = clone $originalDimensions;

        $ratios = [
            $requestedWidth / $originalDimensions->getWidth(),
            $requestedHeight / $originalDimensions->getHeight()
        ];

        $ratio = min($ratios);
        $newDimensions = $newDimensions->scale($ratio);

        if ($this->getAllowUpScaling() === false && $originalDimensions->contains($newDimensions) === false) {
            return clone $originalDimensions;
        }

        return $newDimensions;
    }

    /**
     * Calculate the final dimensions for an outbound box. usually exactly the requested width and height unless that
     * would require upscaling and it is not allowed.
     *
     * @param BoxInterface $originalDimensions
     * @param integer $requestedWidth
     * @param integer $requestedHeight
     * @return BoxInterface
     */
    protected function calculateOutboundBox(BoxInterface $originalDimensions, $requestedWidth, $requestedHeight)
    {
        $newDimensions = new Box($requestedWidth, $requestedHeight);

        if ($this->getAllowUpScaling() === true || $originalDimensions->contains($newDimensions) === true) {
            return $newDimensions;
        }

        // We need to make sure that the new dimensions are such that no upscaling is needed.
        $ratios = [
            $originalDimensions->getWidth() / $requestedWidth,
            $originalDimensions->getHeight() / $requestedHeight
        ];

        $ratio = min($ratios);
        $newDimensions = $newDimensions->scale($ratio);

        return $newDimensions;
    }

    /**
     * Calculates new dimensions with a requested width applied. Takes upscaling into consideration.
     *
     * @param BoxInterface $originalDimensions
     * @param integer $requestedWidth
     * @return BoxInterface
     */
    protected function calculateScalingToWidth(BoxInterface $originalDimensions, $requestedWidth)
    {
        if ($this->getAllowUpScaling() === false && $requestedWidth >= $originalDimensions->getWidth()) {
            return $originalDimensions;
        }

        $newDimensions = clone $originalDimensions;
        $newDimensions = $newDimensions->widen($requestedWidth);

        return $newDimensions;
    }

    /**
     * Calculates new dimensions with a requested height applied. Takes upscaling into consideration.
     *
     * @param BoxInterface $originalDimensions
     * @param integer $requestedHeight
     * @return BoxInterface
     */
    protected function calculateScalingToHeight(BoxInterface $originalDimensions, $requestedHeight)
    {
        if ($this->getAllowUpScaling() === false && $requestedHeight >= $originalDimensions->getHeight()) {
            return $originalDimensions;
        }

        $newDimensions = clone $originalDimensions;
        $newDimensions = $newDimensions->heighten($requestedHeight);

        return $newDimensions;
    }

    /**
     * Executes the actual resizing operation on the Imagine image.
     * In case of an outbound resize the image will be resized and cropped.
     *
     * @param ImagineImageInterface $image
     * @param string $mode
     * @param string $filter
     * @return \Imagine\Image\ManipulatorInterface
     */
    protected function resize(ImagineImageInterface $image, $mode = ImageInterface::RATIOMODE_INSET, $filter = ImagineImageInterface::FILTER_UNDEFINED)
    {
        if ($mode !== ImageInterface::RATIOMODE_INSET &&
            $mode !== ImageInterface::RATIOMODE_OUTBOUND
        ) {
            throw new \InvalidArgumentException('Invalid mode specified');
        }

        $imageSize = $image->getSize();
        $requestedDimensions = $this->calculateDimensions($imageSize);

        $image->strip();

        $resizeDimensions = $requestedDimensions;
        if ($mode === ImageInterface::RATIOMODE_OUTBOUND) {
            $resizeDimensions = $this->calculateOutboundScalingDimensions($imageSize, $requestedDimensions);
        }

        $image->resize($resizeDimensions, $filter);

        if ($mode === ImageInterface::RATIOMODE_OUTBOUND) {
            $image->crop(new Point(
                max(0, round(($resizeDimensions->getWidth() - $requestedDimensions->getWidth()) / 2)),
                max(0, round(($resizeDimensions->getHeight() - $requestedDimensions->getHeight()) / 2))
            ), $requestedDimensions);
        }

        return $image;
    }

    /**
     * Calculates a resize dimension box that allows for outbound resize.
     * The scaled image will be bigger than the requested dimensions in one dimension and then cropped.
     *
     * @param BoxInterface $imageSize
     * @param BoxInterface $requestedDimensions
     * @return BoxInterface
     */
    protected function calculateOutboundScalingDimensions(BoxInterface $imageSize, BoxInterface $requestedDimensions)
    {
        $ratios = [
            $requestedDimensions->getWidth() / $imageSize->getWidth(),
            $requestedDimensions->getHeight() / $imageSize->getHeight()
        ];

        return $imageSize->scale(max($ratios));
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Media\Domain\Model\Adjustment;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * An adjustment for resizing an image
 * @\Neos\Flow\Annotations\Entity
 */
class ResizeImageAdjustment extends ResizeImageAdjustment_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface, \Neos\Flow\Persistence\Aspect\PersistenceMagicInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;

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
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Adjustment\ResizeImageAdjustment', '__construct', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Adjustment\ResizeImageAdjustment', '__construct', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
            return;
        }
        if ('Neos\Media\Domain\Model\Adjustment\ResizeImageAdjustment' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
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
        $this->Flow_Proxy_injectProperties();
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
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Adjustment\ResizeImageAdjustment', '__clone', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Adjustment\ResizeImageAdjustment', '__clone', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Media\Domain\Model\Adjustment\ResizeImageAdjustment', '__clone', $methodArguments, NULL, $result);
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
  'width' => 'integer',
  'height' => 'integer',
  'maximumWidth' => 'integer',
  'maximumHeight' => 'integer',
  'minimumWidth' => 'integer',
  'minimumHeight' => 'integer',
  'ratioMode' => 'string',
  'allowUpScaling' => 'boolean',
  'filter' => 'string',
  'imageVariant' => '\\Neos\\Media\\Domain\\Model\\ImageVariant',
  'Persistence_Object_Identifier' => 'string',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->filter = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Media.image.defaultOptions.resizeFilter');
        $this->Flow_Injected_Properties = array (
  0 => 'filter',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Media/Classes/Domain/Model/Adjustment/ResizeImageAdjustment.php
#