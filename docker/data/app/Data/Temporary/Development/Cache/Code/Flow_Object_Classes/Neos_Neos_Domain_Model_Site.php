<?php 
namespace Neos\Neos\Domain\Model;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Media\Domain\Model\AssetCollection;

/**
 * Domain model of a site
 *
 * @Flow\Entity
 * @api
 */
class Site_Original
{
    /**
     * Site states
     */
    const STATE_ONLINE = 1;
    const STATE_OFFLINE = 2;

    /**
     * Name of the site
     *
     * @var string
     * @Flow\Validate(type="Label")
     * @Flow\Validate(type="NotEmpty")
     * @Flow\Validate(type="StringLength", options={ "minimum"=1, "maximum"=250 })
     */
    protected $name = 'Untitled Site';

    /**
     * Node name of this site in the content repository.
     *
     * The first level of nodes of a site can be reached via a path like
     * "/Sites/MySite/" where "MySite" is the nodeName.
     *
     * @var string
     * @Flow\Identity
     * @Flow\Validate(type="NotEmpty")
     * @Flow\Validate(type="StringLength", options={ "minimum"=1, "maximum"=250 })
     * @Flow\Validate(type="\Neos\Neos\Validation\Validator\NodeNameValidator")
     */
    protected $nodeName;

    /**
     * @var Collection<Domain>
     * @ORM\OneToMany(mappedBy="site")
     * @Flow\Lazy
     */
    protected $domains;

    /**
     * @var Domain
     * @ORM\ManyToOne
     * @ORM\Column(nullable=true)
     */
    protected $primaryDomain;

    /**
     * The site's state
     *
     * @var integer
     * @Flow\Validate(type="NumberRange", options={ "minimum"=1, "maximum"=2 })
     */
    protected $state = self::STATE_OFFLINE;

    /**
     * @var string
     * @Flow\Validate(type="NotEmpty")
     */
    protected $siteResourcesPackageKey;

    /**
     * @var AssetCollection
     * @ORM\ManyToOne
     */
    protected $assetCollection;

    /**
     * Constructs this Site object
     *
     * @param string $nodeName Node name of this site in the content repository
     */
    public function __construct($nodeName)
    {
        $this->nodeName = $nodeName;
        $this->domains = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getNodeName();
    }

    /**
     * Sets the name for this site
     *
     * @param string $name The site name
     * @return void
     * @api
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the name of this site
     *
     * @return string The name
     * @api
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the node name of this site
     *
     * If you need to fetch the root node for this site, use the content
     * context, do not use the NodeDataRepository!
     *
     * @return string The node name
     * @api
     */
    public function getNodeName()
    {
        return $this->nodeName;
    }

    /**
     * Sets the node name for this site
     *
     * @param string $nodeName The site node name
     * @return void
     * @api
     */
    public function setNodeName($nodeName)
    {
        $this->nodeName = $nodeName;
    }

    /**
     * Sets the state for this site
     *
     * @param integer $state The site's state, must be one of the STATUS_* constants
     * @return void
     * @api
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Returns the state of this site
     *
     * @return integer The state - one of the STATUS_* constant's values
     * @api
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Sets the key of a package containing the static resources for this site.
     *
     * @param string $packageKey The package key
     * @return void
     * @api
     */
    public function setSiteResourcesPackageKey($packageKey)
    {
        $this->siteResourcesPackageKey = $packageKey;
    }

    /**
     * Returns the key of a package containing the static resources for this site.
     *
     * @return string The package key
     * @api
     */
    public function getSiteResourcesPackageKey()
    {
        return $this->siteResourcesPackageKey;
    }

    /**
     * @return boolean
     * @api
     */
    public function isOnline()
    {
        return $this->state === self::STATE_ONLINE;
    }

    /**
     * @return boolean
     * @api
     */
    public function isOffline()
    {
        return $this->state === self::STATE_OFFLINE;
    }

    /**
     * @param Collection<Domain> $domains
     * @return void
     * @api
     */
    public function setDomains($domains)
    {
        $this->domains = $domains;
        if (!$this->domains->contains($this->primaryDomain)) {
            $this->primaryDomain = $this->getFirstActiveDomain();
        }
    }

    /**
     * @return Collection<Domain>
     * @api
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * @return boolean true if the site has at least one active domain assigned
     * @api
     */
    public function hasActiveDomains()
    {
        return $this->domains->exists(function ($index, Domain $domain) {
            return $domain->getActive();
        });
    }

    /**
     * @return Collection<Domain>
     * @api
     */
    public function getActiveDomains()
    {
        $activeDomains = $this->domains->filter(function (Domain $domain) {
            return $domain->getActive();
        });
        return $activeDomains;
    }

    /**
     * @return Domain|null
     * @api
     */
    public function getFirstActiveDomain()
    {
        $activeDomains = $this->getActiveDomains();
        return count($activeDomains) > 0 ? $this->getActiveDomains()->first() : null;
    }

