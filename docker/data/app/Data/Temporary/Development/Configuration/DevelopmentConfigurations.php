<?php return array (
  'Caches' => 
  array (
    'Eel_Expression_Code' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\StringFrontend',
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
    ),
    'Fluid_TemplateCache' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\PhpFrontend',
      'backend' => 'Neos\\Cache\\Backend\\FileBackend',
    ),
    'Default' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\VariableFrontend',
      'backend' => 'Neos\\Cache\\Backend\\FileBackend',
      'backendOptions' => 
      array (
        'defaultLifetime' => 0,
      ),
      'persistent' => false,
    ),
    'Flow_Cache_ResourceFiles' => 
    array (
    ),
    'Flow_Core' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\StringFrontend',
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
    ),
    'Flow_I18n_AvailableLocalesCache' => 
    array (
    ),
    'Flow_I18n_XmlModelCache' => 
    array (
    ),
    'Flow_I18n_Cldr_CldrModelCache' => 
    array (
    ),
    'Flow_I18n_Cldr_Reader_DatesReaderCache' => 
    array (
    ),
    'Flow_I18n_Cldr_Reader_NumbersReaderCache' => 
    array (
    ),
    'Flow_I18n_Cldr_Reader_PluralsReaderCache' => 
    array (
    ),
    'Flow_I18n_Cldr_Reader_CurrencyReaderCache' => 
    array (
    ),
    'Flow_Monitor' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\StringFrontend',
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
    ),
    'Flow_Mvc_Routing_Route' => 
    array (
      'backend' => 'Neos\\Cache\\Backend\\FileBackend',
    ),
    'Flow_Mvc_Routing_Resolve' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\VariableFrontend',
      'backend' => 'Neos\\Cache\\Backend\\FileBackend',
    ),
    'Flow_Mvc_ViewConfigurations' => 
    array (
    ),
    'Flow_Object_Classes' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\PhpFrontend',
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
    ),
    'Flow_Object_Configuration' => 
    array (
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
    ),
    'Flow_Persistence_Doctrine' => 
    array (
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
    ),
    'Flow_Persistence_Doctrine_Results' => 
    array (
      'backend' => 'Neos\\Cache\\Backend\\FileBackend',
      'backendOptions' => 
      array (
        'defaultLifetime' => 60,
      ),
    ),
    'Flow_Persistence_Doctrine_SecondLevel' => 
    array (
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
    ),
    'Flow_Reflection_Status' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\StringFrontend',
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
    ),
    'Flow_Reflection_CompiletimeData' => 
    array (
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
    ),
    'Flow_Reflection_RuntimeData' => 
    array (
    ),
    'Flow_Reflection_RuntimeClassSchemata' => 
    array (
    ),
    'Flow_Resource_Status' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\StringFrontend',
    ),
    'Flow_Security_Authorization_Privilege_Method' => 
    array (
    ),
    'Flow_Security_Cryptography_RSAWallet' => 
    array (
      'backendOptions' => 
      array (
        'defaultLifetime' => 30,
      ),
    ),
    'Flow_Security_Cryptography_HashService' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\StringFrontend',
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
      'persistent' => true,
    ),
    'Flow_Session_MetaData' => 
    array (
      'backend' => 'Neos\\Cache\\Backend\\FileBackend',
      'persistent' => true,
    ),
    'Flow_Session_Storage' => 
    array (
      'backend' => 'Neos\\Cache\\Backend\\FileBackend',
      'persistent' => true,
    ),
    'Flow_Aop_RuntimeExpressions' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\StringFrontend',
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
    ),
    'Flow_PropertyMapper' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\VariableFrontend',
      'backend' => 'Neos\\Cache\\Backend\\NullBackend',
    ),
    'Neos_ContentRepository_FullNodeTypeConfiguration' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\VariableFrontend',
      'backend' => 'Neos\\Cache\\Backend\\NullBackend',
    ),
    'Neos_Fusion_Content' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\StringFrontend',
      'backend' => 'Neos\\Cache\\Backend\\FileBackend',
    ),
    'Neos_Media_ImageSize' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\VariableFrontend',
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
      'backendOptions' => 
      array (
        'defaultLifetime' => 0,
      ),
    ),
    'Neos_Neos_Configuration_Version' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\StringFrontend',
      'backend' => 'Neos\\Cache\\Backend\\FileBackend',
    ),
    'Neos_Neos_Fusion' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\VariableFrontend',
      'backend' => 'Neos\\Cache\\Backend\\FileBackend',
    ),
    'Neos_Neos_XliffToJsonTranslations' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\VariableFrontend',
      'backend' => 'Neos\\Cache\\Backend\\FileBackend',
    ),
    'Neos_Neos_LoginTokenCache' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\StringFrontend',
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
      'backendOptions' => 
      array (
        'defaultLifetime' => 30,
      ),
    ),
  ),
  'Objects' => 
  array (
    'Neos.Diff' => 
    array (
    ),
    'Neos.Eel' => 
    array (
      'Neos\\Eel\\CompilingEvaluator' => 
      array (
        'scope' => 'singleton',
        'properties' => 
        array (
          'expressionCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Eel_Expression_Code',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Eel\\EelEvaluatorInterface' => 
      array (
        'className' => 'Neos\\Eel\\CompilingEvaluator',
      ),
      'Neos\\Eel\\FlowQuery\\OperationResolverInterface' => 
      array (
        'className' => 'Neos\\Eel\\FlowQuery\\OperationResolver',
      ),
    ),
    'Neos.FluidAdaptor' => 
    array (
      'Neos\\FluidAdaptor\\Core\\Cache\\CacheAdaptor' => 
      array (
        'properties' => 
        array (
          'flowCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Fluid_TemplateCache',
                ),
              ),
            ),
          ),
        ),
      ),
      'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\TagBuilder' => 
      array (
        'scope' => 'prototype',
      ),
      'Neos\\FluidAdaptor\\Core\\ViewHelper\\AbstractViewHelper' => 
      array (
        'properties' => 
        array (
          'logger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
              'factoryMethodName' => 'get',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'systemLogger',
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    'Neos.Flow' => 
    array (
      'DateTime' => 
      array (
        'scope' => 'prototype',
        'autowiring' => 'off',
      ),
      'Composer\\Autoload\\ClassLoader' => 
      array (
        'scope' => 'singleton',
        'autowiring' => 'off',
      ),
      'Neos\\Cache\\CacheFactoryInterface' => 
      array (
        'className' => 'Neos\\Flow\\Cache\\CacheFactory',
        'arguments' => 
        array (
          1 => 
          array (
            'setting' => 'Neos.Flow.context',
          ),
          3 => 
          array (
            'setting' => 'Neos.Flow.cache.applicationIdentifier',
          ),
        ),
      ),
      'Neos\\Flow\\Cache\\CacheFactory' => 
      array (
        'arguments' => 
        array (
          1 => 
          array (
            'setting' => 'Neos.Flow.context',
          ),
          3 => 
          array (
            'setting' => 'Neos.Flow.cache.applicationIdentifier',
          ),
        ),
      ),
      'Neos\\Flow\\Cache\\CacheManager' => 
      array (
        'properties' => 
        array (
          'logger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
              'factoryMethodName' => 'get',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'systemLogger',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\I18n\\Service' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_I18n_AvailableLocalesCache',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\I18n\\Cldr\\CldrModel' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_I18n_Cldr_CldrModelCache',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\I18n\\Xliff\\Service\\XliffFileProvider' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_I18n_XmlModelCache',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\I18n\\Xliff\\Service\\XliffReader' => 
      array (
        'properties' => 
        array (
          'i18nLogger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
              'factoryMethodName' => 'get',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'i18nLogger',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\I18n\\Xliff\\Model\\FileAdapter' => 
      array (
        'properties' => 
        array (
          'i18nLogger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
              'factoryMethodName' => 'get',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'i18nLogger',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\I18n\\Cldr\\Reader\\DatesReader' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_I18n_Cldr_Reader_DatesReaderCache',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\I18n\\Cldr\\Reader\\NumbersReader' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_I18n_Cldr_Reader_NumbersReaderCache',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\I18n\\Cldr\\Reader\\PluralsReader' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_I18n_Cldr_Reader_PluralsReaderCache',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\I18n\\Cldr\\Reader\\CurrencyReader' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_I18n_Cldr_Reader_CurrencyReaderCache',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Log\\Backend\\FileBackend' => 
      array (
        'autowiring' => 'off',
      ),
      'Neos\\Flow\\Log\\Backend\\NullBackend' => 
      array (
        'autowiring' => 'off',
      ),
      'Neos\\Flow\\Log\\SystemLoggerInterface' => 
      array (
        'scope' => 'singleton',
        'factoryObjectName' => 'Neos\\Flow\\Log\\LoggerFactory',
        'arguments' => 
        array (
          1 => 
          array (
            'value' => 'SystemLogger',
          ),
          2 => 
          array (
            'setting' => 'Neos.Flow.log.systemLogger.logger',
          ),
          3 => 
          array (
            'setting' => 'Neos.Flow.log.systemLogger.backend',
          ),
          4 => 
          array (
            'setting' => 'Neos.Flow.log.systemLogger.backendOptions',
          ),
        ),
      ),
      'Neos\\Flow\\Log\\SecurityLoggerInterface' => 
      array (
        'scope' => 'singleton',
        'factoryObjectName' => 'Neos\\Flow\\Log\\LoggerFactory',
        'arguments' => 
        array (
          1 => 
          array (
            'value' => 'Flow_Security',
          ),
          2 => 
          array (
            'value' => 'Neos\\Flow\\Log\\Logger',
          ),
          3 => 
          array (
            'setting' => 'Neos.Flow.log.securityLogger.backend',
          ),
          4 => 
          array (
            'setting' => 'Neos.Flow.log.securityLogger.backendOptions',
          ),
        ),
      ),
      'Neos\\Flow\\Log\\ThrowableStorageInterface' => 
      array (
        'scope' => 'singleton',
        'className' => 'Neos\\Flow\\Log\\ThrowableStorage\\FileStorage',
      ),
      'Neos\\Flow\\Log\\PsrLoggerFactory' => 
      array (
        'scope' => 'singleton',
        'autowiring' => 'off',
      ),
      'Neos\\Flow\\Log\\PsrLoggerFactoryInterface' => 
      array (
        'className' => 'Neos\\Flow\\Log\\PsrLoggerFactory',
      ),
      'Neos\\Flow\\Log\\PsrSystemLoggerInterface' => 
      array (
        'scope' => 'singleton',
        'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
        'factoryMethodName' => 'get',
        'arguments' => 
        array (
          1 => 
          array (
            'value' => 'systemLogger',
          ),
        ),
      ),
      'Neos\\Flow\\Log\\PsrSecurityLoggerInterface' => 
      array (
        'scope' => 'singleton',
        'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
        'factoryMethodName' => 'get',
        'arguments' => 
        array (
          1 => 
          array (
            'value' => 'securityLogger',
          ),
        ),
      ),
      'Psr\\Log\\LoggerInterface' => 
      array (
        'scope' => 'singleton',
        'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
        'factoryMethodName' => 'get',
        'arguments' => 
        array (
          1 => 
          array (
            'value' => 'systemLogger',
          ),
        ),
      ),
      'Neos\\Flow\\Session\\Aspect\\LoggingAspect' => 
      array (
        'properties' => 
        array (
          'logger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
              'factoryMethodName' => 'get',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'systemLogger',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Monitor\\ChangeDetectionStrategy\\ModificationTimeStrategy' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Monitor',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Monitor\\FileMonitor' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Monitor',
                ),
              ),
            ),
          ),
          'logger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
              'factoryMethodName' => 'get',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'systemLogger',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Http\\Component\\ComponentChain' => 
      array (
        'factoryObjectName' => 'Neos\\Flow\\Http\\Component\\ComponentChainFactory',
        'arguments' => 
        array (
          1 => 
          array (
            'setting' => 'Neos.Flow.http.chain',
          ),
        ),
      ),
      'Neos\\Flow\\Http\\ContentStream' => 
      array (
        'properties' => 
        array (
          'logger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
              'factoryMethodName' => 'get',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'systemLogger',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Mvc\\Routing\\RouterInterface' => 
      array (
        'className' => 'Neos\\Flow\\Mvc\\Routing\\Router',
      ),
      'Neos\\Flow\\Mvc\\Routing\\Router' => 
      array (
        'properties' => 
        array (
          'logger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
              'factoryMethodName' => 'get',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'systemLogger',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Mvc\\Routing\\RouterCachingService' => 
      array (
        'properties' => 
        array (
          'routeCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Mvc_Routing_Route',
                ),
              ),
            ),
          ),
          'resolveCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Mvc_Routing_Resolve',
                ),
              ),
            ),
          ),
          'logger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
              'factoryMethodName' => 'get',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'systemLogger',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Mvc\\ViewConfigurationManager' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Mvc_ViewConfigurations',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface' => 
      array (
        'className' => 'Neos\\Flow\\ObjectManagement\\ObjectManager',
        'scope' => 'singleton',
        'autowiring' => 'off',
      ),
      'Neos\\Flow\\ObjectManagement\\ObjectManager' => 
      array (
        'autowiring' => 'off',
      ),
      'Neos\\Flow\\ObjectManagement\\CompileTimeObjectManager' => 
      array (
        'autowiring' => 'off',
      ),
      'Neos\\Flow\\Package\\PackageManagerInterface' => 
      array (
        'scope' => 'singleton',
      ),
      'Doctrine\\ORM\\EntityManagerInterface' => 
      array (
        'scope' => 'singleton',
        'factoryObjectName' => 'Neos\\Flow\\Persistence\\Doctrine\\EntityManagerFactory',
      ),
      'Doctrine\\Common\\Persistence\\ObjectManager' => 
      array (
        'scope' => 'singleton',
        'factoryObjectName' => 'Neos\\Flow\\Persistence\\Doctrine\\EntityManagerFactory',
      ),
      'Neos\\Flow\\Persistence\\PersistenceManagerInterface' => 
      array (
        'className' => 'Neos\\Flow\\Persistence\\Doctrine\\PersistenceManager',
      ),
      'Neos\\Flow\\Persistence\\Doctrine\\Logging\\SqlLogger' => 
      array (
        'properties' => 
        array (
          'logger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\LoggerFactory',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Sql_Queries',
                ),
                2 => 
                array (
                  'value' => 'Neos\\Flow\\Log\\Logger',
                ),
                3 => 
                array (
                  'value' => 'Neos\\Flow\\Log\\Backend\\FileBackend',
                ),
                4 => 
                array (
                  'setting' => 'Neos.Flow.log.sqlLogger.backendOptions',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Command\\DoctrineCommandController' => 
      array (
        'properties' => 
        array (
          'systemLogger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
              'factoryMethodName' => 'get',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'systemLogger',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Persistence\\Doctrine\\PersistenceManager' => 
      array (
        'properties' => 
        array (
          'logger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
              'factoryMethodName' => 'get',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'systemLogger',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Persistence\\Doctrine\\Query' => 
      array (
        'properties' => 
        array (
          'logger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
              'factoryMethodName' => 'get',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'systemLogger',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Property\\PropertyMapper' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_PropertyMapper',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Reflection\\ReflectionService' => 
      array (
        'factoryObjectName' => 'Neos\\Flow\\Reflection\\ReflectionServiceFactory',
      ),
      'Neos\\Flow\\ResourceManagement\\ResourceTypeConverter' => 
      array (
        'properties' => 
        array (
          'logger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
              'factoryMethodName' => 'get',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'systemLogger',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\ResourceManagement\\ResourceManager' => 
      array (
        'properties' => 
        array (
          'statusCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Resource_Status',
                ),
              ),
            ),
          ),
          'logger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
              'factoryMethodName' => 'get',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'systemLogger',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\ResourceManagement\\Target\\FileSystemTarget' => 
      array (
        'properties' => 
        array (
          'logger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
              'factoryMethodName' => 'get',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'systemLogger',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\ResourceManagement\\Publishing\\MessageCollector' => 
      array (
        'properties' => 
        array (
          'logger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
              'factoryMethodName' => 'get',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'systemLogger',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Security\\Authentication\\AuthenticationManagerInterface' => 
      array (
        'className' => 'Neos\\Flow\\Security\\Authentication\\AuthenticationProviderManager',
      ),
      'Neos\\Flow\\Security\\Cryptography\\RsaWalletServiceInterface' => 
      array (
        'className' => 'Neos\\Flow\\Security\\Cryptography\\RsaWalletServicePhp',
        'scope' => 'singleton',
        'properties' => 
        array (
          'keystoreCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Security_Cryptography_RSAWallet',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Security\\Authorization\\PrivilegeManagerInterface' => 
      array (
        'className' => 'Neos\\Flow\\Security\\Authorization\\PrivilegeManager',
      ),
      'Neos\\Flow\\Security\\Authorization\\FirewallInterface' => 
      array (
        'className' => 'Neos\\Flow\\Security\\Authorization\\FilterFirewall',
      ),
      'Neos\\Flow\\Security\\Cryptography\\HashService' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Security_Cryptography_HashService',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Security\\Cryptography\\Pbkdf2HashingStrategy' => 
      array (
        'scope' => 'singleton',
        'arguments' => 
        array (
          1 => 
          array (
            'setting' => 'Neos.Flow.security.cryptography.Pbkdf2HashingStrategy.dynamicSaltLength',
          ),
          2 => 
          array (
            'setting' => 'Neos.Flow.security.cryptography.Pbkdf2HashingStrategy.iterationCount',
          ),
          3 => 
          array (
            'setting' => 'Neos.Flow.security.cryptography.Pbkdf2HashingStrategy.derivedKeyLength',
          ),
          4 => 
          array (
            'setting' => 'Neos.Flow.security.cryptography.Pbkdf2HashingStrategy.algorithm',
          ),
        ),
      ),
      'Neos\\Flow\\Security\\Cryptography\\BCryptHashingStrategy' => 
      array (
        'scope' => 'singleton',
        'arguments' => 
        array (
          1 => 
          array (
            'setting' => 'Neos.Flow.security.cryptography.BCryptHashingStrategy.cost',
          ),
        ),
      ),
      'Neos\\Flow\\Security\\Authorization\\Privilege\\Method\\MethodTargetExpressionParser' => 
      array (
        'scope' => 'singleton',
      ),
      'Neos\\Flow\\Security\\Authorization\\Privilege\\Method\\MethodPrivilegePointcutFilter' => 
      array (
        'scope' => 'singleton',
        'properties' => 
        array (
          'objectManager' => 
          array (
            'object' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
          ),
        ),
      ),
      'Neos\\Flow\\Security\\Authorization\\Privilege\\Entity\\Doctrine\\EntityPrivilegeExpressionEvaluator' => 
      array (
        'properties' => 
        array (
          'expressionCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Eel_Expression_Code',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Security\\RequestPattern\\CsrfProtection' => 
      array (
        'properties' => 
        array (
          'logger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
              'factoryMethodName' => 'get',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'systemLogger',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Security\\Authentication\\Provider\\TestingProvider' => 
      array (
        'arguments' => 
        array (
          1 => 
          array (
            'value' => 'TestingProvider',
          ),
        ),
      ),
      'Neos\\Flow\\Session\\SessionInterface' => 
      array (
        'factoryObjectName' => 'Neos\\Flow\\Session\\SessionManagerInterface',
        'factoryMethodName' => 'getCurrentSession',
      ),
      'Neos\\Flow\\Session\\Session' => 
      array (
        'properties' => 
        array (
          'metaDataCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Session_MetaData',
                ),
              ),
            ),
          ),
          'storageCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Session_Storage',
                ),
              ),
            ),
          ),
          'logger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\PsrLoggerFactoryInterface',
              'factoryMethodName' => 'get',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'systemLogger',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Session\\SessionManagerInterface' => 
      array (
        'className' => 'Neos\\Flow\\Session\\SessionManager',
      ),
      'Neos\\Flow\\Session\\SessionManager' => 
      array (
        'properties' => 
        array (
          'metaDataCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Session_MetaData',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Utility\\SchemaGenerator' => 
      array (
        'scope' => 'singleton',
      ),
      'Neos\\Utility\\SchemaValidator' => 
      array (
        'scope' => 'singleton',
      ),
    ),
    'Neos.Form' => 
    array (
      'Neos\\Form\\Persistence\\FormPersistenceManagerInterface' => 
      array (
        'className' => 'Neos\\Form\\Persistence\\YamlPersistenceManager',
      ),
    ),
    'Neos.Twitter.Bootstrap' => 
    array (
    ),
    'Neos.Setup' => 
    array (
    ),
    'Neos.ContentRepository' => 
    array (
      'Neos\\ContentRepository\\Domain\\Repository\\ContentDimensionRepository' => 
      array (
        'properties' => 
        array (
          'dimensionsConfiguration' => 
          array (
            'setting' => 'Neos.ContentRepository.contentDimensions',
          ),
        ),
      ),
      'Neos\\ContentRepository\\Domain\\Service\\PublishingServiceInterface' => 
      array (
        'className' => 'Neos\\ContentRepository\\Domain\\Service\\PublishingService',
      ),
      'Neos\\ContentRepository\\Domain\\Model\\NodeInterface' => 
      array (
        'className' => 'Neos\\ContentRepository\\Domain\\Model\\Node',
      ),
      'Neos\\ContentRepository\\Domain\\Service\\ConfigurationContentDimensionPresetSource' => 
      array (
        'properties' => 
        array (
          'configuration' => 
          array (
            'setting' => 'Neos.ContentRepository.contentDimensions',
          ),
        ),
      ),
      'Neos\\ContentRepository\\Domain\\Service\\ContentDimensionPresetSourceInterface' => 
      array (
        'className' => 'Neos\\ContentRepository\\Domain\\Service\\ConfigurationContentDimensionPresetSource',
      ),
      'Neos\\ContentRepository\\Domain\\Model\\NodeLabelGeneratorInterface' => 
      array (
        'className' => 'Neos\\ContentRepository\\Domain\\Model\\ExpressionBasedNodeLabelGenerator',
      ),
      'Neos\\ContentRepository\\Domain\\Service\\NodeServiceInterface' => 
      array (
        'className' => 'Neos\\ContentRepository\\Domain\\Service\\NodeService',
      ),
      'Neos\\ContentRepository\\Domain\\Service\\NodeTypeManager' => 
      array (
        'properties' => 
        array (
          'fullConfigurationCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Neos_ContentRepository_FullNodeTypeConfiguration',
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    'Neos.Fusion' => 
    array (
      'Neos\\Fusion\\View\\FusionView' => 
      array (
        'properties' => 
        array (
          'fallbackView' => 
          array (
            'object' => 'Neos\\FluidAdaptor\\View\\TemplateView',
          ),
        ),
      ),
      'Neos\\Fusion\\Core\\Cache\\ContentCache' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Neos_Fusion_Content',
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    'Neos.Imagine' => 
    array (
      'Imagine\\Image\\ImagineInterface' => 
      array (
        'factoryObjectName' => 'Neos\\Imagine\\ImagineFactory',
      ),
    ),
    'Neos.Media' => 
    array (
      'Neos\\Media\\Domain\\Service\\ImageService' => 
      array (
        'properties' => 
        array (
          'imagineService' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Imagine\\ImagineFactory',
            ),
          ),
          'imageSizeCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Neos_Media_ImageSize',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Media\\Domain\\Model\\ImageInterface' => 
      array (
        'className' => 'Neos\\Media\\Domain\\Model\\Image',
      ),
      'Neos\\Media\\Domain\\Strategy\\AssetModelMappingStrategyInterface' => 
      array (
        'className' => 'Neos\\Media\\Domain\\Strategy\\ConfigurationAssetModelMappingStrategy',
      ),
    ),
    'Neos.Party' => 
    array (
    ),
    'Neos.Kickstarter' => 
    array (
    ),
    'Neos.SiteKickstarter' => 
    array (
    ),
    'Neos.Behat' => 
    array (
    ),
    'Neos.RedirectHandler' => 
    array (
    ),
    'Neos.Neos.Ui.Compiled' => 
    array (
    ),
    'Neos.Fusion.Afx' => 
    array (
    ),
    'Neos.RedirectHandler.DatabaseStorage' => 
    array (
    ),
    'Neos.Media.Browser' => 
    array (
    ),
    'Neos.Neos' => 
    array (
      'Neos\\Neos\\ViewHelpers\\Backend\\ConfigurationCacheVersionViewHelper' => 
      array (
        'properties' => 
        array (
          'configurationCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Neos_Neos_Configuration_Version',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\ContentRepository\\Domain\\Service\\ContextFactoryInterface' => 
      array (
        'className' => 'Neos\\Neos\\Domain\\Service\\ContentContextFactory',
      ),
      'Neos\\ContentRepository\\Domain\\Service\\PublishingServiceInterface' => 
      array (
        'className' => 'Neos\\Neos\\Service\\PublishingService',
      ),
      'Neos\\Neos\\Aspects\\FusionCachingAspect' => 
      array (
        'properties' => 
        array (
          'fusionCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Neos_Neos_Fusion',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\ContentRepository\\Domain\\Service\\ContentDimensionPresetSourceInterface' => 
      array (
        'className' => 'Neos\\Neos\\Domain\\Service\\ConfigurationContentDimensionPresetSource',
      ),
      'Neos\\ContentRepository\\Domain\\Service\\NodeServiceInterface' => 
      array (
        'className' => 'Neos\\Neos\\TYPO3CR\\NeosNodeService',
      ),
      'Neos\\Neos\\TYPO3CR\\NeosNodeServiceInterface' => 
      array (
        'className' => 'Neos\\Neos\\TYPO3CR\\NeosNodeService',
      ),
      'Neos\\Neos\\Domain\\Service\\ContentDimensionPresetSourceInterface' => 
      array (
        'className' => 'Neos\\Neos\\Domain\\Service\\ConfigurationContentDimensionPresetSource',
      ),
      'Neos\\Neos\\Domain\\Service\\ConfigurationContentDimensionPresetSource' => 
      array (
        'properties' => 
        array (
          'configuration' => 
          array (
            'setting' => 'Neos.ContentRepository.contentDimensions',
          ),
        ),
      ),
      'Neos\\Neos\\Routing\\FrontendNodeRoutePartHandlerInterface' => 
      array (
        'className' => 'Neos\\Neos\\Routing\\FrontendNodeRoutePartHandler',
      ),
      'Neos\\Neos\\Domain\\Service\\NodeSearchServiceInterface' => 
      array (
        'className' => 'Neos\\Neos\\Domain\\Service\\NodeSearchService',
      ),
      'Neos\\Neos\\Service\\XliffService' => 
      array (
        'properties' => 
        array (
          'xliffToJsonTranslationsCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Neos_Neos_XliffToJsonTranslations',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Neos\\Controller\\Backend\\BackendController' => 
      array (
        'properties' => 
        array (
          'loginTokenCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Neos_Neos_LoginTokenCache',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Neos\\Controller\\LoginController' => 
      array (
        'properties' => 
        array (
          'loginTokenCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Neos_Neos_LoginTokenCache',
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    'Neos.RedirectHandler.NeosAdapter' => 
    array (
    ),
    'Neos.Neos.Ui' => 
    array (
      'Neos\\Neos\\Ui\\View\\BackendFusionView' => 
      array (
        'properties' => 
        array (
          'typoScriptParser' => 
          array (
            'object' => 'Neos\\Fusion\\Core\\Parser',
          ),
          'fallbackView' => 
          array (
            'object' => 'Neos\\FluidAdaptor\\View\\TemplateView',
          ),
        ),
      ),
    ),
    'Neos.Seo' => 
    array (
    ),
    'Neos.NodeTypes.BaseMixins' => 
    array (
    ),
    'Neos.NodeTypes.Html' => 
    array (
    ),
    'Neos.NodeTypes.ColumnLayouts' => 
    array (
    ),
    'Neos.NodeTypes.AssetList' => 
    array (
    ),
    'Neos.NodeTypes.Navigation' => 
    array (
    ),
    'Neos.NodeTypes.Form' => 
    array (
    ),
    'Neos.NodeTypes.ContentReferences' => 
    array (
    ),
    'Neos.NodeTypes' => 
    array (
    ),
    'Claudio.Ui.Render' => 
    array (
    ),
    'Flowpack.Neos.FrontendLogin' => 
    array (
    ),
    'Neos.Demo' => 
    array (
    ),
    'Flowpack.FusionBP' => 
    array (
    ),
    'Claudio.Site.Render' => 
    array (
    ),
  ),
  'Settings' => 
  array (
    'Neos' => 
    array (
      'Flow' => 
      array (
        'compatibility' => 
        array (
        ),
        'utility' => 
        array (
          'lockStrategyClassName' => 'Neos\\Utility\\Lock\\FlockLockStrategy',
        ),
        'aop' => 
        array (
          'globalObjects' => 
          array (
            'securityContext' => 'Neos\\Flow\\Security\\Context',
            'userInformation' => 'Neos\\Neos\\Service\\UserService',
          ),
        ),
        'cache' => 
        array (
          'applicationIdentifier' => (defined('FLOW_PATH_ROOT') ? constant('FLOW_PATH_ROOT') : null) . '~' . (defined('FLOW_APPLICATION_CONTEXT') ? constant('FLOW_APPLICATION_CONTEXT') : null),
        ),
        'core' => 
        array (
          'context' => 'Development',
          'applicationPackageKey' => 'Neos.Neos',
          'applicationName' => 'Neos',
          'phpBinaryPathAndFilename' => (defined('PHP_BINDIR') ? constant('PHP_BINDIR') : null) . '/php',
          'subRequestEnvironmentVariables' => 
          array (
            'XDEBUG_CONFIG' => 'idekey=FLOW_SUBREQUEST remote_port=9001',
          ),
          'subRequestPhpIniPathAndFilename' => NULL,
          'subRequestIniEntries' => 
          array (
          ),
        ),
        'error' => 
        array (
          'exceptionHandler' => 
          array (
            'className' => 'Neos\\Flow\\Error\\DebugExceptionHandler',
            'defaultRenderingOptions' => 
            array (
              'viewClassName' => 'Neos\\FluidAdaptor\\View\\StandaloneView',
              'viewOptions' => 
              array (
              ),
              'renderTechnicalDetails' => true,
              'logException' => true,
            ),
            'renderingGroups' => 
            array (
              'notFoundExceptions' => 
              array (
                'matchingStatusCodes' => 
                array (
                  0 => 403,
                  1 => 404,
                  2 => 410,
                ),
                'options' => 
                array (
                  'logException' => false,
                  'templatePathAndFilename' => 'resource://Neos.Flow/Private/Templates/Error/Default.html',
                  'variables' => 
                  array (
                    'errorDescription' => 'Sorry, the page you requested was not found.',
                  ),
                  'viewClassName' => '\\Neos\\Neos\\View\\FusionExceptionView',
                ),
              ),
              'databaseConnectionExceptions' => 
              array (
                'matchingExceptionClassNames' => 
                array (
                  0 => 'Neos\\Flow\\Persistence\\Doctrine\\Exception\\DatabaseException',
                  1 => 'Neos\\Flow\\Persistence\\Doctrine\\Exception\\DatabaseConnectionException',
                  2 => 'Neos\\Flow\\Persistence\\Doctrine\\Exception\\DatabaseStructureException',
                ),
                'options' => 
                array (
                  'templatePathAndFilename' => 'resource://Neos.Neos/Private/Templates/Error/Welcome.html',
                  'variables' => 
                  array (
                    'errorDescription' => 'Sorry, the database connection couldn\'t be established.',
                  ),
                  'layoutRootPath' => 'resource://Neos.Neos/Private/Layouts/',
                  'format' => 'html',
                ),
              ),
              'noHomepageException' => 
              array (
                'matchingExceptionClassNames' => 
                array (
                  0 => 'Neos\\Neos\\Routing\\Exception\\NoHomepageException',
                ),
                'options' => 
                array (
                  'templatePathAndFilename' => 'resource://Neos.Neos/Private/Templates/Error/Welcome.html',
                  'layoutRootPath' => 'resource://Neos.Neos/Private/Layouts/',
                  'format' => 'html',
                ),
              ),
            ),
          ),
          'errorHandler' => 
          array (
            'exceptionalErrors' => 
            array (
              0 => (defined('E_USER_ERROR') ? constant('E_USER_ERROR') : null),
              1 => (defined('E_RECOVERABLE_ERROR') ? constant('E_RECOVERABLE_ERROR') : null),
              2 => (defined('E_WARNING') ? constant('E_WARNING') : null),
              3 => (defined('E_NOTICE') ? constant('E_NOTICE') : null),
              4 => (defined('E_USER_WARNING') ? constant('E_USER_WARNING') : null),
              5 => (defined('E_USER_NOTICE') ? constant('E_USER_NOTICE') : null),
              6 => (defined('E_STRICT') ? constant('E_STRICT') : null),
            ),
          ),
          'debugger' => 
          array (
            'ignoredClasses' => 
            array (
              'Neos\\\\Flow\\\\Aop.*' => true,
              'Neos\\\\Flow\\\\Cac.*' => true,
              'Neos\\\\Flow\\\\Core\\\\.*' => true,
              'Neos\\\\Flow\\\\Con.*' => true,
              'Neos\\\\Flow\\\\Http\\\\RequestHandler' => true,
              'Neos\\\\Flow\\\\Uti.*' => true,
              'Neos\\\\Flow\\\\Mvc\\\\Routing.*' => true,
              'Neos\\\\Flow\\\\Log.*' => true,
              'Neos\\\\Flow\\\\Obj.*' => true,
              'Neos\\\\Flow\\\\Pac.*' => true,
              'Neos\\\\Flow\\\\Persistence\\\\(?!Doctrine\\\\Mapping).*' => true,
              'Neos\\\\Flow\\\\Pro.*' => true,
              'Neos\\\\Flow\\\\Ref.*' => true,
              'Neos\\\\Flow\\\\Sec.*' => true,
              'Neos\\\\Flow\\\\Sig.*' => true,
              'Neos\\\\Flow\\\\.*ResourceManager' => true,
              'Neos\\\\FluidAdaptor\\\\.*' => true,
              '.+Service$' => true,
              '.+Repository$' => true,
              'PHPUnit_Framework_MockObject_InvocationMocker' => true,
              'Neos\\\\ContentRepository\\\\Domain\\\\Service\\\\NodeTypeManager' => true,
              'Neos\\\\ContentRepository\\\\Domain\\\\Factory\\\\NodeFactory' => true,
              'Neos\\\\ContentRepository\\\\Domain\\\\Service\\\\Cache\\\\FirstLevelNodeCache' => true,
              'Neos\\\\Neos\\\\Domain\\\\Service\\\\ContentContextFactory' => true,
            ),
            'recursionLimit' => 5,
          ),
        ),
        'http' => 
        array (
          'applicationToken' => 'MinorVersion',
          'baseUri' => NULL,
          'chain' => 
          array (
            'preprocess' => 
            array (
              'position' => 'before process',
              'chain' => 
              array (
                'trustedProxies' => 
                array (
                  'position' => 'start',
                  'component' => 'Neos\\Flow\\Http\\Component\\TrustedProxiesComponent',
                ),
                'getSessionCookieFromRequest' => 
                array (
                  'position' => 'after trustedProxies',
                  'component' => 'Neos\\Flow\\Session\\Http\\SessionRequestComponent',
                ),
              ),
            ),
            'process' => 
            array (
              'chain' => 
              array (
                'routing' => 
                array (
                  'position' => 'start',
                  'component' => 'Neos\\Flow\\Mvc\\Routing\\RoutingComponent',
                ),
                'dispatching' => 
                array (
                  'component' => 'Neos\\Flow\\Mvc\\DispatchComponent',
                ),
                'ajaxWidget' => 
                array (
                  'position' => 'before routing',
                  'component' => 'Neos\\FluidAdaptor\\Core\\Widget\\AjaxWidgetComponent',
                ),
                'redirect' => 
                array (
                  'position' => 'after routing',
                  'component' => 'Neos\\RedirectHandler\\RedirectComponent',
                ),
              ),
            ),
            'postprocess' => 
            array (
              'chain' => 
              array (
                'setSessionCookie' => 
                array (
                  'position' => 'start',
                  'component' => 'Neos\\Flow\\Session\\Http\\SessionResponseComponent',
                ),
                'standardsCompliance' => 
                array (
                  'position' => 'end',
                  'component' => 'Neos\\Flow\\Http\\Component\\StandardsComplianceComponent',
                ),
              ),
            ),
          ),
          'trustedProxies' => 
          array (
            'proxies' => getenv('FLOW_HTTP_TRUSTED_PROXIES'),
            'headers' => 
            array (
              'clientIp' => 'Client-Ip,X-Forwarded-For,X-Forwarded,X-Cluster-Client-Ip,Forwarded-For,Forwarded',
              'host' => 'X-Forwarded-Host',
              'port' => 'X-Forwarded-Port',
              'proto' => 'X-Forwarded-Proto',
            ),
          ),
        ),
        'i18n' => 
        array (
          'defaultLocale' => 'en',
          'fallbackRule' => 
          array (
            'strict' => false,
            'order' => 
            array (
            ),
          ),
          'globalTranslationPath' => (defined('FLOW_PATH_DATA') ? constant('FLOW_PATH_DATA') : null) . 'Translations/',
          'availableLocales' => 
          array (
          ),
          'scan' => 
          array (
            'includePaths' => 
            array (
              '/Public/' => true,
              '/Private/Translations/' => true,
            ),
            'excludePatterns' => 
            array (
              '/node_modules/' => true,
              '/bower_components/' => true,
              '/\\..*/' => true,
            ),
          ),
        ),
        'log' => 
        array (
          'psr3' => 
          array (
            'loggerFactory' => 'legacy',
          ),
          'systemLogger' => 
          array (
            'logger' => 'Neos\\Flow\\Log\\Logger',
            'backend' => 'Neos\\Flow\\Log\\Backend\\FileBackend',
            'backendOptions' => 
            array (
              'logFileURL' => (defined('FLOW_PATH_DATA') ? constant('FLOW_PATH_DATA') : null) . 'Logs/System_Development.log',
              'createParentDirectories' => true,
              'severityThreshold' => (defined('LOG_DEBUG') ? constant('LOG_DEBUG') : null),
              'maximumLogFileSize' => 10485760,
              'logFilesToKeep' => 1,
              'logMessageOrigin' => false,
            ),
          ),
          'securityLogger' => 
          array (
            'backend' => 'Neos\\Flow\\Log\\Backend\\FileBackend',
            'backendOptions' => 
            array (
              'logFileURL' => (defined('FLOW_PATH_DATA') ? constant('FLOW_PATH_DATA') : null) . 'Logs/Security_Development.log',
              'createParentDirectories' => true,
              'severityThreshold' => (defined('LOG_DEBUG') ? constant('LOG_DEBUG') : null),
              'maximumLogFileSize' => 10485760,
              'logFilesToKeep' => 1,
              'logIpAddress' => true,
            ),
          ),
          'sqlLogger' => 
          array (
            'backend' => 'Neos\\Flow\\Log\\Backend\\FileBackend',
            'backendOptions' => 
            array (
              'logFileURL' => (defined('FLOW_PATH_DATA') ? constant('FLOW_PATH_DATA') : null) . 'Logs/Query_Development.log',
              'createParentDirectories' => true,
              'severityThreshold' => (defined('LOG_DEBUG') ? constant('LOG_DEBUG') : null),
              'maximumLogFileSize' => 10485760,
              'logFilesToKeep' => 1,
            ),
          ),
          'i18nLogger' => 
          array (
            'backend' => 'Neos\\Flow\\Log\\Backend\\FileBackend',
            'backendOptions' => 
            array (
              'logFileURL' => (defined('FLOW_PATH_DATA') ? constant('FLOW_PATH_DATA') : null) . 'Logs/I18n_Development.log',
              'createParentDirectories' => true,
              'severityThreshold' => (defined('LOG_DEBUG') ? constant('LOG_DEBUG') : null),
              'maximumLogFileSize' => 1048576,
              'logFilesToKeep' => 1,
            ),
          ),
          'throwables' => 
          array (
            'storageClass' => 'Neos\\Flow\\Log\\ThrowableStorage\\FileStorage',
            'optionsByImplementation' => 
            array (
              'Neos\\Flow\\Log\\ThrowableStorage\\FileStorage' => 
              array (
                'storagePath' => (defined('FLOW_PATH_DATA') ? constant('FLOW_PATH_DATA') : null) . 'Logs/Exceptions',
              ),
            ),
          ),
        ),
        'mvc' => 
        array (
          'routes' => 
          array (
            'Neos.Media' => 
            array (
              'position' => 'before Neos.Neos',
            ),
            'Neos.Media.Browser' => true,
            'Neos.Neos' => 
            array (
              'variables' => 
              array (
                'defaultUriSuffix' => '.html',
              ),
            ),
            'Neos.Neos.Ui' => 
            array (
              'position' => 'before Neos.Neos',
            ),
            'Neos.Seo' => 
            array (
              'variables' => 
              array (
                'xmlSitemapPath' => 'sitemap.xml',
              ),
            ),
          ),
          'view' => 
          array (
            'defaultImplementation' => 'Neos\\FluidAdaptor\\View\\TemplateView',
          ),
        ),
        'object' => 
        array (
          'registerFunctionalTestClasses' => false,
          'includeClasses' => 
          array (
          ),
        ),
        'package' => 
        array (
          'inactiveByDefault' => 
          array (
            0 => 'neos.composerplugin',
            1 => 'Composer.Installers',
          ),
          'packagesPathByType' => 
          array (
            'typo3-flow-package' => 'Application',
            'neos-package' => 'Application',
            'typo3-flow-framework' => 'Framework',
            'neos-framework' => 'Framework',
            'neos-site' => 'Sites',
            'neos-plugin' => 'Plugins',
          ),
        ),
        'persistence' => 
        array (
          'backendOptions' => 
          array (
            'driver' => 'pdo_mysql',
            'host' => 'neos-db',
            'dbname' => 'flow',
            'user' => 'root',
            'password' => 'my-secret-pw',
            'charset' => 'utf8mb4',
            'defaultTableOptions' => 
            array (
              'charset' => 'utf8mb4',
            ),
          ),
          'cacheAllQueryResults' => false,
          'doctrine' => 
          array (
            'enable' => true,
            'sqlLogger' => NULL,
            'filters' => 
            array (
              'Flow_Security_Entity_Filter' => 'Neos\\Flow\\Security\\Authorization\\Privilege\\Entity\\Doctrine\\SqlFilter',
            ),
            'dbal' => 
            array (
              'mappingTypes' => 
              array (
                'flow_json_array' => 
                array (
                  'dbType' => 'json_array',
                  'className' => 'Neos\\Flow\\Persistence\\Doctrine\\DataTypes\\JsonArrayType',
                ),
                'objectarray' => 
                array (
                  'dbType' => 'array',
                  'className' => 'Neos\\Flow\\Persistence\\Doctrine\\DataTypes\\ObjectArray',
                ),
              ),
            ),
            'eventSubscribers' => 
            array (
            ),
            'eventListeners' => 
            array (
              'Neos\\Flow\\Persistence\\Doctrine\\ObjectValidationAndDeDuplicationListener' => 
              array (
                'events' => 
                array (
                  0 => 'onFlush',
                ),
                'listener' => 'Neos\\Flow\\Persistence\\Doctrine\\ObjectValidationAndDeDuplicationListener',
              ),
              'Gedmo\\Timestampable\\TimestampableListener' => 
              array (
                'events' => 
                array (
                  0 => 'prePersist',
                  1 => 'onFlush',
                  2 => 'loadClassMetadata',
                ),
                'listener' => 'Gedmo\\Timestampable\\TimestampableListener',
              ),
              'Neos\\Media\\Domain\\EventListener\\ImageEventListener' => 
              array (
                'events' => 
                array (
                  0 => 'postRemove',
                ),
                'listener' => 'Neos\\Media\\Domain\\EventListener\\ImageEventListener',
              ),
              'Neos\\Neos\\Domain\\EventListener\\AccountPostEventListener' => 
              array (
                'events' => 
                array (
                  0 => 'postPersist',
                  1 => 'postUpdate',
                  2 => 'postRemove',
                ),
                'listener' => 'Neos\\Neos\\Domain\\EventListener\\AccountPostEventListener',
              ),
              'Neos\\Neos\\EventLog\\Integrations\\EntityIntegrationService' => 
              array (
                'events' => 
                array (
                  0 => 'onFlush',
                ),
                'listener' => 'Neos\\Neos\\EventLog\\Integrations\\EntityIntegrationService',
              ),
              'Neos\\Neos\\EventLog\\Integrations\\ContentRepositoryIntegrationService' => 
              array (
                'events' => 
                array (
                  0 => 'preFlush',
                ),
                'listener' => 'Neos\\Neos\\EventLog\\Integrations\\ContentRepositoryIntegrationService',
              ),
            ),
            'secondLevelCache' => 
            array (
            ),
            'migrations' => 
            array (
              'ignoredTables' => 
              array (
              ),
            ),
            'dql' => 
            array (
              'customStringFunctions' => 
              array (
                'NEOSCR_TOSTRING' => 'Neos\\ContentRepository\\Persistence\\Ast\\ToStringFunction',
              ),
            ),
          ),
        ),
        'reflection' => 
        array (
          'ignoredTags' => 
          array (
            'api' => true,
            'package' => true,
            'subpackage' => true,
            'license' => true,
            'copyright' => true,
            'author' => true,
            'const' => true,
            'see' => true,
            'todo' => true,
            'scope' => true,
            'fixme' => true,
            'test' => true,
            'expectedException' => true,
            'expectedExceptionMessage' => true,
            'expectedExceptionCode' => true,
            'depends' => true,
            'dataProvider' => true,
            'group' => true,
            'codeCoverageIgnore' => true,
            'requires' => true,
            'Given' => true,
            'When' => true,
            'Then' => true,
            'BeforeScenario' => true,
            'AfterScenario' => true,
            'fixtures' => true,
            'Isolated' => true,
            'AfterFeature' => true,
            'BeforeFeature' => true,
            'BeforeStep' => true,
            'AfterStep' => true,
            'WithoutSecurityChecks' => true,
            'covers' => true,
            'doesNotPerformAssertions' => true,
            'template' => true,
            'psalm' => true,
          ),
          'logIncorrectDocCommentHints' => false,
        ),
        'resource' => 
        array (
          'uploadExtensionBlacklist' => 
          array (
            'aspx' => true,
            'cgi' => true,
            'php3' => true,
            'php4' => true,
            'php5' => true,
            'phtml' => true,
            'php' => true,
            'pl' => true,
            'py' => true,
            'pyc' => true,
            'pyo' => true,
            'rb' => true,
          ),
          'storages' => 
          array (
            'defaultPersistentResourcesStorage' => 
            array (
              'storage' => 'Neos\\Flow\\ResourceManagement\\Storage\\WritableFileSystemStorage',
              'storageOptions' => 
              array (
                'path' => (defined('FLOW_PATH_DATA') ? constant('FLOW_PATH_DATA') : null) . 'Persistent/Resources/',
              ),
            ),
            'defaultStaticResourcesStorage' => 
            array (
              'storage' => 'Neos\\Flow\\ResourceManagement\\Storage\\PackageStorage',
            ),
          ),
          'collections' => 
          array (
            'static' => 
            array (
              'storage' => 'defaultStaticResourcesStorage',
              'target' => 'localWebDirectoryStaticResourcesTarget',
              'pathPatterns' => 
              array (
                0 => '*/Resources/Public/',
                1 => '*/Resources/Public/*',
              ),
            ),
            'persistent' => 
            array (
              'storage' => 'defaultPersistentResourcesStorage',
              'target' => 'localWebDirectoryPersistentResourcesTarget',
            ),
          ),
          'targets' => 
          array (
            'localWebDirectoryStaticResourcesTarget' => 
            array (
              'target' => 'Neos\\Flow\\ResourceManagement\\Target\\FileSystemSymlinkTarget',
              'targetOptions' => 
              array (
                'path' => (defined('FLOW_PATH_WEB') ? constant('FLOW_PATH_WEB') : null) . '_Resources/Static/Packages/',
                'baseUri' => '_Resources/Static/Packages/',
                'extensionBlacklist' => 
                array (
                  'aspx' => true,
                  'cgi' => true,
                  'php3' => true,
                  'php4' => true,
                  'php5' => true,
                  'phtml' => true,
                  'php' => true,
                  'pl' => true,
                  'py' => true,
                  'pyc' => true,
                  'pyo' => true,
                  'rb' => true,
                ),
              ),
            ),
            'localWebDirectoryPersistentResourcesTarget' => 
            array (
              'target' => 'Neos\\Flow\\ResourceManagement\\Target\\FileSystemSymlinkTarget',
              'targetOptions' => 
              array (
                'path' => (defined('FLOW_PATH_WEB') ? constant('FLOW_PATH_WEB') : null) . '_Resources/Persistent/',
                'baseUri' => '_Resources/Persistent/',
                'extensionBlacklist' => 
                array (
                  'aspx' => true,
                  'cgi' => true,
                  'php3' => true,
                  'php4' => true,
                  'php5' => true,
                  'phtml' => true,
                  'php' => true,
                  'pl' => true,
                  'py' => true,
                  'pyc' => true,
                  'pyo' => true,
                  'rb' => true,
                ),
                'subdivideHashPathSegment' => false,
              ),
            ),
          ),
        ),
        'security' => 
        array (
          'firewall' => 
          array (
            'rejectAll' => false,
            'filters' => 
            array (
              'Neos.Flow:CsrfProtection' => 
              array (
                'pattern' => 'CsrfProtection',
                'interceptor' => 'CsrfTokenMissing',
              ),
            ),
          ),
          'authentication' => 
          array (
            'providers' => 
            array (
              'Neos.Setup:Login' => 
              array (
                'provider' => 'FileBasedSimpleKeyProvider',
                'providerOptions' => 
                array (
                  'keyName' => 'SetupKey',
                  'authenticateRoles' => 
                  array (
                    0 => 'Neos.Setup:SetupUser',
                  ),
                ),
                'requestPatterns' => 
                array (
                  'Neos.Setup:controllerObjectName' => 
                  array (
                    'pattern' => 'ControllerObjectName',
                    'patternOptions' => 
                    array (
                      'controllerObjectNamePattern' => 'Neos\\Setup\\Controller\\.*|Neos\\Setup\\ViewHelpers\\Widget\\Controller\\.*',
                    ),
                  ),
                ),
                'entryPoint' => 'WebRedirect',
                'entryPointOptions' => 
                array (
                  'uri' => 'setup/login',
                ),
              ),
              'Neos.Neos:Backend' => 
              array (
                'requestPatterns' => 
                array (
                  'Neos.Media.Browser:Controllers' => 
                  array (
                    'pattern' => 'ControllerObjectName',
                    'patternOptions' => 
                    array (
                      'controllerObjectNamePattern' => 'Neos\\Media\\Browser\\Controller\\.*',
                    ),
                  ),
                  'Neos.Neos:BackendControllers' => 
                  array (
                    'pattern' => 'ControllerObjectName',
                    'patternOptions' => 
                    array (
                      'controllerObjectNamePattern' => 'Neos\\Neos\\Controller\\.*',
                    ),
                  ),
                  'Neos.Neos:ServiceControllers' => 
                  array (
                    'pattern' => 'ControllerObjectName',
                    'patternOptions' => 
                    array (
                      'controllerObjectNamePattern' => 'Neos\\Neos\\Service\\.*',
                    ),
                  ),
                  'Neos.Neos.Ui:BackendControllers' => 
                  array (
                    'pattern' => 'ControllerObjectName',
                    'patternOptions' => 
                    array (
                      'controllerObjectNamePattern' => 'Neos\\Neos\\Ui\\Controller\\.*',
                    ),
                  ),
                  'Flowpack.Neos.FrontendLogin:NeosBackend' => 
                  array (
                    'pattern' => 'Flowpack\\Neos\\FrontendLogin\\Security\\NeosRequestPattern',
                  ),
                ),
                'label' => 'Neos Backend',
                'provider' => 'PersistedUsernamePasswordProvider',
                'entryPoint' => 'WebRedirect',
                'entryPointOptions' => 
                array (
                  'routeValues' => 
                  array (
                    '@package' => 'Neos.Neos',
                    '@controller' => 'Login',
                    '@action' => 'index',
                    '@format' => 'html',
                  ),
                ),
              ),
              'Flowpack.Neos.FrontendLogin:Frontend' => 
              array (
                'provider' => 'PersistedUsernamePasswordProvider',
                'requestPatterns' => 
                array (
                  'Flowpack.Neos.FrontendLogin:NeosFrontend' => 
                  array (
                    'pattern' => 'Flowpack\\Neos\\FrontendLogin\\Security\\NeosRequestPattern',
                    'patternOptions' => 
                    array (
                      'matchFrontend' => true,
                    ),
                  ),
                ),
              ),
            ),
            'authenticationStrategy' => 'oneToken',
          ),
          'authorization' => 
          array (
            'allowAccessIfAllVotersAbstain' => false,
          ),
          'csrf' => 
          array (
            'csrfStrategy' => 'onePerSession',
          ),
          'cryptography' => 
          array (
            'hashingStrategies' => 
            array (
              'default' => 'bcrypt',
              'pbkdf2' => 'Neos\\Flow\\Security\\Cryptography\\Pbkdf2HashingStrategy',
              'bcrypt' => 'Neos\\Flow\\Security\\Cryptography\\BCryptHashingStrategy',
              'saltedmd5' => 'Neos\\Flow\\Security\\Cryptography\\SaltedMd5HashingStrategy',
            ),
            'Pbkdf2HashingStrategy' => 
            array (
              'dynamicSaltLength' => 8,
              'iterationCount' => 10000,
              'derivedKeyLength' => 64,
              'algorithm' => 'sha256',
            ),
            'BCryptHashingStrategy' => 
            array (
              'cost' => 14,
            ),
            'RSAWalletServicePHP' => 
            array (
              'keystorePath' => (defined('FLOW_PATH_DATA') ? constant('FLOW_PATH_DATA') : null) . 'Persistent/RsaWalletData',
              'paddingAlgorithm' => (defined('OPENSSL_PKCS1_OAEP_PADDING') ? constant('OPENSSL_PKCS1_OAEP_PADDING') : null),
              'openSSLConfiguration' => 
              array (
              ),
            ),
          ),
        ),
        'session' => 
        array (
          'inactivityTimeout' => 3600,
          'name' => 'Neos_Session',
          'garbageCollection' => 
          array (
            'probability' => 1,
            'maximumPerRun' => 1000,
          ),
          'cookie' => 
          array (
            'lifetime' => 0,
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'domain' => NULL,
          ),
        ),
      ),
      'DocTools' => 
      array (
        'collections' => 
        array (
          'Flow' => 
          array (
            'commandReferences' => 
            array (
              0 => 'Flow:FlowCommands',
            ),
            'references' => 
            array (
              0 => 'TYPO3Fluid:ViewHelpers',
              1 => 'Flow:FluidAdaptorViewHelpers',
              2 => 'Flow:FlowValidators',
              3 => 'Flow:FlowSignals',
              4 => 'Flow:FlowTypeConverters',
              5 => 'Flow:FlowAnnotations',
            ),
          ),
          'Media' => 
          array (
            'commandReferences' => 
            array (
              0 => 'Media:Commands',
            ),
            'references' => 
            array (
              0 => 'Media:ViewHelpers',
              1 => 'Media:Validators',
            ),
          ),
          'Neos' => 
          array (
            'commandReferences' => 
            array (
              0 => 'Neos:NeosCommands',
            ),
            'references' => 
            array (
              0 => 'TYPO3Fluid:ViewHelpers',
              1 => 'Neos:FluidAdaptorViewHelpers',
              2 => 'Neos:MediaViewHelpers',
              3 => 'Neos:FormViewHelpers',
              4 => 'Neos:NeosViewHelpers',
              5 => 'Neos:ContentRepositoryViewHelpers',
              6 => 'Neos:FusionViewHelpers',
              7 => 'Neos:FlowValidators',
              8 => 'Neos:PartyValidators',
              9 => 'Neos:MediaValidators',
              10 => 'Neos:FlowSignals',
              11 => 'Neos:NeosSignals',
              12 => 'Neos:MediaSignals',
              13 => 'Neos:ContentRepositorySignals',
              14 => 'Neos:FlowQueryOperations',
              15 => 'Neos:EelHelpers',
            ),
          ),
        ),
        'commandReferences' => 
        array (
          'Flow:FlowCommands' => 
          array (
            'title' => 'Flow Command Reference',
            'packageKeys' => 
            array (
              0 => 'Neos.Flow',
              1 => 'Neos.Party',
              2 => 'Neos.FluidAdaptor',
              3 => 'Neos.Kickstart',
              4 => 'Neos.Welcome',
            ),
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Framework/Neos.Flow/Documentation/TheDefinitiveGuide/PartV/CommandReference.rst',
          ),
          'Media:Commands' => 
          array (
            'title' => 'Media Command Reference',
            'packageKeys' => 
            array (
              0 => 'Neos.Media',
            ),
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Neos/Neos.Media/Documentation/References/Commands.rst',
          ),
          'Neos:NeosCommands' => 
          array (
            'title' => 'Neos Command Reference',
            'packageKeys' => 
            array (
              0 => 'Neos.Flow',
              1 => 'Neos.Party',
              2 => 'Neos.FluidAdaptor',
              3 => 'Neos.Kickstarter',
              4 => 'Neos.Welcome',
              5 => 'Neos.Media',
              6 => 'Neos.ContentRepository',
              7 => 'Neos.SiteKickstarter',
              8 => 'Neos.Neos',
            ),
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Neos/Neos.Neos/Documentation/References/CommandReference.rst',
          ),
        ),
        'references' => 
        array (
          'TYPO3Fluid:ViewHelpers' => 
          array (
            'title' => 'TYPO3 Fluid ViewHelper Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Neos/Neos.Neos/Documentation/References/ViewHelpers/TYPO3Fluid.rst',
            'affectedClasses' => 
            array (
              'parentClassName' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\AbstractViewHelper',
              'classNamePattern' => '/^TYPO3Fluid\\\\Fluid\\\\ViewHelpers\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FluidViewHelperClassParser',
              'options' => 
              array (
                'namespaces' => 
                array (
                  'f' => 'TYPO3Fluid\\Fluid\\ViewHelpers',
                ),
              ),
            ),
          ),
          'Flow:FluidAdaptorViewHelpers' => 
          array (
            'title' => 'FluidAdaptor ViewHelper Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Framework/Neos.Flow/Documentation/TheDefinitiveGuide/PartV/FluidAdaptorViewHelperReference.rst',
            'affectedClasses' => 
            array (
              'parentClassName' => 'Neos\\FluidAdaptor\\Core\\ViewHelper\\AbstractViewHelper',
              'classNamePattern' => '/^Neos\\\\FluidAdaptor\\\\ViewHelpers\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FluidViewHelperClassParser',
              'options' => 
              array (
                'namespaces' => 
                array (
                  'f' => 'Neos\\FluidAdaptor\\ViewHelpers',
                ),
              ),
            ),
          ),
          'Flow:FlowValidators' => 
          array (
            'title' => 'Flow Validator Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Framework/Neos.Flow/Documentation/TheDefinitiveGuide/PartV/ValidatorReference.rst',
            'affectedClasses' => 
            array (
              'parentClassName' => 'Neos\\Flow\\Validation\\Validator\\AbstractValidator',
              'classNamePattern' => '/^Neos\\\\Flow\\\\Validation\\\\Validator\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FlowValidatorClassParser',
            ),
          ),
          'Flow:FlowSignals' => 
          array (
            'title' => 'Flow Signals Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Framework/Neos.Flow/Documentation/TheDefinitiveGuide/PartV/SignalsReference.rst',
            'affectedClasses' => 
            array (
              'classesContainingMethodsAnnotatedWith' => 'Neos\\Flow\\Annotations\\Signal',
              'classNamePattern' => '/^Neos\\\\Flow\\\\.*$/i',
              'includeAbstractClasses' => true,
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\SignalsParser',
            ),
          ),
          'Flow:FlowTypeConverters' => 
          array (
            'title' => 'Flow TypeConverter Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Framework/Neos.Flow/Documentation/TheDefinitiveGuide/PartV/TypeConverterReference.rst',
            'affectedClasses' => 
            array (
              'parentClassName' => 'Neos\\Flow\\Property\\TypeConverter\\AbstractTypeConverter',
              'classNamePattern' => '/^Neos\\\\Flow\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FlowTypeConverterClassParser',
            ),
          ),
          'Flow:FlowAnnotations' => 
          array (
            'title' => 'Flow Annotation Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Framework/Neos.Flow/Documentation/TheDefinitiveGuide/PartV/AnnotationReference.rst',
            'affectedClasses' => 
            array (
              'classNamePattern' => '/^Neos\\\\Flow\\\\Annotations\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FlowAnnotationClassParser',
            ),
          ),
          'Media:Validators' => 
          array (
            'title' => 'Media Validator Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Neos/Neos.Media/Documentation/References/Validators.rst',
            'affectedClasses' => 
            array (
              'parentClassName' => 'Neos\\Flow\\Validation\\Validator\\AbstractValidator',
              'classNamePattern' => '/^Neos\\\\Media\\\\Validator\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FlowValidatorClassParser',
            ),
          ),
          'Media:ViewHelpers' => 
          array (
            'title' => 'Media ViewHelper Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Neos/Neos.Media/Documentation/References/ViewHelpers.rst',
            'affectedClasses' => 
            array (
              'parentClassName' => 'Neos\\FluidAdaptor\\Core\\ViewHelper\\AbstractViewHelper',
              'classNamePattern' => '/^Neos\\\\Media\\\\ViewHelpers\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FluidViewHelperClassParser',
              'options' => 
              array (
                'namespaces' => 
                array (
                  'neos.media' => 'Neos\\Media\\ViewHelpers',
                ),
              ),
            ),
          ),
          'Neos:FluidAdaptorViewHelpers' => 
          array (
            'title' => 'FluidAdaptor ViewHelper Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Neos/Neos.Neos/Documentation/References/ViewHelpers/FluidAdaptor.rst',
            'affectedClasses' => 
            array (
              'parentClassName' => 'Neos\\FluidAdaptor\\Core\\ViewHelper\\AbstractViewHelper',
              'classNamePattern' => '/^Neos\\\\FluidAdaptor\\\\ViewHelpers\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FluidViewHelperClassParser',
              'options' => 
              array (
                'namespaces' => 
                array (
                  'f' => 'Neos\\FluidAdaptor\\ViewHelpers',
                ),
              ),
            ),
          ),
          'Neos:MediaViewHelpers' => 
          array (
            'title' => 'Media ViewHelper Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Neos/Neos.Neos/Documentation/References/ViewHelpers/Media.rst',
            'affectedClasses' => 
            array (
              'parentClassName' => 'Neos\\FluidAdaptor\\Core\\ViewHelper\\AbstractViewHelper',
              'classNamePattern' => '/^Neos\\\\Media\\\\ViewHelpers\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FluidViewHelperClassParser',
              'options' => 
              array (
                'namespaces' => 
                array (
                  'neos.media' => 'Neos\\Media\\ViewHelpers',
                ),
              ),
            ),
          ),
          'Neos:FormViewHelpers' => 
          array (
            'title' => 'Form ViewHelper Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Neos/Neos.Neos/Documentation/References/ViewHelpers/Form.rst',
            'affectedClasses' => 
            array (
              'parentClassName' => 'Neos\\FluidAdaptor\\Core\\ViewHelper\\AbstractViewHelper',
              'classNamePattern' => '/^Neos\\\\Form\\\\ViewHelpers\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FluidViewHelperClassParser',
              'options' => 
              array (
                'namespaces' => 
                array (
                  'neos.form' => 'Neos\\Form\\ViewHelpers',
                ),
              ),
            ),
          ),
          'Neos:NeosViewHelpers' => 
          array (
            'title' => 'Neos ViewHelper Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Neos/Neos.Neos/Documentation/References/ViewHelpers/Neos.rst',
            'affectedClasses' => 
            array (
              'parentClassName' => 'Neos\\FluidAdaptor\\Core\\ViewHelper\\AbstractViewHelper',
              'classNamePattern' => '/^Neos\\\\Neos\\\\ViewHelpers\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FluidViewHelperClassParser',
              'options' => 
              array (
                'namespaces' => 
                array (
                  'f' => 'Neos\\FluidAdaptor\\ViewHelpers',
                  'neos' => 'Neos\\Neos\\ViewHelpers',
                ),
              ),
            ),
          ),
          'Neos:ContentRepositoryViewHelpers' => 
          array (
            'title' => 'Content Repository ViewHelper Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Neos/Neos.Neos/Documentation/References/ViewHelpers/ContentRepository.rst',
            'affectedClasses' => 
            array (
              'parentClassName' => 'Neos\\FluidAdaptor\\Core\\ViewHelper\\AbstractViewHelper',
              'classNamePattern' => '/^Neos\\\\ContentRepository\\\\ViewHelpers\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FluidViewHelperClassParser',
              'options' => 
              array (
                'namespaces' => 
                array (
                  'f' => 'Neos\\FluidAdaptor\\ViewHelpers',
                  'neos' => 'Neos\\Neos\\ViewHelpers',
                ),
              ),
            ),
          ),
          'Neos:FusionViewHelpers' => 
          array (
            'title' => 'Fusion ViewHelper Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Neos/Neos.Neos/Documentation/References/ViewHelpers/Fusion.rst',
            'affectedClasses' => 
            array (
              'parentClassName' => 'Neos\\FluidAdaptor\\Core\\ViewHelper\\AbstractViewHelper',
              'classNamePattern' => '/^Neos\\\\Fusion\\\\ViewHelpers\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FluidViewHelperClassParser',
              'options' => 
              array (
                'namespaces' => 
                array (
                  'f' => 'Neos\\FluidAdaptor\\ViewHelpers',
                  'fusion' => 'Neos\\Fusion\\ViewHelpers',
                ),
              ),
            ),
          ),
          'Neos:FlowValidators' => 
          array (
            'title' => 'Flow Validator Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Neos/Neos.Neos/Documentation/References/Validators/Flow.rst',
            'affectedClasses' => 
            array (
              'parentClassName' => 'Neos\\Flow\\Validation\\Validator\\AbstractValidator',
              'classNamePattern' => '/^Neos\\\\Flow\\\\Validation\\\\Validator\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FlowValidatorClassParser',
            ),
          ),
          'Neos:PartyValidators' => 
          array (
            'title' => 'Party Validator Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Neos/Neos.Neos/Documentation/References/Validators/Party.rst',
            'affectedClasses' => 
            array (
              'parentClassName' => 'Neos\\Flow\\Validation\\Validator\\AbstractValidator',
              'classNamePattern' => '/^Neos\\\\Party\\\\Validation\\\\Validator\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FlowValidatorClassParser',
            ),
          ),
          'Neos:MediaValidators' => 
          array (
            'title' => 'Media Validator Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Neos/Neos.Neos/Documentation/References/Validators/Media.rst',
            'affectedClasses' => 
            array (
              'parentClassName' => 'Neos\\Flow\\Validation\\Validator\\AbstractValidator',
              'classNamePattern' => '/^Neos\\\\Media\\\\Validator\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FlowValidatorClassParser',
            ),
          ),
          'Neos:FlowSignals' => 
          array (
            'title' => 'Flow Signals Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Neos/Neos.Neos/Documentation/References/Signals/Flow.rst',
            'affectedClasses' => 
            array (
              'classesContainingMethodsAnnotatedWith' => 'Neos\\Flow\\Annotations\\Signal',
              'classNamePattern' => '/^Neos\\\\Flow\\\\.*$/i',
              'includeAbstractClasses' => true,
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\SignalsParser',
            ),
          ),
          'Neos:NeosSignals' => 
          array (
            'title' => 'Neos Signals Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Neos/Neos.Neos/Documentation/References/Signals/Neos.rst',
            'affectedClasses' => 
            array (
              'classesContainingMethodsAnnotatedWith' => 'Neos\\Flow\\Annotations\\Signal',
              'classNamePattern' => '/^Neos\\\\Neos\\\\.*$/i',
              'includeAbstractClasses' => true,
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\SignalsParser',
            ),
          ),
          'Neos:MediaSignals' => 
          array (
            'title' => 'Media Signals Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Neos/Neos.Neos/Documentation/References/Signals/Media.rst',
            'affectedClasses' => 
            array (
              'classesContainingMethodsAnnotatedWith' => 'Neos\\Flow\\Annotations\\Signal',
              'classNamePattern' => '/^Neos\\\\Media\\\\.*$/i',
              'includeAbstractClasses' => true,
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\SignalsParser',
            ),
          ),
          'Neos:ContentRepositorySignals' => 
          array (
            'title' => 'Content Repository Signals Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Neos/Neos.Neos/Documentation/References/Signals/ContentRepository.rst',
            'affectedClasses' => 
            array (
              'classesContainingMethodsAnnotatedWith' => 'Neos\\Flow\\Annotations\\Signal',
              'classNamePattern' => '/^Neos\\\\ContentRepository\\\\.*$/i',
              'includeAbstractClasses' => true,
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\SignalsParser',
            ),
          ),
          'Neos:FlowQueryOperations' => 
          array (
            'title' => 'FlowQuery Operation Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Neos/Neos.Neos/Documentation/References/FlowQueryOperationReference.rst',
            'affectedClasses' => 
            array (
              'interface' => 'Neos\\Eel\\FlowQuery\\OperationInterface',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FlowQueryOperationClassParser',
            ),
          ),
          'Neos:EelHelpers' => 
          array (
            'title' => 'Eel Helpers Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Neos/Neos.Neos/Documentation/References/EelHelpersReference.rst',
            'affectedClasses' => 
            array (
              'interface' => 'Neos\\Eel\\ProtectedContextAwareInterface',
              'classNamePattern' => '/^.*Helper$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\EelHelperClassParser',
            ),
          ),
        ),
      ),
      'Diff' => 
      array (
      ),
      'Eel' => 
      array (
      ),
      'FluidAdaptor' => 
      array (
      ),
      'Form' => 
      array (
        'yamlPersistenceManager' => 
        array (
          'savePath' => 'resource://Neos.Demo/Private/Form/',
        ),
        'supertypeResolver' => 
        array (
          'hiddenProperties' => 
          array (
          ),
        ),
        'presets' => 
        array (
          'default' => 
          array (
            'title' => 'Default',
            'stylesheets' => 
            array (
            ),
            'javaScripts' => 
            array (
            ),
            'formElementTypes' => 
            array (
              'Neos.Form:Base' => 
              array (
                'renderingOptions' => 
                array (
                  'templatePathPattern' => 'resource://{@package}/Private/Form/{@type}.html',
                  'partialPathPattern' => 'resource://{@package}/Private/Form/Partials/{@type}.html',
                  'layoutPathPattern' => 'resource://{@package}/Private/Form/Layouts/{@type}.html',
                  'skipUnknownElements' => false,
                  'translationPackage' => 'Neos.Form',
                  'validationErrorTranslationPackage' => 'Neos.Flow',
                ),
              ),
              'Neos.Form:Form' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:Base' => true,
                ),
                'rendererClassName' => 'Neos\\Form\\Core\\Renderer\\FluidFormRenderer',
                'renderingOptions' => 
                array (
                  'renderableNameInTemplate' => 'form',
                  'previousButtonClassAttribute' => 'btn btn-cancel',
                  'nextButtonClassAttribute' => 'btn btn-primary',
                  'submitButtonClassAttribute' => 'btn btn-primary',
                ),
              ),
              'Neos.Form:FormEditMode' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:Form' => true,
                ),
              ),
              'Neos.Form:RemovableMixin' => 
              array (
              ),
              'Neos.Form:ReadOnlyFormElement' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:Base' => true,
                  'Neos.Form:RemovableMixin' => true,
                ),
                'implementationClassName' => 'Neos\\Form\\FormElements\\GenericFormElement',
                'renderingOptions' => 
                array (
                  'renderableNameInTemplate' => 'element',
                ),
              ),
              'Neos.Form:FormElement' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:Base' => true,
                  'Neos.Form:RemovableMixin' => true,
                ),
                'implementationClassName' => 'Neos\\Form\\FormElements\\GenericFormElement',
                'properties' => 
                array (
                  'containerClassAttribute' => 'input',
                  'elementClassAttribute' => '',
                  'elementErrorClassAttribute' => 'error',
                ),
                'renderingOptions' => 
                array (
                  'renderableNameInTemplate' => 'element',
                ),
              ),
              'Neos.Form:Page' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:Base' => true,
                  'Neos.Form:RemovableMixin' => true,
                ),
                'implementationClassName' => 'Neos\\Form\\Core\\Model\\Page',
                'renderingOptions' => 
                array (
                  'renderableNameInTemplate' => 'page',
                ),
              ),
              'Neos.Form:PreviewPage' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:Page' => true,
                ),
              ),
              'Neos.Form:Section' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:FormElement' => true,
                ),
                'implementationClassName' => 'Neos\\Form\\FormElements\\Section',
                'renderingOptions' => 
                array (
                  'renderableNameInTemplate' => 'section',
                ),
              ),
              'Neos.Form:TextMixin' => 
              array (
              ),
              'Neos.Form:SingleLineText' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:FormElement' => true,
                  'Neos.Form:TextMixin' => true,
                ),
              ),
              'Neos.Form:Password' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:FormElement' => true,
                  'Neos.Form:TextMixin' => true,
                ),
              ),
              'Neos.Form:PasswordWithConfirmation' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:Password' => true,
                ),
                'implementationClassName' => 'Neos\\Form\\FormElements\\PasswordWithConfirmation',
                'properties' => 
                array (
                  'elementClassAttribute' => 'input-medium',
                  'confirmationLabel' => 'Confirmation',
                  'confirmationClassAttribute' => 'input-medium',
                ),
              ),
              'Neos.Form:MultiLineText' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:FormElement' => true,
                  'Neos.Form:TextMixin' => true,
                ),
                'properties' => 
                array (
                  'elementClassAttribute' => 'xxlarge',
                ),
              ),
              'Neos.Form:SelectionMixin' => 
              array (
              ),
              'Neos.Form:SingleSelectionMixin' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:SelectionMixin' => true,
                ),
              ),
              'Neos.Form:MultiSelectionMixin' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:SelectionMixin' => true,
                ),
              ),
              'Neos.Form:Checkbox' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:FormElement' => true,
                ),
                'properties' => 
                array (
                  'elementClassAttribute' => 'add-on',
                  'value' => 1,
                ),
              ),
              'Neos.Form:MultipleSelectCheckboxes' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:FormElement' => true,
                  'Neos.Form:MultiSelectionMixin' => true,
                ),
              ),
              'Neos.Form:MultipleSelectDropdown' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:FormElement' => true,
                  'Neos.Form:MultiSelectionMixin' => true,
                ),
                'properties' => 
                array (
                  'elementClassAttribute' => 'xlarge',
                ),
              ),
              'Neos.Form:SingleSelectRadiobuttons' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:FormElement' => true,
                  'Neos.Form:SingleSelectionMixin' => true,
                ),
                'properties' => 
                array (
                  'elementClassAttribute' => 'xlarge',
                ),
              ),
              'Neos.Form:SingleSelectDropdown' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:FormElement' => true,
                  'Neos.Form:SingleSelectionMixin' => true,
                ),
              ),
              'Neos.Form:DatePicker' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:FormElement' => true,
                ),
                'implementationClassName' => 'Neos\\Form\\FormElements\\DatePicker',
                'properties' => 
                array (
                  'elementClassAttribute' => 'small',
                  'timeSelectorClassAttribute' => 'mini',
                  'dateFormat' => 'Y-m-d',
                  'enableDatePicker' => true,
                  'displayTimeSelector' => false,
                ),
              ),
              'Neos.Form:FileUpload' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:FormElement' => true,
                ),
                'implementationClassName' => 'Neos\\Form\\FormElements\\FileUpload',
                'properties' => 
                array (
                  'allowedExtensions' => 
                  array (
                    0 => 'pdf',
                    1 => 'doc',
                  ),
                ),
              ),
              'Neos.Form:ImageUpload' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:FormElement' => true,
                ),
                'implementationClassName' => 'Neos\\Form\\FormElements\\ImageUpload',
                'properties' => 
                array (
                  'allowedTypes' => 
                  array (
                    0 => 'jpeg',
                    1 => 'png',
                    2 => 'bmp',
                  ),
                ),
              ),
              'Neos.Form:StaticText' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:ReadOnlyFormElement' => true,
                ),
                'properties' => 
                array (
                  'text' => '',
                ),
              ),
              'Neos.Form:HiddenField' => 
              array (
                'superTypes' => 
                array (
                  'Neos.Form:FormElement' => true,
                ),
              ),
            ),
            'finisherPresets' => 
            array (
              'Neos.Form:Closure' => 
              array (
                'implementationClassName' => 'Neos\\Form\\Finishers\\ClosureFinisher',
                'options' => 
                array (
                ),
              ),
              'Neos.Form:Confirmation' => 
              array (
                'implementationClassName' => 'Neos\\Form\\Finishers\\ConfirmationFinisher',
                'options' => 
                array (
                ),
              ),
              'Neos.Form:Email' => 
              array (
                'implementationClassName' => 'Neos\\Form\\Finishers\\EmailFinisher',
                'options' => 
                array (
                ),
              ),
              'Neos.Form:FlashMessage' => 
              array (
                'implementationClassName' => 'Neos\\Form\\Finishers\\FlashMessageFinisher',
                'options' => 
                array (
                ),
              ),
              'Neos.Form:Redirect' => 
              array (
                'implementationClassName' => 'Neos\\Form\\Finishers\\RedirectFinisher',
                'options' => 
                array (
                ),
              ),
            ),
            'validatorPresets' => 
            array (
              'Neos.Flow:NotEmpty' => 
              array (
                'implementationClassName' => 'Neos\\Flow\\Validation\\Validator\\NotEmptyValidator',
              ),
              'Neos.Flow:DateTimeRange' => 
              array (
                'implementationClassName' => 'Neos\\Flow\\Validation\\Validator\\DateTimeRangeValidator',
              ),
              'Neos.Flow:Alphanumeric' => 
              array (
                'implementationClassName' => 'Neos\\Flow\\Validation\\Validator\\AlphanumericValidator',
              ),
              'Neos.Flow:Text' => 
              array (
                'implementationClassName' => 'Neos\\Flow\\Validation\\Validator\\TextValidator',
              ),
              'Neos.Flow:StringLength' => 
              array (
                'implementationClassName' => 'Neos\\Flow\\Validation\\Validator\\StringLengthValidator',
              ),
              'Neos.Flow:EmailAddress' => 
              array (
                'implementationClassName' => 'Neos\\Flow\\Validation\\Validator\\EmailAddressValidator',
              ),
              'Neos.Flow:Integer' => 
              array (
                'implementationClassName' => 'Neos\\Flow\\Validation\\Validator\\IntegerValidator',
              ),
              'Neos.Flow:Float' => 
              array (
                'implementationClassName' => 'Neos\\Flow\\Validation\\Validator\\FloatValidator',
              ),
              'Neos.Flow:NumberRange' => 
              array (
                'implementationClassName' => 'Neos\\Flow\\Validation\\Validator\\NumberRangeValidator',
              ),
              'Neos.Flow:RegularExpression' => 
              array (
                'implementationClassName' => 'Neos\\Flow\\Validation\\Validator\\RegularExpressionValidator',
              ),
              'Neos.Flow:Count' => 
              array (
                'implementationClassName' => 'Neos\\Flow\\Validation\\Validator\\CountValidator',
              ),
            ),
          ),
          'neos.setup' => 
          array (
            'title' => 'Setup Elements',
            'parentPreset' => 'default',
            'formElementTypes' => 
            array (
              'Neos.Form:Base' => 
              array (
                'renderingOptions' => 
                array (
                  'layoutPathPattern' => 'resource://Neos.Setup/Private/Form/Layouts/{@type}.html',
                  'validationErrorTranslationPackage' => 'Neos.Flow',
                ),
              ),
              'Neos.Form:Form' => 
              array (
                'renderingOptions' => 
                array (
                  'templatePathPattern' => 'resource://Neos.Setup/Private/Form/{@type}.html',
                ),
              ),
              'Neos.Setup:LinkElement' => 
              array (
                'superTypes' => 
                array (
                  0 => 'Neos.Form:ReadOnlyFormElement',
                ),
                'properties' => 
                array (
                  'text' => '',
                  'class' => 'btn',
                  'href' => '',
                ),
              ),
              'Neos.Setup:DatabaseSelector' => 
              array (
                'superTypes' => 
                array (
                  0 => 'Neos.Form:FormElement',
                ),
                'properties' => 
                array (
                  'elementClassAttribute' => 'form-control',
                ),
              ),
              'Neos.Form:SingleLineText' => 
              array (
                'properties' => 
                array (
                  'elementClassAttribute' => 'form-control',
                ),
              ),
              'Neos.Form:Password' => 
              array (
                'properties' => 
                array (
                  'elementClassAttribute' => 'form-control',
                ),
              ),
              'Neos.Form:PasswordWithConfirmation' => 
              array (
                'renderingOptions' => 
                array (
                  'templatePathPattern' => 'resource://Neos.Setup/Private/Form/{@type}.html',
                ),
                'properties' => 
                array (
                  'elementClassAttribute' => 'form-control',
                  'confirmationClassAttribute' => 'form-control',
                ),
              ),
              'Neos.Form:Checkbox' => 
              array (
                'renderingOptions' => 
                array (
                  'templatePathPattern' => 'resource://Neos.Setup/Private/Form/{@type}.html',
                ),
                'properties' => 
                array (
                  'elementClassAttribute' => 'checkbox',
                ),
              ),
              'Neos.Form:MultipleSelectDropdown' => 
              array (
                'properties' => 
                array (
                  'elementClassAttribute' => 'form-control',
                ),
              ),
              'Neos.Form:SingleSelectDropdown' => 
              array (
                'renderingOptions' => 
                array (
                  'templatePathPattern' => 'resource://Neos.Setup/Private/Form/{@type}.html',
                ),
              ),
              'Neos.Form:StaticText' => 
              array (
                'renderingOptions' => 
                array (
                  'templatePathPattern' => 'resource://Neos.Setup/Private/Form/{@type}.html',
                ),
              ),
            ),
          ),
          'bootstrap' => 
          array (
            'title' => 'Twitter bootstrap',
            'parentPreset' => 'default',
            'formElementTypes' => 
            array (
              'Neos.Form:Base' => 
              array (
                'renderingOptions' => 
                array (
                  'layoutPathPattern' => 'resource://Neos.Demo/Private/Templates/ContactForm/{@type}.html',
                ),
              ),
              'Neos.Form:FormElement' => 
              array (
                'properties' => 
                array (
                  'elementClassAttribute' => 'form-control',
                ),
              ),
              'Neos.Form:MultiLineText' => 
              array (
                'properties' => 
                array (
                  'elementClassAttribute' => 'form-control',
                ),
              ),
            ),
          ),
        ),
      ),
      'Twitter' => 
      array (
        'Bootstrap' => 
        array (
          'viewHelpers' => 
          array (
            'partialRootPath' => 'resource://Neos.Twitter.Bootstrap/Private/Partials/',
            'templates' => 
            array (
              'Neos\\Twitter\\Bootstrap\\ViewHelpers\\Navigation\\MenuViewHelper' => 'resource://Neos.Twitter.Bootstrap/Private/Templates/Navigation/Menu.html',
            ),
          ),
        ),
      ),
      'Setup' => 
      array (
        'initialPasswordFile' => (defined('FLOW_PATH_DATA') ? constant('FLOW_PATH_DATA') : null) . 'SetupPassword.txt',
        'stepOrder' => 
        array (
          0 => 'neosRequirements',
          1 => 'database',
          2 => 'administrator',
          3 => 'siteimport',
          4 => 'final',
        ),
        'steps' => 
        array (
          'database' => 
          array (
            'className' => 'Neos\\Setup\\Step\\DatabaseStep',
            'requiredConditions' => 
            array (
              0 => 
              array (
                'className' => 'Neos\\Setup\\Condition\\PdoDriverCondition',
              ),
            ),
          ),
          'final' => 
          array (
            'className' => 'Neos\\Neos\\Setup\\Step\\FinalStep',
          ),
          'neosRequirements' => 
          array (
            'className' => 'Neos\\Neos\\Setup\\Step\\NeosSpecificRequirementsStep',
          ),
          'administrator' => 
          array (
            'className' => 'Neos\\Neos\\Setup\\Step\\AdministratorStep',
            'requiredConditions' => 
            array (
              0 => 
              array (
                'className' => 'Neos\\Setup\\Condition\\DatabaseConnectionCondition',
              ),
            ),
          ),
          'siteimport' => 
          array (
            'className' => 'Neos\\Neos\\Setup\\Step\\SiteImportStep',
            'requiredConditions' => 
            array (
              0 => 
              array (
                'className' => 'Neos\\Setup\\Condition\\DatabaseConnectionCondition',
              ),
            ),
          ),
        ),
        'view' => 
        array (
          'title' => 'Neos Setup',
        ),
        'http' => 
        array (
          'chain' => 
          array (
            'preprocess' => 
            array (
              'chain' => 
              array (
                'configureRouting' => 
                array (
                  'position' => 'start',
                  'component' => 'Neos\\Setup\\Core\\ConfigureRoutingComponent',
                ),
              ),
            ),
          ),
        ),
      ),
      'ContentRepository' => 
      array (
        'contentDimensions' => 
        array (
          'language' => 
          array (
            'label' => 'Neos.Demo:Main:contentDimensions.language',
            'icon' => 'icon-language',
            'default' => 'en_US',
            'defaultPreset' => 'en_US',
            'presets' => 
            array (
              'en_US' => 
              array (
                'label' => 'English (US)',
                'values' => 
                array (
                  0 => 'en_US',
                ),
                'uriSegment' => 'en',
              ),
              'en_UK' => 
              array (
                'label' => 'English (UK)',
                'values' => 
                array (
                  0 => 'en_UK',
                  1 => 'en_US',
                ),
                'uriSegment' => 'uk',
              ),
              'de' => 
              array (
                'label' => 'German',
                'values' => 
                array (
                  0 => 'de',
                ),
                'uriSegment' => 'de',
              ),
              'fr' => 
              array (
                'label' => 'French',
                'values' => 
                array (
                  0 => 'fr',
                ),
                'uriSegment' => 'fr',
              ),
              'nl' => 
              array (
                'label' => 'Dutch',
                'values' => 
                array (
                  0 => 'nl',
                  1 => 'de',
                ),
                'uriSegment' => 'nl',
              ),
              'da' => 
              array (
                'label' => 'Danish',
                'values' => 
                array (
                  0 => 'da',
                ),
                'uriSegment' => 'da',
              ),
              'lv' => 
              array (
                'label' => 'Latvian',
                'values' => 
                array (
                  0 => 'lv',
                ),
                'uriSegment' => 'lv',
              ),
            ),
          ),
        ),
        'labelGenerator' => 
        array (
          'eel' => 
          array (
            'defaultContext' => 
            array (
              'String' => 'Neos\\Eel\\Helper\\StringHelper',
              'Array' => 'Neos\\Eel\\Helper\\ArrayHelper',
              'Date' => 'Neos\\Eel\\Helper\\DateHelper',
              'Configuration' => 'Neos\\Eel\\Helper\\ConfigurationHelper',
              'Math' => 'Neos\\Eel\\Helper\\MathHelper',
              'Json' => 'Neos\\Eel\\Helper\\JsonHelper',
              'I18n' => 'Neos\\Flow\\I18n\\EelHelper\\TranslationHelper',
            ),
          ),
        ),
        'fallbackNodeType' => 'Neos.Neos:FallbackNode',
      ),
      'Fusion' => 
      array (
        'rendering' => 
        array (
          'exceptionHandler' => 'Neos\\Fusion\\Core\\ExceptionHandlers\\ThrowingHandler',
          'innerExceptionHandler' => 'Neos\\Fusion\\Core\\ExceptionHandlers\\BubblingHandler',
        ),
        'debugMode' => false,
        'enableContentCache' => true,
        'defaultContext' => 
        array (
          'String' => 'Neos\\Eel\\Helper\\StringHelper',
          'Array' => 'Neos\\Eel\\Helper\\ArrayHelper',
          'Date' => 'Neos\\Eel\\Helper\\DateHelper',
          'Configuration' => 'Neos\\Eel\\Helper\\ConfigurationHelper',
          'Math' => 'Neos\\Eel\\Helper\\MathHelper',
          'Json' => 'Neos\\Eel\\Helper\\JsonHelper',
          'Security' => 'Neos\\Eel\\Helper\\SecurityHelper',
          'Translation' => 'Neos\\Flow\\I18n\\EelHelper\\TranslationHelper',
          'Type' => 'Neos\\Eel\\Helper\\TypeHelper',
          'I18n' => 'Neos\\Flow\\I18n\\EelHelper\\TranslationHelper',
          'File' => 'Neos\\Eel\\Helper\\FileHelper',
          'Neos.Node' => 'Neos\\Neos\\Fusion\\Helper\\NodeHelper',
          'Neos.Link' => 'Neos\\Neos\\Fusion\\Helper\\LinkHelper',
          'Neos.Array' => 'Neos\\Neos\\Fusion\\Helper\\ArrayHelper',
          'Neos.Rendering' => 'Neos\\Neos\\Fusion\\Helper\\RenderingHelper',
          'Neos.Caching' => 'Neos\\Neos\\Fusion\\Helper\\CachingHelper',
          'Neos.Ui.Sites' => 'Neos\\Neos\\Ui\\Fusion\\Helper\\SitesHelper',
          'Neos.Ui.Modules' => 'Neos\\Neos\\Ui\\Fusion\\Helper\\ModulesHelper',
          'Neos.Ui.Workspace' => 'Neos\\Neos\\Ui\\Fusion\\Helper\\WorkspaceHelper',
          'Neos.Ui.StaticResources' => 'Neos\\Neos\\Ui\\Fusion\\Helper\\StaticResourcesHelper',
          'Neos.Ui.PositionalArraySorter' => 'Neos\\Neos\\Ui\\Fusion\\Helper\\PositionalArraySorterHelper',
          'Neos.Seo.Image' => 'Neos\\Seo\\Fusion\\Helper\\ImageHelper',
        ),
        'Afx' => 
        array (
        ),
        'dsl' => 
        array (
          'afx' => 'Neos\\Fusion\\Afx\\Dsl\\AfxDslImplementation',
        ),
      ),
      'Imagine' => 
      array (
        'driver' => 'Gmagick',
        'enabledDrivers' => 
        array (
          'Gd' => true,
          'Imagick' => true,
          'Gmagick' => true,
        ),
        'profile' => 
        array (
          'RGB' => 'color.org/sRGB_IEC61966-2-1_black_scaled.icc',
          'CMYK' => 'Adobe/CMYK/USWebUncoated.icc',
          'Grayscale' => 'colormanagement.org/ISOcoated_v2_grey1c_bas.ICC',
        ),
        'driverSpecific' => 
        array (
          'Imagick' => 
          array (
            'limits' => 
            array (
            ),
          ),
        ),
      ),
      'Media' => 
      array (
        'asyncThumbnails' => true,
        'thumbnailPresets' => 
        array (
          'Neos.Media.Browser:Thumbnail' => 
          array (
            'maximumWidth' => 250,
            'maximumHeight' => 250,
          ),
          'Neos.Media.Browser:Preview' => 
          array (
            'maximumWidth' => 1000,
            'maximumHeight' => 1000,
          ),
          'Neos.Seo:OpenGraph.Square' => 
          array (
            'maximumWidth' => 1080,
            'maximumHeight' => 1080,
          ),
          'Neos.Seo:OpenGraph.Landscape' => 
          array (
            'maximumWidth' => 1200,
            'maximumHeight' => 628,
          ),
          'Neos.Seo:TwitterCard.SummaryCardLargeImage' => 
          array (
            'maximumWidth' => 1200,
            'maximumHeight' => 600,
          ),
          'Neos.Seo:TwitterCard.SummaryCard' => 
          array (
            'maximumWidth' => 600,
            'maximumHeight' => 600,
          ),
        ),
        'autoCreateThumbnailPresets' => true,
        'iconSet' => 
        array (
          'path' => 'resource://Neos.Media/Public/IconSets/vivid',
          'extension' => 'svg',
        ),
        'assetSources' => 
        array (
          'neos' => 
          array (
            'assetSource' => 'Neos\\Media\\Domain\\Model\\AssetSource\\Neos\\NeosAssetSource',
            'assetSourceOptions' => 
            array (
              'asyncThumbnails' => NULL,
            ),
          ),
        ),
        'asset' => 
        array (
          'modelMappingStrategy' => 
          array (
            'default' => 'Neos\\Media\\Domain\\Model\\Document',
            'patterns' => 
            array (
              '[image/.*]' => 
              array (
                'className' => 'Neos\\Media\\Domain\\Model\\Image',
              ),
              '[audio/.*]' => 
              array (
                'className' => 'Neos\\Media\\Domain\\Model\\Audio',
              ),
              '[video/.*]' => 
              array (
                'className' => 'Neos\\Media\\Domain\\Model\\Video',
              ),
            ),
          ),
        ),
        'image' => 
        array (
          'defaultOptions' => 
          array (
            'quality' => 90,
            'convertCMYKToRGB' => true,
            'convertFormats' => 
            array (
            ),
            'interlace' => (defined('\\Imagine\\Image\\ImageInterface::INTERLACE_NONE') ? constant('\\Imagine\\Image\\ImageInterface::INTERLACE_NONE') : null),
            'resizeFilter' => (defined('\\Imagine\\Image\\ImageInterface::FILTER_UNDEFINED') ? constant('\\Imagine\\Image\\ImageInterface::FILTER_UNDEFINED') : null),
          ),
        ),
        'variantPresets' => 
        array (
        ),
        'autoCreateImageVariantPresets' => false,
        'thumbnailGenerators' => 
        array (
          'Neos\\Media\\Domain\\Model\\ThumbnailGenerator\\DocumentThumbnailGenerator' => 
          array (
            'resolution' => 120,
            'supportedExtensions' => 
            array (
              0 => 'pdf',
              1 => 'eps',
              2 => 'ai',
            ),
            'paginableDocuments' => 
            array (
              0 => 'pdf',
            ),
          ),
          'Neos\\Media\\Domain\\Model\\ThumbnailGenerator\\FontDocumentThumbnailGenerator' => 
          array (
            'supportedExtensions' => 
            array (
              0 => 'ttf',
              1 => 'otf',
            ),
          ),
        ),
        'Browser' => 
        array (
          'bodyClasses' => 'neos neos-module media-browser',
          'scripts' => 
          array (
            0 => 'resource://Neos.Neos/Public/Library/jquery/jquery-2.0.3.js',
            1 => 'resource://Neos.Twitter.Bootstrap/Public/2/js/bootstrap.min.js',
            2 => 'resource://Neos.Neos/Public/Library/bootstrap-components.js',
            3 => 'resource://Neos.Media.Browser/Public/JavaScript/media-browser.js',
          ),
          'styles' => 
          array (
            0 => 'resource://Neos.Neos/Public/Styles/Neos.css',
            1 => 'resource://Neos.Media.Browser/Public/Styles/MediaBrowser.css',
            2 => 'resource://Neos.Media.Browser/Public/Styles/Main.css',
          ),
          'features' => 
          array (
            'variantsTab' => 
            array (
              'enable' => false,
            ),
          ),
        ),
      ),
      'Party' => 
      array (
      ),
      'Kickstarter' => 
      array (
      ),
      'SiteKickstarter' => 
      array (
      ),
      'Behat' => 
      array (
      ),
      'RedirectHandler' => 
      array (
        'features' => 
        array (
          'hitCounter' => true,
        ),
        'statusCode' => 
        array (
          'redirect' => 307,
          'gone' => 410,
        ),
        'DatabaseStorage' => 
        array (
        ),
        'NeosAdapter' => 
        array (
          'enableRemovedNodeRedirect' => true,
          'pathPrefixConfiguration' => 
          array (
          ),
          'nodeTypeConfiguration' => 
          array (
            'Neos.Neos:Document' => true,
          ),
        ),
      ),
      'Neos' => 
      array (
        'Ui' => 
        array (
          'Compiled' => 
          array (
          ),
          'frontendConfiguration' => 
          array (
            'legacy' => 
            array (
              'enableUiSwitch' => true,
            ),
            'editPreviewModes' => '${Neos.Ui.PositionalArraySorter.sort(Configuration.setting(\'Neos.Neos.userInterface.editPreviewModes\'))}',
            'hotkeys' => 
            array (
              'UI.RightSideBar.toggle' => 't i',
              'UI.FullScreen.toggle' => 't f',
              'UI.LeftSideBar.toggle' => 't l',
              'UI.LeftSideBar.toggleContentTree' => 't c',
              'UI.AddNodeModal.close' => 'c m',
              'UI.Drawer.toggle' => 't d',
              'UI.EditModePanel.toggle' => 't e',
              'UI.InsertionModeModal.cancel' => 'c i',
              'UI.InsertionModeModal.apply' => 'a i',
              'UI.ContentCanvas.reload' => 'r c',
              'UI.Inspector.discard' => 'd i',
              'UI.Inspector.escape' => 'e i',
              'UI.Inspector.resume' => 'r i',
              'UI.NodeCreationDialog.back' => 'b n',
              'UI.NodeCreationDialog.cancel' => 'c n',
              'UI.NodeCreationDialog.apply' => 'a n',
              'UI.NodeVariantCreationDialog.cancel' => 'c v',
              'UI.NodeVariantCreationDialog.createEmpty' => 'c e v',
              'UI.NodeVariantCreationDialog.createAndCopy' => 'c c v',
              'CR.Nodes.unfocus' => 'u n',
            ),
            'defaultInlineEditor' => 'ckeditor5',
          ),
          'splashScreen' => 
          array (
            'partial' => 'SplashScreen',
          ),
          'resources' => 
          array (
            'javascript' => 
            array (
              'Neos.Neos.UI:Vendor' => 
              array (
                'resource' => '${"resource://" + Neos.Ui.StaticResources.compiledResourcePackage() + "/Public/JavaScript/Vendor.js"}',
                'position' => 'start 1000',
              ),
              'Neos.Neos.UI:Host' => 
              array (
                'resource' => '${"resource://" + Neos.Ui.StaticResources.compiledResourcePackage() + "/Public/JavaScript/Host.js"}',
                'position' => 'start 900',
              ),
            ),
            'stylesheets' => 
            array (
              'Neos.Neos.UI:Host' => 
              array (
                'resource' => '${"resource://" + Neos.Ui.StaticResources.compiledResourcePackage() + "/Public/Styles/Host.css"}',
                'position' => 'start 1000',
              ),
              'Neos.Neos.UI:HostOnlyStyles' => 
              array (
                'resource' => '${"resource://" + Neos.Ui.StaticResources.compiledResourcePackage() + "/Public/Styles/HostOnlyStyles.css"}',
                'position' => 'start 900',
              ),
            ),
          ),
          'contentCanvas' => 
          array (
            'backgroundColor' => '#ffffff',
          ),
          'frontendDevelopmentMode' => false,
          'nodeTypeRoles' => 
          array (
            'ignored' => 'unstructured',
            'document' => 'Neos.Neos:Document',
            'content' => 'Neos.Neos:Content',
            'contentCollection' => 'Neos.Neos:ContentCollection',
          ),
          'configurationDefaultEelContext' => 
          array (
            'Neos.Ui.Api' => 'Neos\\Neos\\Ui\\Fusion\\Helper\\ApiHelper',
            'Neos.Ui.Workspace' => 'Neos\\Neos\\Ui\\Fusion\\Helper\\WorkspaceHelper',
            'Neos.Ui.NodeInfo' => 'Neos\\Neos\\Ui\\Fusion\\Helper\\NodeInfoHelper',
            'Neos.Ui.ContentDimensions' => 'Neos\\Neos\\Ui\\Fusion\\Helper\\ContentDimensionsHelper',
            'Neos.Ui.StaticResources' => 'Neos\\Neos\\Ui\\Fusion\\Helper\\StaticResourcesHelper',
            'Neos.Ui.PositionalArraySorter' => 'Neos\\Neos\\Ui\\Fusion\\Helper\\PositionalArraySorterHelper',
          ),
          'documentNodeInformation' => 
          array (
            'metaData' => 
            array (
              'documentNode' => '${q(documentNode).property("_contextPath")}',
              'siteNode' => '${q(site).property(\'_contextPath\')}',
              'previewUrl' => '${Neos.Ui.NodeInfo.createRedirectToNode(controllerContext, q(documentNode).context({workspaceName: documentNode.context.workspace.baseWorkspace.name}).get(0))}',
              'contentDimensions' => 
              array (
                'active' => '${documentNode.context.dimensions}',
                'allowedPresets' => '${Neos.Ui.Api.emptyArrayToObject(Neos.Ui.ContentDimensions.allowedPresetsByName(documentNode.context.dimensions))}',
              ),
              'documentNodeSerialization' => '${Neos.Ui.NodeInfo.renderNodeWithPropertiesAndChildrenInformation(documentNode, controllerContext)}',
              'url' => '${Neos.Ui.NodeInfo.uri(documentNode, controllerContext)}',
            ),
          ),
          'initialState' => 
          array (
            'changes' => 
            array (
              'pending' => 
              array (
              ),
              'processing' => 
              array (
              ),
              'failed' => 
              array (
              ),
            ),
            'cr' => 
            array (
              'nodes' => 
              array (
                'byContextPath' => '${Neos.Ui.NodeInfo.defaultNodesForBackend(site, documentNode, controllerContext)}',
                'siteNode' => '${q(site).property(\'_contextPath\')}',
                'documentNode' => '${q(documentNode).property(\'_contextPath\')}',
                'clipboard' => '${clipboardNode || null}',
                'clipboardMode' => '${clipboardMode || null}',
              ),
              'contentDimensions' => 
              array (
                'byName' => '${Neos.Ui.ContentDimensions.contentDimensionsByName()}',
                'active' => '${documentNode.context.dimensions}',
                'allowedPresets' => '${Neos.Ui.Api.emptyArrayToObject(Neos.Ui.ContentDimensions.allowedPresetsByName(documentNode.context.dimensions))}',
              ),
              'workspaces' => 
              array (
                'personalWorkspace' => '${Neos.Ui.Workspace.getPersonalWorkspace()}',
              ),
            ),
            'ui' => 
            array (
              'contentCanvas' => 
              array (
                'src' => '${Neos.Ui.NodeInfo.uri(documentNode, controllerContext)}',
                'backgroundColor' => '${Configuration.setting(\'Neos.Neos.Ui.contentCanvas.backgroundColor\')}',
              ),
              'debugMode' => false,
              'editPreviewMode' => '${q(user).property("preferences.preferences")["contentEditing.editPreviewMode"] || Configuration.setting(\'Neos.Neos.userInterface.defaultEditPreviewMode\')}',
              'fullScreen' => 
              array (
                'isFullScreen' => false,
              ),
              'editModePanel' => 
              array (
                'isHidden' => true,
              ),
              'leftSideBar' => 
              array (
                'isHidden' => false,
                'contentTree' => 
                array (
                  'isHidden' => true,
                ),
              ),
              'rightSideBar' => 
              array (
                'isHidden' => false,
              ),
              'addNodeModal' => 
              array (
                'referenceNode' => '',
                'mode' => 'insert',
              ),
              'drawer' => 
              array (
                'isHidden' => true,
              ),
              'pageTree' => 
              array (
                'isLoading' => false,
                'hasError' => false,
                'focused' => '${q(documentNode).property(\'_contextPath\')}',
                'active' => '${q(documentNode).property(\'_contextPath\')}',
              ),
              'remote' => 
              array (
                'isSaving' => false,
                'isPublishing' => false,
                'isDiscarding' => false,
              ),
            ),
            'user' => 
            array (
              'name' => 
              array (
                'title' => '${q(user).property(\'name.title\')}',
                'firstName' => '${q(user).property(\'name.firstName\')}',
                'middleName' => '${q(user).property(\'name.middleName\')}',
                'lastName' => '${q(user).property(\'name.lastName\')}',
                'otherName' => '${q(user).property(\'name.otherName\')}',
                'fullName' => '${q(user).property(\'name.fullName\')}',
              ),
              'preferences' => 
              array (
                'interfaceLanguage' => '${q(user).property(\'preferences.interfaceLanguage\')}',
              ),
              'settings' => 
              array (
                'isAutoPublishingEnabled' => false,
                'targetWorkspace' => 'live',
              ),
            ),
          ),
          'changes' => 
          array (
            'types' => 
            array (
              'Neos.Neos.Ui:Property' => 'Neos\\Neos\\Ui\\Domain\\Model\\Changes\\Property',
              'Neos.Neos.Ui:CreateInto' => 'Neos\\Neos\\Ui\\Domain\\Model\\Changes\\Create',
              'Neos.Neos.Ui:CreateBefore' => 'Neos\\Neos\\Ui\\Domain\\Model\\Changes\\CreateBefore',
              'Neos.Neos.Ui:CreateAfter' => 'Neos\\Neos\\Ui\\Domain\\Model\\Changes\\CreateAfter',
              'Neos.Neos.Ui:RemoveNode' => 'Neos\\Neos\\Ui\\Domain\\Model\\Changes\\Remove',
              'Neos.Neos.Ui:CopyBefore' => 'Neos\\Neos\\Ui\\Domain\\Model\\Changes\\CopyBefore',
              'Neos.Neos.Ui:CopyAfter' => 'Neos\\Neos\\Ui\\Domain\\Model\\Changes\\CopyAfter',
              'Neos.Neos.Ui:CopyInto' => 'Neos\\Neos\\Ui\\Domain\\Model\\Changes\\CopyInto',
              'Neos.Neos.Ui:MoveBefore' => 'Neos\\Neos\\Ui\\Domain\\Model\\Changes\\MoveBefore',
              'Neos.Neos.Ui:MoveAfter' => 'Neos\\Neos\\Ui\\Domain\\Model\\Changes\\MoveAfter',
              'Neos.Neos.Ui:MoveInto' => 'Neos\\Neos\\Ui\\Domain\\Model\\Changes\\MoveInto',
            ),
          ),
        ),
        'modules' => 
        array (
          'management' => 
          array (
            'submodules' => 
            array (
              'media' => 
              array (
                'controller' => '\\Neos\\Media\\Browser\\Controller\\AssetController',
                'label' => 'Neos.Media.Browser:Main:module.label',
                'description' => 'Neos.Media.Browser:Main:module.description',
                'icon' => 'fas fa-camera',
                'privilegeTarget' => 'Neos.Media.Browser:ManageAssets',
                'additionalResources' => 
                array (
                  'styleSheets' => 
                  array (
                    0 => 'resource://Neos.Media.Browser/Public/Styles/MediaBrowser.css',
                    1 => 'resource://Neos.Media.Browser/Public/Styles/Main.css',
                  ),
                ),
              ),
              'workspaces' => 
              array (
                'label' => 'Neos.Neos:Modules:workspaces.label',
                'controller' => 'Neos\\Neos\\Controller\\Module\\Management\\WorkspacesController',
                'description' => 'Neos.Neos:Modules:workspaces.description',
                'icon' => 'fas fa-th-large',
              ),
              'history' => 
              array (
                'label' => 'Neos.Neos:Modules:history.label',
                'controller' => 'Neos\\Neos\\Controller\\Module\\Management\\HistoryController',
                'description' => 'Neos.Neos:Modules:history.description',
                'icon' => 'fas fa-calendar-alt',
              ),
            ),
            'label' => 'Neos.Neos:Modules:management.label',
            'controller' => 'Neos\\Neos\\Controller\\Module\\ManagementController',
            'description' => 'Neos.Neos:Modules:management.description',
            'icon' => 'fas fa-briefcase',
          ),
          'administration' => 
          array (
            'label' => 'Neos.Neos:Modules:administration.label',
            'controller' => 'Neos\\Neos\\Controller\\Module\\AdministrationController',
            'description' => 'Neos.Neos:Modules:administration.description',
            'icon' => 'fas fa-cogs',
            'submodules' => 
            array (
              'users' => 
              array (
                'label' => 'Neos.Neos:Modules:users.label',
                'controller' => 'Neos\\Neos\\Controller\\Module\\Administration\\UsersController',
                'description' => 'Neos.Neos:Modules:users.description',
                'icon' => 'fas fa-users',
                'actions' => 
                array (
                  'new' => 
                  array (
                    'label' => 'Neos.Neos:Modules:users.actions.new.label',
                    'title' => 'Neos.Neos:Modules:users.actions.new.title',
                  ),
                ),
              ),
              'packages' => 
              array (
                'label' => 'Neos.Neos:Modules:packages.label',
                'controller' => 'Neos\\Neos\\Controller\\Module\\Administration\\PackagesController',
                'description' => 'Neos.Neos:Modules:packages.description',
                'icon' => 'fas fa-archive',
              ),
              'sites' => 
              array (
                'label' => 'Neos.Neos:Modules:sites.label',
                'controller' => 'Neos\\Neos\\Controller\\Module\\Administration\\SitesController',
                'description' => 'Neos.Neos:Modules:sites.description',
                'icon' => 'fas fa-globe',
                'actions' => 
                array (
                  'newSite' => 
                  array (
                    'label' => 'Neos.Neos:Modules:sites.actions.newSite.label',
                    'title' => 'Neos.Neos:Modules:sites.actions.newSite.title',
                  ),
                ),
              ),
              'configuration' => 
              array (
                'label' => 'Neos.Neos:Modules:configuration.label',
                'controller' => 'Neos\\Neos\\Controller\\Module\\Administration\\ConfigurationController',
                'description' => 'Neos.Neos:Modules:configuration.description',
                'icon' => 'fas fa-list-alt',
              ),
              'dimensions' => 
              array (
                'label' => 'Neos.Neos:Modules:dimensions.label',
                'controller' => 'Neos\\Neos\\Controller\\Module\\Administration\\DimensionController',
                'description' => 'Neos.Neos:Modules:dimensions.description',
                'icon' => 'fas fa-code-branch',
              ),
            ),
          ),
          'user' => 
          array (
            'label' => 'Neos.Neos:Modules:user.label',
            'controller' => '\\Neos\\Neos\\Controller\\Module\\UserController',
            'hideInMenu' => true,
            'submodules' => 
            array (
              'usersettings' => 
              array (
                'label' => 'Neos.Neos:Modules:userSettings.label',
                'controller' => '\\Neos\\Neos\\Controller\\Module\\User\\UserSettingsController',
                'description' => 'Neos.Neos:Modules:userSettings.description',
                'icon' => 'fas fa-user',
              ),
            ),
          ),
        ),
        'userInterface' => 
        array (
          'translation' => 
          array (
            'autoInclude' => 
            array (
              'Neos.Media.Browser' => 
              array (
                0 => 'Main',
              ),
              'Neos.Neos' => 
              array (
                0 => 'Main',
                1 => 'Inspector',
                2 => 'Modules',
                3 => 'NodeTypes/*',
              ),
              'Neos.Neos.Ui' => 
              array (
                0 => 'Main',
              ),
              'Neos.Seo' => 
              array (
                0 => 'NodeTypes/*',
              ),
              'Neos.NodeTypes.BaseMixins' => 
              array (
                0 => 'NodeTypes/*',
              ),
              'Neos.NodeTypes.Html' => 
              array (
                0 => 'NodeTypes/*',
              ),
              'Neos.NodeTypes.ColumnLayouts' => 
              array (
                0 => 'NodeTypes/*',
              ),
              'Neos.NodeTypes.AssetList' => 
              array (
                0 => 'NodeTypes/*',
              ),
              'Neos.NodeTypes.Navigation' => 
              array (
                0 => 'NodeTypes/*',
              ),
              'Neos.NodeTypes.Form' => 
              array (
                0 => 'NodeTypes/*',
              ),
              'Neos.NodeTypes.ContentReferences' => 
              array (
                0 => 'NodeTypes/*',
              ),
              'Neos.NodeTypes' => 
              array (
                0 => 'NodeTypes/*',
              ),
              'Flowpack.Neos.FrontendLogin' => 
              array (
                0 => 'NodeTypes/*',
              ),
              'Neos.Demo' => 
              array (
                0 => 'Main',
                1 => 'NodeTypes/*',
              ),
            ),
          ),
          'routeAfterLogin' => 
          array (
            'values' => 
            array (
              '@package' => 'Neos.Neos.Ui',
              '@controller' => 'Backend',
              '@action' => 'index',
              '@format' => 'html',
            ),
          ),
          'loadMinifiedJavascript' => true,
          'requireJsWaitSeconds' => 30,
          'scrambleTranslatedLabels' => false,
          'requireJsPathMapping' => 
          array (
            'Neos.Neos/Validation' => 'resource://Neos.Neos/Public/JavaScript/Shared/Validation/',
            'Neos.Neos/Inspector/Editors' => 'resource://Neos.Neos/Public/JavaScript/Content/Inspector/Editors/',
            'Neos.Neos/Inspector/Handlers' => 'resource://Neos.Neos/Public/JavaScript/Content/Inspector/Handlers/',
            'Neos.Neos/Inspector/Views' => 'resource://Neos.Neos/Public/JavaScript/Content/Inspector/Views/',
          ),
          'defaultLanguage' => 'en',
          'availableLanguages' => 
          array (
            'da' => 'Dansk – Danish',
            'de' => 'Deutsch – German',
            'en' => 'English – English',
            'es' => 'Español – Spanish',
            'fi' => 'Suomi – Finnish',
            'fr' => 'Français – French',
            'km' => 'ភាសាខ្មែរ – Khmer',
            'lv' => 'Latviešu – Latvian',
            'nl' => 'Nederlands – Dutch',
            'no' => 'Norsk bokmål – Norwegian Bokmål',
            'pl' => 'Polski – Polish',
            'pt-BR' => 'Português (Brasil) – Portuguese (Brazil)',
            'ru' => 'Pусский – Russian',
            'zh-CN' => '简体中文 – Chinese, Simplified',
          ),
          'navigateComponent' => 
          array (
            'nodeTree' => 
            array (
              'loadingDepth' => 1,
              'presets' => 
              array (
                'default' => 
                array (
                  'baseNodeType' => 'Neos.Neos:Document',
                ),
              ),
            ),
            'structureTree' => 
            array (
              'loadingDepth' => 0,
            ),
          ),
          'inspector' => 
          array (
            'dataTypes' => 
            array (
              'string' => 
              array (
                'editor' => 'Neos.Neos/Inspector/Editors/TextFieldEditor',
                'defaultValue' => '',
              ),
              'integer' => 
              array (
                'editor' => 'Neos.Neos/Inspector/Editors/TextFieldEditor',
                'defaultValue' => 0,
              ),
              'boolean' => 
              array (
                'editor' => 'Neos.Neos/Inspector/Editors/BooleanEditor',
                'defaultValue' => false,
              ),
              'array' => 
              array (
                'typeConverter' => 'Neos\\Flow\\Property\\TypeConverter\\ArrayConverter',
                'editor' => 'Neos.Neos/Inspector/Editors/SelectBoxEditor',
                'editorOptions' => 
                array (
                  'multiple' => true,
                  'placeholder' => 'Choose',
                ),
                'defaultValue' => 
                array (
                ),
              ),
              'Neos\\Media\\Domain\\Model\\ImageInterface' => 
              array (
                'typeConverter' => 'Neos\\Neos\\Ui\\TypeConverter\\UiDependentImageSerializer',
                'editor' => 'Neos.Neos/Inspector/Editors/ImageEditor',
                'editorOptions' => 
                array (
                  'maximumFileSize' => NULL,
                  'features' => 
                  array (
                    'crop' => true,
                    'upload' => true,
                    'mediaBrowser' => true,
                    'resize' => false,
                  ),
                  'crop' => 
                  array (
                    'aspectRatio' => 
                    array (
                      'options' => 
                      array (
                        'square' => 
                        array (
                          'width' => 1,
                          'height' => 1,
                          'label' => 'Square',
                        ),
                        'fourFive' => 
                        array (
                          'width' => 4,
                          'height' => 5,
                        ),
                        'fiveSeven' => 
                        array (
                          'width' => 5,
                          'height' => 7,
                        ),
                        'twoThree' => 
                        array (
                          'width' => 2,
                          'height' => 3,
                        ),
                        'fourThree' => 
                        array (
                          'width' => 4,
                          'height' => 3,
                        ),
                        'sixteenNine' => 
                        array (
                          'width' => 16,
                          'height' => 9,
                        ),
                      ),
                      'enableOriginal' => true,
                      'allowCustom' => true,
                      'locked' => 
                      array (
                        'width' => 0,
                        'height' => 0,
                      ),
                    ),
                  ),
                ),
              ),
              'Neos\\Media\\Domain\\Model\\Asset' => 
              array (
                'typeConverter' => 'Neos\\Neos\\TypeConverter\\EntityToIdentityConverter',
                'editor' => 'Neos.Neos/Inspector/Editors/AssetEditor',
                'editorOptions' => 
                array (
                  'features' => 
                  array (
                    'upload' => true,
                    'mediaBrowser' => true,
                  ),
                ),
              ),
              'array<Neos\\Media\\Domain\\Model\\Asset>' => 
              array (
                'typeConverter' => 'Neos\\Flow\\Property\\TypeConverter\\TypedArrayConverter',
                'editor' => 'Neos.Neos/Inspector/Editors/AssetEditor',
                'editorOptions' => 
                array (
                  'multiple' => true,
                  'features' => 
                  array (
                    'upload' => true,
                    'mediaBrowser' => true,
                  ),
                ),
              ),
              'DateTime' => 
              array (
                'typeConverter' => 'Neos\\Neos\\Service\\Mapping\\DateStringConverter',
                'editor' => 'Neos.Neos/Inspector/Editors/DateTimeEditor',
                'editorOptions' => 
                array (
                  'format' => 'd-m-Y',
                ),
              ),
              'reference' => 
              array (
                'typeConverter' => 'Neos\\Neos\\Service\\Mapping\\NodeReferenceConverter',
                'editor' => 'Neos.Neos/Inspector/Editors/ReferenceEditor',
              ),
              'references' => 
              array (
                'typeConverter' => 'Neos\\Neos\\Service\\Mapping\\NodeReferenceConverter',
                'editor' => 'Neos.Neos/Inspector/Editors/ReferencesEditor',
              ),
            ),
            'editors' => 
            array (
              'Neos.Neos/Inspector/Editors/CodeEditor' => 
              array (
                'editorOptions' => 
                array (
                  'buttonLabel' => 'Neos.Neos:Main:content.inspector.editors.codeEditor.editCode',
                ),
              ),
              'Neos.Neos/Inspector/Editors/DateTimeEditor' => 
              array (
                'editorOptions' => 
                array (
                  'placeholder' => 'Neos.Neos:Main:content.inspector.editors.dateTimeEditor.noDateSet',
                ),
              ),
              'Neos.Neos/Inspector/Editors/AssetEditor' => 
              array (
                'editorOptions' => 
                array (
                  'fileUploadLabel' => 'Neos.Neos:Main:choose',
                ),
              ),
              'Neos.Neos/Inspector/Editors/ImageEditor' => 
              array (
                'editorOptions' => 
                array (
                  'fileUploadLabel' => 'Neos.Neos:Main:choose',
                ),
              ),
              'Neos.Neos/Inspector/Editors/LinkEditor' => 
              array (
                'editorOptions' => 
                array (
                  'placeholder' => 'Neos.Neos:Main:content.inspector.editors.linkEditor.search',
                ),
              ),
              'Neos.Neos/Inspector/Editors/ReferencesEditor' => 
              array (
                'editorOptions' => 
                array (
                  'placeholder' => 'Neos.Neos:Main:typeToSearch',
                  'nodeTypes' => 
                  array (
                    0 => 'Neos.Neos:Document',
                  ),
                  'threshold' => 2,
                ),
              ),
              'Neos.Neos/Inspector/Editors/ReferenceEditor' => 
              array (
                'editorOptions' => 
                array (
                  'placeholder' => 'Neos.Neos:Main:typeToSearch',
                  'nodeTypes' => 
                  array (
                    0 => 'Neos.Neos:Document',
                  ),
                  'threshold' => 2,
                ),
              ),
              'Neos.Neos/Inspector/Editors/SelectBoxEditor' => 
              array (
                'editorOptions' => 
                array (
                  'placeholder' => 'Neos.Neos:Main:choose',
                ),
              ),
            ),
          ),
          'defaultEditPreviewMode' => 'inPlace',
          'editPreviewModes' => 
          array (
            'live' => 
            array (
              'isEditingMode' => false,
              'isPreviewMode' => false,
              'fusionRenderingPath' => '',
              'title' => 'Live',
            ),
            'inPlace' => 
            array (
              'isEditingMode' => true,
              'isPreviewMode' => false,
              'fusionRenderingPath' => '',
              'title' => 'Neos.Neos:Main:editPreviewModes.inPlace',
              'position' => 100,
            ),
            'rawContent' => 
            array (
              'isEditingMode' => true,
              'isPreviewMode' => false,
              'fusionRenderingPath' => 'rawContent',
              'title' => 'Neos.Neos:Main:editPreviewModes.rawContent',
              'position' => 200,
            ),
            'desktop' => 
            array (
              'isEditingMode' => false,
              'isPreviewMode' => true,
              'fusionRenderingPath' => '',
              'title' => 'Neos.Neos:Main:editPreviewModes.desktop',
              'position' => 100,
            ),
            'print' => 
            array (
              'isEditingMode' => false,
              'isPreviewMode' => true,
              'fusionRenderingPath' => 'print',
              'title' => 'Neos.Demo:Main:editPreviewModes.print',
              'position' => 200,
            ),
          ),
          'backendLoginForm' => 
          array (
            'backgroundImage' => 'resource://Neos.Neos/Public/Images/Login/Wallpaper.jpg',
            'stylesheets' => 
            array (
              'Neos.Neos:DefaultStyles' => 'resource://Neos.Neos/Public/Styles/Login.css',
            ),
          ),
        ),
        'fusion' => 
        array (
          'enableObjectTreeCache' => false,
          'autoInclude' => 
          array (
            'Neos.Fusion' => true,
            'Neos.Neos' => true,
            'Neos.Neos.Ui' => true,
            'Neos.Seo' => true,
            'Neos.NodeTypes.ColumnLayouts' => true,
            'Neos.NodeTypes.Navigation' => true,
            'Neos.NodeTypes.Form' => true,
            'Neos.NodeTypes.ContentReferences' => true,
            'Neos.NodeTypes' => true,
            'Flowpack.Neos.FrontendLogin' => true,
            'Flowpack.FusionBP' => true,
          ),
        ),
        'defaultSiteNodeName' => NULL,
        'routing' => 
        array (
          'supportEmptySegmentForDimensions' => true,
        ),
        'nodeTypes' => 
        array (
          'groups' => 
          array (
            'general' => 
            array (
              'position' => 'start',
              'label' => 'Neos.Neos:Main:nodeTypes.groups.general',
              'collapsed' => false,
            ),
            'structure' => 
            array (
              'position' => 100,
              'label' => 'Neos.Neos:Main:nodeTypes.groups.structure',
              'collapsed' => false,
            ),
            'plugins' => 
            array (
              'position' => 200,
              'label' => 'Neos.Neos:Main:nodeTypes.groups.plugins',
              'collapsed' => true,
            ),
          ),
        ),
        'moduleConfiguration' => 
        array (
          'widgetTemplatePathAndFileName' => 'resource://Neos.Neos/Private/Templates/Module/Widget.html',
        ),
        'eventLog' => 
        array (
          'enabled' => false,
          'monitorEntities' => 
          array (
            'Neos\\Flow\\Security\\Account' => 
            array (
              'events' => 
              array (
                'created' => 'Account.Created',
                'deleted' => 'Account.Deleted',
              ),
              'data' => 
              array (
                'accountIdentifier' => '${entity.accountIdentifier}',
                'authenticationProviderName' => '${entity.authenticationProviderName}',
                'name' => '${entity.party.name.fullName}',
              ),
            ),
          ),
        ),
        'transliterationRules' => 
        array (
          'da' => 
          array (
            'Å' => 'Aa',
            'Ø' => 'Oe',
            'å' => 'aa',
            'ø' => 'oe',
          ),
          'de' => 
          array (
            'Ä' => 'Ae',
            'Ö' => 'Oe',
            'Ü' => 'Ue',
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
          ),
        ),
      ),
      'Seo' => 
      array (
        'robotsTxt' => 
        array (
          'dimensionsPresets' => NULL,
          'excludedDimensionsPresets' => 
          array (
          ),
        ),
        'twitterCard' => 
        array (
          'siteHandle' => NULL,
        ),
        'facebook' => 
        array (
          'profileId' => '',
          'admins' => 
          array (
          ),
          'pages' => 
          array (
          ),
        ),
        'socialProfile' => 
        array (
          'type' => 'Organization',
          'logo' => '',
          'profiles' => 
          array (
            'twitter' => '',
            'facebook' => '',
            'instagram' => '',
            'linkedIn' => '',
            'youTube' => '',
          ),
        ),
      ),
      'NodeTypes' => 
      array (
        'BaseMixins' => 
        array (
        ),
        'Html' => 
        array (
        ),
        'ColumnLayouts' => 
        array (
        ),
        'AssetList' => 
        array (
        ),
        'Navigation' => 
        array (
        ),
        'Form' => 
        array (
        ),
        'ContentReferences' => 
        array (
        ),
      ),
      'Demo' => 
      array (
        'flickr' => 
        array (
          'tagStreamUriPattern' => 'http://api.flickr.com/services/feeds/photos_public.gne?format=json&nojsoncallback=1&tags=%s',
          'userStreamUriPattern' => 'http://api.flickr.com/services/feeds/photos_public.gne?format=json&nojsoncallback=1&id=%s',
        ),
      ),
    ),
    'Claudio' => 
    array (
      'Ui' => 
      array (
        'Render' => 
        array (
        ),
      ),
      'Site' => 
      array (
        'Render' => 
        array (
        ),
      ),
    ),
    'Flowpack' => 
    array (
      'Neos' => 
      array (
        'FrontendLogin' => 
        array (
          'translation' => 
          array (
            'packageKey' => 'Flowpack.Neos.FrontendLogin',
            'sourceName' => 'Main',
          ),
        ),
      ),
      'FusionBP' => 
      array (
      ),
    ),
  ),
  'Routes' => 
  array (
    0 => 
    array (
      'name' => 'Neos.Media.Browser :: Image Browser',
      'uriPattern' => 'neos/media/browser/images(/{@action}).{@format}',
      'defaults' => 
      array (
        '@package' => 'Neos.Media.Browser',
        '@controller' => 'Image',
        '@format' => 'html',
        '@action' => 'index',
      ),
      'appendExceedingArguments' => true,
    ),
    1 => 
    array (
      'name' => 'Neos.Media.Browser :: Asset Browser',
      'uriPattern' => 'neos/media/browser/assets(/{@action}).{@format}',
      'defaults' => 
      array (
        '@package' => 'Neos.Media.Browser',
        '@controller' => 'Asset',
        '@format' => 'html',
        '@action' => 'index',
      ),
      'appendExceedingArguments' => true,
    ),
    2 => 
    array (
      'name' => 'Neos.Media.Browser :: Asset Proxy Service',
      'uriPattern' => 'neos/media/browser/asset-proxies(/{@action}).{@format}',
      'defaults' => 
      array (
        '@package' => 'Neos.Media.Browser',
        '@controller' => 'AssetProxy',
        '@format' => 'json',
        '@action' => 'index',
      ),
      'appendExceedingArguments' => true,
    ),
    3 => 
    array (
      'name' => 'Neos.Media.Browser :: ImageVariant Service',
      'uriPattern' => 'neos/media/browser/image-variant(/{@action}).{@format}',
      'defaults' => 
      array (
        '@package' => 'Neos.Media.Browser',
        '@controller' => 'ImageVariant',
        '@format' => 'json',
        '@action' => 'index',
      ),
      'appendExceedingArguments' => true,
    ),
    4 => 
    array (
      'name' => 'Neos.Media :: Thumbnail',
      'uriPattern' => 'media/thumbnail/{thumbnail}',
      'defaults' => 
      array (
        '@package' => 'Neos.Media',
        '@controller' => 'Thumbnail',
        '@action' => 'thumbnail',
      ),
      'httpMethods' => 
      array (
        0 => 'GET',
      ),
    ),
    5 => 
    array (
      'name' => 'Neos.Neos.Ui :: Backend :: Hostframe',
      'uriPattern' => 'neos/content',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos.Ui',
        '@action' => 'index',
        '@format' => 'html',
        '@controller' => 'Backend',
      ),
      'appendExceedingArguments' => true,
    ),
    6 => 
    array (
      'name' => 'Neos.Neos.Ui :: Backend :: Redirect to frontend URL',
      'uriPattern' => 'neos/redirect',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos.Ui',
        '@action' => 'redirectTo',
        '@format' => 'html',
        '@controller' => 'Backend',
      ),
      'appendExceedingArguments' => true,
    ),
    7 => 
    array (
      'name' => 'Neos.Neos.Ui :: Backend :: Change',
      'uriPattern' => 'neos/ui-services/change',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos.Ui',
        '@action' => 'change',
        '@format' => 'html',
        '@controller' => 'BackendService',
      ),
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    8 => 
    array (
      'name' => 'Neos.Neos.Ui :: Backend :: Publish',
      'uriPattern' => 'neos/ui-services/publish',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos.Ui',
        '@action' => 'publish',
        '@format' => 'html',
        '@controller' => 'BackendService',
      ),
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    9 => 
    array (
      'name' => 'Neos.Neos.Ui :: Backend :: Discard',
      'uriPattern' => 'neos/ui-services/discard',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos.Ui',
        '@action' => 'discard',
        '@format' => 'html',
        '@controller' => 'BackendService',
      ),
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    10 => 
    array (
      'name' => 'Neos.Neos.Ui :: Backend :: Change Base Workspace',
      'uriPattern' => 'neos/ui-services/change-base-workspace',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos.Ui',
        '@action' => 'changeBaseWorkspace',
        '@format' => 'html',
        '@controller' => 'BackendService',
      ),
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    11 => 
    array (
      'name' => 'Neos.Neos.Ui :: Backend :: Copy node to clipboard',
      'uriPattern' => 'neos/ui-services/copy-node',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos.Ui',
        '@action' => 'copyNode',
        '@format' => 'html',
        '@controller' => 'BackendService',
      ),
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    12 => 
    array (
      'name' => 'Neos.Neos.Ui :: Backend :: Cut node to clipboard',
      'uriPattern' => 'neos/ui-services/cut-node',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos.Ui',
        '@action' => 'cutNode',
        '@format' => 'html',
        '@controller' => 'BackendService',
      ),
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    13 => 
    array (
      'name' => 'Neos.Neos.Ui :: Backend :: Clear clipboard',
      'uriPattern' => 'neos/ui-services/clear-clipboard',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos.Ui',
        '@action' => 'clearClipboard',
        '@format' => 'html',
        '@controller' => 'BackendService',
      ),
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    14 => 
    array (
      'name' => 'Neos.Neos.Ui :: Backend :: Load Tree',
      'uriPattern' => 'neos/ui-services/load-tree',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos.Ui',
        '@action' => 'loadTree',
        '@format' => 'html',
        '@controller' => 'BackendService',
      ),
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    15 => 
    array (
      'name' => 'Neos.Neos.Ui :: Backend :: FlowQuery',
      'uriPattern' => 'neos/ui-services/flow-query',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos.Ui',
        '@action' => 'flowQuery',
        '@format' => 'html',
        '@controller' => 'BackendService',
      ),
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    16 => 
    array (
      'name' => 'Neos.Neos.Ui :: Backend :: Get Workspace Info',
      'uriPattern' => 'neos/ui-services/get-workspace-info',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos.Ui',
        '@action' => 'getWorkspaceInfo',
        '@format' => 'html',
        '@controller' => 'BackendService',
      ),
      'httpMethods' => 
      array (
        0 => 'GET',
      ),
    ),
    17 => 
    array (
      'name' => 'Neos.Neos.Ui :: Backend :: Get Additional Node Metadata',
      'uriPattern' => 'neos/ui-services/get-additional-node-metadata',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos.Ui',
        '@action' => 'getAdditionalNodeMetadata',
        '@format' => 'html',
        '@controller' => 'BackendService',
      ),
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    18 => 
    array (
      'name' => 'Neos.Neos.Ui :: Backend Login New UI Route',
      'uriPattern' => 'neos!',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos.Ui',
        '@controller' => 'Backend',
        '@action' => 'index',
        '@format' => 'html',
      ),
    ),
    19 => 
    array (
      'name' => 'Neos.Neos :: Authentication :: Login form',
      'uriPattern' => 'neos/login(.{@format})',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@controller' => 'Login',
        '@action' => 'index',
        '@format' => 'html',
      ),
      'httpMethods' => 
      array (
        0 => 'GET',
      ),
      'appendExceedingArguments' => true,
    ),
    20 => 
    array (
      'name' => 'Neos.Neos :: Authentication :: Token login',
      'uriPattern' => 'neos/login/token/{token}',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@controller' => 'Login',
        '@action' => 'tokenLogin',
        '@format' => 'html',
      ),
      'httpMethods' => 
      array (
        0 => 'GET',
      ),
    ),
    21 => 
    array (
      'name' => 'Neos.Neos :: Authentication :: Authenticate',
      'uriPattern' => 'neos/login(.{@format})',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@controller' => 'Login',
        '@action' => 'authenticate',
        '@format' => 'html',
      ),
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    22 => 
    array (
      'name' => 'Neos.Neos :: Authentication :: Logout',
      'uriPattern' => 'neos/logout(.{@format})',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@controller' => 'Login',
        '@action' => 'logout',
        '@format' => 'html',
      ),
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    23 => 
    array (
      'name' => 'Neos.Neos :: Backend :: Overview',
      'uriPattern' => 'neos',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'index',
        '@format' => 'html',
        '@controller' => 'Backend\\Backend',
      ),
    ),
    24 => 
    array (
      'name' => 'Neos.Neos :: Backend :: Content Module - Asset upload',
      'uriPattern' => 'neos/content/upload-asset',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'uploadAsset',
        '@format' => 'html',
        '@controller' => 'Backend\\Content',
      ),
      'appendExceedingArguments' => true,
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    25 => 
    array (
      'name' => 'Neos.Neos :: Backend :: Content Module - Image metadata',
      'uriPattern' => 'neos/content/image-with-metadata',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'imageWithMetadata',
        '@format' => 'html',
        '@controller' => 'Backend\\Content',
      ),
      'appendExceedingArguments' => true,
    ),
    26 => 
    array (
      'name' => 'Neos.Neos :: Backend :: Content Module - Asset metadata',
      'uriPattern' => 'neos/content/asset-with-metadata',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'assetsWithMetadata',
        '@format' => 'html',
        '@controller' => 'Backend\\Content',
      ),
    ),
    27 => 
    array (
      'name' => 'Neos.Neos :: Backend :: Content Module - Create ImageVariant',
      'uriPattern' => 'neos/content/create-image-variant',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'createImageVariant',
        '@format' => 'html',
        '@controller' => 'Backend\\Content',
      ),
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    28 => 
    array (
      'name' => 'Neos.Neos :: Backend :: Content Module - Plugin Views',
      'uriPattern' => 'neos/content/plugin-views',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'pluginViews',
        '@format' => 'html',
        '@controller' => 'Backend\\Content',
      ),
    ),
    29 => 
    array (
      'name' => 'Neos.Neos :: Backend :: Content Module - Master Plugins',
      'uriPattern' => 'neos/content/master-plugins',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'masterPlugins',
        '@format' => 'html',
        '@controller' => 'Backend\\Content',
      ),
    ),
    30 => 
    array (
      'name' => 'Neos.Neos :: Backend :: Schema - VIE',
      'uriPattern' => 'neos/schema/vie',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'vieSchema',
        '@format' => 'html',
        '@controller' => 'Backend\\Schema',
      ),
      'appendExceedingArguments' => true,
    ),
    31 => 
    array (
      'name' => 'Neos.Neos :: Backend :: Schema - NodeType',
      'uriPattern' => 'neos/schema/node-type',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'nodeTypeSchema',
        '@format' => 'html',
        '@controller' => 'Backend\\Schema',
      ),
      'appendExceedingArguments' => true,
    ),
    32 => 
    array (
      'name' => 'Neos.Neos :: Backend :: Menu',
      'uriPattern' => 'neos/menu',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'index',
        '@format' => 'html',
        '@controller' => 'Backend\\Menu',
      ),
      'appendExceedingArguments' => true,
    ),
    33 => 
    array (
      'name' => 'Neos.Neos :: Backend :: Settings',
      'uriPattern' => 'neos/settings/{@action}',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'index',
        '@format' => 'html',
        '@controller' => 'Backend\\Settings',
      ),
      'appendExceedingArguments' => true,
    ),
    34 => 
    array (
      'name' => 'Neos.Neos :: Backend :: Modules',
      'uriPattern' => 'neos/{module}',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'index',
        '@format' => 'html',
        '@controller' => 'Backend\\Module',
      ),
      'routeParts' => 
      array (
        'module' => 
        array (
          'handler' => 'Neos\\Neos\\Routing\\BackendModuleRoutePartHandler',
        ),
      ),
    ),
    35 => 
    array (
      'name' => 'Neos.Neos :: Backend :: Modules & arguments',
      'uriPattern' => 'neos/{module}/{moduleArguments}',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'index',
        '@format' => 'html',
        '@controller' => 'Backend\\Module',
      ),
      'routeParts' => 
      array (
        'module' => 
        array (
          'handler' => 'Neos\\Neos\\Routing\\BackendModuleRoutePartHandler',
        ),
        'moduleArguments' => 
        array (
          'handler' => 'Neos\\Neos\\Routing\\BackendModuleArgumentsRoutePartHandler',
        ),
      ),
      'toLowerCase' => false,
      'appendExceedingArguments' => true,
    ),
    36 => 
    array (
      'name' => 'Neos.Neos :: Backend :: Backend switch site',
      'uriPattern' => 'neos/switch/to/{site}',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'switchSite',
        '@format' => 'html',
        '@controller' => 'Backend\\Backend',
      ),
      'routeParts' => 
      array (
        'site' => 
        array (
          'objectType' => 'Neos\\Neos\\Domain\\Model\\Site',
          'uriPattern' => '{name}',
        ),
      ),
    ),
    37 => 
    array (
      'name' => 'Neos.Neos :: Backend :: Backend UI XLIFF labels',
      'uriPattern' => 'neos/xliff.json',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'xliffAsJson',
        '@format' => 'html',
        '@controller' => 'Backend\\Backend',
      ),
      'appendExceedingArguments' => true,
    ),
    38 => 
    array (
      'name' => 'Neos.Neos :: Service :: Nodes - index',
      'uriPattern' => 'neos/service/nodes',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'index',
        '@controller' => 'Service\\Nodes',
      ),
      'appendExceedingArguments' => true,
      'httpMethods' => 
      array (
        0 => 'GET',
      ),
    ),
    39 => 
    array (
      'name' => 'Neos.Neos :: Service :: Nodes - single node',
      'uriPattern' => 'neos/service/nodes/{identifier}',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'show',
        '@controller' => 'Service\\Nodes',
      ),
      'appendExceedingArguments' => true,
      'httpMethods' => 
      array (
        0 => 'HEAD',
        1 => 'GET',
      ),
    ),
    40 => 
    array (
      'name' => 'Neos.Neos :: Service :: Nodes - create/adopt node',
      'uriPattern' => 'neos/service/nodes',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'create',
        '@controller' => 'Service\\Nodes',
      ),
      'appendExceedingArguments' => true,
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    41 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - UserPreferencesController->index',
      'uriPattern' => 'neos/service/user-preferences',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'index',
        '@subpackage' => 'Service',
        '@controller' => 'UserPreference',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'GET',
      ),
    ),
    42 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - UserPreferencesController->update',
      'uriPattern' => 'neos/service/user-preferences',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'update',
        '@subpackage' => 'Service',
        '@controller' => 'UserPreference',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'PUT',
      ),
    ),
    43 => 
    array (
      'name' => 'Neos.Neos :: Service :: Asset Proxies - index',
      'uriPattern' => 'neos/service/asset-proxies',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'index',
        '@controller' => 'Service\\AssetProxies',
        '@format' => 'html',
      ),
      'appendExceedingArguments' => true,
      'httpMethods' => 
      array (
        0 => 'GET',
      ),
    ),
    44 => 
    array (
      'name' => 'Neos.Neos :: Service :: Assets Proxies - import asset proxy',
      'uriPattern' => 'neos/service/asset-proxies/{assetSourceIdentifier}/{assetProxyIdentifier}',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'import',
        '@controller' => 'Service\\AssetProxies',
        '@format' => 'html',
      ),
      'appendExceedingArguments' => true,
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    45 => 
    array (
      'name' => 'Neos.Neos :: Service :: Assets Proxies - single asset proxy',
      'uriPattern' => 'neos/service/asset-proxies/{assetSourceIdentifier}/{assetProxyIdentifier}',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'show',
        '@controller' => 'Service\\AssetProxies',
        '@format' => 'html',
      ),
      'appendExceedingArguments' => true,
      'httpMethods' => 
      array (
        0 => 'HEAD',
        1 => 'GET',
      ),
    ),
    46 => 
    array (
      'name' => 'Neos.Neos :: Service :: Assets - index',
      'uriPattern' => 'neos/service/assets',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'index',
        '@controller' => 'Service\\Assets',
      ),
      'appendExceedingArguments' => true,
      'httpMethods' => 
      array (
        0 => 'GET',
      ),
    ),
    47 => 
    array (
      'name' => 'Neos.Neos :: Service :: Assets - single asset',
      'uriPattern' => 'neos/service/assets/{identifier}',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'show',
        '@controller' => 'Service\\Assets',
      ),
      'appendExceedingArguments' => true,
      'httpMethods' => 
      array (
        0 => 'HEAD',
        1 => 'GET',
      ),
    ),
    48 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - WorkspacesController->indexAction()',
      'uriPattern' => 'neos/service/workspaces',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'index',
        '@controller' => 'Service\\Workspaces',
      ),
      'appendExceedingArguments' => true,
      'httpMethods' => 
      array (
        0 => 'GET',
      ),
    ),
    49 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - WorkspacesController->showAction()',
      'uriPattern' => 'neos/service/workspaces/{workspace}',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'show',
        '@controller' => 'Service\\Workspaces',
      ),
      'httpMethods' => 
      array (
        0 => 'GET',
      ),
    ),
    50 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - WorkspacesController->createAction()',
      'uriPattern' => 'neos/service/workspaces/{baseWorkspace}/{workspaceName}',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'create',
        '@controller' => 'Service\\Workspaces',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    51 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - WorkspacesController->updateAction()',
      'uriPattern' => 'neos/service/workspaces/{workspace.__identity}',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'update',
        '@controller' => 'Service\\Workspaces',
        '@format' => 'json',
      ),
      'appendExceedingArguments' => true,
      'httpMethods' => 
      array (
        0 => 'PUT',
      ),
    ),
    52 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - WorkspaceController->getWorkspaceWideUnpublishedNodes',
      'uriPattern' => 'neos/service/workspaces-rpc/get-workspace-wide-unpublished-nodes',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'getWorkspaceWideUnpublishedNodes',
        '@subpackage' => 'Service',
        '@controller' => 'Workspace',
        '@format' => 'json',
      ),
      'appendExceedingArguments' => true,
      'httpMethods' => 
      array (
        0 => 'GET',
      ),
    ),
    53 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - WorkspaceController->publishNode',
      'uriPattern' => 'neos/service/workspaces-rpc/publish-node',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'publishNode',
        '@subpackage' => 'Service',
        '@controller' => 'Workspace',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'PUT',
      ),
    ),
    54 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - WorkspaceController->publishNodes',
      'uriPattern' => 'neos/service/workspaces-rpc/publish-nodes',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'publishNodes',
        '@subpackage' => 'Service',
        '@controller' => 'Workspace',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'PUT',
      ),
    ),
    55 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - WorkspaceController->discardNode',
      'uriPattern' => 'neos/service/workspaces-rpc/discard-node',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'discardNode',
        '@subpackage' => 'Service',
        '@controller' => 'Workspace',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'PUT',
      ),
    ),
    56 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - WorkspaceController->discardNodes',
      'uriPattern' => 'neos/service/workspaces-rpc/discard-nodes',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'discardNodes',
        '@subpackage' => 'Service',
        '@controller' => 'Workspace',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'PUT',
      ),
    ),
    57 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - WorkspaceController->publishAll',
      'uriPattern' => 'neos/service/workspaces-rpc/publish-all',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'publishAll',
        '@subpackage' => 'Service',
        '@controller' => 'Workspace',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'PUT',
      ),
    ),
    58 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - WorkspaceController->discardAll',
      'uriPattern' => 'neos/service/workspaces-rpc/discard-all',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'discardAll',
        '@subpackage' => 'Service',
        '@controller' => 'Workspace',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'PUT',
      ),
    ),
    59 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - NodeController->getChildNodesForTree',
      'uriPattern' => 'neos/service/node/get-child-nodes-for-tree',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'getChildNodesForTree',
        '@subpackage' => 'Service',
        '@controller' => 'Node',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'GET',
      ),
    ),
    60 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - NodeController->filterChildNodesForTree',
      'uriPattern' => 'neos/service/node/filter-child-nodes-for-tree',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'filterChildNodesForTree',
        '@subpackage' => 'Service',
        '@controller' => 'Node',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'GET',
      ),
    ),
    61 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - NodeController->searchPage',
      'uriPattern' => 'neos/service/node/search-page',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'searchPage',
        '@subpackage' => 'Service',
        '@controller' => 'Node',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'GET',
      ),
    ),
    62 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - NodeController->create',
      'uriPattern' => 'neos/service/node/create',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'create',
        '@subpackage' => 'Service',
        '@controller' => 'Node',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    63 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - NodeController->createAndRender',
      'uriPattern' => 'neos/service/node/create-and-render',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'createAndRender',
        '@subpackage' => 'Service',
        '@controller' => 'Node',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    64 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - NodeController->createNodeForTheTree',
      'uriPattern' => 'neos/service/node/create-node-for-the-tree',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'createNodeForTheTree',
        '@subpackage' => 'Service',
        '@controller' => 'Node',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    65 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - NodeController->discardNode',
      'uriPattern' => 'neos/service/node/discard-node',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'discardNode',
        '@subpackage' => 'Service',
        '@controller' => 'Node',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'PUT',
      ),
    ),
    66 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - NodeController->move',
      'uriPattern' => 'neos/service/node/move',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'move',
        '@subpackage' => 'Service',
        '@controller' => 'Node',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'PUT',
      ),
    ),
    67 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - NodeController->moveAndRender',
      'uriPattern' => 'neos/service/node/move-and-render',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'moveAndRender',
        '@subpackage' => 'Service',
        '@controller' => 'Node',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'PUT',
      ),
    ),
    68 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - NodeController->copy',
      'uriPattern' => 'neos/service/node/copy',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'copy',
        '@subpackage' => 'Service',
        '@controller' => 'Node',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'PUT',
      ),
    ),
    69 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - NodeController->copyAndRender',
      'uriPattern' => 'neos/service/node/copy-and-render',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'copyAndRender',
        '@subpackage' => 'Service',
        '@controller' => 'Node',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'PUT',
      ),
    ),
    70 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - NodeController->update',
      'uriPattern' => 'neos/service/node/update',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'update',
        '@subpackage' => 'Service',
        '@controller' => 'Node',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'PUT',
      ),
    ),
    71 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - NodeController->updateAndRender',
      'uriPattern' => 'neos/service/node/update-and-render',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'updateAndRender',
        '@subpackage' => 'Service',
        '@controller' => 'Node',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'PUT',
      ),
    ),
    72 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - NodeController->delete',
      'uriPattern' => 'neos/service/node/delete',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'delete',
        '@subpackage' => 'Service',
        '@controller' => 'Node',
        '@format' => 'json',
      ),
      'httpMethods' => 
      array (
        0 => 'POST',
      ),
    ),
    73 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - DataSourceController->index',
      'uriPattern' => 'neos/service/data-source(/{dataSourceIdentifier)',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'index',
        '@subpackage' => 'Service',
        '@controller' => 'DataSource',
        '@format' => 'json',
        'dataSourceIdentifier' => '',
      ),
      'appendExceedingArguments' => true,
      'httpMethods' => 
      array (
        0 => 'GET',
      ),
    ),
    74 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - ContentDimensionController->index()',
      'uriPattern' => 'neos/service/content-dimensions',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'index',
        '@controller' => 'Service\\ContentDimensions',
      ),
      'appendExceedingArguments' => true,
      'httpMethods' => 
      array (
        0 => 'GET',
      ),
    ),
    75 => 
    array (
      'name' => 'Neos.Neos :: Service :: Services - ContentDimensionController->show()',
      'uriPattern' => 'neos/service/content-dimensions/{dimensionName}(.{@format})',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@action' => 'show',
        '@controller' => 'Service\\ContentDimensions',
        '@format' => 'html',
      ),
      'appendExceedingArguments' => true,
      'httpMethods' => 
      array (
        0 => 'GET',
      ),
    ),
    76 => 
    array (
      'name' => 'Neos.Neos :: Frontend :: Homepage',
      'uriPattern' => '{node}',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@controller' => 'Frontend\\Node',
        '@action' => 'show',
        '@format' => 'html',
      ),
      'routeParts' => 
      array (
        'node' => 
        array (
          'handler' => 'Neos\\Neos\\Routing\\FrontendNodeRoutePartHandlerInterface',
          'options' => 
          array (
            'onlyMatchSiteNodes' => true,
          ),
        ),
      ),
      'appendExceedingArguments' => true,
    ),
    77 => 
    array (
      'name' => 'Neos.Neos :: Frontend :: content with URI suffix',
      'uriPattern' => '{node}.html',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@controller' => 'Frontend\\Node',
        '@action' => 'show',
        '@format' => 'html',
      ),
      'routeParts' => 
      array (
        'node' => 
        array (
          'handler' => 'Neos\\Neos\\Routing\\FrontendNodeRoutePartHandlerInterface',
        ),
      ),
      'appendExceedingArguments' => true,
    ),
    78 => 
    array (
      'name' => 'Neos.Neos :: Frontend :: Dummy wireframe route to enable uri resolution while in wireframe mode.',
      'uriPattern' => '{node}.html',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@controller' => 'Frontend\\Node',
        '@action' => 'showWireframe',
        '@format' => 'html',
      ),
      'routeParts' => 
      array (
        'node' => 
        array (
          'handler' => 'Neos\\Neos\\Routing\\FrontendNodeRoutePartHandlerInterface',
        ),
      ),
      'appendExceedingArguments' => true,
    ),
    79 => 
    array (
      'name' => 'Neos.Seo :: XML Sitemap',
      'uriPattern' => '{node}/sitemap.xml',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@controller' => 'Frontend\\Node',
        '@action' => 'show',
        '@format' => 'xml.sitemap',
      ),
      'routeParts' => 
      array (
        'node' => 
        array (
          'handler' => 'Neos\\Neos\\Routing\\FrontendNodeRoutePartHandlerInterface',
          'options' => 
          array (
            'onlyMatchSiteNodes' => true,
          ),
        ),
      ),
      'appendExceedingArguments' => true,
    ),
    80 => 
    array (
      'name' => 'Neos.Seo :: XML Sitemap',
      'uriPattern' => '{node}sitemap.xml',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@controller' => 'Frontend\\Node',
        '@action' => 'show',
        '@format' => 'xml.sitemap',
      ),
      'routeParts' => 
      array (
        'node' => 
        array (
          'handler' => 'Neos\\Neos\\Routing\\FrontendNodeRoutePartHandlerInterface',
          'options' => 
          array (
            'onlyMatchSiteNodes' => true,
          ),
        ),
      ),
      'appendExceedingArguments' => true,
    ),
    81 => 
    array (
      'name' => 'Neos.Seo :: robots.txt',
      'uriPattern' => '{node}robots.txt',
      'defaults' => 
      array (
        '@package' => 'Neos.Neos',
        '@controller' => 'Frontend\\Node',
        '@action' => 'show',
        '@format' => 'txt.robots',
      ),
      'routeParts' => 
      array (
        'node' => 
        array (
          'handler' => 'Neos\\Neos\\Routing\\FrontendNodeRoutePartHandlerInterface',
          'options' => 
          array (
            'onlyMatchSiteNodes' => true,
          ),
        ),
      ),
      'appendExceedingArguments' => true,
    ),
  ),
  'Policy' => 
  array (
    'roles' => 
    array (
      'Neos.Flow:Everybody' => 
      array (
        'abstract' => true,
        'privileges' => 
        array (
          0 => 
          array (
            'privilegeTarget' => 'Neos.Setup:LoginController',
            'permission' => 'GRANT',
          ),
          1 => 
          array (
            'privilegeTarget' => 'Neos.Neos:PublicWorkspaceAccess',
            'permission' => 'GRANT',
          ),
          2 => 
          array (
            'privilegeTarget' => 'Neos.Media:Thumbnail',
            'permission' => 'GRANT',
          ),
          3 => 
          array (
            'privilegeTarget' => 'Neos.Media.Browser:Widgets',
            'permission' => 'GRANT',
          ),
          4 => 
          array (
            'privilegeTarget' => 'Neos.Neos:PublicFrontendAccess',
            'permission' => 'GRANT',
          ),
          5 => 
          array (
            'privilegeTarget' => 'Neos.Neos:BackendLogin',
            'permission' => 'GRANT',
          ),
          6 => 
          array (
            'privilegeTarget' => 'Neos.Neos:WidgetControllers',
            'permission' => 'GRANT',
          ),
          7 => 
          array (
            'privilegeTarget' => 'Neos.Neos.Ui:BackendLogin',
            'permission' => 'GRANT',
          ),
          8 => 
          array (
            'privilegeTarget' => 'Flowpack.Neos.FrontendLogin:LoginForm',
            'permission' => 'GRANT',
          ),
          9 => 
          array (
            'privilegeTarget' => 'Neos_Demo_RegistrationAccess',
            'permission' => 'GRANT',
          ),
          10 => 
          array (
            'privilegeTarget' => 'Neos_Demo_FlickrAccess',
            'permission' => 'GRANT',
          ),
        ),
      ),
      'Neos.Flow:Anonymous' => 
      array (
        'abstract' => true,
      ),
      'Neos.Flow:AuthenticatedUser' => 
      array (
        'abstract' => true,
      ),
      'Neos.Setup:SetupUser' => 
      array (
        'privileges' => 
        array (
          0 => 
          array (
            'privilegeTarget' => 'Neos.Setup:SetupController',
            'permission' => 'GRANT',
          ),
          1 => 
          array (
            'privilegeTarget' => 'Neos.Setup:WidgetControllers',
            'permission' => 'GRANT',
          ),
        ),
      ),
      'Neos.ContentRepository:InternalWorkspaceAccess' => 
      array (
        'abstract' => true,
        'privileges' => 
        array (
          0 => 
          array (
            'privilegeTarget' => 'Neos.Neos:OtherWorkspacesAccess',
            'permission' => 'GRANT',
          ),
        ),
      ),
      'Neos.ContentRepository:Administrator' => 
      array (
        'abstract' => true,
        'parentRoles' => 
        array (
          0 => 'Neos.ContentRepository:InternalWorkspaceAccess',
        ),
      ),
      'Neos.Neos:AbstractEditor' => 
      array (
        'privileges' => 
        array (
          0 => 
          array (
            'privilegeTarget' => 'Neos.Media.Browser:ManageAssets',
            'permission' => 'GRANT',
          ),
          1 => 
          array (
            'privilegeTarget' => 'Neos.Media.Browser:AssetUsage',
            'permission' => 'GRANT',
          ),
          2 => 
          array (
            'privilegeTarget' => 'Neos.Media.Browser:ManageTags',
            'permission' => 'GRANT',
          ),
          3 => 
          array (
            'privilegeTarget' => 'Neos.Media.Browser:AssetProxyService',
            'permission' => 'GRANT',
          ),
          4 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.GeneralAccess',
            'permission' => 'GRANT',
          ),
          5 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.Module.Content',
            'permission' => 'GRANT',
          ),
          6 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.PersonalWorkspaceReadAccess.NodeConverter',
            'permission' => 'GRANT',
          ),
          7 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.EditContent',
            'permission' => 'GRANT',
          ),
          8 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.PublishOwnWorkspaceContent',
            'permission' => 'GRANT',
          ),
          9 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.DiscardOwnWorkspaceContent',
            'permission' => 'GRANT',
          ),
          10 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.Service.Workspaces.Index',
            'permission' => 'GRANT',
          ),
          11 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.Service.Workspaces.ManageOwnWorkspaces',
            'permission' => 'GRANT',
          ),
          12 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.Module.Management.Workspaces.ManageOwnWorkspaces',
            'permission' => 'GRANT',
          ),
          13 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.CreateWorkspaces',
            'permission' => 'GRANT',
          ),
          14 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.ContentDimensions',
            'permission' => 'GRANT',
          ),
          15 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.DataSource',
            'permission' => 'GRANT',
          ),
          16 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.Module.User',
            'permission' => 'GRANT',
          ),
          17 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.Module.User.UserSettings',
            'permission' => 'GRANT',
          ),
          18 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.Module.User.UserSettings.UpdateOwnSettings',
            'permission' => 'GRANT',
          ),
          19 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.EditUserPreferences',
            'permission' => 'GRANT',
          ),
          20 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.Module.Management',
            'permission' => 'GRANT',
          ),
          21 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.Module.Management.Workspaces',
            'permission' => 'GRANT',
          ),
          22 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.Module.Management.History',
            'permission' => 'GRANT',
          ),
          23 => 
          array (
            'privilegeTarget' => 'Neos.Neos.Ui:Backend.GeneralAccess',
            'permission' => 'GRANT',
          ),
          24 => 
          array (
            'privilegeTarget' => 'Neos.Neos.Ui:Backend.ServiceAccess',
            'permission' => 'GRANT',
          ),
        ),
        'abstract' => true,
        'parentRoles' => 
        array (
          0 => 'Neos.ContentRepository:Administrator',
        ),
      ),
      'Neos.Neos:LivePublisher' => 
      array (
        'privileges' => 
        array (
          0 => 
          array (
            'privilegeTarget' => 'Neos.Media.Browser:ReplaceAssetResource',
            'permission' => 'GRANT',
          ),
          1 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.PublishToLiveWorkspace',
            'permission' => 'GRANT',
          ),
          2 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.PublishAllToLiveWorkspace',
            'permission' => 'GRANT',
          ),
        ),
      ),
      'Neos.Neos:Administrator' => 
      array (
        'privileges' => 
        array (
          0 => 
          array (
            'privilegeTarget' => 'Neos.Media.Browser:ManageAssetCollections',
            'permission' => 'GRANT',
          ),
          1 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.Module.Administration',
            'permission' => 'GRANT',
          ),
          2 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.Module.Administration.Users',
            'permission' => 'GRANT',
          ),
          3 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.Module.Administration.Packages',
            'permission' => 'GRANT',
          ),
          4 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.Module.Management.Workspaces.ManageInternalWorkspaces',
            'permission' => 'GRANT',
          ),
          5 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.Module.Management.Workspaces.ManageAllPrivateWorkspaces',
            'permission' => 'GRANT',
          ),
          6 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.Module.Administration.Sites',
            'permission' => 'GRANT',
          ),
          7 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.Module.Administration.Configuration',
            'permission' => 'GRANT',
          ),
          8 => 
          array (
            'privilegeTarget' => 'Neos.Neos:Backend.Module.Administration.Dimensions',
            'permission' => 'GRANT',
          ),
        ),
        'parentRoles' => 
        array (
          0 => 'Neos.Neos:Editor',
        ),
      ),
      'Neos.Neos:RestrictedEditor' => 
      array (
        'parentRoles' => 
        array (
          0 => 'Neos.Neos:AbstractEditor',
        ),
      ),
      'Neos.Neos:Editor' => 
      array (
        'parentRoles' => 
        array (
          0 => 'Neos.Neos:AbstractEditor',
          1 => 'Neos.Neos:LivePublisher',
        ),
        'privileges' => 
        array (
          0 => 
          array (
            'privilegeTarget' => 'Neos.Demo:MembersArea',
            'permission' => 'GRANT',
          ),
        ),
      ),
      'Flowpack.Neos.FrontendLogin:User' => 
      array (
        'privileges' => 
        array (
          0 => 
          array (
            'privilegeTarget' => 'Neos.Demo:MembersArea',
            'permission' => 'GRANT',
          ),
        ),
      ),
    ),
    'privilegeTargets' => 
    array (
      'Neos\\Flow\\Security\\Authorization\\Privilege\\Method\\MethodPrivilege' => 
      array (
        'Neos.Setup:LoginController' => 
        array (
          'matcher' => 'method(Neos\\Setup\\Controller\\LoginController->(login|authenticate|generateNewPassword)Action())',
        ),
        'Neos.Setup:SetupController' => 
        array (
          'matcher' => 'method(Neos\\Setup\\Controller\\SetupController->indexAction()) || method(Neos\\Setup\\Controller\\LoginController->logoutAction())',
        ),
        'Neos.Setup:WidgetControllers' => 
        array (
          'matcher' => 'method(public Neos\\Setup\\ViewHelpers\\Widget\\Controller\\.+Controller->.+Action())',
        ),
        'Neos.Neos:PublicWorkspaceAccess' => 
        array (
          'matcher' => 'method(Neos\\ContentRepository\\Domain\\Service\\Context->validateWorkspace()) && evaluate(this.workspace.publicWorkspace === true)',
        ),
        'Neos.Neos:OtherWorkspacesAccess' => 
        array (
          'matcher' => 'method(Neos\\ContentRepository\\Domain\\Service\\Context->validateWorkspace()) && evaluate(this.workspace.publicWorkspace === false) && evaluate(this.workspace.personalWorkspace === false)',
        ),
        'Neos.Media:Thumbnail' => 
        array (
          'matcher' => 'method(Neos\\Media\\Controller\\ThumbnailController->thumbnailAction())',
        ),
        'Neos.Media.Browser:Widgets' => 
        array (
          'matcher' => 'method(Neos\\Media\\Browser\\ViewHelpers\\Controller\\(Paginate)Controller->(index)Action())',
        ),
        'Neos.Media.Browser:ManageAssets' => 
        array (
          'matcher' => 'method(Neos\\Media\\Browser\\Controller\\(Asset|Image)Controller->(index|new|show|edit|update|initializeCreate|create|replaceAssetResource|updateAssetResource|initializeUpload|upload|tagAsset|delete|createTag|editTag|updateTag|deleteTag|addAssetToCollection|relatedNodes|variants)Action()) || method(Neos\\Media\\Browser\\Controller\\ImageVariantController->(update)Action())',
        ),
        'Neos.Media.Browser:AssetUsage' => 
        array (
          'matcher' => 'method(Neos\\Media\\Browser\\Controller\\UsageController->relatedNodesAction())',
        ),
        'Neos.Media.Browser:ManageTags' => 
        array (
          'matcher' => 'method(Neos\\Media\\Browser\\Controller\\TagController->(create|edit|update|delete)Action())',
        ),
        'Neos.Media.Browser:AssetProxyService' => 
        array (
          'matcher' => 'method(Neos\\Media\\Browser\\Controller\\AssetProxyController->(index|import)Action())',
        ),
        'Neos.Media.Browser:ManageAssetCollections' => 
        array (
          'matcher' => 'method(Neos\\Media\\Browser\\Controller\\(Asset|Image)Controller->(createAssetCollection|editAssetCollection|updateAssetCollection|deleteAssetCollection)Action()) || method(Neos\\Media\\Browser\\Controller\\AssetCollectionController->(create|edit|update|delete)Action())',
        ),
        'Neos.Media.Browser:ReplaceAssetResource' => 
        array (
          'matcher' => 'method(Neos\\Media\\Browser\\Controller\\(Asset|Image)Controller->(replaceAssetResource|updateAssetResource)Action())',
        ),
        'Neos.Neos:AllControllerActions' => 
        array (
          'matcher' => 'within(Neos\\Flow\\Mvc\\Controller\\AbstractController) && method(public .*->(?!initialize).*Action())',
        ),
        'Neos.Neos:WidgetControllers' => 
        array (
          'matcher' => 'method(Neos\\FluidAdaptor\\ViewHelpers\\Widget\\Controller\\AutocompleteController->(index|autocomplete)Action()) || method(Neos\\FluidAdaptor\\ViewHelpers\\Widget\\Controller\\PaginateController->indexAction()) || method(Neos\\ContentRepository\\ViewHelpers\\Widget\\Controller\\PaginateController->indexAction()) || method(Neos\\Neos\\ViewHelpers\\Widget\\Controller\\LinkRepositoryController->(index|search|lookup)Action())',
        ),
        'Neos.Neos:PublicFrontendAccess' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Controller\\Frontend\\NodeController->showAction())',
        ),
        'Neos.Neos:BackendLogin' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Controller\\LoginController->(index|tokenLogin|authenticate)Action()) || method(Neos\\Flow\\Security\\Authentication\\Controller\\AbstractAuthenticationController->authenticateAction())',
        ),
        'Neos.Neos:Backend.GeneralAccess' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Controller\\Backend\\BackendController->(index|switchSite|xliffAsJson)Action()) || method(Neos\\Neos\\Controller\\Backend\\ModuleController->indexAction()) || method(Neos\\Neos\\Controller\\LoginController->logoutAction()) || method(Neos\\Flow\\Security\\Authentication\\Controller\\AbstractAuthenticationController->logoutAction()) || method(Neos\\Neos\\Controller\\Module\\AbstractModuleController->indexAction()) || method(Neos\\Neos\\Service\\Controller\\AbstractServiceController->errorAction())',
        ),
        'Neos.Neos:Backend.Module.Content' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Controller\\Backend\\SchemaController->(nodeTypeSchema|vieSchema)Action()) || method(Neos\\Neos\\Controller\\Backend\\MenuController->indexAction()) || method(Neos\\Neos\\Controller\\Backend\\SettingsController->editPreviewAction())',
        ),
        'Neos.Neos:Backend.PersonalWorkspaceReadAccess.NodeConverter' => 
        array (
          'matcher' => 'method(Neos\\Neos\\TypeConverter\\NodeConverter->prepareContextProperties(workspaceName === current.userInformation.personalWorkspaceName))',
        ),
        'Neos.Neos:Backend.OtherUsersPersonalWorkspaceAccess' => 
        array (
          'matcher' => 'method(Neos\\ContentRepository\\Domain\\Service\\Context->validateWorkspace()) && evaluate(this.workspace.owner !== current.userInformation.backendUser, this.workspace.personalWorkspace === true)',
        ),
        'Neos.Neos:Backend.EditContent' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Service\\Controller\\NodeController->(show|getPrimaryChildNode|getChildNodesForTree|filterChildNodesForTree|getChildNodes|getChildNodesFromParent|create|createAndRender|createNodeForTheTree|move|moveBefore|moveAfter|moveInto|moveAndRender|copy|copyBefore|copyAfter|copyInto|copyAndRender|update|updateAndRender|delete|searchPage|error)Action()) || method(Neos\\Neos\\Controller\\Backend\\ContentController->(uploadAsset|assetsWithMetadata|imageWithMetadata|pluginViews|createImageVariant|masterPlugins|error)Action()) || method(Neos\\Neos\\Controller\\Service\\AssetProxiesController->(index|show|import|error)Action()) || method(Neos\\Neos\\Controller\\Service\\AssetsController->(index|show|error)Action()) || method(Neos\\Neos\\Controller\\Service\\NodesController->(index|show|create|error)Action())',
        ),
        'Neos.Neos:Backend.PublishToLiveWorkspace' => 
        array (
          'matcher' => 'method(Neos\\ContentRepository\\Domain\\Model\\Workspace->(publish|publishNode|publishNodes)(targetWorkspace.name === "live"))',
        ),
        'Neos.Neos:Backend.PublishAllToLiveWorkspace' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Controller\\Module\\Management\\WorkspacesController->publishWorkspaceAction(workspace.baseWorkspace.name === "live"))',
        ),
        'Neos.Neos:Backend.PublishOwnWorkspaceContent' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Service\\Controller\\WorkspaceController->(publishNode|publishNodes|error)Action()) || method(Neos\\Neos\\Service\\Controller\\WorkspaceController->publishAllAction(workspaceName = current.userInformation.personalWorkspaceName)) || method(Neos\\Neos\\Service\\Controller\\WorkspaceController->getWorkspaceWideUnpublishedNodesAction(workspace.name = current.userInformation.personalWorkspaceName))',
        ),
        'Neos.Neos:Backend.DiscardOwnWorkspaceContent' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Service\\Controller\\WorkspaceController->(discardNode|discardNodes|error)Action()) || method(Neos\\Neos\\Service\\Controller\\WorkspaceController->discardAllAction(workspace.name === current.userInformation.personalWorkspaceName))',
        ),
        'Neos.Neos:Backend.CreateWorkspaces' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Controller\\Service\\WorkspacesController->(new|create)Action()) || method(Neos\\Neos\\Controller\\Module\\Management\\WorkspacesController->(create|new)Action())',
        ),
        'Neos.Neos:Backend.Module.Management.Workspaces.ManageOwnWorkspaces' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Controller\\Module\\Management\\WorkspacesController->(publishWorkspace|discardWorkspace|edit|update|delete)Action(workspace.owner === current.userInformation.backendUser))',
        ),
        'Neos.Neos:Backend.Module.Management.Workspaces.ManageInternalWorkspaces' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Controller\\Module\\Management\\WorkspacesController->(publishWorkspace|discardWorkspace|edit|update|delete)Action(workspace.owner === null))',
        ),
        'Neos.Neos:Backend.Module.Management.Workspaces.ManageAllPrivateWorkspaces' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Controller\\Module\\Management\\WorkspacesController->(publishWorkspace|discardWorkspace|edit|update|delete)Action()) && evaluate(this.workspace.owner !== current.userInformation.backendUser, this.workspace.personalWorkspace === false)',
        ),
        'Neos.Neos:Backend.Service.Workspaces.Index' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Controller\\Service\\WorkspacesController->(index|error|show)Action())',
        ),
        'Neos.Neos:Backend.Service.Workspaces.ManageOwnWorkspaces' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Controller\\Service\\WorkspacesController->(update|delete)Action(workspace.owner === current.userInformation.backendUser))',
        ),
        'Neos.Neos:Backend.Module.User.UserSettings.UpdateOwnSettings' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Controller\\Module\\User\\UserSettingsController->updateAction(user === current.userInformation.backendUser))',
        ),
        'Neos.Neos:Backend.EditUserPreferences' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Service\\Controller\\UserPreferenceController->(index|update|error)Action())',
        ),
        'Neos.Neos:Backend.ContentDimensions' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Controller\\Service\\ContentDimensionsController->(index|show|error)Action())',
        ),
        'Neos.Neos:Backend.DataSource' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Service\\Controller\\DataSourceController->(index|error)Action())',
        ),
        'Neos.Neos.Ui:BackendLogin' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Ui\\Controller\\LoginController->(index|authenticate)Action()) || method(Neos\\Flow\\Security\\Authentication\\Controller\\AbstractAuthenticationController->authenticateAction())',
        ),
        'Neos.Neos.Ui:Backend.GeneralAccess' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Ui\\Controller\\BackendController->.*())',
        ),
        'Neos.Neos.Ui:Backend.ServiceAccess' => 
        array (
          'matcher' => 'method(Neos\\Neos\\Ui\\Controller\\BackendServiceController->.*())',
        ),
        'Flowpack.Neos.FrontendLogin:LoginForm' => 
        array (
          'matcher' => 'method(Neos\\Flow\\Security\\Authentication\\Controller\\AbstractAuthenticationController->(?!initialize).*Action()) || method(Flowpack\\Neos\\FrontendLogin\\Controller\\AuthenticationController->(?!initialize).*Action())',
        ),
        'Neos_Demo_RegistrationAccess' => 
        array (
          'matcher' => 'method(Neos\\Demo\\Controller\\RegistrationController->(index|newAccount|createAccount|createTemporaryAccount)Action())',
        ),
        'Neos_Demo_FlickrAccess' => 
        array (
          'matcher' => 'method(Neos\\Demo\\Controller\\FlickrController->(tagStream|userStream)Action())',
        ),
      ),
      'Neos\\Neos\\Security\\Authorization\\Privilege\\ModulePrivilege' => 
      array (
        'Neos.Neos:Backend.Module.User' => 
        array (
          'matcher' => 'user',
        ),
        'Neos.Neos:Backend.Module.User.UserSettings' => 
        array (
          'matcher' => 'user/usersettings',
        ),
        'Neos.Neos:Backend.Module.Management' => 
        array (
          'matcher' => 'management',
        ),
        'Neos.Neos:Backend.Module.Management.Workspaces' => 
        array (
          'matcher' => 'management/workspaces',
        ),
        'Neos.Neos:Backend.Module.Management.History' => 
        array (
          'matcher' => 'management/history',
        ),
        'Neos.Neos:Backend.Module.Administration' => 
        array (
          'matcher' => 'administration',
        ),
        'Neos.Neos:Backend.Module.Administration.Users' => 
        array (
          'matcher' => 'administration/users',
        ),
        'Neos.Neos:Backend.Module.Administration.Packages' => 
        array (
          'matcher' => 'administration/packages',
        ),
        'Neos.Neos:Backend.Module.Administration.Sites' => 
        array (
          'matcher' => 'administration/sites',
        ),
        'Neos.Neos:Backend.Module.Administration.Configuration' => 
        array (
          'matcher' => 'administration/configuration',
        ),
        'Neos.Neos:Backend.Module.Administration.Dimensions' => 
        array (
          'matcher' => 'administration/dimensions',
        ),
      ),
      'Neos\\ContentRepository\\Security\\Authorization\\Privilege\\Node\\ReadNodePrivilege' => 
      array (
        'Neos.Demo:MembersArea' => 
        array (
          'matcher' => 'isDescendantNodeOf("fee74676-c42f-89da-208e-1741a66525d4")',
        ),
      ),
    ),
  ),
  'Views' => 
  array (
    0 => 
    array (
      'requestFilter' => 'isFormat("html") && isPackage("Neos.Media.Browser")',
      'options' => 
      array (
        'templatePathAndFilenamePattern' => '@templateRoot/@subpackage/Asset/@action.@format',
        'partialRootPaths' => 
        array (
          'Neos.Neos' => 'resource://Neos.Neos/Private/Partials',
          'Neos.Media.Browser' => 'resource://Neos.Media.Browser/Private/Partials',
        ),
      ),
    ),
    1 => 
    array (
      'requestFilter' => 'parentRequest.isPackage("Neos.Neos") && isFormat("html") && isPackage("Neos.Media.Browser")',
      'options' => 
      array (
        'layoutRootPaths' => 
        array (
          'Neos.Media.Browser' => 'resource://Neos.Media.Browser/Private/Layouts/Module',
        ),
        'partialRootPaths' => 
        array (
          'Neos.Neos' => 'resource://Neos.Neos/Private/Partials',
          'Neos.Media.Browser' => 'resource://Neos.Media.Browser/Private/Partials',
        ),
      ),
    ),
  ),
  'NodeTypes' => 
  array (
    'unstructured' => 
    array (
      'abstract' => false,
      'constraints' => 
      array (
        'nodeTypes' => 
        array (
          '*' => true,
        ),
      ),
    ),
    'Neos.Neos:Node' => 
    array (
      'label' => '${String.cropAtWord(String.trim(String.stripTags(String.pregReplace(q(node).property(\'title\') || q(node).property(\'text\') || ((I18n.translate(node.nodeType.label) || node.nodeType.name) + (node.autoCreated ? \' (\' + node.name + \')\' : \'\')), \'/<br\\W*?\\/?>|\\x{00a0}|[^[:print:]]|\\s+/u\', \' \'))), 100, \'...\')}',
      'abstract' => true,
      'options' => 
      array (
        'fusion' => 
        array (
          'prototypeGenerator' => NULL,
        ),
      ),
      'ui' => 
      array (
        'inspector' => 
        array (
          'tabs' => 
          array (
            'default' => 
            array (
              'label' => 'i18n',
              'position' => 10,
              'icon' => 'icon-pencil',
            ),
            'meta' => 
            array (
              'label' => 'i18n',
              'position' => 20,
              'icon' => 'icon-cog',
            ),
          ),
          'groups' => 
          array (
            'type' => 
            array (
              'label' => 'i18n',
              'tab' => 'meta',
              'icon' => 'icon-exchange',
              'position' => 990,
            ),
            'nodeInfo' => 
            array (
              'label' => 'i18n',
              'icon' => 'icon-info',
              'tab' => 'meta',
              'position' => 1000,
              'collapsed' => true,
            ),
          ),
          'views' => 
          array (
            'nodeInfo' => 
            array (
              'label' => 'i18n',
              'group' => 'nodeInfo',
              'view' => 'Neos.Neos/Inspector/Views/NodeInfoView',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        '_removed' => 
        array (
          'type' => 'boolean',
        ),
        '_creationDateTime' => 
        array (
          'type' => 'DateTime',
        ),
        '_lastModificationDateTime' => 
        array (
          'type' => 'DateTime',
        ),
        '_lastPublicationDateTime' => 
        array (
          'type' => 'DateTime',
        ),
        '_path' => 
        array (
          'type' => 'string',
        ),
        '_name' => 
        array (
          'type' => 'string',
        ),
        '_nodeType' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'type',
              'position' => 100,
              'editor' => 'Neos.Neos/Inspector/Editors/NodeTypeEditor',
              'editorOptions' => 
              array (
                'placeholder' => 'Loading ...',
                'baseNodeType' => '',
              ),
            ),
          ),
        ),
      ),
    ),
    'Neos.Neos:Hidable' => 
    array (
      'abstract' => true,
      'ui' => 
      array (
        'inspector' => 
        array (
          'groups' => 
          array (
            'visibility' => 
            array (
              'label' => 'Neos.Neos:Inspector:groups.visibility',
              'icon' => 'icon-eye',
              'position' => 100,
              'tab' => 'meta',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        '_hidden' => 
        array (
          'type' => 'boolean',
          'ui' => 
          array (
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'visibility',
              'position' => 30,
            ),
          ),
        ),
      ),
    ),
    'Neos.Neos:Timable' => 
    array (
      'abstract' => true,
      'ui' => 
      array (
        'inspector' => 
        array (
          'groups' => 
          array (
            'visibility' => 
            array (
              'label' => 'Neos.Neos:Inspector:groups.visibility',
              'icon' => 'icon-eye',
              'position' => 100,
              'tab' => 'meta',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        '_hiddenBeforeDateTime' => 
        array (
          'type' => 'DateTime',
          'ui' => 
          array (
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'visibility',
              'position' => 10,
              'editorOptions' => 
              array (
                'format' => 'd-m-Y H:i',
              ),
            ),
          ),
          'validation' => 
          array (
            'Neos.Neos/Validation/DateTimeValidator' => 
            array (
            ),
          ),
        ),
        '_hiddenAfterDateTime' => 
        array (
          'type' => 'DateTime',
          'ui' => 
          array (
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'visibility',
              'position' => 20,
              'editorOptions' => 
              array (
                'format' => 'd-m-Y H:i',
              ),
            ),
          ),
          'validation' => 
          array (
            'Neos.Neos/Validation/DateTimeValidator' => 
            array (
            ),
          ),
        ),
      ),
    ),
    'Neos.Neos:Document' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Node' => true,
        'Neos.Neos:Hidable' => true,
        'Neos.Neos:Timable' => true,
        'Neos.Seo:TitleTagMixin' => true,
        'Neos.Seo:SeoMetaTagsMixin' => true,
        'Neos.Seo:TwitterCardMixin' => true,
        'Neos.Seo:CanonicalLinkMixin' => true,
        'Neos.Seo:OpenGraphMixin' => true,
        'Neos.Seo:XmlSitemapMixin' => true,
      ),
      'abstract' => true,
      'aggregate' => true,
      'constraints' => 
      array (
        'nodeTypes' => 
        array (
          '*' => false,
          'Neos.Neos:Document' => true,
        ),
      ),
      'options' => 
      array (
        'fusion' => 
        array (
          'prototypeGenerator' => 'Flowpack\\FusionBP\\Generators\\DefaultDocumentPrototypeGenerator',
        ),
        'nodeCreationHandlers' => 
        array (
          'documentTitle' => 
          array (
            'nodeCreationHandler' => 'Neos\\Neos\\Ui\\NodeCreationHandler\\DocumentTitleNodeCreationHandler',
          ),
        ),
      ),
      'ui' => 
      array (
        'label' => 'Document',
        'group' => 'general',
        'search' => 
        array (
          'searchCategory' => 'Documents',
        ),
        'inspector' => 
        array (
          'groups' => 
          array (
            'document' => 
            array (
              'label' => 'i18n',
              'position' => 10,
              'icon' => 'icon-file',
            ),
          ),
          'tabs' => 
          array (
            'seo' => 
            array (
              'label' => 'Neos.Seo:NodeTypes.Document:tabs.seo',
              'position' => 30,
              'icon' => 'icon-bullseye',
            ),
          ),
        ),
        'creationDialog' => 
        array (
          'elements' => 
          array (
            'title' => 
            array (
              'type' => 'string',
              'ui' => 
              array (
                'label' => 'i18n',
                'editor' => 'Neos.Neos/Inspector/Editors/TextFieldEditor',
              ),
              'validation' => 
              array (
                'Neos.Neos/Validation/NotEmptyValidator' => 
                array (
                ),
              ),
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        '_nodeType' => 
        array (
          'ui' => 
          array (
            'inspector' => 
            array (
              'editorOptions' => 
              array (
                'baseNodeType' => 'Neos.Neos:Document',
              ),
            ),
          ),
        ),
        'title' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadPageIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'document',
            ),
          ),
          'validation' => 
          array (
            'Neos.Neos/Validation/NotEmptyValidator' => 
            array (
            ),
            'Neos.Neos/Validation/StringLengthValidator' => 
            array (
              'minimum' => 1,
              'maximum' => 255,
            ),
          ),
        ),
        'uriPathSegment' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadPageIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'document',
            ),
          ),
          'validation' => 
          array (
            'Neos.Neos/Validation/NotEmptyValidator' => 
            array (
            ),
            'Neos.Neos/Validation/StringLengthValidator' => 
            array (
              'minimum' => 1,
              'maximum' => 255,
            ),
            'Neos.Neos/Validation/RegularExpressionValidator' => 
            array (
              'regularExpression' => '/^[a-z0-9\\-]+$/i',
            ),
          ),
        ),
        '_hidden' => 
        array (
          'ui' => 
          array (
            'reloadPageIfChanged' => true,
          ),
        ),
        '_hiddenInIndex' => 
        array (
          'type' => 'boolean',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadPageIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'visibility',
              'position' => 40,
            ),
          ),
        ),
      ),
    ),
    'Neos.Neos:Shortcut' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Document' => true,
        'Neos.Seo:TitleTagMixin' => false,
        'Neos.Seo:SeoMetaTagsMixin' => false,
        'Neos.Seo:NoindexMixin' => true,
        'Neos.Seo:TwitterCardMixin' => false,
        'Neos.Seo:CanonicalLinkMixin' => false,
        'Neos.Seo:OpenGraphMixin' => false,
        'Neos.Seo:XmlSitemapMixin' => false,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-share',
        'position' => 200,
        'inspector' => 
        array (
          'groups' => 
          array (
            'document' => 
            array (
              'label' => 'i18n',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'targetMode' => 
        array (
          'type' => 'string',
          'defaultValue' => 'firstChildNode',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadPageIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'document',
              'editor' => 'Neos.Neos/Inspector/Editors/SelectBoxEditor',
              'editorOptions' => 
              array (
                'values' => 
                array (
                  'firstChildNode' => 
                  array (
                    'label' => 'i18n',
                  ),
                  'parentNode' => 
                  array (
                    'label' => 'i18n',
                  ),
                  'selectedTarget' => 
                  array (
                    'label' => 'i18n',
                  ),
                ),
              ),
              'editorListeners' => 
              array (
                'removeTargetIfNotUsed' => 
                array (
                  'property' => 'target',
                  'handler' => 'Neos.Neos/Inspector/Handlers/ShortcutHandler',
                ),
              ),
            ),
          ),
        ),
        'target' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadPageIfChanged' => true,
            'inspector' => 
            array (
              'hidden' => 'ClientEval:node.properties.targetMode === "selectedTarget" ? false : true',
              'group' => 'document',
              'editor' => 'Neos.Neos/Inspector/Editors/LinkEditor',
              'editorListeners' => 
              array (
                'setTargetModeIfNotEmpty' => 
                array (
                  'property' => 'targetMode',
                  'handler' => 'Neos.Neos/Inspector/Handlers/ShortcutHandler',
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    'Neos.Neos:Plugin' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
      ),
      'abstract' => true,
      'options' => 
      array (
        'fusion' => 
        array (
          'prototypeGenerator' => 'Neos\\Neos\\Domain\\Service\\DefaultPluginPrototypeGenerator',
        ),
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'group' => 'plugins',
        'icon' => 'icon-puzzle-piece',
        'inspector' => 
        array (
          'groups' => 
          array (
            'pluginSettings' => 
            array (
              'label' => 'i18n',
              'icon' => 'icon-sliders',
            ),
          ),
        ),
      ),
      'postprocessors' => 
      array (
        'PluginPostprocessor' => 
        array (
          'postprocessor' => 'Neos\\Neos\\NodeTypePostprocessor\\PluginNodeTypePostprocessor',
        ),
      ),
    ),
    'Neos.Neos:PluginView' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'group' => 'plugins',
        'icon' => 'icon-puzzle-piece',
        'position' => 100,
        'inspector' => 
        array (
          'groups' => 
          array (
            'pluginViews' => 
            array (
              'label' => 'i18n',
              'position' => 100,
              'icon' => 'icon-puzzle-piece',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'plugin' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'pluginViews',
              'position' => 10,
              'editor' => 'Neos.Neos/Inspector/Editors/MasterPluginEditor',
            ),
          ),
        ),
        'view' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'pluginViews',
              'position' => 20,
              'editor' => 'Neos.Neos/Inspector/Editors/PluginViewEditor',
            ),
          ),
        ),
      ),
    ),
    'Neos.Neos:Content' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Node' => true,
        'Neos.Neos:Hidable' => true,
        'Neos.Neos:Timable' => true,
      ),
      'abstract' => true,
      'constraints' => 
      array (
        'nodeTypes' => 
        array (
          '*' => false,
        ),
      ),
      'options' => 
      array (
        'fusion' => 
        array (
          'prototypeGenerator' => 'Flowpack\\FusionBP\\Generators\\DefaultContentPrototypeGenerator',
        ),
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-square-o',
        'group' => 'general',
        'search' => 
        array (
          'searchCategory' => 'Content',
        ),
        'inspector' => 
        array (
          'groups' => 
          array (
            'type' => 
            array (
              'label' => 'i18n',
              'position' => 100,
              'tab' => 'meta',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        '_nodeType' => 
        array (
          'ui' => 
          array (
            'inspector' => 
            array (
              'editorOptions' => 
              array (
                'baseNodeType' => 'Neos.Neos:Content',
              ),
            ),
          ),
        ),
      ),
    ),
    'Neos.Neos:ContentCollection' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Node' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-folder-open-alt',
        'inlineEditable' => true,
      ),
      'constraints' => 
      array (
        'nodeTypes' => 
        array (
          'Neos.Neos:Document' => false,
          '*' => true,
        ),
      ),
    ),
    'Neos.Neos:FallbackNode' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Node' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-remove-sign',
      ),
      'properties' => 
      array (
        '_nodeType' => 
        array (
          'ui' => 
          array (
            'inspector' => 
            array (
              'editorOptions' => 
              array (
                'baseNodeType' => 'Neos.Neos:Content',
              ),
            ),
          ),
        ),
      ),
    ),
    'Neos.Seo:CanonicalLinkMixin' => 
    array (
      'abstract' => true,
      'ui' => 
      array (
        'inspector' => 
        array (
          'groups' => 
          array (
            'canonicallink' => 
            array (
              'label' => 'i18n',
              'icon' => 'icon-link',
              'position' => 200,
              'tab' => 'seo',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'canonicalLink' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'canonicallink',
              'position' => 30,
              'editor' => 'Neos.Neos/Inspector/Editors/LinkEditor',
              'editorOptions' => 
              array (
                'placeholder' => 'i18n',
                'assets' => false,
                'nodeTypes' => 
                array (
                  0 => 'Neos.Neos:Document',
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    'Neos.Seo:NoindexMixin' => 
    array (
      'abstract' => true,
      'properties' => 
      array (
        'metaRobotsNoindex' => 
        array (
          'defaultValue' => true,
          'ui' => 
          array (
            'inspector' => 
            array (
              'editorOptions' => 
              array (
                'disabled' => true,
              ),
            ),
          ),
        ),
      ),
    ),
    'Neos.Seo:OpenGraphMixin' => 
    array (
      'abstract' => true,
      'ui' => 
      array (
        'inspector' => 
        array (
          'groups' => 
          array (
            'openGraph' => 
            array (
              'label' => 'i18n',
              'icon' => 'icon-share-alt',
              'position' => 300,
              'tab' => 'seo',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'openGraphType' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'openGraph',
              'editor' => 'Neos.Neos/Inspector/Editors/SelectBoxEditor',
              'editorOptions' => 
              array (
                'allowEmpty' => true,
                'placeholder' => 'i18n',
                'values' => 
                array (
                  'website' => 
                  array (
                    'label' => 'i18n',
                    'icon' => 'icon-file',
                  ),
                  'article' => 
                  array (
                    'label' => 'i18n',
                    'icon' => 'icon-newspaper',
                  ),
                ),
              ),
            ),
          ),
        ),
        'openGraphTitle' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'openGraph',
              'editor' => 'Neos.Neos/Inspector/Editors/TextAreaEditor',
              'editorOptions' => 
              array (
                'placeholder' => 'ClientEval: node.properties.titleOverride || node.properties.title',
              ),
            ),
          ),
          'validation' => 
          array (
            'Neos.Neos/Validation/StringLengthValidator' => 
            array (
              'maximum' => 60,
            ),
          ),
        ),
        'openGraphDescription' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'openGraph',
              'editor' => 'Neos.Neos/Inspector/Editors/TextAreaEditor',
              'editorOptions' => 
              array (
                'placeholder' => 'ClientEval: node.properties.metaDescription || "Neos.Seo:NodeTypes.OpenGraphMixin:properties.openGraphDescription.textAreaEditor.placeholder"',
              ),
            ),
          ),
          'validation' => 
          array (
            'Neos.Neos/Validation/StringLengthValidator' => 
            array (
              'maximum' => 200,
            ),
          ),
        ),
        'openGraphImage' => 
        array (
          'type' => 'Neos\\Media\\Domain\\Model\\ImageInterface',
          'ui' => 
          array (
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'openGraph',
              'editorOptions' => 
              array (
                'features' => 
                array (
                  'crop' => true,
                ),
                'crop' => 
                array (
                  'aspectRatio' => 
                  array (
                    'options' => 
                    array (
                      'square' => 
                      array (
                        'width' => 1080,
                        'height' => 1080,
                      ),
                      'twoOne' => 
                      array (
                        'width' => 1200,
                        'height' => 628,
                        'label' => 'Landscape',
                      ),
                      'fourFive' => NULL,
                      'fiveSeven' => NULL,
                      'twoThree' => NULL,
                      'fourThree' => NULL,
                      'sixteenNine' => NULL,
                    ),
                    'enableOriginal' => false,
                    'allowCustom' => false,
                    'defaultOption' => 'twoOne',
                    'forceCrop' => true,
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    'Neos.Seo:SeoMetaTagsMixin' => 
    array (
      'abstract' => true,
      'ui' => 
      array (
        'inspector' => 
        array (
          'groups' => 
          array (
            'seometa' => 
            array (
              'label' => 'i18n',
              'icon' => 'icon-tags',
              'position' => 100,
              'tab' => 'seo',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'metaDescription' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'seometa',
              'position' => 10,
              'editor' => 'Neos.Neos/Inspector/Editors/TextAreaEditor',
              'editorOptions' => 
              array (
                'placeholder' => 'i18n',
              ),
            ),
          ),
          'validation' => 
          array (
            'Neos.Neos/Validation/StringLengthValidator' => 
            array (
              'maximum' => 156,
            ),
          ),
        ),
        'metaKeywords' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'seometa',
              'position' => 20,
              'editor' => 'Neos.Neos/Inspector/Editors/TextAreaEditor',
              'editorOptions' => 
              array (
                'placeholder' => 'i18n',
              ),
            ),
          ),
          'validation' => 
          array (
            'Neos.Neos/Validation/StringLengthValidator' => 
            array (
              'maximum' => 255,
            ),
          ),
        ),
        'metaRobotsNoindex' => 
        array (
          'type' => 'boolean',
          'ui' => 
          array (
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'seometa',
              'position' => 30,
            ),
          ),
        ),
        'metaRobotsNofollow' => 
        array (
          'type' => 'boolean',
          'ui' => 
          array (
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'seometa',
              'position' => 40,
            ),
          ),
        ),
      ),
    ),
    'Neos.Seo:TitleTagMixin' => 
    array (
      'abstract' => true,
      'properties' => 
      array (
        'titleOverride' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'document',
              'position' => 10000,
              'editor' => 'Neos.Neos/Inspector/Editors/TextAreaEditor',
              'editorOptions' => 
              array (
                'placeholder' => 'i18n',
              ),
            ),
          ),
          'validation' => 
          array (
            'Neos.Neos/Validation/StringLengthValidator' => 
            array (
              'maximum' => 60,
            ),
          ),
        ),
      ),
    ),
    'Neos.Seo:TwitterCardMixin' => 
    array (
      'abstract' => true,
      'ui' => 
      array (
        'inspector' => 
        array (
          'groups' => 
          array (
            'twittercard' => 
            array (
              'label' => 'i18n',
              'icon' => 'icon-twitter',
              'position' => 400,
              'tab' => 'seo',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'twitterCardType' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'twittercard',
              'editor' => 'Neos.Neos/Inspector/Editors/SelectBoxEditor',
              'editorOptions' => 
              array (
                'allowEmpty' => true,
                'placeholder' => 'i18n',
                'values' => 
                array (
                  'summary' => 
                  array (
                    'label' => 'i18n',
                    'icon' => 'icon-square',
                  ),
                  'summary_large_image' => 
                  array (
                    'label' => 'i18n',
                    'icon' => 'icon-image',
                  ),
                ),
              ),
            ),
          ),
        ),
        'twitterCardCreator' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'twittercard',
              'editorOptions' => 
              array (
                'placeholder' => 'i18n',
              ),
            ),
          ),
          'validation' => 
          array (
            'Neos.Neos/Validation/RegularExpressionValidator' => 
            array (
              'regularExpression' => '/^@[a-z0-9_]{1,15}$/i',
            ),
          ),
        ),
        'twitterCardTitle' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'twittercard',
              'editor' => 'Neos.Neos/Inspector/Editors/TextAreaEditor',
              'editorOptions' => 
              array (
                'placeholder' => 'ClientEval: node.properties.openGraphTitle || node.properties.titleOverride || node.properties.title',
              ),
            ),
          ),
          'validation' => 
          array (
            'Neos.Neos/Validation/StringLengthValidator' => 
            array (
              'maximum' => 70,
            ),
          ),
        ),
        'twitterCardDescription' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'twittercard',
              'editor' => 'Neos.Neos/Inspector/Editors/TextAreaEditor',
              'editorOptions' => 
              array (
                'placeholder' => 'ClientEval: node.properties.openGraphDescription || node.properties.metaDescription || "Neos.Seo:NodeTypes.TwitterCardMixin:properties.twitterCardDescription.textAreaEditor.placeholder"',
              ),
            ),
          ),
          'validation' => 
          array (
            'Neos.Neos/Validation/StringLengthValidator' => 
            array (
              'maximum' => 200,
            ),
          ),
        ),
        'twitterCardImage' => 
        array (
          'type' => 'Neos\\Media\\Domain\\Model\\ImageInterface',
          'ui' => 
          array (
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'twittercard',
              'editorOptions' => 
              array (
                'features' => 
                array (
                  'crop' => true,
                ),
                'crop' => 
                array (
                  'aspectRatio' => 
                  array (
                    'options' => 
                    array (
                      'twoOne' => 
                      array (
                        'width' => 1200,
                        'height' => 600,
                        'label' => 'Summary Card Large Image',
                      ),
                      'square' => 
                      array (
                        'width' => 600,
                        'height' => 600,
                        'label' => 'Summary Card',
                      ),
                      'fourFive' => NULL,
                      'fiveSeven' => NULL,
                      'twoThree' => NULL,
                      'fourThree' => NULL,
                      'sixteenNine' => NULL,
                    ),
                    'enableOriginal' => false,
                    'allowCustom' => false,
                    'defaultOption' => 'ClientEval: node.properties.twitterCardType == "summary_large_image" ? "twoOne" : "square"',
                    'forceCrop' => true,
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    'Neos.Seo:XmlSitemapMixin' => 
    array (
      'abstract' => true,
      'ui' => 
      array (
        'inspector' => 
        array (
          'groups' => 
          array (
            'xmlsitemap' => 
            array (
              'label' => 'i18n',
              'icon' => 'icon-sitemap',
              'position' => 500,
              'tab' => 'seo',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'xmlSitemapChangeFrequency' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'xmlsitemap',
              'position' => 10,
              'editor' => 'Neos.Neos/Inspector/Editors/SelectBoxEditor',
              'editorOptions' => 
              array (
                'allowEmpty' => true,
                'placeholder' => 'none (unspecified)',
                'values' => 
                array (
                  'always' => 
                  array (
                    'label' => 'i18n',
                    'icon' => 'icon-clock',
                  ),
                  'hourly' => 
                  array (
                    'label' => 'i18n',
                    'icon' => 'icon-hourglass',
                  ),
                  'daily' => 
                  array (
                    'label' => 'i18n',
                    'icon' => 'icon-calendar-day',
                  ),
                  'weekly' => 
                  array (
                    'label' => 'i18n',
                    'icon' => 'icon-calendar-week',
                  ),
                  'monthly' => 
                  array (
                    'label' => 'i18n',
                    'icon' => 'icon-calendar',
                  ),
                  'yearly' => 
                  array (
                    'label' => 'i18n',
                    'icon' => 'icon-glass-cheers',
                  ),
                  'never' => 
                  array (
                    'label' => 'i18n',
                    'icon' => 'icon-time-circle',
                  ),
                ),
              ),
            ),
          ),
        ),
        'xmlSitemapPriority' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'xmlsitemap',
              'position' => 20,
              'editorOptions' => 
              array (
                'placeholder' => 'i18n',
              ),
            ),
          ),
          'validation' => 
          array (
            'Neos.Neos/Validation/NumberRangeValidator' => 
            array (
              'minimum' => 0,
              'maximum' => 1,
            ),
          ),
        ),
      ),
    ),
    'Neos.NodeTypes.BaseMixins:ContentImageMixin' => 
    array (
      'abstract' => true,
      'superTypes' => 
      array (
        'Neos.NodeTypes.BaseMixins:ImageMixin' => true,
        'Neos.NodeTypes.BaseMixins:LinkMixin' => true,
        'Neos.NodeTypes.BaseMixins:ImageCaptionMixin' => true,
        'Neos.NodeTypes.BaseMixins:ImageAlignmentMixin' => true,
      ),
      'properties' => 
      array (
        'link' => 
        array (
          'ui' => 
          array (
            'inspector' => 
            array (
              'group' => 'image',
            ),
          ),
        ),
      ),
    ),
    'Neos.NodeTypes.BaseMixins:ImageMixin' => 
    array (
      'abstract' => true,
      'ui' => 
      array (
        'inspector' => 
        array (
          'groups' => 
          array (
            'image' => 
            array (
              'label' => 'i18n',
              'position' => 5,
              'icon' => 'icon-image',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'image' => 
        array (
          'type' => 'Neos\\Media\\Domain\\Model\\ImageInterface',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'image',
              'position' => 50,
            ),
          ),
        ),
        'alternativeText' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'image',
              'position' => 100,
            ),
          ),
        ),
        'title' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'image',
              'position' => 150,
            ),
          ),
        ),
      ),
    ),
    'Neos.NodeTypes.BaseMixins:ImageAlignmentMixin' => 
    array (
      'abstract' => true,
      'properties' => 
      array (
        'alignment' => 
        array (
          'type' => 'string',
          'defaultValue' => '',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'image',
              'position' => 400,
              'editor' => 'Neos.Neos/Inspector/Editors/SelectBoxEditor',
              'editorOptions' => 
              array (
                'placeholder' => 'i18n',
                'values' => 
                array (
                  '' => 
                  array (
                    'label' => '',
                  ),
                  'center' => 
                  array (
                    'label' => 'i18n',
                  ),
                  'left' => 
                  array (
                    'label' => 'i18n',
                  ),
                  'right' => 
                  array (
                    'label' => 'i18n',
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    'Neos.NodeTypes.BaseMixins:ImageCaptionMixin' => 
    array (
      'abstract' => true,
      'properties' => 
      array (
        'hasCaption' => 
        array (
          'type' => 'boolean',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'image',
              'position' => 200,
            ),
          ),
        ),
        'caption' => 
        array (
          'type' => 'string',
          'defaultValue' => '',
          'ui' => 
          array (
            'inlineEditable' => true,
            'aloha' => 
            array (
              'placeholder' => 'i18n',
              'autoparagraph' => true,
            ),
          ),
        ),
      ),
    ),
    'Neos.NodeTypes.BaseMixins:LinkMixin' => 
    array (
      'abstract' => true,
      'properties' => 
      array (
        'link' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'position' => 300,
              'editor' => 'Neos.Neos/Inspector/Editors/LinkEditor',
            ),
          ),
        ),
      ),
    ),
    'Neos.NodeTypes.BaseMixins:TextMixin' => 
    array (
      'abstract' => true,
      'properties' => 
      array (
        'text' => 
        array (
          'type' => 'string',
          'defaultValue' => '',
          'ui' => 
          array (
            'inlineEditable' => true,
            'aloha' => 
            array (
              'placeholder' => 'i18n',
              'autoparagraph' => true,
              'format' => 
              array (
                'strong' => true,
                'em' => true,
                'u' => false,
                'sub' => false,
                'sup' => false,
                'del' => false,
                'p' => true,
                'h1' => true,
                'h2' => true,
                'h3' => true,
                'pre' => true,
                'removeFormat' => true,
              ),
              'table' => 
              array (
                'table' => true,
              ),
              'list' => 
              array (
                'ol' => true,
                'ul' => true,
              ),
              'link' => 
              array (
                'a' => true,
              ),
            ),
          ),
        ),
      ),
    ),
    'Neos.NodeTypes.BaseMixins:TitleMixin' => 
    array (
      'abstract' => true,
      'properties' => 
      array (
        'title' => 
        array (
          'type' => 'string',
          'defaultValue' => '<h1>Enter headline here</h1>',
          'ui' => 
          array (
            'inlineEditable' => true,
            'aloha' => 
            array (
              'format' => 
              array (
                'p' => false,
                'h1' => true,
                'h2' => true,
                'h3' => true,
                'removeFormat' => true,
              ),
              'link' => 
              array (
                'a' => true,
              ),
            ),
          ),
        ),
      ),
    ),
    'Neos.NodeTypes.Html:Html' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-code',
        'position' => 500,
        'inspector' => 
        array (
          'groups' => 
          array (
            'html' => 
            array (
              'label' => 'i18n',
              'icon' => 'icon-code',
              'position' => 10,
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'source' => 
        array (
          'type' => 'string',
          'defaultValue' => '<p>Enter HTML here</p>',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'html',
              'editor' => 'Neos.Neos/Inspector/Editors/CodeEditor',
              'editorOptions' => 
              array (
                'buttonLabel' => 'i18n',
              ),
            ),
          ),
        ),
      ),
      'abstract' => true,
    ),
    'Neos.NodeTypes.ColumnLayouts:Column' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
      ),
      'abstract' => true,
      'ui' => 
      array (
        'group' => 'structure',
        'label' => 'i18n',
        'icon' => 'icon-columns',
        'inlineEditable' => true,
        'inspector' => 
        array (
          'groups' => 
          array (
            'column' => 
            array (
              'label' => 'i18n',
              'position' => 10,
              'icon' => 'icon-columns',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'layout' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'column',
              'editor' => 'Neos.Neos/Inspector/Editors/SelectBoxEditor',
            ),
          ),
        ),
      ),
    ),
    'Neos.NodeTypes.ColumnLayouts:FourColumn' => 
    array (
      'superTypes' => 
      array (
        'Neos.NodeTypes.ColumnLayouts:Column' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'position' => 400,
      ),
      'childNodes' => 
      array (
        'column0' => 
        array (
          'type' => 'Neos.Neos:ContentCollection',
        ),
        'column1' => 
        array (
          'type' => 'Neos.Neos:ContentCollection',
        ),
        'column2' => 
        array (
          'type' => 'Neos.Neos:ContentCollection',
        ),
        'column3' => 
        array (
          'type' => 'Neos.Neos:ContentCollection',
        ),
      ),
      'properties' => 
      array (
        'layout' => 
        array (
          'defaultValue' => '25-25-25-25',
          'ui' => 
          array (
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'editorOptions' => 
              array (
                'values' => 
                array (
                  '25-25-25-25' => 
                  array (
                    'label' => '25% / 25% / 25% / 25%',
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'abstract' => true,
    ),
    'Neos.NodeTypes.ColumnLayouts:ThreeColumn' => 
    array (
      'superTypes' => 
      array (
        'Neos.NodeTypes.ColumnLayouts:Column' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'position' => 300,
      ),
      'childNodes' => 
      array (
        'column0' => 
        array (
          'type' => 'Neos.Neos:ContentCollection',
        ),
        'column1' => 
        array (
          'type' => 'Neos.Neos:ContentCollection',
        ),
        'column2' => 
        array (
          'type' => 'Neos.Neos:ContentCollection',
        ),
      ),
      'properties' => 
      array (
        'layout' => 
        array (
          'defaultValue' => '33-33-33',
          'ui' => 
          array (
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'editorOptions' => 
              array (
                'values' => 
                array (
                  '33-33-33' => 
                  array (
                    'label' => '33% / 33% / 33%',
                  ),
                  '50-25-25' => 
                  array (
                    'label' => '50% / 25% / 25%',
                  ),
                  '25-50-25' => 
                  array (
                    'label' => '25% / 50% / 25%',
                  ),
                  '25-25-50' => 
                  array (
                    'label' => '25% / 25% / 50%',
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'abstract' => true,
    ),
    'Neos.NodeTypes.ColumnLayouts:TwoColumn' => 
    array (
      'superTypes' => 
      array (
        'Neos.NodeTypes.ColumnLayouts:Column' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'position' => 200,
      ),
      'childNodes' => 
      array (
        'column0' => 
        array (
          'type' => 'Neos.Neos:ContentCollection',
        ),
        'column1' => 
        array (
          'type' => 'Neos.Neos:ContentCollection',
        ),
      ),
      'properties' => 
      array (
        'layout' => 
        array (
          'defaultValue' => '50-50',
          'ui' => 
          array (
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'editorOptions' => 
              array (
                'values' => 
                array (
                  '50-50' => 
                  array (
                    'label' => '50% / 50%',
                  ),
                  '75-25' => 
                  array (
                    'label' => '75% / 25%',
                  ),
                  '25-75' => 
                  array (
                    'label' => '25% / 75%',
                  ),
                  '66-33' => 
                  array (
                    'label' => '66% / 33%',
                  ),
                  '33-66' => 
                  array (
                    'label' => '33% / 66%',
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'abstract' => true,
    ),
    'Neos.NodeTypes.AssetList:AssetList' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-files-o',
        'position' => 700,
        'inspector' => 
        array (
          'groups' => 
          array (
            'resources' => 
            array (
              'label' => 'i18n',
              'position' => 5,
              'icon' => 'icon-files-o',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'assets' => 
        array (
          'type' => 'array<Neos\\Media\\Domain\\Model\\Asset>',
          'ui' => 
          array (
            'inspector' => 
            array (
              'group' => 'resources',
            ),
            'label' => 'i18n',
            'reloadIfChanged' => true,
          ),
        ),
      ),
      'abstract' => true,
    ),
    'Neos.NodeTypes.Navigation:Navigation' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'group' => 'structure',
        'icon' => 'icon-sitemap',
        'position' => 100,
        'inspector' => 
        array (
          'groups' => 
          array (
            'options' => 
            array (
              'label' => 'i18n',
              'position' => 30,
              'icon' => 'icon-sliders',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'startLevel' => 
        array (
          'type' => 'string',
          'defaultValue' => '0',
          'ui' => 
          array (
            'reloadIfChanged' => true,
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'options',
              'editor' => 'Neos.Neos/Inspector/Editors/SelectBoxEditor',
              'editorOptions' => 
              array (
                'values' => 
                array (
                  -4 => 
                  array (
                    'label' => 'i18n',
                  ),
                  -3 => 
                  array (
                    'label' => 'i18n',
                  ),
                  -2 => 
                  array (
                    'label' => 'i18n',
                  ),
                  -1 => 
                  array (
                    'label' => 'i18n',
                  ),
                  0 => 
                  array (
                    'label' => 'i18n',
                  ),
                  1 => 
                  array (
                    'label' => 'i18n',
                  ),
                  2 => 
                  array (
                    'label' => 'i18n',
                  ),
                  3 => 
                  array (
                    'label' => 'i18n',
                  ),
                  4 => 
                  array (
                    'label' => 'i18n',
                  ),
                  5 => 
                  array (
                    'label' => 'i18n',
                  ),
                  6 => 
                  array (
                    'label' => 'i18n',
                  ),
                ),
              ),
            ),
          ),
        ),
        'selection' => 
        array (
          'type' => 'references',
          'ui' => 
          array (
            'reloadIfChanged' => true,
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'options',
            ),
          ),
        ),
        'startingPoint' => 
        array (
          'type' => 'reference',
          'ui' => 
          array (
            'reloadIfChanged' => true,
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'options',
            ),
          ),
        ),
        'maximumLevels' => 
        array (
          'type' => 'string',
          'defaultValue' => '1',
          'ui' => 
          array (
            'reloadIfChanged' => true,
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => 'options',
              'editor' => 'Neos.Neos/Inspector/Editors/SelectBoxEditor',
              'editorOptions' => 
              array (
                'values' => 
                array (
                  1 => 
                  array (
                    'label' => '1',
                  ),
                  2 => 
                  array (
                    'label' => '2',
                  ),
                  3 => 
                  array (
                    'label' => '3',
                  ),
                  4 => 
                  array (
                    'label' => '4',
                  ),
                  5 => 
                  array (
                    'label' => '5',
                  ),
                  6 => 
                  array (
                    'label' => '6',
                  ),
                  7 => 
                  array (
                    'label' => '7',
                  ),
                  8 => 
                  array (
                    'label' => '8',
                  ),
                  9 => 
                  array (
                    'label' => '9',
                  ),
                  10 => 
                  array (
                    'label' => '10',
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'abstract' => true,
    ),
    'Neos.NodeTypes.Form:Form' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-envelope-alt',
        'position' => 600,
        'inspector' => 
        array (
          'groups' => 
          array (
            'form' => 
            array (
              'label' => 'i18n',
              'position' => 30,
              'icon' => 'icon-envelope-alt',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'formIdentifier' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'form',
              'editor' => 'Neos.Neos/Inspector/Editors/SelectBoxEditor',
              'editorOptions' => 
              array (
                'placeholder' => 'i18n',
                'dataSourceIdentifier' => 'neos-nodetypes-form-definitions',
              ),
            ),
          ),
        ),
      ),
      'abstract' => true,
    ),
    'Neos.NodeTypes.ContentReferences:ContentReferences' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-copy',
        'position' => 800,
        'inspector' => 
        array (
          'groups' => 
          array (
            'references' => 
            array (
              'label' => 'i18n',
              'position' => 10,
              'icon' => 'icon-copy',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'references' => 
        array (
          'type' => 'references',
          'ui' => 
          array (
            'inspector' => 
            array (
              'group' => 'references',
              'editorOptions' => 
              array (
                'nodeTypes' => 
                array (
                  0 => 'Neos.Neos:Content',
                ),
              ),
            ),
            'label' => 'i18n',
            'reloadIfChanged' => true,
          ),
        ),
      ),
      'abstract' => true,
    ),
    'Neos.NodeTypes:Headline' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
        'Neos.NodeTypes:TitleMixin' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-header',
        'position' => 100,
      ),
      'abstract' => true,
    ),
    'Neos.NodeTypes:Image' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
        'Neos.NodeTypes:ContentImageMixin' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-picture',
        'position' => 300,
      ),
      'abstract' => true,
    ),
    'Neos.NodeTypes:AssetList' => 
    array (
      'superTypes' => 
      array (
        'Neos.NodeTypes.AssetList:AssetList' => true,
      ),
    ),
    'Neos.NodeTypes:ContentReferences' => 
    array (
      'superTypes' => 
      array (
        'Neos.NodeTypes.ContentReferences:ContentReferences' => true,
      ),
    ),
    'Neos.NodeTypes:Form' => 
    array (
      'superTypes' => 
      array (
        'Neos.NodeTypes.Form:Form' => true,
      ),
      'properties' => 
      array (
        'formIdentifier' => 
        array (
          'ui' => 
          array (
            'inspector' => 
            array (
              'editorOptions' => 
              array (
                'values' => 
                array (
                  '' => NULL,
                  'contact-form' => 
                  array (
                    'label' => 'Neos.Demo:NodeTypes.Content.Form:properties.formIdentifier.selectBoxEditor.value.contact-form',
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    'Neos.NodeTypes:Html' => 
    array (
      'superTypes' => 
      array (
        'Neos.NodeTypes.Html:Html' => true,
      ),
    ),
    'Neos.NodeTypes:Menu' => 
    array (
      'superTypes' => 
      array (
        'Neos.NodeTypes.Navigation:Navigation' => true,
      ),
    ),
    'Neos.NodeTypes:Column' => 
    array (
      'abstract' => true,
      'superTypes' => 
      array (
        'Neos.NodeTypes.ColumnLayouts:Column' => true,
      ),
    ),
    'Neos.NodeTypes:TwoColumn' => 
    array (
      'superTypes' => 
      array (
        'Neos.NodeTypes.ColumnLayouts:TwoColumn' => true,
      ),
      'abstract' => true,
    ),
    'Neos.NodeTypes:ThreeColumn' => 
    array (
      'superTypes' => 
      array (
        'Neos.NodeTypes.ColumnLayouts:ThreeColumn' => true,
      ),
      'abstract' => true,
    ),
    'Neos.NodeTypes:FourColumn' => 
    array (
      'superTypes' => 
      array (
        'Neos.NodeTypes.ColumnLayouts:FourColumn' => true,
      ),
      'abstract' => true,
    ),
    'Neos.NodeTypes:Records' => 
    array (
      'superTypes' => 
      array (
        'Neos.NodeTypes:ContentReferences' => true,
      ),
      'abstract' => true,
    ),
    'Neos.NodeTypes:Text' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
        'Neos.NodeTypes:TextMixin' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-file-text',
        'position' => 200,
      ),
      'abstract' => true,
    ),
    'Neos.NodeTypes:TextWithImage' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
        'Neos.NodeTypes:TextMixin' => true,
        'Neos.NodeTypes:ContentImageMixin' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-picture',
        'position' => 400,
      ),
      'abstract' => true,
    ),
    'Neos.NodeTypes:Page' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Document' => true,
      ),
      'childNodes' => 
      array (
        'main' => 
        array (
          'type' => 'Neos.Neos:ContentCollection',
        ),
      ),
      'properties' => 
      array (
        'layout' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => NULL,
              'position' => 100,
              'editor' => 'Neos.Neos/Inspector/Editors/SelectBoxEditor',
              'editorOptions' => 
              array (
                'placeholder' => 'i18n',
                'values' => 
                array (
                  '' => 
                  array (
                    'label' => '',
                  ),
                ),
              ),
            ),
          ),
        ),
        'subpageLayout' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'inspector' => 
            array (
              'group' => NULL,
              'position' => 110,
              'editor' => 'Neos.Neos/Inspector/Editors/SelectBoxEditor',
              'editorOptions' => 
              array (
                'placeholder' => 'i18n',
                'values' => 
                array (
                  '' => 
                  array (
                    'label' => '',
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-file',
        'position' => 100,
        'inspector' => 
        array (
          'groups' => 
          array (
            'document' => 
            array (
              'label' => 'i18n',
            ),
            'layout' => 
            array (
              'label' => 'i18n',
              'icon' => 'icon-paint-brush',
              'position' => 150,
            ),
          ),
        ),
      ),
      'abstract' => true,
    ),
    'Neos.NodeTypes:ContentImageMixin' => 
    array (
      'abstract' => true,
      'superTypes' => 
      array (
        'Neos.NodeTypes.BaseMixins:ContentImageMixin' => true,
      ),
    ),
    'Neos.NodeTypes:ImageMixin' => 
    array (
      'abstract' => true,
      'superTypes' => 
      array (
        'Neos.NodeTypes.BaseMixins:ImageMixin' => true,
      ),
    ),
    'Neos.NodeTypes:ImageAlignmentMixin' => 
    array (
      'abstract' => true,
      'superTypes' => 
      array (
        'Neos.NodeTypes.BaseMixins:ImageAlignmentMixin' => true,
      ),
    ),
    'Neos.NodeTypes:ImageCaptionMixin' => 
    array (
      'abstract' => true,
      'superTypes' => 
      array (
        'Neos.NodeTypes.BaseMixins:ImageCaptionMixin' => true,
      ),
    ),
    'Neos.NodeTypes:LinkMixin' => 
    array (
      'abstract' => true,
      'superTypes' => 
      array (
        'Neos.NodeTypes.BaseMixins:LinkMixin' => true,
      ),
    ),
    'Neos.NodeTypes:TextMixin' => 
    array (
      'abstract' => true,
      'superTypes' => 
      array (
        'Neos.NodeTypes.BaseMixins:TextMixin' => true,
      ),
    ),
    'Neos.NodeTypes:TitleMixin' => 
    array (
      'abstract' => true,
      'superTypes' => 
      array (
        'Neos.NodeTypes.BaseMixins:TitleMixin' => true,
      ),
    ),
    'Flowpack.Neos.FrontendLogin:LoginForm' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Plugin' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-key',
        'inspector' => 
        array (
          'groups' => 
          array (
            'pluginSettings' => 
            array (
              'label' => 'i18n',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'redirectAfterLogin' => 
        array (
          'type' => 'reference',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => false,
            'inspector' => 
            array (
              'group' => 'pluginSettings',
              'editorOptions' => 
              array (
                'nodeTypes' => 
                array (
                  0 => 'Neos.Neos:Document',
                ),
              ),
            ),
          ),
        ),
        'redirectAfterLogout' => 
        array (
          'type' => 'reference',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => false,
            'inspector' => 
            array (
              'group' => 'pluginSettings',
              'editorOptions' => 
              array (
                'nodeTypes' => 
                array (
                  0 => 'Neos.Neos:Document',
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    'Neos.Demo:Collection.Content.Carousel' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:ContentCollection' => true,
      ),
      'constraints' => 
      array (
        'nodeTypes' => 
        array (
          '*' => false,
          'Neos.Demo:Constraint.Content.Carousel' => true,
        ),
      ),
    ),
    'Neos.Demo:Constraint.Content.Carousel' => 
    array (
      'abstract' => true,
    ),
    'Neos.Demo:Collection.Content.Column' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:ContentCollection' => true,
      ),
      'constraints' => 
      array (
        'nodeTypes' => 
        array (
          '*' => false,
          'Neos.Demo:Constraint.Content.Column' => true,
        ),
      ),
    ),
    'Neos.Demo:Constraint.Content.Column' => 
    array (
      'abstract' => true,
    ),
    'Neos.Demo:Collection.Content.Footer' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:ContentCollection' => true,
      ),
      'constraints' => 
      array (
        'nodeTypes' => 
        array (
          '*' => false,
          'Neos.Demo:Content.Headline' => true,
          'Neos.Demo:Content.Text' => true,
          'Neos.Demo:Content.Image' => true,
        ),
      ),
    ),
    'Neos.Demo:Collection.Content.Main' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:ContentCollection' => true,
      ),
    ),
    'Neos.Demo:Collection.Content.Teaser' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:ContentCollection' => true,
      ),
      'constraints' => 
      array (
        'nodeTypes' => 
        array (
          '*' => false,
          'Neos.Demo:Content.Headline' => true,
          'Neos.Demo:Content.Text' => true,
          'Neos.Demo:Content.Image' => true,
        ),
      ),
    ),
    'Neos.Demo:Content.Carousel' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
      ),
      'childNodes' => 
      array (
        'carouselitems' => 
        array (
          'type' => 'Neos.Demo:Collection.Content.Carousel',
        ),
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'group' => 'plugins',
        'icon' => 'icon-picture',
        'inlineEditable' => true,
        'help' => 
        array (
          'message' => 'Bootstrap carousel which can display the following types of content:
* Headline
* Text
* TextWithImage
* Image
* HTML
* YouTube
* References
',
        ),
      ),
    ),
    'Neos.Demo:Content.Carousel.Collection' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
      ),
    ),
    'Neos.Demo:Content.ChapterMenu' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'help' => 
        array (
          'message' => 'The ChapterMenu renders an overview of all chapters below the current document.
It displays:
* title
* chapter description
* chapter image
',
        ),
      ),
    ),
    'Neos.Demo:Content.Columns.Four' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-columns',
        'group' => 'structure',
        'position' => 400,
      ),
      'childNodes' => 
      array (
        'column0' => 
        array (
          'type' => 'Neos.Demo:Collection.Content.Column',
        ),
        'column1' => 
        array (
          'type' => 'Neos.Demo:Collection.Content.Column',
        ),
        'column2' => 
        array (
          'type' => 'Neos.Demo:Collection.Content.Column',
        ),
        'column3' => 
        array (
          'type' => 'Neos.Demo:Collection.Content.Column',
        ),
      ),
    ),
    'Neos.Demo:Content.Columns.Three' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-columns',
        'group' => 'structure',
        'position' => 400,
      ),
      'childNodes' => 
      array (
        'column0' => 
        array (
          'type' => 'Neos.Demo:Collection.Content.Column',
        ),
        'column1' => 
        array (
          'type' => 'Neos.Demo:Collection.Content.Column',
        ),
        'column2' => 
        array (
          'type' => 'Neos.Demo:Collection.Content.Column',
        ),
      ),
    ),
    'Neos.Demo:Content.Columns.Two' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-columns',
        'group' => 'structure',
        'position' => 400,
      ),
      'childNodes' => 
      array (
        'column0' => 
        array (
          'type' => 'Neos.Demo:Collection.Content.Column',
        ),
        'column1' => 
        array (
          'type' => 'Neos.Demo:Collection.Content.Column',
        ),
      ),
    ),
    'Neos.Demo:Content.Flickr' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Plugin' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-flickr',
        'inspector' => 
        array (
          'groups' => 
          array (
            'feed' => 
            array (
              'label' => 'Feed',
              'icon' => 'icon-rss',
            ),
          ),
        ),
        'help' => 
        array (
          'message' => 'Displays a gallery of images from a flickr stream based on tags.',
        ),
      ),
      'options' => 
      array (
        'pluginViews' => 
        array (
          'UserFeed' => 
          array (
            'label' => 'Neos.Demo:NodeTypes.Content.Flickr:options.pluginViews.userFeed',
            'controllerActions' => 
            array (
              'Neos\\Demo\\Controller\\FlickrController' => 
              array (
                0 => 'userStream',
              ),
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'tags' => 
        array (
          'type' => 'string',
          'defaultValue' => '',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'feed',
            ),
          ),
        ),
      ),
    ),
    'Neos.Demo:Content.Headline' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
        'Neos.NodeTypes.BaseMixins:TitleMixin' => true,
        'Neos.Demo:Constraint.Content.Carousel' => true,
        'Neos.Demo:Constraint.Content.Column' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-header',
        'position' => 100,
      ),
      'properties' => 
      array (
        'title' => 
        array (
          'ui' => 
          array (
            'aloha' => 
            array (
              'format' => 
              array (
                'h4' => true,
                'h5' => true,
              ),
            ),
          ),
        ),
      ),
    ),
    'Neos.Demo:Content.Image' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
        'Neos.NodeTypes.BaseMixins:ImageMixin' => true,
        'Neos.NodeTypes.BaseMixins:ImageCaptionMixin' => true,
        'Neos.Demo:Constraint.Content.Carousel' => true,
        'Neos.Demo:Constraint.Content.Column' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-picture',
        'position' => 300,
      ),
    ),
    'Neos.Demo:Content.Registration' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Plugin' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-user',
        'help' => 
        array (
          'message' => 'A very simple registration to allow quick access to the backend of the demo site.

**Note:** For security reasons you should disable this on live sites.
',
        ),
      ),
    ),
    'Neos.Demo:Content.Text' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
        'Neos.NodeTypes.BaseMixins:TextMixin' => true,
        'Neos.Demo:Constraint.Content.Carousel' => true,
        'Neos.Demo:Constraint.Content.Column' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-file-text',
        'position' => 200,
      ),
    ),
    'Neos.Demo:Content.TextWithImage' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
        'Neos.NodeTypes.BaseMixins:TextMixin' => true,
        'Neos.NodeTypes.BaseMixins:ImageMixin' => true,
        'Neos.NodeTypes.BaseMixins:ImageCaptionMixin' => true,
        'Neos.Demo:Constraint.Content.Carousel' => true,
        'Neos.Demo:Constraint.Content.Column' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-picture',
        'position' => 400,
      ),
    ),
    'Neos.Demo:Content.YouTube' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
        'Neos.Demo:Constraint.Content.Carousel' => true,
        'Neos.Demo:Constraint.Content.Column' => true,
      ),
      'ui' => 
      array (
        'label' => 'YouTube',
        'icon' => 'icon-youtube',
        'inspector' => 
        array (
          'groups' => 
          array (
            'video' => 
            array (
              'label' => 'i18n',
              'icon' => 'icon-film',
              'position' => 50,
            ),
          ),
        ),
        'help' => 
        array (
          'message' => 'Embeds a YouTube video as content defined by a video ID.',
        ),
      ),
      'properties' => 
      array (
        'video' => 
        array (
          'type' => 'string',
          'defaultValue' => '',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'video',
            ),
            'help' => 
            array (
              'message' => 'The video identifier is visible in the URL to a youtube video

Eg. in the URL

`https://youtu.be/G6D1YI-41ao`

the video id is `G6D1YI-41ao`.
',
            ),
          ),
        ),
        'width' => 
        array (
          'type' => 'integer',
          'defaultValue' => 400,
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'video',
            ),
          ),
        ),
        'height' => 
        array (
          'type' => 'integer',
          'defaultValue' => 300,
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'video',
            ),
          ),
        ),
      ),
    ),
    'Neos.Demo:Document.Chapter' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Document' => true,
      ),
      'constraints' => 
      array (
        'nodeTypes' => 
        array (
          'Neos.Demo:Document.Homepage' => false,
          'Neos.Demo:Document.NotFoundPage' => false,
        ),
      ),
      'childNodes' => 
      array (
        'main' => 
        array (
          'type' => 'Neos.Demo:Collection.Content.Main',
        ),
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'icon-book',
        'help' => 
        array (
          'message' => 'The Chapter node will work with all other chapter nodes on the same level to form a browsable book.',
        ),
        'inspector' => 
        array (
          'groups' => 
          array (
            'document' => 
            array (
              'label' => 'i18n',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'layout' => 
        array (
          'type' => 'string',
          'defaultValue' => 'chapter',
        ),
        'chapterDescription' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'document',
              'editor' => 'Neos.Neos/Inspector/Editors/TextAreaEditor',
            ),
            'help' => 
            array (
              'message' => 'The description will only be used in the chapter menu.',
            ),
          ),
        ),
        'chapterImage' => 
        array (
          'type' => 'Neos\\Media\\Domain\\Model\\ImageInterface',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'document',
            ),
            'help' => 
            array (
              'message' => 'This image will also appear in the chapter menu.',
            ),
          ),
        ),
        'title' => 
        array (
          'validation' => NULL,
          'ui' => 
          array (
            'inlineEditable' => true,
            'inspector' => 
            array (
              'group' => NULL,
            ),
            'aloha' => 
            array (
              'placeholder' => 'Enter title here',
              'format' => 
              array (
                'b' => true,
                'i' => true,
                'u' => true,
                'sub' => true,
                'sup' => true,
                'p' => false,
                'h1' => false,
                'h2' => false,
                'h3' => false,
                'pre' => false,
                'removeFormat' => false,
              ),
              'table' => 
              array (
              ),
              'list' => 
              array (
              ),
              'link' => 
              array (
                'a' => true,
              ),
              'alignment' => 
              array (
              ),
              'formatlesspaste' => 
              array (
                'button' => false,
                'formatlessPasteOption' => true,
              ),
            ),
          ),
        ),
      ),
    ),
    'Neos.Demo:Document.Homepage' => 
    array (
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'globe',
        'help' => 
        array (
          'message' => 'The homepage type is to be used only once and adds the shared footer.',
        ),
      ),
      'superTypes' => 
      array (
        'Neos.Demo:Document.Page' => true,
      ),
      'childNodes' => 
      array (
        'notfound' => 
        array (
          'type' => 'Neos.Demo:Document.NotFoundPage',
        ),
        'footer' => 
        array (
          'position' => 'end',
          'type' => 'Neos.Demo:Collection.Content.Footer',
        ),
      ),
    ),
    'Neos.Demo:Document.LandingPage' => 
    array (
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'file-import',
        'help' => 
        array (
          'message' => 'A landing Page',
        ),
      ),
      'superTypes' => 
      array (
        'Neos.Demo:Document.Page' => true,
      ),
      'childNodes' => 
      array (
        'teaser' => 
        array (
          'position' => 'end',
          'type' => 'Neos.Demo:Collection.Content.Teaser',
        ),
      ),
    ),
    'Neos.Demo:Document.NotFoundPage' => 
    array (
      'superTypes' => 
      array (
        'Neos.Demo:Document.Page' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'times',
      ),
      'constraints' => 
      array (
        'nodeTypes' => 
        array (
          'Neos.Neos:Document' => false,
        ),
      ),
      'properties' => 
      array (
        'title' => 
        array (
          'defaultValue' => 'Not Found',
        ),
        'uriPathSegment' => 
        array (
          'defaultValue' => 'notfound',
        ),
        '_hidden' => 
        array (
          'defaultValue' => true,
        ),
        '_hiddenInIndex' => 
        array (
          'defaultValue' => true,
        ),
        'metaRobotsNoindex' => 
        array (
          'defaultValue' => true,
        ),
      ),
    ),
    'Neos.Demo:Document.Page' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Document' => true,
      ),
      'ui' => 
      array (
        'label' => 'i18n',
        'icon' => 'file',
        'help' => 
        array (
          'message' => 'A Page',
        ),
        'inspector' => 
        array (
          'groups' => 
          array (
            'image' => 
            array (
              'label' => 'i18n',
              'position' => 200,
              'icon' => 'icon-image',
            ),
          ),
        ),
      ),
      'constraints' => 
      array (
        'nodeTypes' => 
        array (
          'Neos.Demo:Document.Homepage' => false,
          'Neos.Demo:Document.NotFoundPage' => false,
        ),
      ),
      'childNodes' => 
      array (
        'main' => 
        array (
          'type' => 'Neos.Demo:Collection.Content.Main',
        ),
        'teaser' => 
        array (
          'type' => 'Neos.Demo:Collection.Content.Teaser',
        ),
      ),
      'properties' => 
      array (
        'image' => 
        array (
          'type' => 'Neos\\Media\\Domain\\Model\\ImageInterface',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'image',
              'position' => 50,
              'editorOptions' => 
              array (
                'crop' => 
                array (
                  'aspectRatio' => 
                  array (
                    'locked' => 
                    array (
                      'width' => 2,
                      'height' => 1,
                    ),
                  ),
                ),
              ),
            ),
          ),
        ),
        'imageTitleText' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'i18n',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'image',
              'position' => 100,
            ),
          ),
        ),
      ),
    ),
    'Flowpack.FusionBP:Cover' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Document' => true,
        'Flowpack.FusionBP:SectionMixin' => true,
      ),
      'ui' => 
      array (
        'label' => 'Cover',
        'inspector' => 
        array (
          'groups' => 
          array (
            'image' => 
            array (
              'label' => 'Image',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'backgroundImage' => 
        array (
          'type' => 'Neos\\Media\\Domain\\Model\\ImageInterface',
          'ui' => 
          array (
            'label' => 'Background image',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'image',
            ),
          ),
        ),
      ),
    ),
    'Flowpack.FusionBP:Feature' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
      ),
      'ui' => 
      array (
        'label' => 'Feature',
        'inlineEditable' => true,
        'inspector' => 
        array (
          'groups' => 
          array (
            'color' => 
            array (
              'ui' => 
              array (
                'label' => 'Color',
              ),
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'title' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'aloha' => 
            array (
              'placeholder' => 'Title',
            ),
          ),
        ),
        'subTitle' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'aloha' => 
            array (
              'placeholder' => 'Subtitle',
            ),
          ),
        ),
        'color' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'Line color',
            'inspector' => 
            array (
              'group' => 'color',
              'editor' => 'Neos.Neos/Inspector/Editors/SelectBoxEditor',
              'editorOptions' => 
              array (
                'placeholder' => 'Choose the color',
                'values' => 
                array (
                  'blue' => 
                  array (
                    'label' => 'blue',
                  ),
                  'yellow' => 
                  array (
                    'label' => 'yellow',
                  ),
                  'purple' => 
                  array (
                    'label' => 'purple',
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    'Flowpack.FusionBP:Features' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Document' => true,
        'Flowpack.FusionBP:SectionMixin' => true,
      ),
      'childNodes' => 
      array (
        'features' => 
        array (
          'type' => 'Neos.Neos:ContentCollection',
        ),
      ),
      'ui' => 
      array (
        'label' => 'Features',
        'inspector' => 
        array (
          'groups' => 
          array (
            'image' => 
            array (
              'label' => 'Image',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'image' => 
        array (
          'type' => 'Neos\\Media\\Domain\\Model\\ImageInterface',
          'ui' => 
          array (
            'label' => 'Image',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'image',
            ),
          ),
        ),
      ),
    ),
    'Flowpack.FusionBP:Invite' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Document' => true,
        'Flowpack.FusionBP:SectionMixin' => true,
      ),
      'ui' => 
      array (
        'label' => 'Invite',
      ),
    ),
    'Flowpack.FusionBP:Press' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Document' => true,
      ),
      'ui' => 
      array (
        'label' => 'Press',
      ),
      'childNodes' => 
      array (
        'pressLinks' => 
        array (
          'type' => 'Neos.Neos:ContentCollection',
        ),
      ),
    ),
    'Flowpack.FusionBP:PressLink' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
      ),
      'ui' => 
      array (
        'label' => 'Press link',
        'inlineEditable' => true,
        'inspector' => 
        array (
          'groups' => 
          array (
            'image' => 
            array (
              'label' => 'Image',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'title' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'aloha' => 
            array (
              'placeholder' => 'Title',
            ),
          ),
        ),
        'source' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'aloha' => 
            array (
              'placeholder' => 'Source',
            ),
          ),
        ),
        'logo' => 
        array (
          'type' => 'Neos\\Media\\Domain\\Model\\ImageInterface',
          'ui' => 
          array (
            'label' => 'Logo',
            'reloadIfChanged' => true,
            'inspector' => 
            array (
              'group' => 'image',
            ),
          ),
        ),
      ),
    ),
    'Flowpack.FusionBP:Price' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Content' => true,
      ),
      'ui' => 
      array (
        'label' => 'Price',
        'inlineEditable' => true,
      ),
      'properties' => 
      array (
        'title' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'aloha' => 
            array (
              'placeholder' => 'Title',
            ),
          ),
        ),
        'info' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'aloha' => 
            array (
              'placeholder' => 'Info',
            ),
          ),
        ),
        'amount' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'aloha' => 
            array (
              'placeholder' => 'Amount',
            ),
          ),
        ),
        'projects' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'aloha' => 
            array (
              'placeholder' => 'Projects',
            ),
          ),
        ),
        'storage' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'aloha' => 
            array (
              'placeholder' => 'Storage',
            ),
          ),
        ),
      ),
    ),
    'Flowpack.FusionBP:Pricing' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Document' => true,
        'Flowpack.FusionBP:SectionMixin' => true,
      ),
      'childNodes' => 
      array (
        'pricing' => 
        array (
          'type' => 'Neos.Neos:ContentCollection',
        ),
      ),
      'ui' => 
      array (
        'label' => 'Pricing',
      ),
    ),
    'Flowpack.FusionBP:Root' => 
    array (
      'superTypes' => 
      array (
        'Neos.Neos:Document' => true,
      ),
      'ui' => 
      array (
        'label' => 'Root aggregate page',
      ),
    ),
    'Flowpack.FusionBP:SectionMixin' => 
    array (
      'abstract' => true,
      'ui' => 
      array (
        'label' => 'Pricing',
        'inspector' => 
        array (
          'groups' => 
          array (
            'section' => 
            array (
              'label' => 'Section',
            ),
          ),
        ),
      ),
      'properties' => 
      array (
        'navTitle' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'label' => 'Navigation title (for menu)',
            'inspector' => 
            array (
              'group' => 'section',
            ),
          ),
        ),
        'title' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'aloha' => 
            array (
              'placeholder' => 'Title',
            ),
          ),
        ),
        'subTitle' => 
        array (
          'type' => 'string',
          'ui' => 
          array (
            'aloha' => 
            array (
              'placeholder' => 'Subtitle',
            ),
          ),
        ),
      ),
    ),
  ),
);