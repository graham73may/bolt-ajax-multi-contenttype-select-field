<?php

namespace Bolt\Extension\Soapbox\AjaxMultiCTSelectField\Provider;

use Bolt\Extension\Soapbox\AjaxMultiCTSelectField\Field\AjaxMultiCTSelectFieldType;
use Bolt\Storage\FieldManager;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * This class extends the the service provider
 *
 * @author Robert Hunt <robert.hunt@soapbox.co.uk>
 */
class AjaxMultiCTSelectFieldProvider implements ServiceProviderInterface
{

    public function register(Application $app)
    {

        $app['storage.typemap'] = array_merge($app['storage.typemap'], [
            'ajaxmultictselect' => AjaxMultiCTSelectFieldType::class
        ]);

        $app['storage.field_manager'] = $app->share($app->extend('storage.field_manager', function (FieldManager $manager) {

            $manager->addFieldType('ajaxmultictselect', new AjaxMultiCTSelectFieldType());

            return $manager;
        }));

    }

    public function boot(Application $app)
    {
    }
}
