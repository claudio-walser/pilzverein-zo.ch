<?php 
namespace Neos\Neos\Domain\Service;

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
use Neos\Utility\Arrays;
use Neos\Neos\Domain\Model\Domain;
use Neos\Neos\Domain\Model\Site;
use Neos\ContentRepository\Domain\Service\ContextFactory;
use Neos\ContentRepository\Exception\InvalidNodeContextException;

/**
 * ContentContextFactory which ensures contexts stay unique. Make sure to
 * get ContextFactoryInterface injected instead of this class.
 *
 * See \Neos\ContentRepository\Domain\Service\ContextFactory->build for detailed
 * explanations about the usage.
 *
 * @Flow\Scope("singleton")
 */
class ContentContextFactory_Original extends ContextFactory
{
    /**
     * @Flow\Inject
     * @var \Neos\Neos\Domain\Repository\DomainRepository
     */
    protected $domainRepository;

    /**
     * @Flow\Inject
     * @var \Neos\Neos\Domain\Repository\SiteRepository
     */
    protected $siteRepository;

    /**
     * The context implementation this factory will create
     *
     * @var string
     */
    protected $contextImplementation = ContentContext::class;

    /**
     * Creates the actual Context instance.
     * This needs to be overridden if the Builder is extended.
     *
     * @param array $contextProperties
     * @return ContentContext
     */
    protected function buildContextInstance(array $contextProperties)
    {
        $contextProperties = $this->removeDeprecatedProperties($contextProperties);

        return new ContentContext(
            $contextProperties['workspaceName'],
            $contextProperties['currentDateTime'],
            $contextProperties['dimensions'],
            $contextProperties['targetDimensions'],
            $contextProperties['invisibleContentShown'],
            $contextProperties['removedContentShown'],
            $contextProperties['inaccessibleContentShown'],
            $contextProperties['currentSite'],
            $contextProperties['currentDomain']
        );
    }

    /**
     * Merges the given context properties with sane defaults for the context implementation.
     *
     * @param array $contextProperties
     * @return array
     */
    protected function mergeContextPropertiesWithDefaults(array $contextProperties)
    {
        $contextProperties = $this->removeDeprecatedProperties($contextProperties);

        $defaultContextProperties = [
            'workspaceName' => 'live',
            'currentDateTime' => $this->now,
            'dimensions' => [],
            'targetDimensions' => [],
            'invisibleContentShown' => false,
            'removedContentShown' => false,
            'inaccessibleContentShown' => false,
            'currentSite' => null,
            'currentDomain' => null
        ];

        if (!isset($contextProperties['currentSite'])) {
            $defaultContextProperties = $this->setDefaultSiteAndDomainFromCurrentRequest($defaultContextProperties);
        }

        $mergedProperties = Arrays::arrayMergeRecursiveOverrule($defaultContextProperties, $contextProperties, true);

        $this->mergeDimensionValues($contextProperties, $mergedProperties);
        $this->mergeTargetDimensionContextProperties($contextProperties, $mergedProperties, $defaultContextProperties);

        return $mergedProperties;
    }

    /**
     * Determines the current domain and site from the request and sets the resulting values as
     * as defaults.
     *
     * @param array $defaultContextProperties
     * @return array
     */
    protected function setDefaultSiteAndDomainFromCurrentRequest(array $defaultContextProperties)
    {
        $currentDomain = $this->domainRepository->findOneByActiveRequest();
        if ($currentDomain !== null) {
            $defaultContextProperties['currentSite'] = $currentDomain->getSite();
            $defaultContextProperties['currentDomain'] = $currentDomain;
        } else {
            $defaultContextProperties['currentSite'] = $this->siteRepository->findDefault();
        }

        return $defaultContextProperties;
    }


    /**
     * This creates the actual identifier and needs to be overridden by builders extending this.
     *
     * @param array $contextProperties
     * @return string
     */
    protected function getIdentifierSource(array $contextProperties)
    {
        ksort($contextProperties);
        $identifierSource = $this->contextImplementation;
        foreach ($contextProperties as $propertyName => $propertyValue) {
            if ($propertyName === 'dimensions') {
                $stringParts = [];
                foreach ($propertyValue as $dimensionName => $dimensionValues) {
                    $stringParts[] = $dimensionName . '=' . implode(',', $dimensionValues);
                }
                $stringValue = implode('&', $stringParts);
            } elseif ($propertyName === 'targetDimensions') {
                $stringParts = [];
                foreach ($propertyValue as $dimensionName => $dimensionValue) {
                    $stringParts[] = $dimensionName . '=' . $dimensionValue;
                }
                $stringValue = implode('&', $stringParts);
            } elseif ($propertyValue instanceof \DateTimeInterface) {
                $stringValue = $propertyValue->getTimestamp();
            } elseif ($propertyValue instanceof Site) {
                $stringValue = $propertyValue->getNodeName();
            } elseif ($propertyValue instanceof Domain) {
                $stringValue = $propertyValue->getHostname();
            } else {
                $stringValue = (string)$propertyValue;
            }
            $identifierSource .= ':' . $stringValue;
        }

        return $identifierSource;
    }

