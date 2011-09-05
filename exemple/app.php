<?php
include(__DIR__.'/../autoload.php');

$app = new Eirbware\Application();

$app->secureWithCAS(false);
$app->connectDb('localhost', 'eirbware', 'root', 'admin');

/**
 * Page principale
 */
$app->get('/', function() use ($app) {
    return $app->render('index.html.twig', array(
        'user' => $app['user']
    ));
});

/***
 * Affiche la liste des personnes
 */
$app->get('/personnes', function() use ($app) {
    return $app->render('personnes.html.twig', array(
        'personnes' => $app['db']->fetchAll('SELECT * FROM personnes')
    ));
});

$app->run();
