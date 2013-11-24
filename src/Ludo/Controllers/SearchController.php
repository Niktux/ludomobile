<?php

namespace Ludo\Controllers;

use Ludo\Search\Patterns\Pattern;
use Ludo\Search\Patterns\String;
use Ludo\Search\Patterns\BeginsWith;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController
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
        $html = $this->twig->render('pages/index.twig');
        
        return new Response($html);
    }
    
    public function searchAction()
    {
        $searchPattern = new String($this->filterSearchString());
        
        return $this->searchResultAction($searchPattern);
    }
    
    private function filterSearchString()
    {
        return $this->request->request->getAlnum('q');
    }
    
    public function searchByLetterAction($letter)
    {
        $searchPattern = $this->createBeginsWith($letter);
                
        return $this->searchResultAction($searchPattern);
    }
    
    public function searchByLettersAction($letter1, $letter2)
    {
        $searchPattern = $this->createBeginsWith($letter1)->or_($this->createBeginsWith($letter2));
        
        return $this->searchResultAction($searchPattern);
    }
    
    /**
     * @returns \Ludo\Search\Patterns\Pattern
     */
    private function createBeginsWith($letter)
    {
        if($letter !== '09')
        {
            return new BeginsWith($letter);
        }
        
        $p = new BeginsWith(0);
        for($i = 1; $i <= 9; $i++)
        {
            $p = $p->or_(new BeginsWith($i));
        }
        
        return $p;
    }
    
    private function searchResultAction(Pattern $searchPattern)
    {
        $includeExtensions = $this->request->request->getInt('extensions') === 1;
        
        $html = $this->twig->render('pages/search.twig', array(
            'games' => $this->searchEngine->gamesByName($searchPattern, $includeExtensions)
        ));
        
        return new Response($html);
    }
}