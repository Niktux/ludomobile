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
        var_dump($_POST);

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
        var_dump($_POST);
    }
}