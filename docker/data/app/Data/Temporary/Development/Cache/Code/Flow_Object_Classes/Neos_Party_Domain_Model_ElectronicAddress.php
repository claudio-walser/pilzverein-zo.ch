<?php 
namespace Neos\Party\Domain\Model;

/*
 * This file is part of the Neos.Party package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * An electronic address
 *
 * @Flow\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 */
class ElectronicAddress_Original
{
    const TYPE_AIM = 'Aim';
    const TYPE_EMAIL = 'Email';
    const TYPE_ICQ = 'Icq';
    const TYPE_JABBER = 'Jabber';
    const TYPE_MSN = 'Msn';
    const TYPE_SIP = 'Sip';
    const TYPE_SKYPE = 'Skype';
    const TYPE_URL = 'Url';
    const TYPE_YAHOO = 'Yahoo';

    const USAGE_HOME = 'Home';
    const USAGE_WORK = 'Work';

    /**
     * @var array
     * @Flow\Transient
     */
    protected $availableElectronicAddressTypes = [
        self::TYPE_AIM,
        self::TYPE_EMAIL,
        self::TYPE_ICQ,
        self::TYPE_JABBER,
        self::TYPE_MSN,
        self::TYPE_SIP,
        self::TYPE_SKYPE,
        self::TYPE_URL,
        self::TYPE_YAHOO
    ];

    /**
     * @var array
     * @Flow\Transient
     */
    protected $availableUsageTypes = [
        self::USAGE_HOME,
        self::USAGE_WORK
    ];

    /**
     * @var string
     * @Flow\Validate(type="NotEmpty")
     * @Flow\Validate(type="StringLength", options={ "minimum"=1, "maximum"=255 })
     */
    protected $identifier;

    /**
     * @var string
     * @Flow\Validate(type="Alphanumeric")
     * @Flow\Validate(type="NotEmpty")
     * @Flow\Validate(type="StringLength", options={ "minimum"=1, "maximum"=20 })
     * @ORM\Column(length=20)
     */
    protected $type;

    /**
     * @var string
     * @Flow\Validate(type="Alphanumeric")
     * @Flow\Validate(type="StringLength", options={ "minimum"=1, "maximum"=20 })
     * @ORM\Column(name="usagetype", length=20, nullable=true)
     */
    protected $usage;

    /**
     * @var boolean
     */
    protected $approved = false;

    /**
     * Get all electronic address types
     *
     * @return array
     */
    public function getAvailableElectronicAddressTypes()
    {
        return $this->availableElectronicAddressTypes;
    }

    /**
     * Get all usage types
     *
     * @return array
     */
    public function getAvailableUsageTypes()
    {
        return $this->availableUsageTypes;
    }

    /**
     * Sets the identifier (= the value) of this electronic address.
     *
     * Example: john@example.com
     *
     * @param string $identifier The identifier
     * @return void
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Returns the identifier (= the value) of this electronic address.
     *
     * @return string The identifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Returns the type of this electronic address
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the type of this electronic address
     *
     * @param string $type If possible, use one of the TYPE_ constants
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Returns the usage of this electronic address
     *
     * @return string
     */
    public function getUsage()
    {
        return $this->usage;
    }

    /**
     * Sets the usage of this electronic address
     *
     * @param string $usage If possible, use one of the USAGE_ constants
     * @return void
     */
    public function setUsage($usage)
    {
        $this->usage = $usage;
    }

    /**
     * Sets the approved status
     *
     * @param boolean $approved If this address has been approved or not
     * @return void
     */
    public function setApproved($approved)
    {
        $this->approved = $approved ? true : false;
    }

    /**
     * Tells if this address has been approved
     *
     * @return boolean TRUE if the address has been approved, otherwise FALSE
     */
    public function isApproved()
    {
        return $this->approved;
    }

    /**
     * An alias for getIdentifier()
     *
     * @return string The identifier of this electronic address
     */
    public function __toString()
    {
        return $this->identifier;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Party\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * An electronic address
 * @\Neos\Flow\Annotations\Entity
 * @\Doctrine\ORM\Mapping\InheritanceType("SINGLE_TABLE")
 */
class ElectronicAddress extends ElectronicAddress_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface, \Neos\Flow\Persistence\Aspect\PersistenceMagicInterface {

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
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Party\Domain\Model\ElectronicAddress', '__construct', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Party\Domain\Model\ElectronicAddress', '__construct', $methodArguments);
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
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Party\Domain\Model\ElectronicAddress', '__clone', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Party\Domain\Model\ElectronicAddress', '__clone', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Party\Domain\Model\ElectronicAddress', '__clone', $methodArguments, NULL, $result);
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
  0 => 'availableElectronicAddressTypes',
  1 => 'availableUsageTypes',
);
        $propertyVarTags = array (
  'availableElectronicAddressTypes' => 'array',
  'availableUsageTypes' => 'array',
  'identifier' => 'string',
  'type' => 'string',
  'usage' => 'string',
  'approved' => 'boolean',
  'Persistence_Object_Identifier' => 'string',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Party/Classes/Domain/Model/ElectronicAddress.php
#