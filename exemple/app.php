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

/**
 * Formulaire de démonstration
 *
 * (Match support GET et POST)
 */
$app->match('/form', function() use ($app) {
    $form = new Gregwar\DSD\Form('forms/demo.html');

    $type = null;
    if ($form->posted()) {
        $type = $form->champ;
    }

    return $app->render('form.html.twig', array(
        'form' => $form,
        'type' => $type
    ));
});

$app->run();
