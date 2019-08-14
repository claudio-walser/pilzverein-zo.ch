<?php 
namespace Neos\Neos\Ui\Domain\Service;

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
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Neos\Service\LinkingService;
use Neos\Neos\Ui\ContentRepository\Service\NodeService;

class NodeTreeBuilder_Original
{
    /**
     * The site node
     *
     * @var NodeInterface
     */
    protected $root;

    /**
     * The currently active node in the tree
     *
     * @var NodeInterface
     */
    protected $active;

    /**
     * An (optional) node type filter
     *
     * @var string|null
     */
    protected $nodeTypeFilter = null;

    /**
     * An (optional) search term filter
     *
     * @var string
     */
    protected $searchTermFilter = '';

    /**
     * Determines how many levels of the tree should be loaded
     *
     * @var integer
     */
    protected $depth = 1;

    /**
     * @var ControllerContext
     */
    protected $controllerContext;

    /**
     * @Flow\Inject
     * @var LinkingService
     */
    protected $linkingService;

    /**
     * @Flow\Inject
     * @var NodeService
     */
    protected $nodeService;

    /**
     * Set the root node
     *
     * @param string $rootContextPath
     * @return void
     */
    public function setRoot($rootContextPath)
    {
        $this->root = $this->nodeService->getNodeFromContextPath($rootContextPath);
    }

    /**
     * Get the root node
     *
     * @return NodeInterface
     */
    public function getRoot()
    {
        if (!$this->root) {
            $this->root = $this->active->getContext()->getCurrentSiteNode();
        }

        return $this->root;
    }

    /**
     * Set the active node
     *
     * @param string $activeContextPath
     * @return void
     */
    public function setActive($activeContextPath)
    {
        $this->active = $this->nodeService->getNodeFromContextPath($activeContextPath);
    }

    /**
     * Set the node type filter
     *
     * @param string $nodeTypeFilter
     * @return void
     */
    public function setNodeTypeFilter($nodeTypeFilter)
    {
        $this->nodeTypeFilter = $nodeTypeFilter;
    }

    /**
     * Set the search term filter
     *
     * @param string $searchTermFilter
     * @return void
     */
    public function setSearchTermFilter($searchTermFilter)
    {
        $this->searchTermFilter = $searchTermFilter;
    }

    /**
     * Set the depth
     *
     * @param integer $depth
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;
    }

    /**
     * Set the controller context
     *
     * @param ControllerContext $controllerContext
     * @return void
     */
    public function setControllerContext(ControllerContext $controllerContext)
    {
        $this->controllerContext = $controllerContext;
    }

    /**
     * Build a json serializable tree structure containing node information
     *
     * @param bool $includeRoot
     * @param null $root
     * @param null $depth
     * @return array
     */
    public function build($includeRoot = false, $root = null, $depth = null)
    {
        $root = $root === null ? $this->getRoot() : $root;
        $depth = $depth === null ? $this->depth : $depth;

        $result = [];

        /** @var NodeInterface $childNode */
        foreach ($root->getChildNodes($this->nodeTypeFilter) as $childNode) {
            $hasChildNodes = $childNode->hasChildNodes($this->nodeTypeFilter);
            $shouldLoadChildNodes = $hasChildNodes && ($depth > 1 || $this->isInRootLine($this->active, $childNode));

            $result[$childNode->getName()] = [
                'label' => $childNode->getNodeType()->isOfType('Neos.Neos:Document') ?
                    $childNode->getProperty('title') : $childNode->getLabel(),
                'contextPath' => $childNode->getContextPath(),
                'nodeType' => $childNode->getNodeType()->getName(),
                'hasChildren' => $hasChildNodes,
                'isActive' => $this->active && ($childNode->getPath() === $this->active->getPath()),
                'isFocused' => $this->active && ($childNode->getPath() === $this->active->getPath()),
                'isCollapsed' => !$shouldLoadChildNodes,
                'isCollapsable' => $hasChildNodes
            ];

            if ($shouldLoadChildNodes) {
                $result[$childNode->getName()]['children'] =
                    $this->build(false, $childNode, $depth - 1);
            }

            if ($childNode->getNodeType()->isOfType('Neos.Neos:Document')) {
                $result[$childNode->getName()]['href'] = $this->linkingService->createNodeUri(
                    /* $controllerContext */ $this->controllerContext,
                    /* $node */ $childNode,
                    /* $baseNode */ null,
                    /* $format */ null,
                    /* $absolute */ true,
                    /* $arguments */ [],
                    /* $section */ '',
                    /* $addQueryString */ false,
                    /* $argumentsToBeExcludedFromQueryString */ [],
                    /* $resolveShortcuts */ false
                );
            }
        }

        if ($includeRoot) {
            return [
                $root->getName() => [
                    'label' => $root->getNodeType()->isOfType('Neos.Neos:Document') ?
                        $root->getProperty('title') : $root->getLabel(),
                    'icon' => 'globe',
                    'contextPath' => $root->getContextPath(),
                    'nodeType' => $root->getNodeType()->getName(),
                    'hasChildren' => count($result),
                    'isCollapsed' => false,
                    'isActive' => $this->active && ($root->getPath() === $this->active->getPath()),
                    'isFocused' => $this->active && ($root->getPath() === $this->active->getPath()),
                    'children' => $result
                ]
            ];
        }

        return $result;
    }

    protected function isInRootLine(NodeInterface $haystack = null, NodeInterface $needle)
    {
        if ($haystack === null) {
            return false;
        }

        return mb_strrpos($haystack->getPath(), $needle->getPath(), null, 'UTF-8') === 0;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Ui\Domain\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 */
class NodeTreeBuilder extends NodeTreeBuilder_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\Neos\Ui\Domain\Service\NodeTreeBuilder' === get_class($this)) {
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
  'root' => 'Neos\\ContentRepository\\Domain\\Model\\NodeInterface',
  'active' => 'Neos\\ContentRepository\\Domain\\Model\\NodeInterface',
  'nodeTypeFilter' => 'string|null',
  'searchTermFilter' => 'string',
  'depth' => 'integer',
  'controllerContext' => 'Neos\\Flow\\Mvc\\Controller\\ControllerContext',
  'linkingService' => 'Neos\\Neos\\Service\\LinkingService',
  'nodeService' => 'Neos\\Neos\\Ui\\ContentRepository\\Service\\NodeService',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Ui\ContentRepository\Service\NodeService', 'Neos\Neos\Ui\ContentRepository\Service\NodeService', 'nodeService', 'c1132e56328e2286433a0639d659934e', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Ui\ContentRepository\Service\NodeService'); });
        $this->Flow_Injected_Properties = array (
  0 => 'linkingService',
  1 => 'nodeService',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos.Ui/Classes/Domain/Service/NodeTreeBuilder.php
#