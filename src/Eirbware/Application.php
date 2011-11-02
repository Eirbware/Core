<?php


namespace Eirbware;

use Silex\Application as BaseApplication;

use Silex\Extension\SessionExtension;
use Silex\Extension\TwigExtension;
use Silex\Extension\DoctrineExtension;
use Silex\Extension\UrlGeneratorExtension;

use Symfony\Component\HttpFoundation\Request;

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
        'security.redirect_key' => 'redirect_after_login',
        'security.provider' => '\Eirbware\Security\EirbAuth',

        // Table d'extention utilisateur
        'user.extension' => null,
        'user.default_datas' => null,

        // Paramètres pour la base de données de Eirbware 
        // (accès en lecture seule pour les utilisateurs)
        'eirbware_db.host' => 'localhost',
        'eirbware_db.dbname' => 'core',
        'eirbware_db.user' => 'eirbware',
        'eirbware_db.password' => 'eirbware',

        // Répértoire des vues
        'templates.dir' => 'views',

        // Classe à utiliser pour les utilisateurs
        'user.class' => '\Eirbware\User',
        // Active l'utilsation du mappage objet des utilisateurs
        'user.object' => true,
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

        // Prise en compte du reverse proxy de l'enseirb-matmeca
        Request::trustProxyData();

        // Sécurité 
        $this['security'] = $this->share(function() use ($app) {
            $providerClass = $app['security.provider'];
            return new $providerClass($app);
        });

        // Obtenir l'utilisateur courant, stocké dans la session
        //
        // Ne pose pas de problème : le code de Pimple est explicite :
        // si la closure renvoi null, le prochain appel au wrapper share 
        // rapellera la closure.
        $app['user'] = $app->share(function() use ($app) {
            $app->assertDB();
            if ($app['security']->getUserEid()) {
                $user = $app['users']->getByEid($app['security']->getUserEid());
                return $user;
            } else {
                return null;
            }
        });

        // Gestionnaire d'utilisateurs
        $app['users'] = $app->share(function() use ($app) {
            $app->assertDB();
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
            $twig->addExtension(new Twig\Extension($app));
        });

        // Générateur d'URLs
        $app->register(new UrlGeneratorExtension());
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
        $options = array();

        if (null !== $host) {
            $options = array(
                'driver'    => 'pdo_mysql',
                'host'      => $host,
                'dbname'    => $dbname,
                'user'      => $username,
                'password'  => $password,
            );
        } else {
            $options = array(
                'driver'    => 'pdo_mysql',
                'host'      => $this['eirbware_db.host'],
                'dbname'    => $this['eirbware_db.dbname'],
                'user'      => $this['eirbware_db.user'],
                'password'  => $this['eirbware_db.password'],
            );
        }

        $this->register(new DoctrineExtension(), array(
            'dbs.options' => array(
                'connection' => $options
            ),
            'db.dbal.class_path'    => __DIR__.'/../../vendor/silex/vendor/doctrine-dbal/lib',
            'db.common.class_path'  => __DIR__.'/../../vendor/silex/vendor/doctrine-common/lib',
        ));

        $this['db']->query('SET CHARACTER SET UTF8');
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

    /**
     * Assure que la base de donnnée à bien été initialisée : connectDb()
     * dans le cas contraire : termine le scripte et affiche une erreur
     */
    public function assertDB()
    {
        if (!isset($this['db'])) {
            echo 'Base de donnée non initialisée';
            throw new \ErrorException('Base de donnée non initialisée');
        }
    }
}
