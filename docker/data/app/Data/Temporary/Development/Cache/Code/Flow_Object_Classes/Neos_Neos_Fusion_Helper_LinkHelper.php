<?php 
namespace Neos\Neos\Fusion\Helper;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Neos\Service\LinkingService;
use Neos\Flow\Http\Uri;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\ContentRepository\Domain\Model\NodeInterface;

/**
 * Eel helper for the linking service
 */
class LinkHelper_Original implements ProtectedContextAwareInterface
{
    /**
     * @Flow\Inject
     * @var LinkingService
     */
    protected $linkingService;

    /**
     * @param string|Uri $uri
     * @return boolean
     */
    public function hasSupportedScheme($uri)
    {
        return $this->linkingService->hasSupportedScheme($uri);
    }

    /**
     * @param string|Uri $uri
     * @return string
     */
    public function getScheme($uri)
    {
        return $this->linkingService->getScheme($uri);
    }

    /**
     * @param string|Uri $uri
     * @param NodeInterface $contextNode
     * @param ControllerContext $controllerContext
     * @return string
     */
    public function resolveNodeUri($uri, NodeInterface $contextNode, ControllerContext $controllerContext)
    {
        return $this->linkingService->resolveNodeUri($uri, $contextNode, $controllerContext);
    }

    /**
     * @param string|Uri $uri
     * @return string
     */
    public function resolveAssetUri($uri)
    {
        return $this->linkingService->resolveAssetUri($uri);
    }

    /**
     * @param string|Uri $uri
     * @param NodeInterface $contextNode
     * @return NodeInterface|AssetInterface|NULL
     */
    public function convertUriToObject($uri, NodeInterface $contextNode = null)
    {
        return $this->linkingService->convertUriToObject($uri, $contextNode);
    }

    /**
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Fusion\Helper;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Eel helper for the linking service
 */
class LinkHelper extends LinkHelper_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\Neos\Fusion\Helper\LinkHelper' === get_class($this)) {
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
  'linkingService' => 'Neos\\Neos\\Service\\LinkingService',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\LinkingService', 'Neos\Neos\Service\LinkingService', 'linkingService', '4473b90cfba243c7f02dd86c13d56fd2', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\LinkingService'); });
        $this->Flow_Injected_Properties = array (
  0 => 'linkingService',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Fusion/Helper/LinkHelper.php
#