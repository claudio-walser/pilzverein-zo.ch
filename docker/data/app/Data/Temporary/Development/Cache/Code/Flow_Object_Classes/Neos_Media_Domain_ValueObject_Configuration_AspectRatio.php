<?php 
declare(strict_types=1);

namespace Neos\Media\Domain\ValueObject\Configuration;

/*
 * This file is part of the Neos.Media package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

class AspectRatio_Original
{
    public const ORIENTATION_LANDSCAPE = 'landscape';
    public const ORIENTATION_PORTRAIT = 'portrait';
    public const ORIENTATION_SQUARE = 'square';

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @param int $width
     * @param int $height
     */
    public function __construct(int $width, int $height)
    {
        if ($width <= 0 || $height <= 0) {
            throw new \InvalidArgumentException('aspect ratio: width and height must be positive integers.', 1549455812);
        }

        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @param string $ratio
     * @return self
     */
    public static function fromString(string $ratio): self
    {
        if (preg_match('/^\d+:\d+$/', $ratio) !== 1) {
            throw new \InvalidArgumentException(sprintf('Invalid aspect ratio specified ("%s").', $ratio), 1552641724);
        }
        [$width, $height] = explode(':', $ratio);
        return new static((int)$width, (int)$height);
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @return float
     */
    public function getRatio(): float
    {
        return $this->width / $this->height;
    }

    /**
     * @return string
     */
    public function getOrientation(): string
    {
        $ratio = $this->getRatio();
        if ($ratio === (float)1) {
            return self::ORIENTATION_SQUARE;
        }
        return $ratio > 1 ? self::ORIENTATION_LANDSCAPE : self::ORIENTATION_PORTRAIT;
    }

    /**
     * @return bool
     */
    public function isOrientationLandscape(): bool
    {
        return $this->getOrientation() === self::ORIENTATION_LANDSCAPE;
    }

    /**
     * @return bool
     */
    public function isOrientationPortrait(): bool
    {
        return $this->getOrientation() === self::ORIENTATION_PORTRAIT;
    }

    /**
     * @return bool
     */
    public function isOrientationSquare(): bool
    {
        return $this->getOrientation() === self::ORIENTATION_SQUARE;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->width . ':' . $this->height;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Media\Domain\ValueObject\Configuration;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 */
final class AspectRatio extends AspectRatio_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param int $width
     * @param int $height
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $width in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $height in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
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
  'width' => 'integer',
  'height' => 'integer',
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
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Media/Classes/Domain/ValueObject/Configuration/AspectRatio.php
#