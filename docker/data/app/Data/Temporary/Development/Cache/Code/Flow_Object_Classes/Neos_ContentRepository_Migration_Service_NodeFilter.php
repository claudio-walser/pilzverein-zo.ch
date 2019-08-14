<?php 
namespace Neos\ContentRepository\Migration\Service;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\ContentRepository\Migration\Exception\MigrationException;
use Neos\ContentRepository\Migration\Filters\FilterInterface;
use Neos\ContentRepository\Migration\Filters\DoctrineFilterInterface;
use Neos\Flow\Persistence\Doctrine\Query;

/**
 * Service to determine if a given node matches a series of filters given by configuration.
 *
 * @Flow\Scope("singleton")
 */
class NodeFilter_Original
{
    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $filterConjunctions = [];

    /**
     * Return array with Doctrine expressions
     *
     * @param array $filterConfigurations
     * @param Query $baseQuery
     * @return array
     * @throws MigrationException
     */
    public function getFilterExpressions(array $filterConfigurations, Query $baseQuery): array
    {
        $filterExpressions = [];
        foreach ($filterConfigurations as $filterConfiguration) {
            $filterObject = $this->constructFilterObject($filterConfiguration);
            if ($filterObject instanceof DoctrineFilterInterface) {
                foreach ($filterObject->getFilterExpressions($baseQuery) as $filterExpression) {
                    $filterExpressions[] = $filterExpression;
                }
            }
        }
        return $filterExpressions;
    }

    /**
     * Apply local filters to result.
     *
     * @param NodeData $nodeData
     * @param array $filterConfiguration
     * @return boolean
     * @throws MigrationException
     */
    public function matchFilters(NodeData $nodeData, array $filterConfiguration)
    {
        $filterConjunction = $this->buildFilterConjunction($filterConfiguration);
        foreach ($filterConjunction as $filter) {
            if (!$filter->matches($nodeData)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array $filterConfigurations
     * @return array<\Neos\ContentRepository\Migration\FilterInterface>
     * @throws MigrationException
     */
    protected function buildFilterConjunction(array $filterConfigurations)
    {
        $conjunctionIdentifier = md5(serialize($filterConfigurations));
        if (isset($this->filterConjunctions[$conjunctionIdentifier])) {
            return $this->filterConjunctions[$conjunctionIdentifier];
        }

        $conjunction = [];
        foreach ($filterConfigurations as $filterConfiguration) {
            $filterObject = $this->constructFilterObject($filterConfiguration);
            if ($filterObject instanceof FilterInterface) {
                $conjunction[] = $filterObject;
            }
        }
        $this->filterConjunctions[$conjunctionIdentifier] = $conjunction;

        return $conjunction;
    }

    /**
     * @param array $filterConfiguration
     * @return FilterInterface|DoctrineFilterInterface
     * @throws MigrationException
     */
    protected function constructFilterObject($filterConfiguration)
    {
        $filterClassName = $this->resolveFilterClass($filterConfiguration['type']);
        $filter = new $filterClassName;
        foreach ($filterConfiguration['settings'] as $propertyName => $propertyValue) {
            $setterName = 'set' . ucfirst($propertyName);
            if (method_exists($filter, $setterName)) {
                $filter->$setterName($propertyValue);
            } else {
                throw new MigrationException('Filter "' . $filterClassName . '" does not have a setter for "' . $propertyName . '", so maybe it is not supported.', 1343199531);
            }
        }

        return $filter;
    }

    /**
     * Resolves the class name for the filter by first assuming it is a full qualified class name and otherwise searching
     * in this package (so filters delivered in Neos.ContentRepository can be used by simply giving the class name without namespace).
     *
     * @param string $name
     * @return string
     * @throws MigrationException
     */
    protected function resolveFilterClass($name)
    {
        $resolvedObjectName = $this->objectManager->getCaseSensitiveObjectName($name);
        if ($resolvedObjectName !== false) {
            return $resolvedObjectName;
        }

        $resolvedObjectName = $this->objectManager->getCaseSensitiveObjectName('Neos\ContentRepository\Migration\Filters\\' . $name);
        if ($resolvedObjectName !== false) {
            return $resolvedObjectName;
        }

        throw new MigrationException('A filter with the name "' . $name . '" could not be found.', 1343199467);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\ContentRepository\Migration\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Service to determine if a given node matches a series of filters given by configuration.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class NodeFilter extends NodeFilter_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\ContentRepository\Migration\Service\NodeFilter') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\ContentRepository\Migration\Service\NodeFilter', $this);
        if ('Neos\ContentRepository\Migration\Service\NodeFilter' === get_class($this)) {
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
  'filterConjunctions' => 'array',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\ContentRepository\Migration\Service\NodeFilter') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\ContentRepository\Migration\Service\NodeFilter', $this);

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
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.ContentRepository/Classes/Migration/Service/NodeFilter.php
#