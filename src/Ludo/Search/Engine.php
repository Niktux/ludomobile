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

    public function gamesByName(Patterns\Pattern $filter, $includeExtensions = false)
    {
        $query = sprintf(
            "SELECT jeu AS name, idjeu, img_miniature AS thumbnail, duree, jmin, jmax
             FROM ludo_jeu
             WHERE %s
             %s
             ORDER BY statut ASC, date_achat DESC, jeu ASC",
            $filter->sql('jeu'),
            $includeExtensions ? '' : "AND idjeu NOT IN ( SELECT DISTINCT idextension FROM ludo_extension )"
        );

        return $this->games->convertRows($this->db->fetchAll($query));
    }

    public function gamesByCriterias(array $criterias, $includeExtensions = false)
    {
        $nbj = $criterias['players'];

        $criteriasSQL = "";

        if(isset($criterias['dmin']))
        {
            $dmin = $criterias['dmin'];
            $dmax = $criterias['dmax'];
            $criteriasSQL .= " AND (duree >= $dmin AND duree <= $dmax) ";
        }
        if(isset($criterias['type']))
        {
            $type = $criterias['type'];
            $criteriasSQL .= " $type ";
        }
        if(isset($criterias['envy']))
        {
            $envy = $criterias['envy'];
            $criteriasSQL .= " $envy ";
        }

        $query = sprintf(
            "SELECT jeu AS name, idjeu, img_miniature AS thumbnail, duree, jmin, jmax
             FROM ludo_jeu
             LEFT JOIN (
                 SELECT *
                 FROM ludo_meeple
                 WHERE nb_joueurs = $nbj
             ) AS meeple USING (idjeu)
             LEFT JOIN (
                 SELECT idjeu, MAX(date_partie) AS last_partie, COUNT(*) AS nb_parties
                 FROM ludo_partie
                 GROUP BY idjeu
             ) AS partie USING (idjeu)
             WHERE jmax >= $nbj AND jmin <= $nbj
             AND (color = 'green' OR color IS NULL)
             AND statut = 0
             %s
             %s
             ORDER BY date_achat DESC, jeu ASC",
            $criteriasSQL,
            $includeExtensions ? '' : "AND idjeu NOT IN ( SELECT DISTINCT idextension FROM ludo_extension )"
        );

        return $this->games->convertRows($this->db->fetchAll($query));
    }
}