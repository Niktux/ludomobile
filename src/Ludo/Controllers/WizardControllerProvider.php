<?php

namespace Ludo\Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;

class WizardControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $app['wizard.controller'] = $app->share(function() use($app) {
            return new WizardController($app['twig'], $app['searchEngine'], $app['request']);
        });

        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'wizard.controller:indexAction');

        $controllers->get('/step/2/type/{type}', 'wizard.controller:step2Action')
            ->assert('type', '\w+');

        $controllers->get('/step/3/type/{type}/duration/{duration}', 'wizard.controller:step3Action')
            ->assert('type', '\w+')
            ->assert('duration', '\d+');

        $controllers->get('/step/4/type/{type}/duration/{duration}/envy/{envy}', 'wizard.controller:step4Action')
            ->assert('type', '\w+')
            ->assert('duration', '\d+')
            ->assert('envy', '\w+');

        $controllers->get('/result/type/{type}/duration/{duration}/envy/{envy}/players/{nbPlayers}', 'wizard.controller:resultAction')
            ->assert('type', '\w+')
            ->assert('duration', '\d+')
            ->assert('envy', '\w+')
            ->assert('nbPlayers', '\d+');

        return $controllers;
    }
}
