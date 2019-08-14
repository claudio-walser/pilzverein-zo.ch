<?php 
namespace Neos\ContentRepository\Migration\Domain\Model;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */


/**
 * Migration.
 *
 */
class Migration_Original
{
    /**
     * Version that was migrated to.
     *
     * @var string
     */
    protected $version;

    /**
     * @var MigrationConfiguration
     */
    protected $upConfiguration;

    /**
     * @var MigrationConfiguration
     */
    protected $downConfiguration;

    /**
     * @param string $version
     * @param array $configuration
     */
    public function __construct($version, array $configuration)
    {
        $this->version = $version;
        $this->upConfiguration = new MigrationConfiguration($configuration[MigrationStatus::DIRECTION_UP]);
        $this->downConfiguration = new MigrationConfiguration($configuration[MigrationStatus::DIRECTION_DOWN]);
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return MigrationConfiguration
     */
    public function getDownConfiguration()
    {
        return $this->downConfiguration;
    }

    /**
     * @return MigrationConfiguration
     */
    public function getUpConfiguration()
    {
        return $this->upConfiguration;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\ContentRepository\Migration\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Migration.
 */
class Migration extends Migration_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param string $version
     * @param array $configuration
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $version in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $configuration in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
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
  'version' => 'string',
  'upConfiguration' => 'Neos\\ContentRepository\\Migration\\Domain\\Model\\MigrationConfiguration',
  'downConfiguration' => 'Neos\\ContentRepository\\Migration\\Domain\\Model\\MigrationConfiguration',
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
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.ContentRepository/Classes/Migration/Domain/Model/Migration.php
#