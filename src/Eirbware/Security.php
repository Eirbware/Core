<?php

namespace Eirbware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Jasig\phpCAS;


/**
 * Gestion de la sécurité de l'application
 *
 * @author Grégoire Passault <g.passault@gmail.com>
 */
class Security
{
    /**
     * Paramètres de sécurité par défaut
     */
    public static $options = array(
        'force_auth' => true,
        'login_url' => '/login',
        'logout_url' => '/logout',
        'redirect' => '/',
        'callback' => null
    );

    /**
     * Application
     */
    private $app;

    public function __construct($app)
    {
        $this->app = $app;

        // Gestionnaire d'utilisateurs
        $app['users'] = $app->share(function() use ($app) {
            return new UsersManager($app['dbs']['eirbware']);
        });

        // Obtenir l'utilisateur courant, stocké dans la session
        $app['user'] = $app->share(function() use ($app) {
            return $app['security']->getUser();
        });
    }

    /**
     * Sécuriser l'accès à l'application à l'aide de CAS
     *
     * @param array $options les options
     */
    public function secure(array $options = array())
    {
        $options = array_replace(self::$options, $options);
        $app = $this->app;

        phpCAS::client(CAS_VERSION_2_0, $app['cas.host'], $app['cas.port'], $app['cas.context'], false);
        phpCAS::setNoCasServerValidation();

        // Lorsque l'authentification est forcé, redirection vers l'identification
        $app->before(function(Request $request) use ($app, $options) {
            if ($options['force_auth'] && !$app['user']) {
                return $app->redirect($options['login_url']);
            }
        });

        // Connexion
        $app->get($options['login_url'], function() use ($app, $options) {
            phpCAS::forceAuthentication();

            $user = new User(phpCAS::getUser());

            if (($callback = $options['callback']) !== null) {
                if (!$callback($user)) {
                    return $app->abort(403, 'Acces denied');
                }
            }

            $app['security']->setUser($user);

            return $app->redirect($options['redirect']);
        });

        // Déconnexion
        $app->get($options['logout_url'], function() use ($app, $options) {
            $app['security']->logout();
            return $app->redirect($options['redirect']);
        });
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        $this->app['session']->remove($this->app['security.session_key']);
        phpCAS::logout();
    }

    /**
     * Obtenir l'utilisateur courrant
     */
    public function getUser()
    {
        if ($this->app['session']->has($this->app['security.session_key'])) {
            return $this->app['session']->get($this->app['security.session_key']);
        }
        
        return null;
    }

    /**
     * Changer l'utilisateur courrant
     *
     * @param mixed $user l'utilisateur
     */
    public function setUser($user)
    {
        $this->app['session']->set($this->app['security.session_key'], $user);
    }
}
