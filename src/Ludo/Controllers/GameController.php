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
                for($n = 1; $n <= $nbPlayers; $n++)
                {
                  //  var_dump($postFields["pts$n"]);
                  // see iphone_profil.php
                  // and iphone_assoc_joueurs_classement.php
                }
                
                $message = "Partie enregistrée";
            }
        }
        
        return new Response($this->twig->render('pages/addPlay/confirmation.twig', array(
            'game' => $this->games->fetchById($gameId),
            'postFields' => $postFields,
            'message' => $message,
        )));
    }
}