<?php
include(__DIR__.'/../autoload.php');

$app = new Eirbware\Application();

$app->secureWithCAS();

$app->get('/', function() use ($app) {
    return $app['twig']->render('index.html.twig');
});

$app->run();
