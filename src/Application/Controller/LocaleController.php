<?php

namespace Application\Controller;

use Application\Transformer\LocaleTransformer;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;


/**
 * Locale controller
 */
class LocaleController implements ControllerProviderInterface
{
    /**
     * Create controller collection
     *
     * @param Application $app
     *
     * @return ControllerCollection
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', function () use ($app) {
            $locales = $app['repository.locale']->findAll();

            return LocaleTransformer::transformAll($locales);
        });

        return $controllers;
    }
}