<?php

namespace Eirbware\Security;

use Symfony\Component\HttpFoundation\Request;

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
        'redirect_logout' => 'http://auth.eirb.fr/logout',
        'callback' => null,
        'patterns' => array(
            '^/'
        ),
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
        $app->before(function(Request $request) use ($app, $options, $self) {

            $path = $app['request']->getPathInfo();
            if ($path == $options['login_check_url'] || $path == $options['login_url']) {
                return;
            }
            $matched = false;
            foreach ($options['patterns'] as $pattern) {
                if (preg_match('#'.$pattern.'#', $path)) {
                    $matched = true;
                    break;
                }
            }

            $user = $self->getUserEid();

            if ($matched && $options['force_auth'] && empty($user)) {
                $app['session']->set('redirect_after_login', $app['request']->getUri());
                return $app->redirect($app['url_generator']->generate('login_check'));
            }
        });

        // Vérification des identifiants
        $app->get($options['login_check_url'], function(Request $request) use ($app, $options, $self) {

            $login = $self->authenticate($options, $request);
            $user = $app['users']->getByLogin($login);

            if (($callback = $options['callback']) !== null) {
                $return = $callback($user);
            }
            else {
                $return = true;
            }

            if (!$return || empty($user)) {
                return $app->abort(403, 'Acces denied for '.$login);
            }

            $eid = null;
            if ($app['user.object']) {
                $eid = $user->eid();
            }
            else {
                $eid = $user['eid'];
            }

            $self->setUserEid($eid);

            return $app->redirect($self->getRedirectUrl() ?: $options['redirect']);

        })->bind('login_check');

        // Déconnexion
        $app->get($options['logout_url'], function() use ($app, $options, $self) {
            $self->logout();
            return $app->redirect($options['redirect_logout']);
        })->bind('logout');
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        $this->app['session']->remove($this->app['security.session_key']);
    }

    /**
     * Obtenir l'utilisateur courrant par son eid
     */
    public function getUserEid()
    {
        if ($this->app['session']->has($this->app['security.session_key'])) {
            return $this->app['session']->get($this->app['security.session_key']);
        }

        return null;
    }

    /**
     * Changer l'utilisateur courrant par son eid
     *
     * @param mixed $user l'utilisateur
     */
    public function setUserEid($eid)
    {
        if (isset($eid)) {
            $this->app['session']->set($this->app['security.session_key'], $eid);
        }
    }

    /**
     * Définir l'URL de redirection après le login
     */
    public function setRedirectUrl($url)
    {
        $this->app['session']->set($this->app['security.redirect_key'], $url);
    }

    /**
     * Obtenir l'URL de redirection après le login
     */
    public function getRedirectUrl()
    {
        $key = $this->app['security.redirect_key'];
        if ($this->app['session']->has($key)) {
            return $this->app['session']->get($key);
        }
        return null;
    }

    /**
     * Initialiser le module de sécurité
     */
    public function initialize(array &$options) 
    {
    }

    /**
     * Authentifier l'utilisateur
     *
     * Retourne le login de l'utilisateur
     */
    public abstract function authenticate(array &$options, Request $request);
}
