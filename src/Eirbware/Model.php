<?php

namespace Eirbware;

/**
 * Modèle de base
 */
class Model 
{
    protected $app;
    protected $db;

    public function __construct($app)
    {
        $this->app = $app;
        $this->db = $app['db'];
    }

    /**
     * Formate un objet de type datetime en une chaîne compréhensible
     * pour la base de données
     */
    public function dateToSql(\DateTime $date)
    {
        return $date->format('Y/m/d H:i:s');
    }

    /**
     * Insère un enregistrement dans la base de données
     */
    public function insert($table, array $data, array $types = array())
    {
        return $this->db->insert($table, $data, $types);
    }

    /**
     * Met à jour un ou des enregistrements
     */
    public function update($table, array $data, array $conditions, array $types = array())
    {
        return $this->db->update($table, $data, $conditions, $types);
    }

    /**
     * Obtenir des enregistrements
     */
    public function fetchAssoc($sql, array $params = array())
    {
        return $this->db->fetchAssoc($sql, $params);
    }

    /**
     * Obtient un QueryBuilder
     */
    public function createQueryBuilder()
    {
        return $this->db->createQueryBuilder();
    }

    /**
     * Supprime des enregistrements
     */
    public function delete($table, array $conditions)
    {
        return $this->db->delete($table, $conditions);
    }
}
