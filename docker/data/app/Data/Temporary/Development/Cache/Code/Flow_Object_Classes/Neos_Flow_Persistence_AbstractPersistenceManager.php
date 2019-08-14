<?php 
namespace Neos\Flow\Persistence;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Utility\ObjectAccess;
use Neos\Flow\Persistence\Exception as PersistenceException;

/**
 * The Flow Persistence Manager base class
 *
 * @api
 */
abstract class AbstractPersistenceManager_Original implements PersistenceManagerInterface
{
    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var array
     */
    protected $newObjects = [];

    /**
     * @var boolean
     */
    protected $hasUnpersistedChanges = false;

    /**
     * @var \SplObjectStorage
     */
    protected $whitelistedObjects;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->whitelistedObjects = new \SplObjectStorage();
    }

    /**
     * Injects the Flow settings, the persistence part is kept
     * for further use.
     *
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings)
    {
        $this->settings = $settings['persistence'];
    }

    /**
     * Clears the in-memory state of the persistence.
     *
     * @return void
     */
    public function clearState()
    {
        $this->newObjects = [];
    }

    /**
     * Registers an object which has been created or cloned during this request.
     *
     * The given object must contain the Persistence_Object_Identifier property, thus
     * the PersistenceMagicInterface type hint. A "new" object does not necessarily
     * have to be known by any repository or be persisted in the end.
     *
     * Objects registered with this method must be known to the getObjectByIdentifier()
     * method.
     *
     * @param Aspect\PersistenceMagicInterface $object The new object to register
     * @return void
     */
    public function registerNewObject(Aspect\PersistenceMagicInterface $object)
    {
        $identifier = ObjectAccess::getProperty($object, 'Persistence_Object_Identifier', true);
        $this->newObjects[$identifier] = $object;
    }

    /**
     * Adds the given object to a whitelist of objects which may be persisted even if the current HTTP request
     * is considered a "safe" request.
     *
     * @param object $object The object
     * @return void
     * @api
     */
    public function whitelistObject($object)
    {
        $this->whitelistedObjects->attach($object);
    }

    /**
     * Checks if the given object is whitelisted and if not, throws an exception
     *
     * @param object $object
     * @return void
     * @throws \Neos\Flow\Persistence\Exception
     */
    protected function throwExceptionIfObjectIsNotWhitelisted($object)
    {
        if (!$this->whitelistedObjects->contains($object)) {
            $message = 'Detected modified or new objects (' . get_class($object) . ', uuid:' . $this->getIdentifierByObject($object) . ') to be persisted which is not allowed for "safe requests"' . chr(10) .
                    'According to the HTTP 1.1 specification, so called "safe request" (usually GET or HEAD requests)' . chr(10) .
                    'should not change your data on the server side and should be considered read-only. If you need to add,' . chr(10) .
                    'modify or remove data, you should use the respective request methods (POST, PUT, DELETE and PATCH).' . chr(10) . chr(10) .
                    'If you need to store some data during a safe request (for example, logging some data for your analytics),' . chr(10) .
                    'you are still free to call PersistenceManager->persistAll() manually.';
            throw new PersistenceException($message, 1377788621);
        }
    }

    /**
     * Converts the given object into an array containing the identity of the domain object.
     *
     * @param object $object The object to be converted
     * @return array The identity array in the format array('__identity' => '...')
     * @throws Exception\UnknownObjectException if the given object is not known to the Persistence Manager
     */
    public function convertObjectToIdentityArray($object)
    {
        $identifier = $this->getIdentifierByObject($object);
        if ($identifier === null) {
            throw new Exception\UnknownObjectException(sprintf('Tried to convert an object of type "%s" to an identity array, but it is unknown to the Persistence Manager.', get_class($object)), 1302628242);
        }
        return ['__identity' => $identifier];
    }

    /**
     * Recursively iterates through the given array and turns objects
     * into an arrays containing the identity of the domain object.
     *
     * @param array $array The array to be iterated over
     * @return array The modified array without objects
     * @throws Exception\UnknownObjectException if array contains objects that are not known to the Persistence Manager
     */
    public function convertObjectsToIdentityArrays(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->convertObjectsToIdentityArrays($value);
            } elseif (is_object($value) && $value instanceof \Traversable) {
                $array[$key] = $this->convertObjectsToIdentityArrays(iterator_to_array($value));
            } elseif (is_object($value)) {
                $array[$key] = $this->convertObjectToIdentityArray($value);
            }
        }
        return $array;
    }

    /**
     * Gives feedback if the persistence Manager has unpersisted changes.
     *
     * This is primarily used to inform the user if he tries to save
     * data in an unsafe request.
     *
     * @return boolean
     */
    public function hasUnpersistedChanges()
    {
        return $this->hasUnpersistedChanges;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Persistence;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * The Flow Persistence Manager base class
 */
abstract class AbstractPersistenceManager extends AbstractPersistenceManager_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\Aop\AdvicesTrait;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        parent::__construct();
    }

    /**
     * Autogenerated Proxy Method
     */
    protected function Flow_Aop_Proxy_buildMethodsAndAdvicesArray()
    {
        if (method_exists(get_parent_class(), 'Flow_Aop_Proxy_buildMethodsAndAdvicesArray') && is_callable('parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray')) parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        $objectManager = \Neos\Flow\Core\Bootstrap::$staticObjectManager;
        $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array(
            'convertObjectToIdentityArray' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Neos\Routing\NodeIdentityConverterAspect', 'convertNodeToContextPathForRouting', $objectManager, NULL),
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
    }

    /**
     * Autogenerated Proxy Method
     * @param object $object The object to be converted
     * @return array The identity array in the format array('__identity' => '...')
     * @throws Exception\UnknownObjectException if the given object is not known to the Persistence Manager
     */
    public function convertObjectToIdentityArray($object)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['convertObjectToIdentityArray'])) {
            $result = parent::convertObjectToIdentityArray($object);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['convertObjectToIdentityArray'] = true;
            try {
            
                $methodArguments = [];

                $methodArguments['object'] = $object;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('convertObjectToIdentityArray');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Persistence\AbstractPersistenceManager', 'convertObjectToIdentityArray', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['convertObjectToIdentityArray']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['convertObjectToIdentityArray']);
        }
        return $result;
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Framework/Neos.Flow/Classes/Persistence/AbstractPersistenceManager.php
#