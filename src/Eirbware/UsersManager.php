<?php

namespace Eirbware;

/**
 * Classe permettant d'intéragir avec les utilisateurs disponibles
 *
 * @author Grégoire Passault <g.passault@gmail.com>
 */
class UsersManager
{
    /**
     * Base de donnée
     */
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Obtenir un utilisateur par login
     *
     * @param $login l'identifiant
     *
     * @return array les données correspondantes de la base de données
     */
    public function getByLogin($login)
    {
	$query = $this->db->prepare('SELECT logins.id, logins.prenom, logins.nom, logins.annee,
	    filieres.nom as filiere_nom, filieres.id_syllabus as filiere_id_syllabus
	    FROM logins 
	    INNER JOIN filieres ON logins.filiere_id = filieres.id 
	    WHERE login = ?');
	$query->bindValue(1, $login);

	if ($query->execute()) {
	    return $query->fetch(\PDO::FETCH_ASSOC);
	}

	return null;
    }
}
