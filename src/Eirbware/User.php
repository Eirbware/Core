<?php

namespace Eirbware;

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

    /**
     * Application
     */
    protected $app;

    /**
     * Données provenant de la base de données
     */
    protected $datas = null;

    /**
     * L'utilisateur a t-il été chargé depuis la base ?
     */
    protected $loaded = false;

    public function __construct($login, $app = null)
    {
	$this->login = $login;
	$this->app = $app;
    }

    /**
     * Définir l'application
     */
    public function setApp($app)
    {
	$this->app = $app;
    }

    /**
     * Getters
     */
    public function __call($method, $args)
    {
	if (preg_match('#^get#', $method)) {
	    $property = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', substr($method, 3)));
	    if (isset($this->datas[$property])) {
		return $this->datas[$property];
	    } else { 
		return null;
	    }
	} else {
	    throw new \RuntimeException('Unknown method: '.$method);
	}
    }

    /**
     * Charger l'utilisateur depuis la base
     */
    public function load()
    {
	if (!$this->loaded && $this->app) {
	    $this->datas = $this->app['users']->getByLogin($this->getLogin()); 
	    $this->loaded = true;
	}
	return (null !== $this->datas);
    }

    /**
     * La conversion en chaîne donne le login
     */
    public function __toString()
    {
        return $this->getLogin();
    }

    /**
     * Seul le login est stocké dans la sérialization
     */
    public function __sleep()
    {
	return array('login');
    }
}
