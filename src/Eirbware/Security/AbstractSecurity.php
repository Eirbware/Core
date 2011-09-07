<?php

namespace Eirbware\Security;

use Symfony\Component\HttpFoundation\Request;

use Eirbware\User;

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
        'login_check_url' => '/login_check',
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

        $self->initialize($options);

        // Lorsque l'authentification est forcé, redirection vers l'identification
	$app->before(function(Request $request) use ($app, $options) { 
	    if ($app['request']->getPathInfo() == $options['login_check_url']) {
		return;
	    }
            if ($options['force_auth'] && !$app['user']) {
                return $app->redirect($options['login_url']);
            }
        });

        // Connexion
        $app->get($options['login_check_url'], function(Request $request) use ($app, $options, $self) {

            $user = new User($self->authenticate($options, $request));

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
    public function initialize(array &$options) 
    {
    }

    /**
     * Authentifier l'utilisateur
     */
    public abstract function authenticate(array &$options, Request $request);
}
