<?php

namespace Eirbware\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gestion de la sécurité de l'application
 *
 * @author Grégoire Passault <g.passault@gmail.com>
 */
abstract class AbstractSecurity
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
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
        $self = $this;

        // Gestionnaire d'utilisateurs
        $app['users'] = $app->share(function() use ($app) {
            return new UserProvider($app['dbs']['eirbware']);
        });

        // Obtenir l'utilisateur courant, stocké dans la session
        $app['user'] = $app->share(function() use ($app, $self) {
            return $self->getUser();
        });
    }

    /**
     * Sécuriser l'accès à l'application
     *
     * @param array $options les options
     */
    public function secure(array $options = array())
    {
        $options = array_replace(self::$options, $options);
        $app = $this->app;
        $self = $this;

        $self->initialize();

        // Lorsque l'authentification est forcé, redirection vers l'identification
        $app->before(function(Request $request) use ($app, $options) {
            if ($options['force_auth'] && !$app['user']) {
                return $app->redirect($options['login_url']);
            }
        });

        // Connexion
        $app->get($options['login_url'], function() use ($app, $options, $self) {
            $user = $self->authenticate();

            if (($callback = $options['callback']) !== null) {
                $return = $callback($user);
            } else {
                $return = true;
            }

            if (!($user && $return)) {
                return $app->abort(403, 'Acces denied');
            }

            $self->setUser($user);

            return $app->redirect($options['redirect']);
        });

        // Déconnexion
        $app->get($options['logout_url'], function() use ($app, $options, $self) {
            $self->logout();
            return $app->redirect($options['redirect']);
        });
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        $this->app['session']->remove($this->app['security.session_key']);
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

    /**
     * Initialiser le module de sécurité
     */
    public abstract function initialize();

    /**
     * Authentifier l'utilisateur
     */
    public abstract function authenticate();
}