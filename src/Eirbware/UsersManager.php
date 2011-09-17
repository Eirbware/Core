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
        $query = $this->db->prepare('SELECT logins.id as eid, logins.prenom, logins.nom, logins.annee,
            filieres.nom as filiere_nom, filieres.id_syllabus as filiere_id_syllabus, filieres.id as filiere_id
            FROM eleves.logins 
            INNER JOIN eleves.filieres ON logins.filiere_id = filieres.id 
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
     * @param array $conditions des conditions
     * @param string $order l'ordre de tri
     */
    public function getAll(array $conditions = array(), $order = null)
    {
        $sql = 'SELECT logins.* FROM eleves.logins INNER JOIN eleves.filieres ON filieres.id = logins.filiere_id';
        $params = array();

        if (count($conditions)) {
            $conds = array();

            foreach ($conditions as $name => $value) {
                $conds[] = $name.' = ?';
                $params[] = $value;
            }
            $sql.= ' WHERE '.implode(' AND ', $conds);
        }

        if (null !== $order) {
            $sql.= ' ORDER BY '.$order;
        }

        return $this->db->fetchAll($sql, $params);
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
