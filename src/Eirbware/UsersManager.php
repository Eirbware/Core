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
     * Application
     */
    protected $app;

    /**
     * Base de données
     */
    protected $db;

    public function __construct($app)
    {
        $this->app = $app;
        $this->db = $app['db'];
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
	return $this->db->fetchAssoc('SELECT logins.id as eid, logins.prenom, logins.nom, logins.annee,
            filieres.nom as filiere_nom, filieres.id_syllabus as filiere_id_syllabus, filieres.id as filiere_id
            FROM eleves.logins 
            INNER JOIN eleves.filieres ON logins.filiere_id = filieres.id 
            WHERE login = ?', array($login));
    }

    /**
     * Obtenir la liste des élèves, en filtrant éventuellement par filière
     *
     * @param boolean $queryBuilder obtenir un QueryBuilder au lieu du résultat
     */
    public function getAll($queryBuilder = false)
    {
	$query = $this->db->createQueryBuilder()
            ->select('eleves.id as eid, eleves.prenom, eleves.nom, eleves.annee,
                filieres.nom as filiere_nom, eleves.login,
		filieres.id_syllabus as filiere_id_syllabus, filieres.id as filiere_id')
	    ->from('eleves.logins', 'eleves')
	    ->join('eleves', 'eleves.filieres', 'filieres', 'filieres.id = eleves.filiere_id');

	if ($queryBuilder) {
	    return $query;
	}

        return $this->db->fetchAll($query);
    }

    /**
     * Créé une instance d'utilisateur
     *
     * @param string $login identifiant
     */
    public function create($login)
    {
        $user_class = $this->app['user.class'];

        return new $user_class($login, $this->app);
    }

    /**
     * Obtenir la liste des filières
     *
     * @return array la liste des filières
     */
    public function getFilieres()
    {
        return $this->db->fetchAll('SELECT * FROM eleves.filieres');
    }

    /**
     * Obtenir une filière par code syllabus
     *
     * @param string $code le code syllabus
     *
     * @return array la filiere
     */
    public function getFiliere($syllabus)
    {
        return $this->db->fetchAssoc('SELECT * FROM eleves.filieres WHERE id_syllabus = ?', array($syllabus));
    }
}
