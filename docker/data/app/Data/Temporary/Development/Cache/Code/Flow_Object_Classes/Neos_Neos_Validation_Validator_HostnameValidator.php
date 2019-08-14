<?php 
namespace Neos\Neos\Validation\Validator;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Validation\Validator\AbstractValidator;

/**
 * Validator for http://tools.ietf.org/html/rfc1123 compatible host names
 */
class HostnameValidator_Original extends AbstractValidator
{
    /**
     * @var array
     */
    protected $supportedOptions = [
        'ignoredHostnames' => ['', 'Hostnames that are not to be validated', 'string'],
    ];

    /**
     * Validates if the hostname is valid.
     *
     * @param mixed $hostname The hostname that should be validated
     * @return void
     */
    protected function isValid($hostname)
    {
        $pattern = '/(?=^.{4,253}$)(^((?!-)[a-zA-Z0-9-]{1,63}(?<!-)\.)*(?!-)[a-zA-Z]{2,63}(?<!-)$)/';

        if ($this->options['ignoredHostnames']) {
            $ignoredHostnames = explode(',', $this->options['ignoredHostnames']);
            if (in_array($hostname, $ignoredHostnames)) {
                return;
            }
        }

        if (!preg_match($pattern, $hostname)) {
            $this->addError('The hostname "%1$s" was not valid.', 1415392993, [$hostname]);
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Neos\Validation\Validator;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Validator for http://tools.ietf.org/html/rfc1123 compatible host names
 */
class HostnameValidator extends HostnameValidator_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
  'supportedOptions' => 'array',
  'acceptsEmptyValues' => 'boolean',
  'options' => 'array',
  'result' => 'Neos\\Error\\Messages\\Result',
  'resultStack' => 'array<Neos\\Error\\Messages\\Result>',
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
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Neos/Classes/Validation/Validator/HostnameValidator.php
#