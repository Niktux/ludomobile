<?php

namespace Ludo\Controllers;

use Ludo\Search\Patterns\Pattern;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WizardController
{
    private
        $request,
        $twig,
        $searchEngine;

    public function __construct(\Twig_Environment $twig, \Ludo\Search\Engine $searchEngine, Request $request)
    {
        $this->request = $request;
        $this->twig = $twig;
        $this->searchEngine = $searchEngine;
    }

    public function indexAction()
    {
        $html = $this->twig->render('pages/wizard.twig');

        return new Response($html);
    }

    public function step2Action($type)
    {
        $html = $this->twig->render('pages/wizard/step2.twig', [
            'type' => $type
        ]);

        return new Response($html);
    }

    public function step3Action($type, $duration)
    {
        $html = $this->twig->render('pages/wizard/step3.twig', [
            'type' => $type,
            'duration' => $duration,
        ]);

        return new Response($html);
    }

    public function step4Action($type, $duration, $envy)
    {
        $html = $this->twig->render('pages/wizard/step4.twig', [
            'type' => $type,
            'duration' => $duration,
            'envy' => $envy,
        ]);

        return new Response($html);
    }

    public function resultAction($type, $duration, $envy, $nbPlayers)
    {
        $criterias = [
            'players' => $nbPlayers,
        ];

        $durations = [
            15 => ['dmin' => 0, 'dmax' => 20],
            30 => ['dmin' => 15, 'dmax' => 30],
            60 => ['dmin' => 20, 'dmax' => 60],
            100 => ['dmin' => 45, 'dmax' => 310],
            120 => ['dmin' => 75, 'dmax' => 310],
        ];

        if($duration !== '999')
        {
            $criterias['dmin'] = $durations[$duration]['dmin'];
            $criterias['dmax'] = $durations[$duration]['dmax'];
        }

        $types = [
            'recent' => " AND DATEDIFF(CURDATE(), date_achat) < 250 ",
            'new' => " AND nb_parties IS NULL ",
            'renew' => " AND nb_parties < 3 AND DATEDIFF(CURDATE(), date_achat) < 250 ",
            'known' => " AND nb_parties >= 5 AND DATEDIFF(CURDATE(), last_partie) < 1400 ",
            'past' => " AND DATEDIFF(CURDATE(), last_partie) > 1000 AND nb_parties <= 10 AND nb_parties > 2 ",
            'oldies' => " AND DATEDIFF(CURDATE(), last_partie) > 700 AND nb_parties > 10 ",
            'sure' => " AND nb_parties > 20 ",
        ];

        if($type !== 'default')
        {
            $criterias['type'] = $types[$type];
        }

        $envies = [
            'ambiance' => " AND idjeu IN ( SELECT idjeu FROM ludo_mecanisme_jeu WHERE idmecanisme = 12 ) ",
            'apero' => " AND idjeu IN ( SELECT idjeu FROM ludo_critere_jeu WHERE idcritere = 1 ) ",
            'coop' => " AND idjeu IN ( SELECT idjeu FROM ludo_mecanisme_jeu WHERE idmecanisme = 17 ) ",
            'lulu' => " AND idjeu IN ( SELECT idjeu FROM ludo_critere_jeu WHERE idcritere = 29 ) ",
            'complexe' => " AND idcomplexite = 4 ",
            'deux' => " AND idjeu IN ( SELECT idjeu FROM ludo_critere_jeu WHERE idcritere = 26 ) ",
        ];

        if($envy !== 'default')
        {
            $criterias['envy'] = $envies[$envy];
        }

        $html = $this->twig->render('pages/search.twig', array(
            'games' => $this->searchEngine->gamesByCriterias($criterias)
        ));

        return new Response($html);
    }
}