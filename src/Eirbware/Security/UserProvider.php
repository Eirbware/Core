<?php

namespace Eirbware\Security;

/**
 * Classe permettant d'intéragir avec les utilisateurs disponibles
 *
 * @author Grégoire Passault <g.passault@gmail.com>
 */
class UserProvider
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
