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
     * Gérer un utilisateur
     *
     * @param User l'utilisateur
     */
    public function manage($user)
    {
	if (is_object($user)) {
	    $user->setManager($this);
	}
    }

    /**
     * Obtenir les données utilisateur par login
     *
     * @param $login l'identifiant
     *
     * @return array les données correspondantes de la base de données
     */
    public function getByLogin($login)
    {
	$query = $this->db->prepare('SELECT logins.id, logins.prenom, logins.nom, logins.annee,
	    filieres.nom as filiere_nom, filieres.id_syllabus as filiere_id_syllabus, filieres.id as filiere_id
	    FROM logins 
	    INNER JOIN filieres ON logins.filiere_id = filieres.id 
	    WHERE login = ?');
	$query->bindValue(1, $login);

	if ($query->execute()) {
	    return $query->fetch(\PDO::FETCH_ASSOC);
	}

	return null;
    }

    /**
     * Obtenir la liste des élèves, en filtrant éventuellement par filière
     *
     * @param int $filiere l'id de la filiere
     */
    public function getAll(array $conditions = array())
    {
	$sql = 'SELECT logins.* FROM logins INNER JOIN filieres ON filieres.id = logins.filiere_id';
	$params = array();

	if (count($conditions)) {
	    $conds = array();

	    foreach ($conditions as $name => $value) {
		$conds[] = $name.' = ?';
		$params[] = $value;
	    }
	    $sql.= ' WHERE '.implode(' AND ', $conds);
	}

	return $this->db->fetchAll($sql, $params);
    }

    /**
     * Obtenir la liste des filières
     *
     * @return array la liste des filières
     */
    public function getFilieres()
    {
	return $this->db->fetchAll('SELECT * FROM filieres');
    }
}
