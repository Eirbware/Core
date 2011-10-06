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
        $query = $this->db->createQueryBuilder()
            ->select($selects = 'logins.id as eid, logins.prenom, logins.nom, logins.annee,
                filieres.nom as filiere_nom, filieres.id_syllabus as filiere_id_syllabus, filieres.id as filiere_id')
                ->from('core.logins', 'logins')
                ->join('logins', 'core.filieres', 'filieres', 'logins.filiere_id = filieres.id');

        $this->addExtension($query, $selects);

        $query->where('logins.login = ?')
            ->getSQL()
            ;

        return $this->db->fetchAssoc($query, array($login));
    }

    /**
     * Ajout l'utilisateur extension à la requête
     *
     * @param QueryBuilder $queryBuilder le queryBuilder
     * @param string $selects les élément séléctionnés
     */
    public function addExtension($queryBuilder, $selects = '')
    {
        if (null !== $this->app['user.extension']) {
            $queryBuilder->leftJoin('logins', $this->app['user.extension'], 'user_extension', 'user_extension.eid = logins.id')
                ->select('user_extension.*, '.$selects);
        }
    }

    /**
     * Obtenir la liste des élèves, en filtrant éventuellement par filière
     *
     * @param boolean $queryBuilder obtenir un QueryBuilder au lieu du résultat
     */
    public function getAll($queryBuilder = false)
    {
        $query = $this->db->createQueryBuilder()
            ->select($selects = 'logins.id as eid, core.prenom, core.nom, logins.annee,
                filieres.nom as filiere_nom, logins.login,
                filieres.id_syllabus as filiere_id_syllabus, filieres.id as filiere_id')
                ->from('core.logins', 'logins')
                ->join('logins', 'core.filieres', 'filieres', 'filieres.id = logins.filiere_id');

        $this->addExtension($query, $selects);

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
        return $this->db->fetchAll('SELECT * FROM core.filieres');
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
        return $this->db->fetchAssoc('SELECT * FROM core.filieres WHERE id_syllabus = ?', array($syllabus));
    }
}
