<?php 
namespace Neos\Neos\View;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\Projection\Content\TraversableNodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Response;
use Neos\Flow\I18n\Locale;
use Neos\Flow\I18n\Service;
use Neos\Flow\Mvc\View\AbstractView;
use Neos\Neos\Domain\Service\FusionService;
use Neos\Neos\Exception;
use Neos\ContentRepository\Domain\Model\NodeInterface as LegacyNodeInterface;
use Neos\Fusion\Core\Runtime;
use Neos\Fusion\Exception\RuntimeException;
use Neos\Flow\Security\Context;

/**
 * A Fusion view for Neos
 */
class FusionView_Original extends AbstractView
{
    /**
     * @Flow\Inject
     * @var Service
     */
    protected $i18nService;

    /**
     * This contains the supported options, their default values, descriptions and types.
     *
     * @var array
     */
    protected $supportedOptions = [
        'enableContentCache' => [null, 'Flag to enable content caching inside Fusion (overriding the global setting).', 'boolean']
    ];

    /**
     * @Flow\Inject
     * @var FusionService
     */
    protected $fusionService;

    /**
     * The Fusion path to use for rendering the node given in "value", defaults to "page".
     *
     * @var string
     */
    protected $fusionPath = 'root';

    /**
     * @var Runtime
     */
    protected $fusionRuntime;

    /**
     * @Flow\Inject
     * @var Context
     */
    protected $securityContext;

    /**
     * Renders the view
     *
     * @return string The rendered view
     * @throws \Exception if no node is given
     * @api
     */
    public function render()
    {
        $currentNode = $this->getCurrentNode();
        $currentSiteNode = $this->getCurrentSiteNode();
        $fusionRuntime = $this->getFusionRuntime($currentSiteNode);

        $dimensions = $currentNode->getContext()->getDimensions();
        if (array_key_exists('language', $dimensions) && $dimensions['language'] !== []) {
            $currentLocale = new Locale($dimensions['language'][0]);
            $this->i18nService->getConfiguration()->setCurrentLocale($currentLocale);
            $this->i18nService->getConfiguration()->setFallbackRule(['strict' => false, 'order' => array_reverse($dimensions['language'])]);
        }

        $fusionRuntime->pushContextArray([
            'node' => $currentNode,
            'documentNode' => $this->getClosestDocumentNode($currentNode) ?: $currentNode,
            'site' => $currentSiteNode,
            'editPreviewMode' => isset($this->variables['editPreviewMode']) ? $this->variables['editPreviewMode'] : null
        ]);
        try {
            $output = $fusionRuntime->render($this->fusionPath);
            $output = $this->mergeHttpResponseFromOutput($output, $fusionRuntime);
        } catch (RuntimeException $exception) {
            throw $exception->getPrevious();
        }
        $fusionRuntime->popContext();

        return $output;
    }

    /**
     * @param string $output
     * @param Runtime $fusionRuntime
     * @return string The message body without the message head
     */
    protected function mergeHttpResponseFromOutput($output, Runtime $fusionRuntime)
    {
        if (substr($output, 0, 5) === 'HTTP/') {
            $endOfHeader = strpos($output, "\r\n\r\n");
            if ($endOfHeader !== false) {
                $header = substr($output, 0, $endOfHeader + 4);
                try {
                    $renderedResponse = Response::createFromRaw($header);

                    /** @var Response $response */
                    $response = $fusionRuntime->getControllerContext()->getResponse();
                    $response->setStatus($renderedResponse->getStatusCode());
                    foreach ($renderedResponse->getHeaders()->getAll() as $headerName => $headerValues) {
                        $response->setHeader($headerName, $headerValues);
                    }

                    $output = substr($output, strlen($header));
                } catch (\InvalidArgumentException $exception) {
                }
            }
        }

        return $output;
    }

    /**
     * Is it possible to render $node with $his->fusionPath?
     *
     * @return boolean true if $node can be rendered at fusionPath
     *
     * @throws Exception
     */
    public function canRenderWithNodeAndPath()
    {
        $currentSiteNode = $this->getCurrentSiteNode();
        $fusionRuntime = $this->getFusionRuntime($currentSiteNode);

        return $fusionRuntime->canRender($this->fusionPath);
    }

