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
 * A person name
 *
 * @Flow\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 */
class PersonName_Original
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $middleName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var string
     */
    protected $otherName;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var string
     */
    protected $fullName;

    /**
     * Constructs this person name
     *
     * @param string $title the title, e.g. "Mr." or "Mr. Phd"
     * @param string $firstName the first name
     * @param string $middleName the middle name
     * @param string $lastName the last name
     * @param string $otherName the "other" name, e.g. "IV." or "jr."
     * @param string $alias an alias or nickname
     * @var string
     */
    public function __construct($title = '', $firstName = '', $middleName = '', $lastName = '', $otherName = '', $alias = '')
    {
        $this->title = $title;
        $this->firstName = $firstName;
        $this->middleName = $middleName;
        $this->lastName = $lastName;
        $this->otherName = $otherName;
        $this->alias = $alias;

        $this->generateFullName();
    }

    /**
     * @return void
     */
    protected function generateFullName()
    {
        $nameParts = [
            $this->title,
            $this->firstName,
            $this->middleName,
            $this->lastName,
            $this->otherName
        ];
        $nameParts = array_map('trim', $nameParts);
        $filledNameParts = [];
        foreach ($nameParts as $namePart) {
            if ($namePart !== '') {
                $filledNameParts[] = $namePart;
            }
        }
        $this->fullName = implode(' ', $filledNameParts);
    }

    /**
     * Setter for firstName
     *
     * @param string $firstName
     * @return void
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        $this->generateFullName();
    }

    /**
     * Setter for middleName
     *
     * @param string $middleName
     * @return void
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;
        $this->generateFullName();
    }

    /**
     * Setter for lastName
     *
     * @param string $lastName
     * @return void
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        $this->generateFullName();
    }

    /**
     * Setter for title
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
        $this->generateFullName();
    }

    /**
     * Setter for otherName
     *
     * @param string $otherName
     * @return void
     */
    public function setOtherName($otherName)
    {
        $this->otherName = $otherName;
        $this->generateFullName();
    }

    /**
     * Setter for alias
     *
     * @param string $alias
     * @return void
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * Getter for firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Getter for middleName
     *
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * Getter for lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Getter for title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Getter for otherName
     *
     * @return string
     */
    public function getOtherName()
    {
        return $this->otherName;
    }

    /**
     * Getter for alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Returns the full name, e.g. "Mr. PhD John W. Doe"
     *
     * @return string The full person name
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * An alias for getFullName()
     *
     * @return string The full person name
     */
    public function __toString()
    {
        return $this->fullName;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Party\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A person name
 * @\Neos\Flow\Annotations\Entity
 * @\Doctrine\ORM\Mapping\InheritanceType("SINGLE_TABLE")
 */
class PersonName extends PersonName_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface, \Neos\Flow\Persistence\Aspect\PersistenceMagicInterface {

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
     * @param string $title the title, e.g. "Mr." or "Mr. Phd
     * @param string $firstName the first name
     * @param string $middleName the middle name
     * @param string $lastName the last name
     * @param string $otherName the "other" name, e.g. "IV." or "jr.
     * @param string $alias an alias or nickname
     */
    public function __construct()
    {
        $arguments = func_get_args();

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'])) {
        call_user_func_array('parent::__construct', $arguments);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'] = true;
            try {
            
                $methodArguments = [];

                if (array_key_exists(0, $arguments)) $methodArguments['title'] = $arguments[0];
                if (array_key_exists(1, $arguments)) $methodArguments['firstName'] = $arguments[1];
                if (array_key_exists(2, $arguments)) $methodArguments['middleName'] = $arguments[2];
                if (array_key_exists(3, $arguments)) $methodArguments['lastName'] = $arguments[3];
                if (array_key_exists(4, $arguments)) $methodArguments['otherName'] = $arguments[4];
                if (array_key_exists(5, $arguments)) $methodArguments['alias'] = $arguments[5];
            
                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['Neos\Flow\Aop\Advice\BeforeAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['Neos\Flow\Aop\Advice\BeforeAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Party\Domain\Model\PersonName', '__construct', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Party\Domain\Model\PersonName', '__construct', $methodArguments);
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
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Party\Domain\Model\PersonName', '__clone', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Party\Domain\Model\PersonName', '__clone', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Party\Domain\Model\PersonName', '__clone', $methodArguments, NULL, $result);
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
  'title' => 'string',
  'firstName' => 'string',
  'middleName' => 'string',
  'lastName' => 'string',
  'otherName' => 'string',
  'alias' => 'string',
  'fullName' => 'string',
  'Persistence_Object_Identifier' => 'string',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Party/Classes/Domain/Model/PersonName.php
#