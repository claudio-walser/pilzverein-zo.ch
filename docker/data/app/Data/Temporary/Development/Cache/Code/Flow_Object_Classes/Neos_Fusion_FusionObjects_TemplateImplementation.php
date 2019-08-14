<?php 
namespace Neos\Fusion\FusionObjects;

/*
 * This file is part of the Neos.Fusion package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Mvc\ActionRequest;
use Neos\FluidAdaptor\Core\Parser\Interceptor\ResourceInterceptor;
use TYPO3Fluid\Fluid\Core\Parser\InterceptorInterface;

/**
 * Fusion object rendering a fluid template
 *
 * //tsPath variables TODO The result of this TS object is made available inside the template as "variables"
 * @api
 */
class TemplateImplementation_Original extends AbstractArrayFusionObject
{
    /**
     * Path to the template which should be rendered
     *
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->fusionValue('templatePath');
    }

    /**
     * Path to the partial root
     *
     * @return string
     */
    public function getPartialRootPath()
    {
        return $this->fusionValue('partialRootPath');
    }

    /**
     * Path to the layout root
     *
     * @return string
     */
    public function getLayoutRootPath()
    {
        return $this->fusionValue('layoutRootPath');
    }

    /**
     * Name of a specific section, if only this section should be rendered.
     *
     * @return string
     */
    public function getSectionName()
    {
        return $this->fusionValue('sectionName');
    }

    /**
     * @return string
     * @internal
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function evaluate()
    {
        $actionRequest =  $this->runtime->getControllerContext()->getRequest();
        if (!$actionRequest instanceof ActionRequest) {
            $actionRequest = null;
        }
        $fluidTemplate = new Helpers\FluidView($this, $actionRequest);

        $templatePath = $this->getTemplatePath();
        if ($templatePath === null) {
            throw new \Exception(sprintf("
                No template path set.
                Most likely you didn't configure `templatePath` in your Fusion object correctly.
                For example you could add and adapt the following line to your Fusion:
                `prototype(%s) < prototype(Neos.Fusion:Template) {
                  templatePath = 'resource://Vendor.Package/Private/Templates/MyObject.html'
                }`
                ", $this->fusionObjectName));
        }
        $fluidTemplate->setTemplatePathAndFilename($templatePath);

        $partialRootPath = $this->getPartialRootPath();
        if ($partialRootPath !== null) {
            $fluidTemplate->setPartialRootPath($partialRootPath);
        }

        $layoutRootPath = $this->getLayoutRootPath();
        if ($layoutRootPath !== null) {
            $fluidTemplate->setLayoutRootPath($layoutRootPath);
        }

        // Template resources need to be evaluated from the templates package not the requests package.
        if (strpos($templatePath, 'resource://') === 0) {
            $templateResourcePathParts = parse_url($templatePath);
            foreach ($fluidTemplate->getRenderingContext()->buildParserConfiguration()->getInterceptors(InterceptorInterface::INTERCEPT_TEXT) as $interceptor) {
                if ($interceptor instanceof ResourceInterceptor) {
                    $interceptor->setDefaultPackageKey($templateResourcePathParts['host']);
                }
            }
        }

        foreach ($this->properties as $key => $value) {
            if (in_array($key, $this->ignoreProperties)) {
                continue;
            }
            if (!is_array($value)) {
                // if a value is a SIMPLE TYPE, e.g. neither an Eel expression nor a Fusion object,
                // we can just evaluate it (to handle processors) and then assign it to the template.
                $evaluatedValue = $this->fusionValue($key);
                $fluidTemplate->assign($key, $evaluatedValue);
            } else {
                // It is an array; so we need to create a "proxy" for lazy evaluation, as it could be a
                // nested Fusion object, Eel expression or simple value.
                $fluidTemplate->assign($key, new Helpers\FusionPathProxy($this, $this->path . '/' . $key, $value));
            }
        }

        $this->initializeView($fluidTemplate);

        $sectionName = $this->getSectionName();
        if ($sectionName !== null) {
            return $fluidTemplate->renderSection($sectionName);
        } else {
            return $fluidTemplate->render();
        }
    }

    /**
     * This is a template method which can be overridden in subclasses to add new variables which should
     * be available inside the Fluid template. It is needed e.g. for Expose.
     *
     * @param Helpers\FluidView $view
     * @return void
     */
    protected function initializeView(Helpers\FluidView $view)
    {
        // template method
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Fusion\FusionObjects;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Fusion object rendering a fluid template
 * 
 * //tsPath variables TODO The result of this TS object is made available inside the template as "variables"
 */
class TemplateImplementation extends TemplateImplementation_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param Runtime $runtime
     * @param string $path
     * @param string $fusionObjectName
     */
    public function __construct()
    {
        $arguments = func_get_args();

        if (!array_key_exists(0, $arguments)) $arguments[0] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Fusion\Core\Runtime');
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $runtime in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $path in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(2, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $fusionObjectName in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
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
  'properties' => 'array',
  'ignoreProperties' => 'array',
  'runtime' => 'Neos\\Fusion\\Core\\Runtime',
  'path' => 'string',
  'fusionObjectName' => 'string',
  'tsValueCache' => 'array',
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
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Fusion/Classes/FusionObjects/TemplateImplementation.php
#