    /**
     * Set the Fusion path to use for rendering the node given in "value"
     *
     * @param string $fusionPath
     * @return void
     */
    public function setFusionPath($fusionPath)
    {
        $this->fusionPath = $fusionPath;
    }

    /**
     * @return string
     */
    public function getFusionPath()
    {
        return $this->fusionPath;
    }

    /**
     * @param TraversableNodeInterface $node
     * @return TraversableNodeInterface
     */
    protected function getClosestDocumentNode(TraversableNodeInterface $node)
    {
        while ($node !== null && !$node->getNodeType()->isOfType('Neos.Neos:Document')) {
            $node = $node->findParentNode();
        }
        return $node;
    }

    /**
     * @return TraversableNodeInterface
     * @throws Exception
     */
    protected function getCurrentSiteNode(): TraversableNodeInterface
    {
        $currentNode = isset($this->variables['site']) ? $this->variables['site'] : null;
        if ($currentNode === null && $this->getCurrentNode() instanceof LegacyNodeInterface) {
            // fallback to Legacy node API
            /* @var $node LegacyNodeInterface */
            $node = $this->getCurrentNode();
            return $node->getContext()->getCurrentSiteNode();
        }
        if (!$currentNode instanceof TraversableNodeInterface) {
            throw new Exception('FusionView needs a variable \'site\' set with a Node object.', 1538996432);
        }
        return $currentNode;
    }

    /**
     * @return TraversableNodeInterface
     * @throws Exception
     */
    protected function getCurrentNode(): TraversableNodeInterface
    {
        $currentNode = isset($this->variables['value']) ? $this->variables['value'] : null;
        if (!$currentNode instanceof TraversableNodeInterface) {
            throw new Exception('FusionView needs a variable \'value\' set with a Node object.', 1329736456);
        }
        return $currentNode;
    }


    /**
     * @param TraversableNodeInterface $currentSiteNode
     * @return \Neos\Fusion\Core\Runtime
     */
    protected function getFusionRuntime(TraversableNodeInterface $currentSiteNode)
    {
        if ($this->fusionRuntime === null) {
            $this->fusionRuntime = $this->fusionService->createRuntime($currentSiteNode, $this->controllerContext);

            if (isset($this->options['enableContentCache']) && $this->options['enableContentCache'] !== null) {
                $this->fusionRuntime->setEnableContentCache($this->options['enableContentCache']);
            }
        }
        return $this->fusionRuntime;
    }

    /**
     * Clear the cached runtime instance on assignment of variables
     *
     * @param string $key
     * @param mixed $value
     * @return FusionView
     */
    public function assign($key, $value)
    {
        $this->fusionRuntime = null;
        return parent::assign($key, $value);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\View;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A Fusion view for Neos
 */
class FusionView extends FusionView_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param array $options
     * @throws Exception
     */
    public function __construct()
    {
        $arguments = func_get_args();
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Neos\View\FusionView' === get_class($this)) {
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
  'i18nService' => 'Neos\\Flow\\I18n\\Service',
  'supportedOptions' => 'array',
  'fusionService' => 'Neos\\Neos\\Domain\\Service\\FusionService',
  'fusionPath' => 'string',
  'fusionRuntime' => 'Neos\\Fusion\\Core\\Runtime',
  'securityContext' => 'Neos\\Flow\\Security\\Context',
  'options' => 'array',
  'variables' => 'array',
  'controllerContext' => 'Neos\\Flow\\Mvc\\Controller\\ControllerContext',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\I18n\Service', 'Neos\Flow\I18n\Service', 'i18nService', 'bdcad09aa1b6973b35f2987887987892', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\Service'); });
        $this->fusionService = new \Neos\Neos\Domain\Service\FusionService();
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Context', 'Neos\Flow\Security\Context', 'securityContext', 'f7e2ddeaebd191e228b8c2e4dc7f1f83', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Context'); });
        $this->Flow_Injected_Properties = array (
  0 => 'i18nService',
  1 => 'fusionService',
  2 => 'securityContext',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/View/FusionView.php
#