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
use Neos\Flow\Exception as FlowException;
use Neos\Flow\Http\ContentStream;
use Neos\Flow\Http\Response;
use Neos\Flow\Security\Authorization\PrivilegeManagerInterface;
use Neos\Flow\Utility\Environment;
use Neos\FluidAdaptor\View\StandaloneView;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Fusion\Core\ExceptionHandlers\AbstractRenderingExceptionHandler;
use Neos\Fusion\Core\ExceptionHandlers\HtmlMessageHandler;
use Neos\Neos\Service\ContentElementWrappingService;

/**
 * A special exception handler that is used on the outer path to catch all unhandled exceptions and uses other exception
 * handlers depending on the login status.
 */
class PageHandler_Original extends AbstractRenderingExceptionHandler
{
    /**
     * @Flow\Inject
     * @var PrivilegeManagerInterface
     */
    protected $privilegeManager;

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
     * Handle an exception by displaying an error message inside the Neos backend, if logged in and not displaying the live workspace.
     *
     * @param string $fusionPath path causing the exception
     * @param \Exception $exception exception to handle
     * @param integer $referenceCode
     * @return string
     */
    protected function handle($fusionPath, \Exception $exception, $referenceCode)
    {
        $handler = new HtmlMessageHandler($this->environment->getContext()->isDevelopment());
        $handler->setRuntime($this->runtime);
        $output = $handler->handleRenderingException($fusionPath, $exception);
        $currentContext = $this->runtime->getCurrentContext();
        /** @var NodeInterface $documentNode */
        $documentNode = isset($currentContext['documentNode']) ? $currentContext['documentNode'] : null;

        /** @var NodeInterface $node */
        $node = isset($currentContext['node']) ? $currentContext['node'] : null;

        $fluidView = $this->prepareFluidView();
        $isBackend = false;
        /** @var NodeInterface $siteNode */
        $siteNode = isset($currentContext['site']) ? $currentContext['site'] : null;

        if ($documentNode === null) {
            // Actually we cannot be sure that $node is a document. But for fallback purposes this should be safe.
            $documentNode = $siteNode ? $siteNode : $node;
        }

        if ($documentNode !== null && $documentNode->getContext()->getWorkspace()->getName() !== 'live' && $this->privilegeManager->isPrivilegeTargetGranted('Neos.Neos:Backend.GeneralAccess')) {
            $isBackend = true;
            $fluidView->assign('metaData', $this->contentElementWrappingService->wrapCurrentDocumentMetadata($documentNode, '<div id="neos-document-metadata"></div>', $fusionPath));
        }

        $fluidView->assignMultiple([
            'isBackend' => $isBackend,
            'message' => $output,
            'node' => $node
        ]);

        return $this->wrapHttpResponse($exception, $fluidView->render());
    }

    /**
     * Renders an actual HTTP response including the correct status and cache control header.
     *
     * @param \Exception the exception
     * @param string $bodyContent
     * @return string
     */
    protected function wrapHttpResponse(\Exception $exception, $bodyContent)
    {
        $body = fopen('php://temp', 'rw');
        fputs($body, $bodyContent);
        rewind($body);

        /** @var Response $response */
        $response = (new Response())
            ->withStatus($exception instanceof FlowException ? $exception->getStatusCode() : 500)
            ->withBody(new ContentStream($body))
            ->withHeader('Cache-Control', 'no-store');

        return implode("\r\n", $response->renderHeaders()) . "\r\n\r\n" . $response->getContent();
    }

    /**
     * Prepare a Fluid view for rendering an error page with the Neos backend
     *
     * @return StandaloneView
     */
    protected function prepareFluidView()
    {
        $fluidView = new StandaloneView();
        $fluidView->setControllerContext($this->runtime->getControllerContext());
        $fluidView->setFormat('html');
        $fluidView->setTemplatePathAndFilename('resource://Neos.Neos/Private/Templates/Error/NeosBackendMessage.html');
        $fluidView->setLayoutRootPath('resource://Neos.Neos/Private/Layouts/');
        // FIXME find a better way than using templates as partials
        $fluidView->setPartialRootPath('resource://Neos.Neos/Private/Templates/FusionObjects/');
        return $fluidView;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Fusion\ExceptionHandlers;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A special exception handler that is used on the outer path to catch all unhandled exceptions and uses other exception
 * handlers depending on the login status.
 */
class PageHandler extends PageHandler_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\Neos\Fusion\ExceptionHandlers\PageHandler' === get_class($this)) {
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
  'privilegeManager' => 'Neos\\Flow\\Security\\Authorization\\PrivilegeManagerInterface',
  'contentElementWrappingService' => 'Neos\\Neos\\Service\\ContentElementWrappingService',
  'environment' => 'Neos\\Flow\\Utility\\Environment',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Authorization\PrivilegeManagerInterface', 'Neos\Flow\Security\Authorization\PrivilegeManager', 'privilegeManager', '68ada25ea2828278e185a684d1c86739', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Authorization\PrivilegeManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Service\ContentElementWrappingService', 'Neos\Neos\Service\ContentElementWrappingService', 'contentElementWrappingService', '5349aae33eae2b864859514294d80044', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Service\ContentElementWrappingService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Utility\Environment', 'Neos\Flow\Utility\Environment', 'environment', 'cce2af5ed9f80b598c497d98c35a5eb3', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Utility\Environment'); });
        $this->Flow_Injected_Properties = array (
  0 => 'privilegeManager',
  1 => 'contentElementWrappingService',
  2 => 'environment',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Fusion/ExceptionHandlers/PageHandler.php
#