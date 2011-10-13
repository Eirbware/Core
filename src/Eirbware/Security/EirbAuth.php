<?php

/**
 * Module de sécurité utilisant Eirb'Auth
 *
 * @author Quentin Rouxel
 */

namespace Eirbware\Security;

use Symfony\Component\HttpFoundation\Request;

class EirbAuth extends AbstractSecurity
{
    /**
     * Api de Eirb'Auth
     */
    protected $auth;

    /**
     * Initialisation
     */
    public function initialize(array &$options)
    {
        $this->auth = new Eirbware\Auth($options);
    }
    
    /**
     * Authentifie l'utilisateur
     * 
     * @param Request $request la requête
     */
    public function authenticate(array &$options, Request $request)
    {
        $this->auth->forceAuthentication();
        return $this->auth->getUser();
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        parent::logout();
        $this->auth->logout();
    }
}

