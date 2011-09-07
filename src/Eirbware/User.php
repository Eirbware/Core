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
     * Référence vers le manager
     */
    protected $manager;

    /**
     * Données provenant de la base de données
     */
    protected $datas = null;

    /**
     * L'utilisateur a t-il été chargé depuis la base ?
     */
    protected $loaded = false;

    public function __construct($login, $manager = null)
    {
	$this->login = $login;
    }

    /**
     * Définir le manager
     */
    public function setManager($manager)
    {
	$this->manager = $manager;
    }

    /**
     * Obtenir le login
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * L'utilisateur existe t-il dans la base de données ?
     */
    public function exists()
    {
	return $this->load();
    }

    /**
     * ID de l'utilisateur
     */
    public function getId()
    {
	return $this->load() ? $this->datas['id'] : null;
    }

    /**
     * Nom de l'utilisateur
     */
    public function getNom()
    {
	return $this->load() ? $this->datas['nom'] : null;
    }

    /**
     * Prénom de l'utilisateur
     */
    public function getPrenom()
    {
	return $this->load() ? $this->datas['prenom'] : null;
    }

    /**
     * Filière de l'utilisateur
     */
    public function getFiliere()
    {
	return $this->load() ? $this->datas['filiere_id_syllabus'] : null;
    }

    /**
     * Nom de la filière de l'utilisateur
     */
    public function getFiliereNom()
    {
	return $this->load() ? $this->datas['filiere_nom'] : null;
    }

    /**
     * Année de l'utilisateur
     */
    public function getAnnee()
    {
	return $this->load() ? $this->datas['annee'] : null;
    }

    /**
     * Nom de l'utilisateur
     */

    /**
     * Charger l'utilisateur depuis la base
     */
    public function load()
    {
	if (!$this->loaded && $this->manager) {
	    $this->datas = $this->manager->getByLogin($this->getLogin()); 
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
