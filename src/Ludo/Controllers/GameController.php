<?php

namespace Ludo\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GameController
{
    private
        $request,
        $games,
        $twig;
    
    public function __construct(\Twig_Environment $twig, \Ludo\Model\Games $games, Request $request)
    {
        $this->request = $request;
        $this->twig = $twig;
        $this->games = $games;
    }
    
    public function postAddPlayAction($gameId)
    {
        return $this->addPlayAction($gameId);
    }
    
    public function addPlayAction($gameId)
    {
        $html = $this->twig->render('pages/addPlay.twig', array(
            'game' => $this->games->fetchById($gameId),
            'extensions' => $this->games->fetchExtensions($gameId)
        ));
        
        return new Response($html);
    }
    
    public function selectPlayersAction($gameId)
    {
        return new Response($this->twig->render('pages/addPlay/selectPlayers.twig', array(
            'game' => $this->games->fetchById($gameId),
            'postFields' => $this->request->request->all(),
            'profiles' => $this->games->fetchProfiles($this->request->request->getInt('nbPlayers'))
        )));
    }
    
    public function scoresAction($gameId)
    {
        $template = 'pages/scores/gagnantPerdant.twig';
        $game = $this->games->fetchById($gameId);
        
        if($game['has_points'] === '1')
        {
            $template = 'pages/scores/points.twig';
        }
        
        $html = $this->twig->render($template, array(
            'game' => $game,
            'postFields' => $this->request->request->all(),
            'players' => $this->games->fetchPlayers(explode('j', $this->request->get('profil'))),
        ));
        
        return new Response($html);
    }
    
    public function saveAction($gameId)
    {
        $postFields = $this->request->request->all();

        $message = "ERREUR";
        if(isset($postFields['nbPlayers']))
        {
            $nbPlayers = trim($postFields['nbPlayers']);
            
            if(is_numeric($nbPlayers) && $nbPlayers > 0)
            {
                $extensions = array();
                if($this->request->request->has('extensions'))
                {
                    $extensionList = $this->request->request->get('extensions');
                    $extensions = explode(',', $extensionList);
                }
                    
                $playId = $this->games->insertPlay($gameId, $nbPlayers, $this->request->request->get('date'), $extensions);
                $players = $this->extractDataFromRequest($gameId, $postFields);
                //var_dump($players);
                $this->games->savePlayersScore($playId, $players);
                
                $message = "Partie enregistrée";
            }
        }
        
        return new Response($this->twig->render('pages/addPlay/confirmation.twig', array(
            'game' => $this->games->fetchById($gameId),
            'postFields' => $postFields,
            'message' => $message,
        )));
    }
    
    private function extractDataFromRequest($gameId, array $postFields)
    {
        $nbPlayers = $postFields['nbPlayers'];
        $players = array();
        
        for($i = 1; $i <= $nbPlayers; $i++)
        {
            $playerData = array(
            	'id' => (int) $postFields['player' . $i],
                'pts' => (int) isset($postFields['pts' . $i]) ? $postFields['pts' . $i] : 0 ,
                'rank' => (int) $postFields['rank' . $i],
            );
            
            $players[] = $playerData;
        }
        
        if(isset($postFields['auto']))
        {
            $points = array();
            foreach($postFields as $name => $value)
            {
                if(stripos($name, 'pts') === 0)
                {
                    $points[] = $value;
                }
            }
        
            $game = $this->games->fetchById($gameId);
            if(isset($game['mthf_ranking']) && $game['mthf_ranking'] === 0)
            {
                rsort($points);    
            }
            else
            {
                sort($points);
            }
            
            foreach($players as $key => $player)
            {
                $players[$key]['rank'] = array_search($player['pts'], $points) + 1; 
            }
        }
        
        return $players;
    }
}