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
    public function __call($method, $args)
    {
        return $this->__get($method);
    }

    public function __get($property)
    {
        if ($this->exists() && isset($this->datas[$property])) {
            return $this->datas[$property];
        } else { 
            return null;
        }
    }
    
    /**
     * Existe t-il ?
     */
    public function exists()
    {
        return isset($this->datas) && !empty($this->datas['eid']) && !empty($this->datas['login']);
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
