<?php

namespace Eirbware\Lib;

/**
 * Classe d'API pour Eirb'auth
 *
 * @author Grégoire Passault <g.passault@gmail.com>
 */
class Auth
{
    /**
     * Options :
     * server : url complète du serveur d'Eirb'Auth
     */
    protected $options = array('server' => 'http://auth.eirb.fr',
                               'session_key_user' => 'eirbauth_user',
                               'session_key_url' => 'eirbauth_url',
                              );

    /**
     * Entrée dans la session correspondant à l'utilisateur
     */
    protected $session_key;

    public function __construct($options = array())
    {
        $this->options = array_replace($this->options, $options);

        $this->authenticate();
    }

    /**
     * Obtenir l'utilisateur actuellement identifié
     */
    public function getUser()
    {
        $options = $this->options;
        if (isset($_SESSION[$options['session_key_user']])) {
            return $_SESSION[$options['session_key_user']];
        }

        return null;
    }

    /**
     * Forcer l'authentification
     */
    public function forceAuthentication()
    {
        if (!$this->getUser()) {
            $url = 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI'];

            $_SESSION[$this->options['session_key_url']] = $url;

            header('location: ' . $this->options['server'] . '/login?redirect=' . $url);
            exit(0);
        }
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        unset($_SESSION[$this->options['session_key_user']]);
    }

    /**
     * Définir l'utilisateur
     */
    public function setUser($user)
    {
        $_SESSION[$this->options['session_key_user']] = $user;
    }

    /**
     * Authentification
     */
    public function authenticate()
    {
        $options = $this->options;
        if (!$this->getUser()) {
            if (isset($_GET['auth_ticket'])) {

                $url = $options['server'] . '/validate?auth_ticket=' . $_GET['auth_ticket'];
                $handle = fopen($url, 'r');
                if (!$handle) {
                    return;
                }
                $user = fgets($handle);

                if ($user) {
                    $this->setUser($user);
                }

                if (isset($_SESSION[$options['session_key_url']])) {
                    header('location: ' . $_SESSION[$options['session_key_url']]);
                    exit(0);
                }
                else {
                    header('location: http://' . $_SERVER["HTTP_HOST"]);
                    exit(0);
                }

            }
        }
    }
}
