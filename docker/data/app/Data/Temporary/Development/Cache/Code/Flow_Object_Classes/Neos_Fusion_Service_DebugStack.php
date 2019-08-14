<?php 
namespace Neos\Fusion\Service;

/*
 * This file is part of the Neos.Fusion package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\DebugMessage;

/**
 * @Flow\Scope("singleton")
 */
class DebugStack_Original
{
    /**
     * @var DebugMessage[]
     */
    protected $data = [];

    public function register(DebugMessage $data)
    {
        $this->data[] = $data;
    }

    public function hasMessage(): bool
    {
        return count($this->data) > 0;
    }

    public function dump()
    {
        $data = $this->data;
        $this->flush();
        $output = '';
        foreach ($data as $debugMessage) {
            $output .= \Neos\Flow\var_dump($debugMessage->getData(), $debugMessage->getTitle(), true, $debugMessage->isPlaintext());
        }
        return $output;
    }

    public function flush()
    {
        $this->data = [];
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Fusion\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class DebugStack extends DebugStack_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Fusion\Service\DebugStack') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Fusion\Service\DebugStack', $this);
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
  'data' => 'array<Neos\\Fusion\\DebugMessage>',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Fusion\Service\DebugStack') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Fusion\Service\DebugStack', $this);

        $this->Flow_setRelatedEntities();
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Fusion/Classes/Service/DebugStack.php
#