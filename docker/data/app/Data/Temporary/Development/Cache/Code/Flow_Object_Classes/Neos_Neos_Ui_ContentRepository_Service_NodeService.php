<?php 
namespace Neos\Neos\Ui\ContentRepository\Service;

/*
 * This file is part of the Neos.Neos.Ui package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\Workspace;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\ContentRepository\Domain\Utility\NodePaths;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Error\Messages\Error;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Domain\Model\Domain;
use Neos\Neos\Domain\Model\Site;
use Neos\Neos\Domain\Repository\DomainRepository;
use Neos\Neos\Domain\Repository\SiteRepository;

/**
 * @Flow\Scope("singleton")
 */
class NodeService_Original
{
    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var SiteRepository
     */
    protected $siteRepository;

    /**
     * @Flow\Inject
     * @var DomainRepository
     */
    protected $domainRepository;

    /**
     * Helper method to retrieve the closest document for a node
     *
     * @param NodeInterface $node
     * @return NodeInterface
     */
    public function getClosestDocument(NodeInterface $node)
    {
        if ($node->getNodeType()->isOfType('Neos.Neos:Document')) {
            return $node;
        }

        $flowQuery = new FlowQuery([$node]);

        return $flowQuery->closest('[instanceof Neos.Neos:Document]')->get(0);
    }

    /**
     * Helper method to check if a given node is a document node.
     *
     * @param  NodeInterface $node The node to check
     * @return boolean             A boolean which indicates if the given node is a document node.
     */
    public function isDocument(NodeInterface $node)
    {
        return ($this->getClosestDocument($node) === $node);
    }

    /**
     * Converts a given context path to a node object
     *
     * @param string $contextPath
     * @return NodeInterface|Error
     */
    public function getNodeFromContextPath($contextPath, Site $site = null, Domain $domain = null, $includeAll = false)
    {
        $nodePathAndContext = NodePaths::explodeContextPath($contextPath);
        $nodePath = $nodePathAndContext['nodePath'];
        $workspaceName = $nodePathAndContext['workspaceName'];
        $dimensions = $nodePathAndContext['dimensions'];

        $contextProperties = $this->prepareContextProperties($workspaceName, $dimensions);

        if ($site === null) {
            list(, , $siteNodeName) = explode('/', $nodePath);
            $site = $this->siteRepository->findOneByNodeName($siteNodeName);
        }

        if ($domain === null) {
            $domain = $this->domainRepository->findOneBySite($site);
        }

        $contextProperties['currentSite'] = $site;
        $contextProperties['currentDomain'] = $domain;
        if ($includeAll === true) {
            $contextProperties['invisibleContentShown'] = true;
            $contextProperties['removedContentShown'] = true;
        }

        $context = $this->contextFactory->create(
            $contextProperties
        );

        $workspace = $context->getWorkspace(false);
        if (!$workspace) {
            return new Error(
                sprintf('Could not convert the given source to Node object because the workspace "%s" as specified in the context node path does not exist.', $workspaceName), 1451392329);
        }

        return $context->getNode($nodePath);
    }

    /**
     * Checks if the given node exists in the given workspace
     *
     * @param NodeInterface $node
     * @param Workspace $workspace
     * @return boolean
     */
    public function nodeExistsInWorkspace(NodeInterface $node, Workspace $workspace)
    {
        $context = ['workspaceName' => $workspace->getName()];
        $flowQuery = new FlowQuery([$node]);

        return $flowQuery->context($context)->count() > 0;
    }

    /**
     * Prepares the context properties for the nodes based on the given workspace and dimensions
     *
     * @param string $workspaceName
     * @param array $dimensions
     * @return array
     */
    protected function prepareContextProperties($workspaceName, array $dimensions = null)
    {
        $contextProperties = [
            'workspaceName' => $workspaceName,
            'invisibleContentShown' => false,
            'removedContentShown' => false
        ];

        if ($workspaceName !== 'live') {
            $contextProperties['invisibleContentShown'] = true;
        }

        if ($dimensions !== null) {
            $contextProperties['dimensions'] = $dimensions;
        }

        return $contextProperties;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Ui\ContentRepository\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class NodeService extends NodeService_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Neos\Ui\ContentRepository\Service\NodeService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Ui\ContentRepository\Service\NodeService', $this);
        if ('Neos\Neos\Ui\ContentRepository\Service\NodeService' === get_class($this)) {
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
  'contextFactory' => 'Neos\\ContentRepository\\Domain\\Service\\ContextFactoryInterface',
  'siteRepository' => 'Neos\\Neos\\Domain\\Repository\\SiteRepository',
  'domainRepository' => 'Neos\\Neos\\Domain\\Repository\\DomainRepository',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Neos\Ui\ContentRepository\Service\NodeService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Ui\ContentRepository\Service\NodeService', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Service\ContextFactoryInterface', 'Neos\Neos\Domain\Service\ContentContextFactory', 'contextFactory', '98dca7b1f95a25ec173662fc4e785341', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Service\ContextFactoryInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\SiteRepository', 'Neos\Neos\Domain\Repository\SiteRepository', 'siteRepository', '42785f5eca4dff104f1860b84f531a9f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\SiteRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\DomainRepository', 'Neos\Neos\Domain\Repository\DomainRepository', 'domainRepository', '37b1b7f7b2d5d92dae299591af3b7e10', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\DomainRepository'); });
        $this->Flow_Injected_Properties = array (
  0 => 'contextFactory',
  1 => 'siteRepository',
  2 => 'domainRepository',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos.Ui/Classes/ContentRepository/Service/NodeService.php
#