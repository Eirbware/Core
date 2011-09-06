<?php
include(__DIR__.'/src/Eirbware/ClassLoader.php');

include('vendor/silex/autoload.php');

$loader = new Eirbware\ClassLoader();

$loader->registerNamespaces(array(
    'Eirbware'  => __DIR__.'/src',
    'Gregwar'   => __DIR__.'/src',
    'Jasig'     => __DIR__.'/src',
));

$loader->registerPrefixes(array(
    'Twig_Extensions_' => __DIR__.'/vendor/silex/vendor/twig/lib',
    'Twig_' => __DIR__.'/vendor/silex/vendor/twig/lib',
));

$loader->register();
