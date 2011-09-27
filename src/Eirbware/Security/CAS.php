<?php

namespace Eirbware\Security;

use Symfony\Component\HttpFoundation\Request;

use Jasig\phpCAS;

/**
 * Sécurité CAS
 *
 * @author Grégoire Passault <g.passault@gmail.com>
 */
class CAS extends AbstractSecurity
{
    /**
     * Initialiser CAS
     */
    public function initialize(array &$options)
    {
        phpCAS::client(
            CAS_VERSION_2_0, 
            $this->app['cas.host'],
            $this->app['cas.port'],
            $this->app['cas.context'], 
            false
        );
        phpCAS::setNoCasServerValidation();

	$app = $this->app;

	// Connexion
	$app->get($options['login_url'], function() use ($app) {
	    return $app->redirect($app['url_generator']->generate('login_check'));
	})->bind('login');
    }

    /**
     * Authentifier l'utilisateur
     *
     * @param Request $request la requête
     */
    public function authenticate(array &$options, Request $request)
    {
        phpCAS::forceAuthentication();
        return phpCAS::getUser();
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        parent::logout();
        phpCAS::logout();
    }
}
