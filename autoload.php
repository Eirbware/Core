<?php
include(__DIR__.'/src/Eirbware/ClassLoader.php');

$loader = new Eirbware\ClassLoader();

$loader->registerNamespaces(array(
    'Eirbware'  => __DIR__.'/src',
    'Silex'     => __DIR__.'/src',
    'Jasig'     => __DIR__.'/src'
));

$loader->register();
