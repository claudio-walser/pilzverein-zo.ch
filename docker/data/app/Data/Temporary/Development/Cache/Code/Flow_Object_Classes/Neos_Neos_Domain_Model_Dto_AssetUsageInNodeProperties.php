<?php 
namespace Neos\Neos\Domain\Model\Dto;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\Dto\UsageReference;

/**
 * A DTO for storing information related to a usage of an asset in node properties.
 */
class AssetUsageInNodeProperties_Original extends UsageReference
{
    /**
     * @var string
     */
    protected $nodeIdentifier;

    /**
     * @var string
     */
    protected $workspaceName;

    /**
     * @var array
     */
    protected $dimensionValues;

    /**
     * @var string
     */
    protected $nodeTypeName;

    /**
     * @param AssetInterface $asset
     * @param string $nodeIdentifier
     * @param string $workspaceName
     * @param array $dimensionValues
     * @param string $nodeTypeName
     */
    public function __construct(AssetInterface $asset, $nodeIdentifier, $workspaceName, $dimensionValues, $nodeTypeName)
    {
        parent::__construct($asset);
        $this->nodeIdentifier = $nodeIdentifier;
        $this->workspaceName = $workspaceName;
        $this->dimensionValues = $dimensionValues;
        $this->nodeTypeName = $nodeTypeName;
    }

    /**
     * @return string
     */
    public function getNodeIdentifier()
    {
        return $this->nodeIdentifier;
    }

    /**
     * @return string
     */
    public function getWorkspaceName()
    {
        return $this->workspaceName;
    }

    /**
     * @return array
     */
    public function getDimensionValues()
    {
        return $this->dimensionValues;
    }

    /**
     * @return string
     */
    public function getNodeTypeName()
    {
        return $this->nodeTypeName;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Domain\Model\Dto;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A DTO for storing information related to a usage of an asset in node properties.
 */
class AssetUsageInNodeProperties extends AssetUsageInNodeProperties_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param AssetInterface $asset
     * @param string $nodeIdentifier
     * @param string $workspaceName
     * @param array $dimensionValues
     * @param string $nodeTypeName
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $asset in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $nodeIdentifier in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(2, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $workspaceName in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(3, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $dimensionValues in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(4, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $nodeTypeName in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
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
  'nodeIdentifier' => 'string',
  'workspaceName' => 'string',
  'dimensionValues' => 'array',
  'nodeTypeName' => 'string',
  'asset' => 'Neos\\Media\\Domain\\Model\\AssetInterface',
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
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Domain/Model/Dto/AssetUsageInNodeProperties.php
#