    /**
     * @param array $contextProperties
     * @return void
     * @throws InvalidNodeContextException
     */
    protected function validateContextProperties($contextProperties)
    {
        parent::validateContextProperties($contextProperties);

        if (isset($contextProperties['currentSite'])) {
            if (!$contextProperties['currentSite'] instanceof Site) {
                throw new InvalidNodeContextException('You tried to set currentSite in the context and did not provide a \\Neos\Neos\\Domain\\Model\\Site object as value.', 1373145297);
            }
        }
        if (isset($contextProperties['currentDomain'])) {
            if (!$contextProperties['currentDomain'] instanceof Domain) {
                throw new InvalidNodeContextException('You tried to set currentDomain in the context and did not provide a \\Neos\Neos\\Domain\\Model\\Domain object as value.', 1373145384);
            }
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Domain\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * ContentContextFactory which ensures contexts stay unique. Make sure to
 * get ContextFactoryInterface injected instead of this class.
 * 
 * See \Neos\ContentRepository\Domain\Service\ContextFactory->build for detailed
 * explanations about the usage.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class ContentContextFactory extends ContentContextFactory_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (method_exists(get_parent_class(), 'Flow_Aop_Proxy_buildMethodsAndAdvicesArray') && is_callable('parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray')) parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        if (get_class($this) === 'Neos\Neos\Domain\Service\ContentContextFactory') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Domain\Service\ContentContextFactory', $this);
        if (get_class($this) === 'Neos\Neos\Domain\Service\ContentContextFactory') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\ContentRepository\Domain\Service\ContextFactoryInterface', $this);
        if ('Neos\Neos\Domain\Service\ContentContextFactory' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (method_exists(get_parent_class(), 'Flow_Aop_Proxy_buildMethodsAndAdvicesArray') && is_callable('parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray')) parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        if (get_class($this) === 'Neos\Neos\Domain\Service\ContentContextFactory') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Neos\Domain\Service\ContentContextFactory', $this);
        if (get_class($this) === 'Neos\Neos\Domain\Service\ContentContextFactory') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\ContentRepository\Domain\Service\ContextFactoryInterface', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
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
  'domainRepository' => '\\Neos\\Neos\\Domain\\Repository\\DomainRepository',
  'siteRepository' => '\\Neos\\Neos\\Domain\\Repository\\SiteRepository',
  'contextImplementation' => 'string',
  'contextInstances' => 'array<Neos\\ContentRepository\\Domain\\Service\\Context>',
  'contentDimensionRepository' => 'Neos\\ContentRepository\\Domain\\Repository\\ContentDimensionRepository',
  'now' => 'Neos\\Flow\\Utility\\Now',
  'securityContext' => 'Neos\\Flow\\Security\\Context',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\DomainRepository', 'Neos\Neos\Domain\Repository\DomainRepository', 'domainRepository', '37b1b7f7b2d5d92dae299591af3b7e10', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\DomainRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Neos\Domain\Repository\SiteRepository', 'Neos\Neos\Domain\Repository\SiteRepository', 'siteRepository', '42785f5eca4dff104f1860b84f531a9f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Neos\Domain\Repository\SiteRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\ContentRepository\Domain\Repository\ContentDimensionRepository', 'Neos\ContentRepository\Domain\Repository\ContentDimensionRepository', 'contentDimensionRepository', '0e2f039d4a6a71ad5a12a908317ff91a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\ContentRepository\Domain\Repository\ContentDimensionRepository'); });
        $this->now = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Utility\Now');
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Context', 'Neos\Flow\Security\Context', 'securityContext', 'f7e2ddeaebd191e228b8c2e4dc7f1f83', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Context'); });
        $this->Flow_Injected_Properties = array (
  0 => 'domainRepository',
  1 => 'siteRepository',
  2 => 'contentDimensionRepository',
  3 => 'now',
  4 => 'securityContext',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Domain/Service/ContentContextFactory.php
#