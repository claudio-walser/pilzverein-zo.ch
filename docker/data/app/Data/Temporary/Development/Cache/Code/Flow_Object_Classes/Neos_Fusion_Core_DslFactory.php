<?php 
namespace Neos\Fusion\Core;

/*
 * This file is part of the Neos.Fusion package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Fusion;

/**
 * This dsl factory takes care of instantiating a Fusion dsl transpilers.
 *
 * @Flow\Scope("singleton")
 */
class DslFactory_Original
{
    /**
     * @Flow\InjectConfiguration("dsl")
     * @var
     */
    protected $dslSettings;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManger;

    /**
     * @param string $identifier
     * @return DslInterface
     * @throws Fusion/Exception
     */
    public function create($identifier)
    {
        if (isset($this->dslSettings) && is_array($this->dslSettings) && isset($this->dslSettings[$identifier])) {
            $dslObjectName = $this->dslSettings[$identifier];
            if (!class_exists($dslObjectName)) {
                throw new Fusion\Exception(sprintf('The fusion dsl-object %s was not found.', $dslObjectName), 1490776462);
            }
            $dslObject = new $dslObjectName();
            if (!$dslObject instanceof DslInterface) {
                throw new Fusion\Exception(sprintf('The fusion dsl-object was of type %s but was supposed to be of type %s', get_class($dslObject), DslInterface::class), 1490776470);
            }
            return new $dslObject();
        }
        throw new Fusion\Exception(sprintf('The fusion dsl-object for the key %s was not configured', $identifier), 1490776550);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Fusion\Core;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * This dsl factory takes care of instantiating a Fusion dsl transpilers.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class DslFactory extends DslFactory_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Fusion\Core\DslFactory') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Fusion\Core\DslFactory', $this);
        if ('Neos\Fusion\Core\DslFactory' === get_class($this)) {
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
  'dslSettings' => NULL,
  'objectManger' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Fusion\Core\DslFactory') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Fusion\Core\DslFactory', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManger', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->dslSettings = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Fusion.dsl');
        $this->Flow_Injected_Properties = array (
  0 => 'objectManger',
  1 => 'dslSettings',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Fusion/Classes/Core/DslFactory.php
#