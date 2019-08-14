<?php 
namespace Neos\Neos\Routing;

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
use Neos\Flow\Log\PsrSystemLoggerInterface;
use Neos\Flow\Mvc\Routing\DynamicRoutePart;
use Neos\Flow\Security\Context;
use Neos\Neos\Domain\Repository\DomainRepository;
use Neos\Neos\Domain\Repository\SiteRepository;
use Neos\Neos\Domain\Service\ContentContext;
use Neos\Neos\Domain\Service\ContentContextFactory;
use Neos\Neos\Domain\Service\ContentDimensionPresetSourceInterface;
use Neos\Neos\Domain\Service\SiteService;
use Neos\Neos\Routing\Exception\InvalidDimensionPresetCombinationException;
use Neos\Neos\Routing\Exception\InvalidRequestPathException;
use Neos\Neos\Routing\Exception\NoSuchDimensionValueException;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Utility\NodePaths;

/**
 * A route part handler for finding nodes specifically in the website's frontend.
 */
class FrontendNodeRoutePartHandler_Original extends DynamicRoutePart implements FrontendNodeRoutePartHandlerInterface
{

    /**
     * @Flow\Inject
     * @var PsrSystemLoggerInterface
     */
    protected $systemLogger;

    /**
     * @Flow\Inject
     * @var ContentContextFactory
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var Context
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var DomainRepository
     */
    protected $domainRepository;

    /**
     * @Flow\Inject
     * @var SiteRepository
     */
    protected $siteRepository;

    /**
     * @Flow\InjectConfiguration("routing.supportEmptySegmentForDimensions")
     * @var boolean
     */
    protected $supportEmptySegmentForDimensions;

    /**
     * @Flow\Inject
     * @var ContentDimensionPresetSourceInterface
     */
    protected $contentDimensionPresetSource;

    const DIMENSION_REQUEST_PATH_MATCHER = '|^
        (?<firstUriPart>[^/@]+)                    # the first part of the URI, before the first slash, may contain the encoded dimension preset
        (?:                                        # start of non-capturing submatch for the remaining URL
            /?                                     # a "/"; optional. it must also match en@user-admin
            (?<remainingRequestPath>.*)            # the remaining request path
        )?                                         # ... and this whole remaining URL is optional
        $                                          # make sure we consume the full string
    |x';

    /**
     * Extracts the node path from the request path.
     *
     * @param string $requestPath The request path to be matched
     * @return string value to match, or an empty string if $requestPath is empty or split string was not found
     */
    protected function findValueToMatch($requestPath)
    {
        if ($this->splitString !== '') {
            $splitStringPosition = strpos($requestPath, $this->splitString);
            if ($splitStringPosition !== false) {
                return substr($requestPath, 0, $splitStringPosition);
            }
        }

        return $requestPath;
    }

    /**
     * Matches a frontend URI pointing to a node (for example a page).
     *
     * This function tries to find a matching node by the given request path. If one was found, its
     * absolute context node path is set in $this->value and true is returned.
     *
     * Note that this matcher does not check if access to the resolved workspace or node is allowed because at the point
     * in time the route part handler is invoked, the security framework is not yet fully initialized.
     *
     * @param string $requestPath The request path (without leading "/", relative to the current Site Node)
     * @return boolean true if the $requestPath could be matched, otherwise false
     * @throws \Exception
     * @throws Exception\NoHomepageException if no node could be found on the homepage (empty $requestPath)
     */
    protected function matchValue($requestPath)
    {
        try {
            /** @var NodeInterface $node */
            $node = null;

            // Build context explicitly without authorization checks because the security context isn't available yet
            // anyway and any Entity Privilege targeted on Workspace would fail at this point:
            $this->securityContext->withoutAuthorizationChecks(function () use (&$node, $requestPath) {
                $node = $this->convertRequestPathToNode($requestPath);
            });
        } catch (Exception $exception) {
            $this->systemLogger->debug('FrontendNodeRoutePartHandler matchValue(): ' . $exception->getMessage());
            if ($requestPath === '') {
                throw new Exception\NoHomepageException('Homepage could not be loaded. Probably you haven\'t imported a site yet', 1346950755, $exception);
            }

            return false;
        }
        if ($this->onlyMatchSiteNodes() && $node !== $node->getContext()->getCurrentSiteNode()) {
            return false;
        }

        $this->value = $node->getContextPath();

        return true;
    }

