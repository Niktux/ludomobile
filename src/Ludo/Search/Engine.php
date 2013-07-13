<?php

namespace Ludo\Search;

class Engine
{
     private
        $db;
    
    public function __construct(\Doctrine\DBAL\Driver\Connection $db)
    {
        $this->db = $db;
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
        
        return \Ludo\Model\Games::convertRows($this->db->fetchAll($query));
    }
}