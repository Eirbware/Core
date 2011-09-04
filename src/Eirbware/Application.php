<?php

namespace Eirbware;

use Silex\Application as BaseApplication;

use Silex\Extension\SessionExtension;
use Silex\Extension\TwigExtension;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Jasig\phpCAS;

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
        'cas.host' => 'cas.ipb.fr',
        'cas.port' => 443,
        'cas.context' => '',

        // Répértoire des vues
        'templates.dir' => 'views'
    );

    /**
     * Construction de l'applicaiton
     */
    public function __construct(array $parameters = array())
    {
        parent::__construct();

        $parameters = array_replace($this->defaultParameters, $parameters);

        foreach ($parameters as $key => $value) {
            $this[$key] = $value;
        }

        $this->register(new SessionExtension());
        $this['session']->start();

        $this->register(new TwigExtension(), array(
            'twig.path'       => $this['templates.dir'],
            'twig.class_path' => __DIR__.'/../../vendor/twig/lib',
        ));
    }

    /**
     * Sécuriser l'accès à l'application à l'aide de CAS
     *
     * @param boolean $force forcer l'authentification ?
     * @param string $logout_url URL de déconnexion
     * @param string $login_url URL de connexion (dans le cas non-forcé)
     */
    public function secureWithCAS($force = true, $login_url = '/login', $logout_url = '/logout', $redirect = '/')
    {
        $app = $this;

        phpCAS::client(CAS_VERSION_2_0, $app['cas.host'], $app['cas.port'], $app['cas.context'], false);
        phpCAS::setNoCasServerValidation();

        // Obtenir l'utilisateur courant
        $this['user'] = $this->share(function() {
            return phpCAS::isAuthenticated() ? phpCAS::getUser() : '';
        });

        // Lorsque l'authentification est forcé, redirection vers l'identification
        $this->before(function(Request $request) use ($app, $force) {
            if ($force && !$app['user']) {
                $app['session']->set($app['cas.redirect_key'], $app['request']->getUri());
                return $app->redirect($login_url);
            }
        });

        // Connexion
        $this->get($login_url, function() use ($app, $redirect) {
            phpCAS::forceAuthentication();
            return $app->redirect($redirect);
        });

        // Déconnexion
        $this->get($logout_url, function() {
            phpCAS::logout();
        });
    }
}