    /**
     * Returns the initialized node that is referenced by $requestPath, based on the node's
     * "uriPathSegment" property.
     *
     * Note that $requestPath will be modified (passed by reference) by buildContextFromRequestPath().
     *
     * @param string $requestPath The request path, for example /the/node/path@some-workspace
     * @return NodeInterface
     * @throws \Neos\Neos\Routing\Exception\NoWorkspaceException
     * @throws \Neos\Neos\Routing\Exception\NoSiteException
     * @throws \Neos\Neos\Routing\Exception\NoSuchNodeException
     * @throws \Neos\Neos\Routing\Exception\NoSiteNodeException
     * @throws \Neos\Neos\Routing\Exception\InvalidRequestPathException
     */
    protected function convertRequestPathToNode($requestPath)
    {
        $contentContext = $this->buildContextFromRequestPath($requestPath);
        $requestPathWithoutContext = $this->removeContextFromPath($requestPath);

        $workspace = $contentContext->getWorkspace();
        if ($workspace === null) {
            throw new Exception\NoWorkspaceException(sprintf('No workspace found for request path "%s"', $requestPath), 1346949318);
        }

        $site = $contentContext->getCurrentSite();
        if ($site === null) {
            throw new Exception\NoSiteException(sprintf('No site found for request path "%s"', $requestPath), 1346949693);
        }

        $siteNode = $contentContext->getCurrentSiteNode();
        if ($siteNode === null) {
            $currentDomain = $contentContext->getCurrentDomain() ? 'Domain with hostname "' . $contentContext->getCurrentDomain()->getHostname() . '" matched.' : 'No specific domain matched.';
            throw new Exception\NoSiteNodeException(sprintf('No site node found for request path "%s". %s', $requestPath, $currentDomain), 1346949728);
        }

        if ($requestPathWithoutContext === '') {
            $node = $siteNode;
        } else {
            $relativeNodePath = $this->getRelativeNodePathByUriPathSegmentProperties($siteNode, $requestPathWithoutContext);
            $node = ($relativeNodePath !== false) ? $siteNode->getNode($relativeNodePath) : null;
        }

        if (!$node instanceof NodeInterface) {
            throw new Exception\NoSuchNodeException(sprintf('No node found on request path "%s"', $requestPath), 1346949857);
        }

        return $node;
    }

    /**
     * Checks, whether given value is a Node object and if so, sets $this->value to the respective node path.
     *
     * In order to render a suitable frontend URI, this function strips off the path to the site node and only keeps
     * the actual node path relative to that site node. In practice this function would set $this->value as follows:
     *
     * absolute node path: /sites/neostypo3org/homepage/about
     * $this->value:       homepage/about
     *
     * absolute node path: /sites/neostypo3org/homepage/about@user-admin
     * $this->value:       homepage/about@user-admin
     *
     * @param mixed $node Either a Node object or an absolute context node path
     * @return boolean true if value could be resolved successfully, otherwise false.
     */
    protected function resolveValue($node)
    {
        if (!$node instanceof NodeInterface && !is_string($node)) {
            return false;
        }

        if (is_string($node)) {
            $nodeContextPath = $node;
            $contentContext = $this->buildContextFromPath($nodeContextPath, true);
            if ($contentContext->getWorkspace() === null) {
                return false;
            }
            $nodePath = $this->removeContextFromPath($nodeContextPath);
            $node = $contentContext->getNode($nodePath);

            if ($node === null) {
                return false;
            }
        } else {
            $contentContext = $node->getContext();
        }

        if (!$node->getNodeType()->isOfType('Neos.Neos:Document')) {
            return false;
        }

        $siteNode = $contentContext->getCurrentSiteNode();
        if ($this->onlyMatchSiteNodes() && $node !== $siteNode) {
            return false;
        }

        $routePath = $this->resolveRoutePathForNode($node);
        $this->value = $routePath;

        return true;
    }

