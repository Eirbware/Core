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

        $options['login_check_url'] = $options['login_url'];
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
