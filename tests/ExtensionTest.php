<?php

namespace Bolt\Extension\Soapbox\AjaxMultiCTSelectField\Tests;

use Bolt\Tests\BoltUnitTest;
use Bolt\Extension\Soapbox\AjaxMultiCTSelectField\AjaxMultiCTSelectFieldExtension;

/**
 * ExtensionName testing class.
 *
 * @author Robert Hunt <robert.hunt@soapbox.co.uk>
 */
class ExtensionTest extends BoltUnitTest
{

    /**
     * Ensure that the ExtensionName extension loads correctly.
     */
    public function testExtensionRegister()
    {

        $app       = $this->getApp(false);
        $extension = new AjaxMultiCTSelectFieldExtension($app);
        $app['extensions']->add($extension);

        $name = $extension->getName();
        $this->assertSame($name, 'ExtensionName');
        $this->assertInstanceOf('\Bolt\Extension\ExtensionInterface', $app['extensions']->get('ExtensionName'));
    }
}