    /**
     * Creates a content context from the given request path, considering possibly mentioned content dimension values.
     *
     * @param string &$requestPath The request path. If at least one content dimension is configured, the first path segment will identify the content dimension values
     * @return ContentContext The built content context
     */
    protected function buildContextFromRequestPath(&$requestPath)
    {
        $workspaceName = 'live';
        $dimensionsAndDimensionValues = $this->parseDimensionsAndNodePathFromRequestPath($requestPath);

        // This is a workaround as NodePaths::explodeContextPath() (correctly)
        // expects a context path to have something before the '@', but the requestPath
        // could potentially contain only the context information.
        if (strpos($requestPath, '@') === 0) {
            $requestPath = '/' . $requestPath;
        }

        if ($requestPath !== '' && NodePaths::isContextPath($requestPath)) {
            try {
                $nodePathAndContext = NodePaths::explodeContextPath($requestPath);
                $workspaceName = $nodePathAndContext['workspaceName'];
            } catch (\InvalidArgumentException $exception) {
            }
        }
        return $this->buildContextFromWorkspaceNameAndDimensions($workspaceName, $dimensionsAndDimensionValues);
    }

    /**
     * Creates a content context from the given "context path", i.e. a string used for _resolving_ (not matching) a node.
     *
     * @param string $path a path containing the context, such as /sites/examplecom/home@user-johndoe or /assets/pictures/my-picture or /assets/pictures/my-picture@user-john;language=de&country=global
     * @param boolean $convertLiveDimensions Whether to parse dimensions from the context path in a non-live workspace
     * @return ContentContext based on the specified path; only evaluating the context information (i.e. everything after "@")
     * @throws Exception\InvalidRequestPathException
     */
    protected function buildContextFromPath($path, $convertLiveDimensions)
    {
        $workspaceName = 'live';
        $dimensions = null;

        if ($path !== '' && NodePaths::isContextPath($path)) {
            $nodePathAndContext = NodePaths::explodeContextPath($path);
            $workspaceName = $nodePathAndContext['workspaceName'];
            $dimensions = ($workspaceName !== 'live' || $convertLiveDimensions === true) ? $nodePathAndContext['dimensions'] : null;
        }

        return $this->buildContextFromWorkspaceName($workspaceName, $dimensions);
    }

    /**
     * @param string $workspaceName
     * @param array $dimensions
     * @return ContentContext
     */
    protected function buildContextFromWorkspaceName($workspaceName, array $dimensions = null)
    {
        $contextProperties = [
            'workspaceName' => $workspaceName,
            'invisibleContentShown' => true,
            'inaccessibleContentShown' => true
        ];

        if ($dimensions !== null) {
            $contextProperties['dimensions'] = $dimensions;
        }

        return $this->contextFactory->create($contextProperties);
    }

    /**
     * @param string $path an absolute or relative node path which possibly contains context information, for example "/sites/somesite/the/node/path@some-workspace"
     * @return string the same path without context information
     */
    protected function removeContextFromPath($path)
    {
        if ($path === '' || NodePaths::isContextPath($path) === false) {
            return $path;
        }
        try {
            $nodePathAndContext = NodePaths::explodeContextPath($path);
            // This is a workaround as we potentially prepend the context path with "/" in buildContextFromRequestPath to create a valid context path,
            // the code in this class expects an empty nodePath though for the site node, so we remove it again at this point.
            return $nodePathAndContext['nodePath'] === '/' ? '' : $nodePathAndContext['nodePath'];
        } catch (\InvalidArgumentException $exception) {
        }

        return null;
    }

    /**
     * Whether the current route part should only match/resolve site nodes (e.g. the homepage)
     *
     * @return boolean
     */
    protected function onlyMatchSiteNodes()
    {
        return isset($this->options['onlyMatchSiteNodes']) && $this->options['onlyMatchSiteNodes'] === true;
    }

