<?php

namespace Eirbware;

use Silex\Application as BaseApplication;

use Silex\Extension\SessionExtension;
use Silex\Extension\TwigExtension;
use Silex\Extension\DoctrineExtension;

use Eirbware\Twig\ImageExtension;

/**
 * Classe de base pour les applications web de Eirbware
 *
 * @author Grégoire Passault <g.passault@gmail.com
 */
class Application extends BaseApplication
{
    /**
     * Paramètres
     */
    private $defaultParameters = array(
        // Paramètres du serveur CAS
        'cas.host' => 'cas.enseirb-matmeca.fr',
        'cas.port' => 443,
        'cas.context' => 'cas',

        // Paramètres de sécurité
        'security.session_key' => 'user',

        // Paramètres pour la base de données de Eirbware 
        // (accès en lecture seule pour les utilisateurs)
        'eirbware_db.host' => 'localhost',
        'eirbware_db.dbname' => 'eirbware',
        'eirbware_db.user' => 'eirbware',
        'eirbware_db.password' => 'eirbware',

        // Répértoire des vues
	'templates.dir' => 'views',

	// Classe à utiliser pour les utilisateurs
	'user.class' => '\Eirbware\User',
    );

    /**
     * Construction de l'applicaiton
     */
    public function __construct(array $parameters = array())
    {
        parent::__construct();
        $app = $this;

        $parameters = array_replace($this->defaultParameters, $parameters);

        foreach ($parameters as $key => $value) {
            $this[$key] = $value;
        }

        // Sécurité CAS
        $this['security'] = $this->share(function() use ($app) {
            return new Security\CAS($app);
        });

        // Obtenir l'utilisateur courant, stocké dans la session
        $app['user'] = $app->share(function() use ($app) {
	    $user = $app['security']->getUser();
	    if (is_object($user)) {
		$user->setApp($app);
	    }

	    return $user;
        });

        // Gestionnaire d'utilisateurs
	$app['users'] = $app->share(function() use ($app) {
	    return new UsersManager($app);
        });

        // Session 
        $this->register(new SessionExtension());
        $this['session']->start();

        // Extension Twig
        $this->register(new TwigExtension(), array(
            'twig.path'       => $this['templates.dir'],
	    'twig.class_path' => __DIR__.'/../../vendor/silex/vendor/twig/lib',
	));

	// Ajout de l'extension Image
        $oldConfigure = isset($app['twig.configure']) ? $app['twig.configure']: function(){};
	$app['twig.configure'] = $app->protect(function($twig) use ($oldConfigure, $app) {
	    $oldConfigure($twig);
	    $twig->addExtension(new ImageExtension);
	});
    }

    /**
     * Créé la connexion à la base de données
     *
     * @param string $host l'hôte de connexion
     * @param string $dbname la base de données
     * @param string $username le nom d'utilisateur
     * @param string $password le mot de passe
     */
    public function connectDb($host = null, $dbname = null, $username = null, $password = null)
    {
        $dbs = array ();

        if (null !== $host) {
            $dbs['default'] = array(
                'driver'    => 'pdo_mysql',
                'host'      => $host,
                'dbname'    => $dbname,
                'user'      => $username,
                'password'  => $password,
            );
        }

        $dbs['eirbware'] = array(
            'driver'    => 'pdo_mysql',
            'host'      => $this['eirbware_db.host'],
            'dbname'    => $this['eirbware_db.dbname'],
            'user'      => $this['eirbware_db.user'],
            'password'  => $this['eirbware_db.password'],
        );

        $this->register(new DoctrineExtension(), array(
            'dbs.options' => $dbs,
            'db.dbal.class_path'    => __DIR__.'/../../vendor/silex/vendor/doctrine-dbal/lib',
            'db.common.class_path'  => __DIR__.'/../../vendor/silex/vendor/doctrine-common/lib',
	));

	$this['dbs']['eirbware']->query('SET CHARACTER SET UTF8');

	if (null !== $host) {
	    $this['db']->query('SET CHARACTER SET UTF8');
	}
    }

    /**
     * Fonction render, raccourcie pour Twig
     *
     * @param string $template la template à rendre
     * @param array $parametres les paramètres de template
     */
    public function render($template, array $parametres = array())
    {
        return $this['twig']->render($template, $parametres);
    }
}
