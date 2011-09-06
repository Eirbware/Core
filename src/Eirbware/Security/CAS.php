<?php

namespace Eirbware\Security;

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
    public function initialize()
    {
        phpCAS::client(
            CAS_VERSION_2_0, 
            $this->app['cas.host'],
            $this->app['cas.port'],
            $this->app['cas.context'], 
            false
        );
        phpCAS::setNoCasServerValidation();
    }

    /**
     * Authentifier l'utilisateur
     */
    public function authenticate()
    {
        phpCAS::forceAuthentication();
        return new User(phpCAS::getUser());
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
