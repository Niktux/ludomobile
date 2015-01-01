<?php

namespace Ludo\Model;

class Games
{
    private
        $db;

    public function __construct(\Doctrine\DBAL\Connection $db, $domain)
    {
        $this->db = $db;
        $this->domain = rtrim($domain, '/') . '/';
    }

    public function fetchById($gameId)
    {
        $query = sprintf(
            "SELECT *, img_miniature as thumbnail
             FROM ludo_jeu
             WHERE idjeu = %d",
            $gameId
        );

        return $this->convertRow($this->db->fetchAssoc($query));
    }

    public function fetchExtensions($gameId)
    {
        $query = sprintf(
            "SELECT idextension, j.*, img_miniature as thumbnail
             FROM ludo_extension as e
             INNER JOIN ludo_jeu as j ON e.idextension = j.idjeu
             WHERE idjeudebase = %d",
            $gameId
        );

        return $this->convertRows($this->db->fetchAll($query));
    }

    public function fetchProfiles($nbPlayers, $nbProfiles = 13)
    {
        $profiles = $this->fetchLastUsedProfile($nbPlayers);

        $lastProfil = '';
        if(isset($profiles[0]['profil']))
        {
            $lastProfil = $profiles[0]['profil'];
        }

        $query = sprintf(
            "SELECT profil, profil_image,  profil_nom, COUNT(*) AS nb_parties
    		 FROM (
	             SELECT pj.idpartie, pj.num_partie,
                        GROUP_CONCAT(j.idjoueur ORDER BY pj.idjoueur) AS profil,
    		            GROUP_CONCAT(j.image ORDER BY pj.idjoueur) AS profil_image,
                        GROUP_CONCAT(j.nom ORDER BY pj.idjoueur) AS profil_nom
    		     FROM ludo_partie_joueur AS pj
                 INNER JOIN ludo_partie AS p ON pj.idpartie = p.idpartie
                 INNER JOIN ludo_joueur AS j ON j.idjoueur = pj.idjoueur
    		     WHERE p.nb_joueurs = %d
    		     GROUP BY pj.idpartie, pj.num_partie
    		     ORDER BY pj.idpartie, pj.num_partie
             ) AS t
             WHERE profil <> '%s'
    		 GROUP BY profil
    		 ORDER BY nb_parties DESC
    		 LIMIT 0, %d",
            $nbPlayers,
            $lastProfil,
            $nbProfiles - 1
        );

        $profiles = array_merge($profiles, $this->db->fetchAll($query));

        array_walk($profiles, function(&$profile){
            $profile['profilId'] = str_replace(',', 'j', $profile['profil']);
            $profile['noms'] = explode(',', $profile['profil_nom']);
            $profile['images'] = explode(',', $this->translateImagePath($profile['profil_image']));
            array_walk($profile['images'], function(&$image) {
               if(empty($image))
               {
                   $image = $this->domain . 'images/profil.gif';
               }
            });
        });

        return $profiles;
    }

    public function fetchLastUsedProfile($nbPlayers)
    {
        $query = sprintf(
            "SELECT GROUP_CONCAT(pj.idjoueur ORDER BY pj.idjoueur) AS profil,
                    GROUP_CONCAT(j.image ORDER BY pj.idjoueur) AS profil_image,
                    GROUP_CONCAT(j.nom ORDER BY pj.idjoueur) AS profil_nom
             FROM ludo_partie AS p
             INNER JOIN ludo_partie_joueur AS pj ON p.idpartie = pj.idpartie
             INNER JOIN ludo_joueur AS j ON pj.idjoueur = j.idjoueur
             WHERE nb_joueurs = %d AND pj.num_partie = 1
             GROUP BY pj.idpartie
             ORDER BY date_partie DESC, pj.idpartie DESC, j.idjoueur ASC
             LIMIT 0,1",
            $nbPlayers
        );

        return $this->db->fetchAll($query);
    }

    public function convertRows(array $games)
    {
        $filteredResult = array();

        foreach($games as $game)
        {
            $filteredResult[] = $this->convertRow($game);
        }

        return $filteredResult;
    }

    public function convertRow($game)
    {
        if(isset($game['thumbnail']))
        {
            $game['thumbnail'] = $this->translateImagePath($game['thumbnail']);
        }
        if(isset($game['image']))
        {
            $game['image'] = $this->translateImagePath($game['image']);
        }
        if(isset($game['name']))
        {
            $game['name'] = $this->filterName($game['name']);
        }

        $game['method_pts'] = '';

        return $game;
    }

    private function translateImagePath($imagePath)
    {
        if(empty($imagePath))
        {
            return $this->domain . 'images/profil.gif';
        }

        if(stripos($imagePath, './img_copied/') !== false)
        {
            return str_replace('./img_copied/', '/assets/img_copied/', $imagePath);
        }

        return str_replace('./', $this->domain, $imagePath);
    }

    private function filterName($name)
    {
        return str_replace('&apos;', "'", $name);
    }

    public function fetchPlayers(array $ids)
    {
        $query = sprintf(
            "SELECT idjoueur AS id, image, nom AS name
            FROM ludo_joueur
            WHERE idjoueur IN (%s)",
            implode(',', $ids)
        );

        return $this->convertRows($this->db->fetchAll($query));
    }

    public function insertPlay($gameId, $nbPlayers, $date, array $extensions = array())
    {
        $this->db->insert('ludo_partie', array(
        	'idjeu' => $gameId,
            'date_partie' => $date,
            'nb_joueurs' => $nbPlayers,
            'nb_parties' => 1,
            'en_ligne' => 0
        ));

        $playId = $this->db->lastInsertId();

        if($playId <= 0)
        {
            throw new \RuntimeException('Error while trying to insert new play');
        }

        foreach($extensions as $extension)
        {
            // != -1
            if($extension > 0)
            {
                $this->db->insert('ludo_partie_extension', array(
                	'idpartie' => $playId,
                    'idextension' => $extension
                ));
            }
        }

        return $playId;
    }

    public function savePlayersScore($playId, array $players)
    {
        foreach($players as $player)
        {
            $this->db->insert('ludo_partie_joueur', array(
                'idpartie' => $playId,
                'num_partie' => 1,
                'idjoueur' => $player['id'],
                'idrespartie' => $player['rank'] === 1 ? $player['rank'] : 3,
                'classement' => $player['rank'],
                'points' => $player['pts']
            ));
        }
    }

    public function fetchPlayersByFirstLetters(array $letters)
    {
        if(empty($letters))
        {
            return array();
        }

        $lettersSql = implode(', ', array_map(function($item) {
            return "'$item'";
        }, $letters));

        $qb = $this->db->createQueryBuilder();
        $st = $qb
            ->select('j.idjoueur', 'nom', 'image AS photo', 'image', 'COUNT(idpartie) AS parties')
            ->from('ludo_joueur', 'j')
            ->innerJoin('j', 'ludo_partie_joueur', 'pj', 'j.idjoueur = pj.idjoueur')
            ->where("substr(nom, 1, 1) IN ($lettersSql)")
            ->groupBy('j.idjoueur')
            ->orderBy('anonyme', 'ASC')
            ->addOrderBy('parties', 'DESC')
            ->execute();

        return $this->convertRows($st->fetchAll(\PDO::FETCH_ASSOC));
    }
}