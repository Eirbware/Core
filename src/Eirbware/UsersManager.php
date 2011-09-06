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
}
