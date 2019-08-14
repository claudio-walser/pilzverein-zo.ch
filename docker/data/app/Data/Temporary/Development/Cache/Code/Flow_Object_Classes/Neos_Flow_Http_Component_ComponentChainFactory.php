<?php 
namespace Neos\Flow\Http\Component;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Utility\PositionalArraySorter;

/**
 * Creates a new ComponentChain according to the specified settings
 *
 * @Flow\Scope("singleton")
 */
class ComponentChainFactory_Original
{
    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param array $chainConfiguration
     * @return ComponentChain
     * @throws Exception
     */
    public function create(array $chainConfiguration)
    {
        if (empty($chainConfiguration)) {
            return null;
        }
        $arraySorter = new PositionalArraySorter($chainConfiguration);
        $sortedChainConfiguration = $arraySorter->toArray();

        $chainComponents = [];
        foreach ($sortedChainConfiguration as $componentName => $configuration) {
            $componentOptions = isset($configuration['componentOptions']) ? $configuration['componentOptions'] : [];
            if (isset($configuration['chain'])) {
                $component = $this->create($configuration['chain']);
            } else {
                if (!isset($configuration['component'])) {
                    throw new Exception(sprintf('Component chain could not be created because no component class name is configured for component "%s"', $componentName), 1401718283);
                }
                $component = $this->objectManager->get($configuration['component'], $componentOptions);
                if (!$component instanceof ComponentInterface) {
                    throw new Exception(sprintf('Component chain could not be created because the class "%s" does not implement the ComponentInterface, in component "%s" does not implement', $configuration['component'], $componentName), 1401718283);
                }
            }
            $chainComponents[] = $component;
        }

        return new ComponentChain(['components' => $chainComponents]);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Http\Component;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Creates a new ComponentChain according to the specified settings
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class ComponentChainFactory extends ComponentChainFactory_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Flow\Http\Component\ComponentChainFactory') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Http\Component\ComponentChainFactory', $this);
        if ('Neos\Flow\Http\Component\ComponentChainFactory' === get_class($this)) {
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
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Flow\Http\Component\ComponentChainFactory') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Http\Component\ComponentChainFactory', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'objectManager',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Framework/Neos.Flow/Classes/Http/Component/ComponentChainFactory.php
#