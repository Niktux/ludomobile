<?php

namespace Ludo\Search;

class Engine
{
     private
        $games,
        $db;
    
    public function __construct(\Doctrine\DBAL\Driver\Connection $db, \Ludo\Model\Games $games)
    {
        $this->db = $db;
        $this->games = $games;
    }

    public function gamesByName(Patterns\Pattern $filter)
    {
        $query = sprintf(
            "SELECT jeu AS name, idjeu, img_miniature AS thumbnail, duree, jmin, jmax
             FROM ludo_jeu
             WHERE %s
             AND idjeu NOT IN ( SELECT DISTINCT idextension FROM ludo_extension )
             ORDER BY statut ASC, date_achat DESC, jeu ASC",
            $filter->sql('jeu')
        );
        
        return $this->games->convertRows($this->db->fetchAll($query));
    }
}