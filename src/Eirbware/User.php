<?php

namespace Eirbware;

/**
 * Représente un utilisateur
 *
 * @author Grégoire Passault <g.passault@gmail.com>
 * @author Quentin Rouxel
 */
class User
{
    /**
     * Application
     */
    protected $app;

    /**
     * Données provenant de la base de données
     */
    protected $datas;
    
    public function __construct($datas, $app = null)
    {
        $this->datas = $datas;
        $this->app = $app;
    }
    
    /**
     * Eid
     */
    public function getEid()
    {
       return $this->eid();
    }

    /**
     * Login
     */
    public function getLogin()
    {
        return $this->login();
    }

    /**
     * Getters
     */
    public function __call($property, $args)
    {
        if ($this->exists() && isset($this->datas[$property])) {
            return $this->datas[$property];
        } else { 
            return null;
        }
    }

    public function __get($property)
    {
        return $this->$property();
    }
    
    /**
     * Existe t-il ?
     */
    public function exists()
    {
        return (bool)(isset($this->datas) && !empty($this->datas['eid']) && !empty($this->datas['login']));
    }
    /**
     * Alias de exists()
     */
    public function exist()
    {
        return $this->exists();
    }

    /**
     * La conversion en chaîne donne le login
     */
    public function __toString()
    {
        return $this->getLogin();
    }

    /**
     * Sauvegarde des données dans la sérialization
     */
    public function __sleep()
    {
        return array('datas');
    }
}
