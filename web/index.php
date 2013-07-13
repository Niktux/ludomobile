<?php

require '../src/bootstrap.php';

$app = new Ludo\Application(__DIR__ . '/../config/db.yml');

$app->enableDebug()
    ->enableProfiling();

$app->mount('/', new Ludo\Controllers\SearchControllerProvider());
$app->mount('/games/{gameId}', new Ludo\Controllers\GameControllerProvider());

$app->run();