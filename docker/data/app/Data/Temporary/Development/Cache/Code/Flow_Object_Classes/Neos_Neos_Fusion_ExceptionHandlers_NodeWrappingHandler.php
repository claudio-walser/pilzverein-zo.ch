<?php 
namespace Neos\Neos\Fusion\ExceptionHandlers;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Authorization\PrivilegeManagerInterface;
use Neos\Flow\Utility\Environment;
use Neos\Neos\Service\ContentElementWrappingService;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Fusion\Core\ExceptionHandlers\AbstractRenderingExceptionHandler;
use Neos\Fusion\Core\ExceptionHandlers\ContextDependentHandler;

/**
 * Provides a nicely formatted html error message
 * including all wrappers of an content element (i.e. menu allowing to
 * discard the broken element)
 */
class NodeWrappingHandler_Original extends AbstractRenderingExceptionHandler
{
    /**
     * @Flow\Inject
     * @var ContentElementWrappingService
     */
    protected $contentElementWrappingService;

    /**
     * @Flow\Inject
     * @var Environment
     */
    protected $environment;

    /**
     * @Flow\Inject
     * @var PrivilegeManagerInterface
     */
    protected $privilegeManager;

    /**
     * renders the exception to nice html content element to display, edit, remove, ...
     *
     * @param string $fusionPath - path causing the exception
     * @param \Exception $exception - exception to handle
     * @param integer $referenceCode - might be unset
     * @return string
     */
    protected function handle($fusionPath, \Exception $exception, $referenceCode)
    {
        $handler = new ContextDependentHandler();
        $handler->setRuntime($this->runtime);
        $output = $handler->handleRenderingException($fusionPath, $exception);

        $currentContext = $this->getRuntime()->getCurrentContext();
        if (isset($currentContext['node'])) {
            /** @var NodeInterface $node */
            $node = $currentContext['node'];
            $applicationContext = $this->environment->getContext();
            if ($applicationContext->isProduction() && $this->privilegeManager->isPrivilegeTargetGranted('Neos.Neos:Backend.GeneralAccess') && $node->getContext()->getWorkspaceName() !== 'live') {
                $output = '<div class="neos-rendering-exception"><div class="neos-rendering-exception-title">Failed to render element' . $output . '</div></div>';
            }

            return $this->contentElementWrappingService->wrapContentObject($node, $output, $fusionPath);
        }

        return $output;
    }

    /**
     * appends the given reference code to the exception's message
     * unless it is unset
     *
     * @param \Exception $exception
     * @param $referenceCode
     * @return string
     */
    protected function getMessage(\Exception $exception, $referenceCode)
    {
        if (isset($referenceCode)) {
            return sprintf('%s (%s)', $exception->getMessage(), $referenceCode);
        }
        return $exception->getMessage();
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Fusion\ExceptionHandlers;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Provides a nicely formatted html error message
 * including all wrappers of an content element (i.e. menu allowing to
 * discard the broken element)
 */
class NodeWrappingHandler extends NodeWrappingHandler_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\Neos\Fusion\ExceptionHandlers\NodeWrappingHandler' === get_class($this)) {
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
  'contentElementWrappingService' => 'Neos\\Neos\\Service\\ContentElementWrappingService',
  'environment' => 'Neos\\Flow\\Utility\\Environment',
  'privilegeManager' => 'Neos\\Flow\\Security\\Authorization\\PrivilegeManagerInterface',
  'runtime' => 'Neos\\Fusion\\Core\\Runtime',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\ContentElementWrappingService', 'Neos\Neos\Service\ContentElementWrappingService', 'contentElementWrappingService', '5349aae33eae2b864859514294d80044', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\ContentElementWrappingService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Utility\Environment', 'Neos\Flow\Utility\Environment', 'environment', 'cce2af5ed9f80b598c497d98c35a5eb3', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Utility\Environment'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Authorization\PrivilegeManagerInterface', 'Neos\Flow\Security\Authorization\PrivilegeManager', 'privilegeManager', '68ada25ea2828278e185a684d1c86739', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Authorization\PrivilegeManagerInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'contentElementWrappingService',
  1 => 'environment',
  2 => 'privilegeManager',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Fusion/ExceptionHandlers/NodeWrappingHandler.php
#