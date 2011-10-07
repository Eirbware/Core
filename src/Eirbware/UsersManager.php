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
        return $this->getUser(array('login' => $login));
    }

    /**
     * Obtenir un utilisateur par eid
     *
     * @param $eid l'eid
     *
     * @return array les données correspondantes
     */
    public function getByEid($eid)
    {
        return $this->getUser(array('id' => $eid));
    }

    /**
     * Obtenir un utilisateur sous conditions
     */
    public function getUser(array $conditions = array())
    {
        $query = $this->db->createQueryBuilder()
            ->select($selects = 'logins.id as eid, logins.prenom, logins.nom, logins.annee,
                filieres.nom as filiere_nom, filieres.id_syllabus as filiere_id_syllabus, filieres.id as filiere_id')
                ->from('core.logins', 'logins')
                ->join('logins', 'core.filieres', 'filieres', 'logins.filiere_id = filieres.id');

        $this->addExtension($query, $selects);

        $values = array();
        foreach ($conditions as $field => $value) {
            $query->andWhere('logins.'.$field.' = ?')
                ->getSQL()
                ;
            $values[] = $value;
        }

        $datas = $this->db->fetchAssoc($query, $values);

        if ($datas && isset($datas['id']) && null === $datas['id'] && $this->app['user.default_datas']) {
            $newDatas = array_merge(array(
                'eid' => $datas['eid']
            ), $this->app['user.default_datas']);

            $this->db->insert($this->app['user.extension'], $newDatas);

            $datas = array_replace($datas, $newDatas);
        }

        return $datas;
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
            ->select($selects = 'logins.id as eid, logins.prenom, logins.nom, logins.annee,
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
     * @param boolean $onlyReals n'inclure que les "vraies" filières
     *
     * @return array la liste des filières
     */
    public function getFilieres($onlyReals = true)
    {
        $query = $this->db->createQueryBuilder()
            ->select('filieres.*')
            ->from('core.filieres', 'filieres');

        if ($onlyReals) {
            $query->where('filieres.real = 1');
        }

        return $this->db->fetchAll($query);
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
