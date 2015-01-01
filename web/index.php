<?php

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollection;
use Silex\ServiceProviderInterface;
use Silex\Application;
require '../src/bootstrap.php';

$app = new Ludo\Application(__DIR__ . '/../config/db.yml');

$app->enableProfiling();

$app->mount('/', new Ludo\Controllers\SearchControllerProvider());
$app->mount('/games/{gameId}', new Ludo\Controllers\GameControllerProvider());
$app->mount('/ajax/', new Ludo\Controllers\AjaxControllerProvider());

$app->run();
