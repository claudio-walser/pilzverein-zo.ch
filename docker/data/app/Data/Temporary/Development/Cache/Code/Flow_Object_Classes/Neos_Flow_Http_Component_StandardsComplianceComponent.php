<?php 
namespace Neos\Flow\Http\Component;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Helper\ResponseInformationHelper;

/**
 * HTTP component that makes sure that the current response is standards-compliant. It is usually the last component in the chain.
 */
class StandardsComplianceComponent_Original implements ComponentInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Just call makeStandardsCompliant on the Response for now
     *
     * @param ComponentContext $componentContext
     * @return void
     */
    public function handle(ComponentContext $componentContext)
    {
        $response = $componentContext->getHttpResponse();
        $response = ResponseInformationHelper::makeStandardsCompliant($response, $componentContext->getHttpRequest());
        $componentContext->replaceHttpResponse($response);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Http\Component;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * HTTP component that makes sure that the current response is standards-compliant. It is usually the last component in the chain.
 */
class StandardsComplianceComponent extends StandardsComplianceComponent_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


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
  'options' => 'array',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {

        $this->Flow_setRelatedEntities();
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Framework/Neos.Flow/Classes/Http/Component/StandardsComplianceComponent.php
#