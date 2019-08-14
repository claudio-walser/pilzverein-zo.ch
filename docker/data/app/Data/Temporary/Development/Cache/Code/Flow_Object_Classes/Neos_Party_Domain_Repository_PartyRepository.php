<?php 
namespace Neos\Party\Domain\Repository;

/*
 * This file is part of the Neos.Party package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Repository;
use Neos\Flow\Security\Account;
use Neos\Party\Domain\Model\AbstractParty;

/**
 * Repository for parties
 *
 * @Flow\Scope("singleton")
 */
class PartyRepository_Original extends Repository
{
    const ENTITY_CLASSNAME = AbstractParty::class;

    /**
     * Finds a Party instance, if any, which has the given Account attached.
     *
     * @param Account $account
     * @return AbstractParty
     */
    public function findOneHavingAccount(Account $account)
    {
        $query = $this->createQuery();

        return $query->matching($query->contains('accounts', $account))->execute()->getFirst();
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Party\Domain\Repository;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Repository for parties
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class PartyRepository extends PartyRepository_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Party\Domain\Repository\PartyRepository') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Party\Domain\Repository\PartyRepository', $this);
        parent::__construct();
        if ('Neos\Party\Domain\Repository\PartyRepository' === get_class($this)) {
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
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'entityClassName' => 'string',
  'defaultOrderings' => 'array',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Party\Domain\Repository\PartyRepository') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Party\Domain\Repository\PartyRepository', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'persistenceManager',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Party/Classes/Domain/Repository/PartyRepository.php
#