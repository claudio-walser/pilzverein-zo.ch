<?php 
namespace Neos\Fusion\Afx\Dsl;

use Neos\Flow\Annotations as Flow;
use Neos\Fusion;
use Neos\Fusion\Core\DslInterface;
use Neos\Fusion\Afx\Service\AfxService;
use Neos\Fusion\Afx\Exception\AfxException;

/**
 * Class Fusion AFX Dsl
 *
 * @Flow\Scope("singleton")
 */
class AfxDslImplementation_Original implements DslInterface
{

    /**
     * Transpile the given dsl-code to fusion-code
     *
     * @param string $code
     * @return string
     * @throws Fusion\Exception
     */
    public function transpile($code)
    {
        try {
            return AfxService::convertAfxToFusion($code);
        } catch (AfxException $afxException) {
            throw new FusionException(sprintf('Error during AFX-parsing: %s', $afxException->getMessage()));
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Fusion\Afx\Dsl;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Class Fusion AFX Dsl
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class AfxDslImplementation extends AfxDslImplementation_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Fusion\Afx\Dsl\AfxDslImplementation') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Fusion\Afx\Dsl\AfxDslImplementation', $this);
        if (get_class($this) === 'Neos\Fusion\Afx\Dsl\AfxDslImplementation') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Fusion\Core\DslInterface', $this);
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
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Fusion\Afx\Dsl\AfxDslImplementation') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Fusion\Afx\Dsl\AfxDslImplementation', $this);
        if (get_class($this) === 'Neos\Fusion\Afx\Dsl\AfxDslImplementation') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Fusion\Core\DslInterface', $this);

        $this->Flow_setRelatedEntities();
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Fusion.Afx/Classes/Dsl/AfxDslImplementation.php
#