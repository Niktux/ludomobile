<?php

namespace Ludo\Model;

class Games
{
    const
        IMAGES_DOMAIN_NAME = 'http://nico.ludotheque.net/';
    
    private
        $db;
    
    public function __construct(\Doctrine\DBAL\Connection $db)
    {
        $this->db = $db;
    }
    
    public function fetchById($gameId)
    {
        $query = sprintf(
            "SELECT *, img_miniature as thumbnail
             FROM ludo_jeu
             WHERE idjeu = %d",
            $gameId
        );
        
        return self::convertRow($this->db->fetchAssoc($query));
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
        
        return self::convertRows($this->db->fetchAll($query));
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
            $profile['images'] = explode(',', self::translateImagePath($profile['profil_image']));
            array_walk($profile['images'], function(&$image) {
               if(empty($image))
               {
                   $image = self::IMAGES_DOMAIN_NAME . 'images/profil.gif';
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
    
    public static function convertRows(array $games)
    {
        $filteredResult = array();
    
        foreach($games as $game)
        {
            $filteredResult[] = self::convertRow($game);
        }
    
        return $filteredResult;
    }
    
    public static function convertRow($game)
    {
        if(isset($game['thumbnail']))
        {
            $game['thumbnail'] = self::translateImagePath($game['thumbnail']);
        }
        if(isset($game['name']))
        {
            $game['name'] = self::filterName($game['name']);
        }
        
        return $game;
    }
    
    private static function translateImagePath($imagePath)
    {
        return  str_replace('./', self::IMAGES_DOMAIN_NAME, $imagePath);
    }
    
    private static function filterName($name)
    {
        return str_replace('&apos;', "'", $name);
    }
}