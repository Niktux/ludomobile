<?php

namespace Ludo\Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;

class GameControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $app['game.controller'] = $app->share(function() use($app) {
            return new GameController($app['twig'], $app['games'], $app['request']);
        });
        
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];
        
        $controllers->get('/add/play', 'game.controller:addPlayAction')
                    ->assert('gameId', '\d+');
        $controllers->post('/add/play', 'game.controller:postAddPlayAction')
                    ->assert('gameId', '\d+');
        $controllers->post('/select/players', 'game.controller:selectPlayersAction')
                    ->assert('gameId', '\d+');
        $controllers->post('/scores', 'game.controller:scoresAction')
        ->assert('gameId', '\d+');
        
        return $controllers;
    }
}
