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

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Cache\CacheAwareInterface;

/**
 * Domain Model of a Domain.
 *
 * It is used to connect a site root node to a specific hostname.
 *
 * @Flow\Entity
 * @Flow\Scope("prototype")
 */
class Domain_Original implements CacheAwareInterface
{
    /**
     * @var string
     * @Flow\Identity
     * @Flow\Validate(type="NotEmpty")
     * @Flow\Validate(type="StringLength", options={ "minimum"=1, "maximum"=255 })
     * @Flow\Validate(type="\Neos\Neos\Validation\Validator\HostnameValidator", options={"ignoredHostnames"="localhost"})
     */
    protected $hostname;

    /**
     * @var string
     * @Flow\Validate(type="RegularExpression", options={ "regularExpression"="/^(http|https)$/" })
     * @ORM\Column(nullable=true)
     */
    protected $scheme;

    /**
     * @var integer
     * @Flow\Validate(type="NumberRange", options={ "minimum"=0, "maximum"=49151 })
     * @ORM\Column(nullable=true)
     */
    protected $port;

    /**
     * @var Site
     * @ORM\ManyToOne(inversedBy="domains")
     * @Flow\Validate(type="NotEmpty")
     */
    protected $site;

    /**
     * If domain is active
     *
     * @var boolean
     */
    protected $active = true;

    /**
     * Sets the hostname
     *
     * @param string $hostname
     * @return void
     * @api
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * Returns the hostname
     *
     * @return string
     * @api
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * Sets the scheme for the domain
     *
     * @param string $scheme Scheme for the domain
     * @return void
     * @api
     */
    public function setScheme($scheme = null)
    {
        $this->scheme = $scheme;
    }

    /**
     * Returns the scheme for this domain
     *
     * @return string The scheme
     * @api
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Sets the port for the domain
     *
     * @param integer $port Port for the domain
     * @return void
     * @api
     */
    public function setPort($port = null)
    {
        $this->port = $port;
    }

    /**
     * Returns the port for this domain
     *
     * @return integer The port
     * @api
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Sets the site this domain is pointing to
     *
     * @param Site $site The site
     * @return void
     * @api
     */
    public function setSite(Site $site)
    {
        $this->site = $site;
    }

    /**
     * Returns the site this domain is pointing to
     *
     * @return Site
     * @api
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Sets if the domain is active
     *
     * @param boolean $active If the domain is active
     * @return void
     * @api
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Returns if the domain is active
     *
     * @return boolean If active or not
     * @api
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Internal event handler to forward domain changes to the "siteChanged" signal
     *
     * @ORM\PostPersist
     * @ORM\PostUpdate
     * @ORM\PostRemove
     * @return void
     */
    public function onPostFlush()
    {
        $this->site->emitSiteChanged();
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheEntryIdentifier(): string
    {
        return $this->hostname;
    }

    /**
     * Returns a URI string representation of this domain
     *
     * @return string This domain as a URI string
     */
    public function __toString()
    {
        $domain = '';
        $domain .= $this->scheme ? $this->scheme . '://' : '';
        $domain .= $this->hostname;
        if ($this->port !== null) {
            switch ($this->scheme) {
                case 'http':
                    $domain .= ($this->port !== 80 ? ':' . $this->port : '');
                    break;
                case 'https':
                    $domain .= ($this->port !== 443 ? ':' . $this->port : '');
                    break;
                default:
                    $domain .= (isset($this->port) ? ':' . $this->port : '');
            }
        }
        return $domain;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Domain Model of a Domain.
 * 
 * It is used to connect a site root node to a specific hostname.
 * @\Neos\Flow\Annotations\Entity
 * @\Neos\Flow\Annotations\Scope("prototype")
 */
class Domain extends Domain_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface, \Neos\Flow\Persistence\Aspect\PersistenceMagicInterface {

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
     */
    public function __construct()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'])) {

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'] = true;
            try {
            
                $methodArguments = [];

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['Neos\Flow\Aop\Advice\BeforeAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['Neos\Flow\Aop\Advice\BeforeAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Domain\Model\Domain', '__construct', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Domain\Model\Domain', '__construct', $methodArguments);
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
            '__construct' => array(
                'Neos\Flow\Aop\Advice\BeforeAdvice' => array(
                    new \Neos\Flow\Aop\Advice\BeforeAdvice('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', 'generateUuid', $objectManager, NULL),
                ),
            ),
            '__clone' => array(
                'Neos\Flow\Aop\Advice\BeforeAdvice' => array(
                    new \Neos\Flow\Aop\Advice\BeforeAdvice('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', 'generateUuid', $objectManager, NULL),
                ),
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', 'cloneObject', $objectManager, NULL),
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
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Domain\Model\Domain', '__clone', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Domain\Model\Domain', '__clone', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Neos\Domain\Model\Domain', '__clone', $methodArguments, NULL, $result);
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
     */
    public function __sleep()
    {
            $result = NULL;
        $this->Flow_Object_PropertiesToSerialize = array();

        $transientProperties = array (
);
        $propertyVarTags = array (
  'hostname' => 'string',
  'scheme' => 'string',
  'port' => 'integer',
  'site' => 'Neos\\Neos\\Domain\\Model\\Site',
  'active' => 'boolean',
  'Persistence_Object_Identifier' => 'string',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Domain/Model/Domain.php
#