    /**
     * Resolves the request path, also known as route path, identifying the given node.
     *
     * A path is built, based on the uri path segment properties of the parents of and the given node itself.
     * If content dimensions are configured, the first path segment will the identifiers of the dimension
     * values according to the current context.
     *
     * @param NodeInterface $node The node where the generated path should lead to
     * @return string The relative route path, possibly prefixed with a segment for identifying the current content dimension values
     */
    protected function resolveRoutePathForNode(NodeInterface $node)
    {
        $workspaceName = $node->getContext()->getWorkspaceName();

        $nodeContextPath = $node->getContextPath();
        $nodeContextPathSuffix = ($workspaceName !== 'live') ? substr($nodeContextPath, strpos($nodeContextPath, '@')) : '';

        $currentNodeIsSiteNode = ($node->getParentPath() === SiteService::SITES_ROOT_PATH);
        $dimensionsUriSegment = $this->getUriSegmentForDimensions($node->getContext()->getDimensions(), $currentNodeIsSiteNode);
        $requestPath = $this->getRequestPathByNode($node);

        return trim($dimensionsUriSegment . $requestPath, '/') . $nodeContextPathSuffix;
    }

    /**
     * Builds a node path which matches the given request path.
     *
     * This method traverses the segments of the given request path and tries to find nodes on the current level which
     * have a matching "uriPathSegment" property. If no node could be found which would match the given request path,
     * false is returned.
     *
     * @param NodeInterface $siteNode The site node, used as a starting point while traversing the tree
     * @param string $relativeRequestPath The request path, relative to the site's root path
     * @throws \Neos\Neos\Routing\Exception\NoSuchNodeException
     * @return string
     */
    protected function getRelativeNodePathByUriPathSegmentProperties(NodeInterface $siteNode, $relativeRequestPath)
    {
        $relativeNodePathSegments = [];
        $node = $siteNode;

        foreach (explode('/', $relativeRequestPath) as $pathSegment) {
            $foundNodeInThisSegment = false;
            foreach ($node->getChildNodes('Neos.Neos:Document') as $node) {
                /** @var NodeInterface $node */
                if ($node->getProperty('uriPathSegment') === $pathSegment) {
                    $relativeNodePathSegments[] = $node->getName();
                    $foundNodeInThisSegment = true;
                    break;
                }
            }
            if (!$foundNodeInThisSegment) {
                return false;
            }
        }

        return implode('/', $relativeNodePathSegments);
    }

    /**
     * Renders a request path based on the "uriPathSegment" properties of the nodes leading to the given node.
     *
     * @param NodeInterface $node The node where the generated path should lead to
     * @return string A relative request path
     * @throws Exception\MissingNodePropertyException if the given node doesn't have a "uriPathSegment" property set
     */
    protected function getRequestPathByNode(NodeInterface $node)
    {
        if ($node->getParentPath() === SiteService::SITES_ROOT_PATH) {
            return '';
        }

        // To allow building of paths to non-hidden nodes beneath hidden nodes, we assume
        // the input node is allowed to be seen and we must generate the full path here.
        // To disallow showing a node actually hidden itself has to be ensured in matching
        // a request path, not in building one.
        $contextProperties = $node->getContext()->getProperties();
        $contextAllowingHiddenNodes = $this->contextFactory->create(array_merge($contextProperties, ['invisibleContentShown' => true]));
        $currentNode = $contextAllowingHiddenNodes->getNodeByIdentifier($node->getIdentifier());

        $requestPathSegments = [];
        while ($currentNode instanceof NodeInterface && $currentNode->getParentPath() !== SiteService::SITES_ROOT_PATH) {
            if (!$currentNode->hasProperty('uriPathSegment')) {
                throw new Exception\MissingNodePropertyException(sprintf('Missing "uriPathSegment" property for node "%s". Nodes can be migrated with the "flow node:repair" command.', $node->getPath()), 1415020326);
            }

            $pathSegment = $currentNode->getProperty('uriPathSegment');
            $requestPathSegments[] = $pathSegment;
            $currentNode = $currentNode->getParent();
        }

        return implode('/', array_reverse($requestPathSegments));
    }

