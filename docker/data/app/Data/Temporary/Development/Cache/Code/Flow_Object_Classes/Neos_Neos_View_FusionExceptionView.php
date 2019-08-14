<?php 
declare(strict_types=1);

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

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Mvc\ActionResponse;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Flow\Mvc\View\AbstractView;
use Neos\Fusion\Exception\RuntimeException;
use Neos\Neos\Domain\Service\FusionService;
use Neos\Fusion\Core\Runtime as FusionRuntime;
use Neos\Neos\Domain\Repository\SiteRepository;
use Neos\Neos\Domain\Repository\DomainRepository;
use Neos\Neos\Domain\Service\ContentContextFactory;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\I18n\Locale;
use Neos\Flow\I18n\Service;
use Neos\Flow\Security\Context;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Routing\UriBuilder;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Mvc\Controller\Arguments;

class FusionExceptionView_Original extends AbstractView implements ViewInterface
{
    /**
     * This contains the supported options, their default values, descriptions and types.
     * @var array
     */
    protected $supportedOptions = [
        'enableContentCache' => ['defaultValue', true, 'boolean'],
    ];

    /**
     * @Flow\Inject
     * @var Bootstrap
     */
    protected $bootstrap;

    /**
     * @var ObjectManagerInterface
     * @Flow\Inject
     */
    protected $objectManager;

    /**
     * @Flow\Inject
     * @var Service
     */
    protected $i18nService;

    /**
     * @var FusionService
     * @Flow\Inject
     */
    protected $fusionService;

    /**
     * @var FusionRuntime
     */
    protected $fusionRuntime;

    /**
     * @var SiteRepository
     * @Flow\Inject
     */
    protected $siteRepository;

    /**
     * @var DomainRepository
     * @Flow\Inject
     */
    protected $domainRepository;

    /**
     * @var ContentContextFactory
     * @Flow\Inject
     */
    protected $contentContextFactory;

    /**
     * @return string
     * @throws \Neos\Flow\I18n\Exception\InvalidLocaleIdentifierException
     * @throws \Neos\Fusion\Exception
     * @throws \Neos\Neos\Domain\Exception
     */
    public function render()
    {
        $domain = $this->domainRepository->findOneByActiveRequest();

        if ($domain) {
            $site = $domain->getSite();
        } else {
            $site = $this->siteRepository->findDefault();
        }

        $httpRequest = $this->bootstrap->getActiveRequestHandler()->getHttpRequest();
        $request = new ActionRequest($httpRequest);
        $request->setControllerPackageKey('Neos.Neos');
        $request->setFormat('html');
        $uriBuilder = new UriBuilder();
        $uriBuilder->setRequest($request);
        $controllerContext = new ControllerContext(
            $request,
            new ActionResponse(),
            new Arguments([]),
            $uriBuilder
        );

        $securityContext = $this->objectManager->get(Context::class);
        $securityContext->setRequest($request);

        $contentContext = $this->contentContextFactory->create(['currentSite' => $site]);
        $currentSiteNode = $contentContext->getCurrentSiteNode();

        $fusionRuntime = $this->getFusionRuntime($currentSiteNode, $controllerContext);

        $dimensions = $currentSiteNode->getContext()->getDimensions();
        if (array_key_exists('language', $dimensions) && $dimensions['language'] !== []) {
            $currentLocale = new Locale($dimensions['language'][0]);
            $this->i18nService->getConfiguration()->setCurrentLocale($currentLocale);
            $this->i18nService->getConfiguration()->setFallbackRule(['strict' => false, 'order' => array_reverse($dimensions['language'])]);
        }

        $fusionRuntime->pushContextArray(array_merge(
            $this->variables,
            [
                'node' => $currentSiteNode,
                'documentNode' => $currentSiteNode,
                'site' => $currentSiteNode,
                'editPreviewMode' => null
            ]
        ));

        try {
            $output = $fusionRuntime->render('error');
            $output = $this->extractBodyFromOutput($output);
        } catch (RuntimeException $exception) {
            throw $exception->getPrevious();
        }
        $fusionRuntime->popContext();

        return $output;
    }

    /**
     * @param string $output
     * @return string The message body without the message head
     */
    protected function extractBodyFromOutput(string $output): string
    {
        if (substr($output, 0, 5) === 'HTTP/') {
            $endOfHeader = strpos($output, "\r\n\r\n");
            if ($endOfHeader !== false) {
                $output = substr($output, $endOfHeader + 4);
            }
        }
        return $output;
    }

    /**
     * @param NodeInterface $currentSiteNode
     * @param ControllerContext $controllerContext
     * @return FusionRuntime
     * @throws \Neos\Fusion\Exception
     * @throws \Neos\Neos\Domain\Exception
     */
    protected function getFusionRuntime(NodeInterface $currentSiteNode, ControllerContext  $controllerContext): \Neos\Fusion\Core\Runtime
    {
        if ($this->fusionRuntime === null) {
            $this->fusionRuntime = $this->fusionService->createRuntime($currentSiteNode, $controllerContext);

            if (isset($this->options['enableContentCache']) && $this->options['enableContentCache'] !== null) {
                $this->fusionRuntime->setEnableContentCache($this->options['enableContentCache']);
            }
        }
        return $this->fusionRuntime;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\View;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 */
class FusionExceptionView extends FusionExceptionView_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
        if ('Neos\Neos\View\FusionExceptionView' === get_class($this)) {
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
  'supportedOptions' => 'array',
  'bootstrap' => 'Neos\\Flow\\Core\\Bootstrap',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'i18nService' => 'Neos\\Flow\\I18n\\Service',
  'fusionService' => 'Neos\\Neos\\Domain\\Service\\FusionService',
  'fusionRuntime' => 'Neos\\Fusion\\Core\\Runtime',
  'siteRepository' => 'Neos\\Neos\\Domain\\Repository\\SiteRepository',
  'domainRepository' => 'Neos\\Neos\\Domain\\Repository\\DomainRepository',
  'contentContextFactory' => 'Neos\\Neos\\Domain\\Service\\ContentContextFactory',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Core\Bootstrap', 'Neos\Flow\Core\Bootstrap', 'bootstrap', 'aed14e789673142988a77dfdf496f415', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Core\Bootstrap'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\I18n\Service', 'Neos\Flow\I18n\Service', 'i18nService', 'bdcad09aa1b6973b35f2987887987892', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\Service'); });
        $this->fusionService = new \Neos\Neos\Domain\Service\FusionService();
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\SiteRepository', 'Neos\Neos\Domain\Repository\SiteRepository', 'siteRepository', '42785f5eca4dff104f1860b84f531a9f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\SiteRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\DomainRepository', 'Neos\Neos\Domain\Repository\DomainRepository', 'domainRepository', '37b1b7f7b2d5d92dae299591af3b7e10', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\DomainRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Service\ContentContextFactory', 'Neos\Neos\Domain\Service\ContentContextFactory', 'contentContextFactory', 'bf6447fb48e80589ca3a024bc3882005', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Service\ContentContextFactory'); });
        $this->Flow_Injected_Properties = array (
  0 => 'bootstrap',
  1 => 'objectManager',
  2 => 'i18nService',
  3 => 'fusionService',
  4 => 'siteRepository',
  5 => 'domainRepository',
  6 => 'contentContextFactory',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/View/FusionExceptionView.php
#