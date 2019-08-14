<?php 
namespace Neos\Flow\Persistence\Doctrine;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Exception\ObjectValidationFailedException;
use Neos\Flow\Reflection\ClassSchema;
use Neos\Flow\Reflection\ReflectionService;
use Neos\Flow\Validation\ValidatorResolver;
use Neos\Utility\ObjectAccess;
use Neos\Utility\TypeHandling;

/**
 * An onFlush listener for Flow's Doctrine PersistenceManager.
 *
 * Used to de-duplicate value objects and validate new and updated objects during flush.
 *
 * @Flow\Scope("singleton")
 * @api
 */
class ObjectValidationAndDeDuplicationListener_Original
{
    /**
     * @Flow\Inject
     * @var ValidatorResolver
     */
    protected $validatorResolver;

    /**
     * @Flow\Inject
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * @Flow\Inject
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * An onFlush event listener used to act upon persistence.
     *
     * @param OnFlushEventArgs $eventArgs
     * @return void
     * @throws ObjectValidationFailedException
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $this->entityManager = $eventArgs->getEntityManager();
        $validatedInstancesContainer = new \SplObjectStorage();

        $this->deduplicateValueObjectInsertions();

        $unitOfWork = $this->entityManager->getUnitOfWork();
        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            $this->validateObject($entity, $validatedInstancesContainer);
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            $this->validateObject($entity, $validatedInstancesContainer);
        }
    }

    /**
     * Loops over scheduled insertions and checks for duplicate value objects. Any duplicates are unset from the
     * list of scheduled insertions.
     *
     * @return void
     */
    private function deduplicateValueObjectInsertions()
    {
        $unitOfWork = $this->entityManager->getUnitOfWork();
        $entityInsertions = $unitOfWork->getScheduledEntityInsertions();

        $knownValueObjects = [];
        foreach ($entityInsertions as $entity) {
            $className = TypeHandling::getTypeForValue($entity);
            if ($this->reflectionService->getClassSchema($className)->getModelType() === ClassSchema::MODELTYPE_VALUEOBJECT) {
                $identifier = $this->persistenceManager->getIdentifierByObject($entity);

                if (isset($knownValueObjects[$className][$identifier]) || $unitOfWork->getEntityPersister($className)->exists($entity)) {
                    unset($entityInsertions[spl_object_hash($entity)]);
                    continue;
                }

                $knownValueObjects[$className][$identifier] = true;
            }
        }

        ObjectAccess::setProperty($unitOfWork, 'entityInsertions', $entityInsertions, true);
    }

    /**
     * Validates the given object and throws an exception if validation fails.
     *
     * @param object $object
     * @param \SplObjectStorage $validatedInstancesContainer
     * @return void
     * @throws ObjectValidationFailedException
     */
    private function validateObject($object, \SplObjectStorage $validatedInstancesContainer)
    {
        $className = $this->entityManager->getClassMetadata(get_class($object))->getName();
        $validator = $this->validatorResolver->getBaseValidatorConjunction($className, ['Persistence', 'Default']);
        if ($validator === null) {
            return;
        }

        $validator->setValidatedInstancesContainer($validatedInstancesContainer);
        $validationResult = $validator->validate($object);
        if ($validationResult->hasErrors()) {
            $errorMessages = '';
            $errorCount = 0;
            $allErrors = $validationResult->getFlattenedErrors();
            foreach ($allErrors as $path => $errors) {
                $errorMessages .= $path . ':' . PHP_EOL;
                foreach ($errors as $error) {
                    $errorCount++;
                    $errorMessages .= (string)$error . PHP_EOL;
                }
            }
            throw new ObjectValidationFailedException('An instance of "' . get_class($object) . '" failed to pass validation with ' . $errorCount . ' error(s): ' . PHP_EOL . $errorMessages, 1322585164);
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Persistence\Doctrine;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * An onFlush listener for Flow's Doctrine PersistenceManager.
 * 
 * Used to de-duplicate value objects and validate new and updated objects during flush.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class ObjectValidationAndDeDuplicationListener extends ObjectValidationAndDeDuplicationListener_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Flow\Persistence\Doctrine\ObjectValidationAndDeDuplicationListener') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Persistence\Doctrine\ObjectValidationAndDeDuplicationListener', $this);
        if ('Neos\Flow\Persistence\Doctrine\ObjectValidationAndDeDuplicationListener' === get_class($this)) {
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
  'validatorResolver' => 'Neos\\Flow\\Validation\\ValidatorResolver',
  'reflectionService' => 'Neos\\Flow\\Reflection\\ReflectionService',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\Doctrine\\PersistenceManager',
  'entityManager' => 'Doctrine\\ORM\\EntityManagerInterface',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Flow\Persistence\Doctrine\ObjectValidationAndDeDuplicationListener') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Persistence\Doctrine\ObjectValidationAndDeDuplicationListener', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Validation\ValidatorResolver', 'Neos\Flow\Validation\ValidatorResolver', 'validatorResolver', 'e992f50de62d81bfe770d5c5f1242621', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Validation\ValidatorResolver'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Reflection\ReflectionService', 'Neos\Flow\Reflection\ReflectionService', 'reflectionService', '464c26aa94c66579c050985566cbfc1f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Reflection\ReflectionService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\Doctrine\PersistenceManager', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '90135528ef7af4a013b4d45f90addf22', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\Doctrine\PersistenceManager'); });
        $this->Flow_Injected_Properties = array (
  0 => 'validatorResolver',
  1 => 'reflectionService',
  2 => 'persistenceManager',
);
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Framework/Neos.Flow/Classes/Persistence/Doctrine/ObjectValidationAndDeDuplicationListener.php
#