<?php

namespace Eirbware;

use Silex\Application as BaseApplication;

use Silex\Extension\SessionExtension;
use Silex\Extension\TwigExtension;

use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Classe de base pour les applications web de Eirbware
 *
 * @author Grégoire Passault <g.passault@gmail.com
 */
class Application extends BaseApplication
{
    /**
     * Construction de l'applicaiton
     */
    public function __construct()
    {
        parent::__construct();

        $this->register(new SessionExtension());

        $this->register(new TwigExtension(), array(
            'twig.path'       => 'views',
            'twig.class_path' => __DIR__.'/../../vendor/twig/lib',
        ));
    }

    /**
     * Sécuriser l'accès à l'application à l'aide de CAS
     */
    public function secureWithCAS()
    {
        $this->before(function($request) {
        });
    }
}
