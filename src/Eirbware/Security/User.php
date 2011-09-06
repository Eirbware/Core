<?php

namespace Eirbware\Security;

/**
 * Représente un utilisateur
 *
 * @author Grégoire Passault <g.passault@gmail.com>
 */
class User
{
    /**
     * Identifiant de l'utilisateur
     */
    protected $login;

    public function __construct($login)
    {
        $this->login = $login;
    }

    /**
     * Obtenir le login
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * La conversion en chaîne donne le login
     */
    public function __toString()
    {
        return $this->getLogin();
    }
}
