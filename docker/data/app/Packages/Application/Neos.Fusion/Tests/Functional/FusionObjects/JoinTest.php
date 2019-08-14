<?php
namespace Neos\Fusion\Tests\Functional\FusionObjects;

/*
 * This file is part of the Neos.Fusion package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * Testcase for the Fusion Concat
 */
class JoinTest extends AbstractFusionObjectTest
{
    /**
     * @test
     */
    public function basicOrderingWorks()
    {
        $view = $this->buildView();

        $view->setFusionPath('join/basicOrdering');
        $this->assertEquals('Xtest10Xtest100', $view->render());
    }

    /**
     * @test
     */
    public function positionalOrderingWorks()
    {
        $view = $this->buildView();

        $view->setFusionPath('join/positionalOrdering');
        $this->assertEquals('XbeforeXmiddleXafter', $view->render());
    }

    /**
     * @test
     */
    public function startEndOrderingWorks()
    {
        $view = $this->buildView();

        $view->setFusionPath('join/startEndOrdering');
        $this->assertEquals('XbeforeXmiddleXafter', $view->render());
    }

    /**
     * @test
     */
    public function advancedStartEndOrderingWorks()
    {
        $view = $this->buildView();

        $view->setFusionPath('join/advancedStartEndOrdering');
        $this->assertEquals('XeXdXfoobarXfXgX100XbXaXc', $view->render());
    }

    /**
     * @test
     */
    public function ignoredPropertiesWork()
    {
        $view = $this->buildView();

        $view->setFusionPath('join/ignoreProperties');
        $this->assertEquals('XbeforeXafter', $view->render());
    }

    /**
     * @test
     */
    public function glueWorks()
    {
        $view = $this->buildView();
        $view->setFusionPath('join/withGlue');
        $this->assertEquals('a, b, d', $view->render());
    }
}