    /**
    * Choose between default method for parsing dimensions or the one which allows uriSegment to be empty for default preset.
    *
    * @param string &$requestPath The request path currently being processed by this route part handler, e.g. "de_global/startseite/ueber-uns"
    * @return array An array of dimension name => dimension values (array of string)
    */
    protected function parseDimensionsAndNodePathFromRequestPath(&$requestPath)
    {
        if ($this->supportEmptySegmentForDimensions) {
            $dimensionsAndDimensionValues = $this->parseDimensionsAndNodePathFromRequestPathAllowingEmptySegment($requestPath);
        } else {
            $dimensionsAndDimensionValues = $this->parseDimensionsAndNodePathFromRequestPathAllowingNonUniqueSegment($requestPath);
        }
        return $dimensionsAndDimensionValues;
    }

    /**
     * Parses the given request path and checks if the first path segment is one or a set of content dimension preset
     * identifiers. If that is the case, the return value is an array of dimension names and their preset URI segments.
     * Allows uriSegment to be empty for default dimension preset.
     *
     * If the first path segment contained content dimension information, it is removed from &$requestPath.
     *
     * @param string &$requestPath The request path currently being processed by this route part handler, e.g. "de_global/startseite/ueber-uns"
     * @return array An array of dimension name => dimension values (array of string)
     * @throws InvalidDimensionPresetCombinationException
     */
    protected function parseDimensionsAndNodePathFromRequestPathAllowingEmptySegment(&$requestPath)
    {
        $dimensionPresets = $this->contentDimensionPresetSource->getAllPresets();
        if (count($dimensionPresets) === 0) {
            return [];
        }
        $dimensionsAndDimensionValues = [];
        $chosenDimensionPresets = [];
        $matches = [];
        preg_match(self::DIMENSION_REQUEST_PATH_MATCHER, $requestPath, $matches);
        $firstUriPartIsValidDimension = true;
        foreach ($dimensionPresets as $dimensionName => $dimensionPreset) {
            $dimensionsAndDimensionValues[$dimensionName] = $dimensionPreset['presets'][$dimensionPreset['defaultPreset']]['values'];
            $chosenDimensionPresets[$dimensionName] = $dimensionPreset['defaultPreset'];
        }
        if (isset($matches['firstUriPart'])) {
            $firstUriPartExploded = explode('_', $matches['firstUriPart']);
            foreach ($firstUriPartExploded as $uriSegment) {
                $uriSegmentIsValid = false;
                foreach ($dimensionPresets as $dimensionName => $dimensionPreset) {
                    $preset = $this->contentDimensionPresetSource->findPresetByUriSegment($dimensionName, $uriSegment);
                    if ($preset !== null) {
                        $uriSegmentIsValid = true;
                        $dimensionsAndDimensionValues[$dimensionName] = $preset['values'];
                        $chosenDimensionPresets[$dimensionName] = $preset['identifier'];
                        break;
                    }
                }
                if (!$uriSegmentIsValid) {
                    $firstUriPartIsValidDimension = false;
                    break;
                }
            }
            if ($firstUriPartIsValidDimension) {
                $requestPath = (isset($matches['remainingRequestPath']) ? $matches['remainingRequestPath'] : '');
            }
        }
        if (!$this->contentDimensionPresetSource->isPresetCombinationAllowedByConstraints($chosenDimensionPresets)) {
            throw new InvalidDimensionPresetCombinationException(sprintf('The resolved content dimension preset combination (%s) is invalid or restricted by content dimension constraints. Check your content dimension settings if you think that this is an error.', implode(', ', array_keys($chosenDimensionPresets))), 1428657721);
        }
        return $dimensionsAndDimensionValues;
    }

