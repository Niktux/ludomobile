<?php

namespace Ludo\Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;

class SearchControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $app['search.controller'] = $app->share(function() use($app) {
            return new SearchController($app['twig'], $app['searchEngine'], $app['request']);
        });
        
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];
        
        $controllers->get('/', 'search.controller:indexAction');
        $controllers->post('/search', 'search.controller:searchAction');
        $controllers->get('/searchByLetter/{letter}', 'search.controller:searchByLetterAction')
                    ->assert('letter', '\w|09');
        $controllers->get('/searchByLetters/{letter1}/{letter2}', 'search.controller:searchByLettersAction')
                    ->assert('letter1', '\w|09')
                    ->assert('letter2', '\w|09');
        
        return $controllers;
    }
}
