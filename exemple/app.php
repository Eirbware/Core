<?php
include(__DIR__.'/../autoload.php');

$app['debug'] = true;

$app = new Eirbware\Application();

$app->secureWithCAS(false);

$app->get('/', function() use ($app) {
    return $app['twig']->render('index.html.twig', array(
        'user' => $app['user']
    ));
});

$app->run();