    /**
     * Sets (and adds if necessary) the primary domain of this site.
     *
     * @param Domain|null $domain The domain
     * @return void
     * @api
     */
    public function setPrimaryDomain(Domain $domain = null)
    {
        if ($domain === null) {
            $this->primaryDomain = null;
            return;
        }

        if (!$domain->getActive()) {
            return;
        }

        $this->primaryDomain = $domain;
        if (!$this->domains->contains($domain)) {
            $this->domains->add($domain);
        }
    }

    /**
     * Returns the primary domain, if one has been defined.
     *
     * @return Domain The primary domain or NULL
     * @api
     */
    public function getPrimaryDomain()
    {
        return isset($this->primaryDomain) && $this->primaryDomain->getActive() ? $this->primaryDomain : $this->getFirstActiveDomain();
    }

    /**
     * @return AssetCollection
     */
    public function getAssetCollection()
    {
        return $this->assetCollection;
    }

    /**
     * @param AssetCollection $assetCollection
     * @return void
     */
    public function setAssetCollection(AssetCollection $assetCollection = null)
    {
        $this->assetCollection = $assetCollection;
    }

    /**
     * Internal event handler to forward site changes to the "siteChanged" signal
     *
     * @ORM\PostPersist
     * @ORM\PostUpdate
     * @ORM\PostRemove
     * @return void
     */
    public function onPostFlush()
    {
        $this->emitSiteChanged();
    }

    /**
     * Internal signal
     *
     * @Flow\Signal
     * @return void
     */
    public function emitSiteChanged()
    {
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Domain model of a site
 * @\Neos\Flow\Annotations\Entity
 */
class Site extends Site_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface, \Neos\Flow\Persistence\Aspect\PersistenceMagicInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(length=40)
     * introduced by Neos\Flow\Persistence\Aspect\PersistenceMagicAspect
     */
    protected $Persistence_Object_Identifier = NULL;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     * @param string $nodeName Node name of this site in the content repository
     */
    public function __construct()
    {
        $arguments = func_get_args();

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'])) {
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $nodeName in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'] = true;
            try {
            
                $methodArguments = [];

                if (array_key_exists(0, $arguments)) $methodArguments['nodeName'] = $arguments[0];
            
                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['Neos\Flow\Aop\Advice\BeforeAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['Neos\Flow\Aop\Advice\BeforeAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Domain\Model\Site', '__construct', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Domain\Model\Site', '__construct', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
            return;
        }
    }

    /**
     * Autogenerated Proxy Method
     */
    protected function Flow_Aop_Proxy_buildMethodsAndAdvicesArray()
    {
        if (method_exists(get_parent_class(), 'Flow_Aop_Proxy_buildMethodsAndAdvicesArray') && is_callable('parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray')) parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        $objectManager = \Neos\Flow\Core\Bootstrap::$staticObjectManager;
        $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array(
            '__clone' => array(
                'Neos\Flow\Aop\Advice\BeforeAdvice' => array(
                    new \Neos\Flow\Aop\Advice\BeforeAdvice('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', 'generateUuid', $objectManager, NULL),
                ),
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', 'cloneObject', $objectManager, NULL),
                ),
            ),
            '__construct' => array(
                'Neos\Flow\Aop\Advice\BeforeAdvice' => array(
                    new \Neos\Flow\Aop\Advice\BeforeAdvice('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', 'generateUuid', $objectManager, NULL),
                ),
            ),
            'emitSiteChanged' => array(
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\SignalSlot\SignalAspect', 'forwardSignalToDispatcher', $objectManager, NULL),
                ),
            ),
        );
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        $this->Flow_setRelatedEntities();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __clone()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone'])) {
            $result = NULL;

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone'] = true;
            try {
            
                $methodArguments = [];

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\BeforeAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\BeforeAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Domain\Model\Site', '__clone', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Domain\Model\Site', '__clone', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Domain\Model\Site', '__clone', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     * @\Neos\Flow\Annotations\Signal
     */
    public function emitSiteChanged()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitSiteChanged'])) {
            $result = parent::emitSiteChanged();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitSiteChanged'] = true;
            try {
            
                $methodArguments = [];

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Domain\Model\Site', 'emitSiteChanged', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitSiteChanged']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitSiteChanged']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Domain\Model\Site', 'emitSiteChanged', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitSiteChanged']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitSiteChanged']);
        }
        return $result;
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
  'name' => 'string',
  'nodeName' => 'string',
  'domains' => 'Doctrine\\Common\\Collections\\Collection<Neos\\Neos\\Domain\\Model\\Domain>',
  'primaryDomain' => 'Neos\\Neos\\Domain\\Model\\Domain',
  'state' => 'integer',
  'siteResourcesPackageKey' => 'string',
  'assetCollection' => 'Neos\\Media\\Domain\\Model\\AssetCollection',
  'Persistence_Object_Identifier' => 'string',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Domain/Model/Site.php
#