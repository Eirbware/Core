Coeur des sites d'Eirbware
==========================

Ces fichiers servent à gérer une partie commune aux applications
web proposées par Eirbware.

Utilisation
-----------

Ce coeur est principalement basé sur [Silex](http://silex.sensiolabs.org/),
un microframework utilisant les composants de Symfony2.

Une application web utilisant ce noyau ressemble simplement à ceci :

```php
<?php
include('/net/www/core/autoload.php');

$app = new Eirbware\Application();

// Vos actions et personnalisations

$app->run();
```

Vous pourrez alors utiliser les mêmes fonctions que Silex, pour plus d'informations,
reportez vous à la [Documentation de Silex](http://silex.sensiolabs.org/documentation).

URL Rewriting
-------------

L'application utilise par défaut le PathInfo (ex: `app.php/mon/url`), mais il est
possible à l'aide de la réécriture d'URL d'obtenir des adresses plus jolies, pour 
cela, ajouter le fichier `.htaccess` suivant :

    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^(.*)$ app.php [QSA,L]
    </IfModule>

Identification CAS
------------------

Il est possible de sécuriser l'application à l'aide du CAS de l'IPB qui est pré-configuré.
Pour ce faire :

```php
<?php
//...
$app->secureWithCAS();
//...
```

Il sera par la suite possible d'obtenir l'identifiant de l'utilisateur par $app['user'].

Pour que l'identification soit optionelle, passez `false` à `secureWithCas()`, et 
envoyez les utilisateurs vers `/login` pour initier l'identification.

Envoyez les utilisateur vers `/logout` pour les désidentifier.

Templating
----------

Le moteur de template [Twig](http://www.twig-project.org/) est chargé par défaut 
et pré-configuré. Vous pourrez alors l'utiliser en plaçant vos templates dans le 
sous-répértoire `views` de votre projet et en procédant ainsi :

```php
<?php
//...
$app->get('/test', function() use ($app) {
    return $app['twig']->render('test.html.twig');
});
//...
```

Voir :
* [L'extension Twig de Silex](http://silex.sensiolabs.org/doc/extensions/twig.html)
* [La documentation de Twig](http://twig.sensiolabs.org/documentation)

Bases de données
---------------

La couche d'abstraction de la base de données [Doctrine DBAL](http://www.doctrine-project.org/docs/dbal/2.0/en/)
peut être utilisé :

```php
<?php
//...
$app->connectDb('host', 'base', 'user', 'pass');
//..
```
Voir :
* [L'extension Doctrine de Silex](http://silex.sensiolabs.org/doc/extensions/doctrine.html)

Application d'exemple
---------------------

Ce dépôt contient une application d'exemple sous `exemple/`, consultez la pour découvrir
les fonctionnalités !

Note de développement
---------------------

Si vous comptez utiliser le noyau Eirbware sur une autre machine que notre serveur de production,
clonez ce dépôt et tappez :

    git submodule init
    git submodule update
    cd vendor/silex
    git submodule init
    git submodule update

Pour télécharger les bibliothèques des dépendances, et vous pourrez commencer à travailler !
