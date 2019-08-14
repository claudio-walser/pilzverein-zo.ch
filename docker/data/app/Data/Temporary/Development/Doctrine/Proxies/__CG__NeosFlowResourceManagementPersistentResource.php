<?php

namespace Neos\Flow\Persistence\Doctrine\Proxies\__CG__\Neos\Flow\ResourceManagement;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class PersistentResource extends \Neos\Flow\ResourceManagement\PersistentResource implements \Doctrine\ORM\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Common\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array properties to be lazy loaded, with keys being the property
     *            names and values being their default values
     *
     * @see \Doctrine\Common\Persistence\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = [];



    /**
     * @param \Closure $initializer
     * @param \Closure $cloner
     */
    public function __construct($initializer = null, $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }







    /**
     * {@inheritDoc}
     * @return array
     */
    public function __sleep()
    {
        $properties = array_merge(['__isInitialized__'], parent::__sleep());

        if ($this->__isInitialized__) {
            $properties = array_diff($properties, array_keys($this->__getLazyProperties()));
        }

        return $properties;
    }

    /**
     * {@inheritDoc}
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (PersistentResource $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy->__getLazyProperties() as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
        parent::__wakeup();
    }

    /**
     * {@inheritDoc}
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', []);

        parent::__clone();
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load()
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', []);
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized)
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null)
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer()
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null)
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner()
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @static
     */
    public function __getLazyProperties()
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function initializeObject($initializationCause)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'initializeObject', [$initializationCause]);

        return parent::initializeObject($initializationCause);
    }

    /**
     * {@inheritDoc}
     */
    public function getStream()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getStream', []);

        return parent::getStream();
    }

    /**
     * {@inheritDoc}
     */
    public function setCollectionName($collectionName)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCollectionName', [$collectionName]);

        return parent::setCollectionName($collectionName);
    }

    /**
     * {@inheritDoc}
     */
    public function getCollectionName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCollectionName', []);

        return parent::getCollectionName();
    }

    /**
     * {@inheritDoc}
     */
    public function setFilename($filename)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setFilename', [$filename]);

        return parent::setFilename($filename);
    }

    /**
     * {@inheritDoc}
     */
    public function getFilename()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFilename', []);

        return parent::getFilename();
    }

    /**
     * {@inheritDoc}
     */
    public function getFileExtension()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFileExtension', []);

        return parent::getFileExtension();
    }

    /**
     * {@inheritDoc}
     */
    public function setRelativePublicationPath($path)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setRelativePublicationPath', [$path]);

        return parent::setRelativePublicationPath($path);
    }

    /**
     * {@inheritDoc}
     */
    public function getRelativePublicationPath()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getRelativePublicationPath', []);

        return parent::getRelativePublicationPath();
    }

    /**
     * {@inheritDoc}
     */
    public function setMediaType($mediaType)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setMediaType', [$mediaType]);

        return parent::setMediaType($mediaType);
    }

    /**
     * {@inheritDoc}
     */
    public function getMediaType()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getMediaType', []);

        return parent::getMediaType();
    }

    /**
     * {@inheritDoc}
     */
    public function setFileSize($fileSize)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setFileSize', [$fileSize]);

        return parent::setFileSize($fileSize);
    }

    /**
     * {@inheritDoc}
     */
    public function getFileSize()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFileSize', []);

        return parent::getFileSize();
    }

    /**
     * {@inheritDoc}
     */
    public function getSha1()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getSha1', []);

        return parent::getSha1();
    }

    /**
     * {@inheritDoc}
     */
    public function setSha1($sha1)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setSha1', [$sha1]);

        return parent::setSha1($sha1);
    }

    /**
     * {@inheritDoc}
     */
    public function getMd5()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getMd5', []);

        return parent::getMd5();
    }

    /**
     * {@inheritDoc}
     */
    public function setMd5($md5)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setMd5', [$md5]);

        return parent::setMd5($md5);
    }

    /**
     * {@inheritDoc}
     */
    public function createTemporaryLocalCopy()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'createTemporaryLocalCopy', []);

        return parent::createTemporaryLocalCopy();
    }

    /**
     * {@inheritDoc}
     */
    public function postPersist()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'postPersist', []);

        return parent::postPersist();
    }

    /**
     * {@inheritDoc}
     */
    public function preRemove()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'preRemove', []);

        return parent::preRemove();
    }

    /**
     * {@inheritDoc}
     */
    public function disableLifecycleEvents()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'disableLifecycleEvents', []);

        return parent::disableLifecycleEvents();
    }

    /**
     * {@inheritDoc}
     */
    public function setDeleted($flag = true)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDeleted', [$flag]);

        return parent::setDeleted($flag);
    }

    /**
     * {@inheritDoc}
     */
    public function isDeleted()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isDeleted', []);

        return parent::isDeleted();
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheEntryIdentifier(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCacheEntryIdentifier', []);

        return parent::getCacheEntryIdentifier();
    }

    /**
     * {@inheritDoc}
     */
    public function shutdownObject()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'shutdownObject', []);

        return parent::shutdownObject();
    }

    /**
     * {@inheritDoc}
     */
    public function Flow_Aop_Proxy_invokeJoinPoint(\Neos\Flow\Aop\JoinPointInterface $joinPoint)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'Flow_Aop_Proxy_invokeJoinPoint', [$joinPoint]);

        return parent::Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
    }

}