    /**
     * Parses the given request path and checks if the first path segment is one or a set of content dimension preset
     * identifiers. If that is the case, the return value is an array of dimension names and their preset URI segments.
     * Doesn't allow empty uriSegment, but allows uriSegment to be not unique across presets.
     *
     * If the first path segment contained content dimension information, it is removed from &$requestPath.
     *
     * @param string &$requestPath The request path currently being processed by this route part handler, e.g. "de_global/startseite/ueber-uns"
     * @return array An array of dimension name => dimension values (array of string)
     * @throws InvalidDimensionPresetCombinationException
     * @throws InvalidRequestPathException
     * @throws NoSuchDimensionValueException
     */
    protected function parseDimensionsAndNodePathFromRequestPathAllowingNonUniqueSegment(&$requestPath)
    {
        $dimensionPresets = $this->contentDimensionPresetSource->getAllPresets();
        if (count($dimensionPresets) === 0) {
            return [];
        }

        $dimensionsAndDimensionValues = [];
        $chosenDimensionPresets = [];
        $matches = [];

        preg_match(self::DIMENSION_REQUEST_PATH_MATCHER, $requestPath, $matches);

        if (!isset($matches['firstUriPart'])) {
            foreach ($dimensionPresets as $dimensionName => $dimensionPreset) {
                $dimensionsAndDimensionValues[$dimensionName] = $dimensionPreset['presets'][$dimensionPreset['defaultPreset']]['values'];
                $chosenDimensionPresets[$dimensionName] = $dimensionPreset['defaultPreset'];
            }
        } else {
            $firstUriPart = explode('_', $matches['firstUriPart']);

            if (count($firstUriPart) !== count($dimensionPresets)) {
                throw new InvalidRequestPathException(sprintf('The first path segment of the request URI (%s) does not contain the necessary content dimension preset identifiers for all configured dimensions. This might be an old URI which doesn\'t match the current dimension configuration anymore.', $requestPath), 1413389121);
            }

            foreach ($dimensionPresets as $dimensionName => $dimensionPreset) {
                $uriSegment = array_shift($firstUriPart);
                $preset = $this->contentDimensionPresetSource->findPresetByUriSegment($dimensionName, $uriSegment);
                if ($preset === null) {
                    throw new NoSuchDimensionValueException(sprintf('Could not find a preset for content dimension "%s" through the given URI segment "%s".', $dimensionName, $uriSegment), 1413389321);
                }
                $dimensionsAndDimensionValues[$dimensionName] = $preset['values'];
                $chosenDimensionPresets[$dimensionName] = $preset['identifier'];
            }

            $requestPath = (isset($matches['remainingRequestPath']) ? $matches['remainingRequestPath'] : '');
        }

        if (!$this->contentDimensionPresetSource->isPresetCombinationAllowedByConstraints($chosenDimensionPresets)) {
            throw new InvalidDimensionPresetCombinationException(sprintf('The resolved content dimension preset combination (%s) is invalid or restricted by content dimension constraints. Check your content dimension settings if you think that this is an error.', implode(', ', array_keys($chosenDimensionPresets))), 1462175794);
        }

        return $dimensionsAndDimensionValues;
    }

    /**
     * Sets context properties like "invisibleContentShown" according to the workspace (live or not) and returns a
     * ContentContext object.
     *
     * @param string $workspaceName Name of the workspace to use in the context
     * @param array $dimensionsAndDimensionValues An array of dimension names (index) and their values (array of strings). See also: ContextFactory
     * @return ContentContext
     */
    protected function buildContextFromWorkspaceNameAndDimensions($workspaceName, array $dimensionsAndDimensionValues)
    {
        $contextProperties = [
            'workspaceName' => $workspaceName,
            'invisibleContentShown' => ($workspaceName !== 'live'),
            'inaccessibleContentShown' => ($workspaceName !== 'live'),
            'dimensions' => $dimensionsAndDimensionValues
        ];

        return $this->contextFactory->create($contextProperties);
    }

