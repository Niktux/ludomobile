<?php

namespace Ludo\Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;

class AjaxControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $app['ajax.controller'] = $app->share(function() use($app) {
            return new AjaxController($app['request'], $app['games']);
        });

        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->get('/players/{letters}', 'ajax.controller:playersLettersAction')
            ->bind('players_letters')
            ->assert('letters', '[a-z]*');

        return $controllers;
    }
}
