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
    private $parameters = array(
        // Paramètres du serveur CAS
        'cas_host' => 'cas.ipb.fr',
        'cas_port' => 443,
        'cas_context' => '',
        'cas_session_key'  => 'cas',

        // Répértoire des vues
        'views_dir' => 'views'
    );

    /**
     * Construction de l'applicaiton
     */
    public function __construct()
    {
        parent::__construct();

        foreach ($this->parameters as $key => $value) {
            $this[$key] = $value;
        }

        $this->register(new SessionExtension());
        $this['session']->start();

        $this->register(new TwigExtension(), array(
            'twig.path'       => $this['views_dir'],
            'twig.class_path' => __DIR__.'/../../vendor/twig/lib',
        ));
    }

    /**
     * Sécuriser l'accès à l'application à l'aide de CAS
     */
    public function secureWithCAS($logout_url = '/logout')
    {
        // Support de l'identification
        $app = $this;
        $this->before(function(Request $request) use ($app) {
            phpCAS::client(CAS_VERSION_2_0, $app['cas_host'], $app['cas_port'], $app['cas_context'], false);
            phpCAS::setNoCasServerValidation();
            phpCAS::forceAuthentication();
        });

        // Obtenir l'utilisateur courant
        $this['user'] = $this->share(function() {
            return phpCAS::getUser();
        });

        // Déconnexion
        $this->get($logout_url, function() {
            phpCAS::logout();
        });
    }
}