    /**
     * Find a URI segment in the content dimension presets for the given "language" dimension values
     *
     * This will do a reverse lookup from actual dimension values to a preset and fall back to the default preset if none
     * can be found.
     *
     * @param array $dimensionsValues An array of dimensions and their values, indexed by dimension name
     * @param boolean $currentNodeIsSiteNode If the current node is actually the site node
     * @return string
     * @throws \Exception
     */
    protected function getUriSegmentForDimensions(array $dimensionsValues, $currentNodeIsSiteNode)
    {
        $uriSegment = '';
        $allDimensionPresetsAreDefault = true;

        foreach ($this->contentDimensionPresetSource->getAllPresets() as $dimensionName => $dimensionPresets) {
            $preset = null;
            if (isset($dimensionsValues[$dimensionName])) {
                $preset = $this->contentDimensionPresetSource->findPresetByDimensionValues($dimensionName, $dimensionsValues[$dimensionName]);
            }
            $defaultPreset = $this->contentDimensionPresetSource->getDefaultPreset($dimensionName);
            if ($preset === null) {
                $preset = $defaultPreset;
            }
            if ($preset !== $defaultPreset) {
                $allDimensionPresetsAreDefault = false;
            }
            if (!isset($preset['uriSegment'])) {
                throw new \Exception(sprintf('No "uriSegment" configured for content dimension preset "%s" for dimension "%s". Please check the content dimension configuration in Settings.yaml', $preset['identifier'], $dimensionName), 1395824520);
            }
            $uriSegment .= $preset['uriSegment'] . '_';
        }

        if ($this->supportEmptySegmentForDimensions && $allDimensionPresetsAreDefault && $currentNodeIsSiteNode) {
            return '/';
        } else {
            return ltrim(trim($uriSegment, '_') . '/', '/');
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Routing;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A route part handler for finding nodes specifically in the website's frontend.
 */
class FrontendNodeRoutePartHandler extends FrontendNodeRoutePartHandler_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\Neos\Routing\FrontendNodeRoutePartHandler' === get_class($this)) {
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
  'systemLogger' => 'Neos\\Flow\\Log\\PsrSystemLoggerInterface',
  'contextFactory' => 'Neos\\Neos\\Domain\\Service\\ContentContextFactory',
  'securityContext' => 'Neos\\Flow\\Security\\Context',
  'domainRepository' => 'Neos\\Neos\\Domain\\Repository\\DomainRepository',
  'siteRepository' => 'Neos\\Neos\\Domain\\Repository\\SiteRepository',
  'supportEmptySegmentForDimensions' => 'boolean',
  'contentDimensionPresetSource' => 'Neos\\Neos\\Domain\\Service\\ContentDimensionPresetSourceInterface',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'splitString' => 'string',
  'parameters' => 'Neos\\Flow\\Mvc\\Routing\\Dto\\RouteParameters',
  'name' => 'string',
  'value' => 'mixed',
  'defaultValue' => 'mixed',
  'isOptional' => 'boolean',
  'lowerCase' => 'boolean',
  'options' => 'array',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\PsrSystemLoggerInterface', '', 'systemLogger', '35a47897f04aa042c1ab70dc21ff014f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\PsrSystemLoggerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Service\ContentContextFactory', 'Neos\Neos\Domain\Service\ContentContextFactory', 'contextFactory', 'bf6447fb48e80589ca3a024bc3882005', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Service\ContentContextFactory'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Context', 'Neos\Flow\Security\Context', 'securityContext', 'f7e2ddeaebd191e228b8c2e4dc7f1f83', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Context'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\DomainRepository', 'Neos\Neos\Domain\Repository\DomainRepository', 'domainRepository', '37b1b7f7b2d5d92dae299591af3b7e10', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\DomainRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\SiteRepository', 'Neos\Neos\Domain\Repository\SiteRepository', 'siteRepository', '42785f5eca4dff104f1860b84f531a9f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\SiteRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Service\ContentDimensionPresetSourceInterface', 'Neos\Neos\Domain\Service\ConfigurationContentDimensionPresetSource', 'contentDimensionPresetSource', '96bb2f02eb23939468e8a031d3fe4c1a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Service\ContentDimensionPresetSourceInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->supportEmptySegmentForDimensions = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Neos.routing.supportEmptySegmentForDimensions');
        $this->Flow_Injected_Properties = array (
  0 => 'systemLogger',
  1 => 'contextFactory',
  2 => 'securityContext',
  3 => 'domainRepository',
  4 => 'siteRepository',
  5 => 'contentDimensionPresetSource',
  6 => 'persistenceManager',
  7 => 'supportEmptySegmentForDimensions',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Routing/FrontendNodeRoutePartHandler.php
#