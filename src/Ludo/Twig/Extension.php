<?php

namespace Ludo\Twig;

use Puzzle\Images\ImageHandler;

class Extension extends \Twig_Extension
{
    private
        $handler;
    
    public function __construct(ImageHandler $handler)
    {
        $this->handler = $handler;
    }
    
    public function getName()
    {
        return 'ludomobile';
    }
    
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('image', function($path, $format) {
                
                if(stripos($path, '.') === false)
                {
                    return $path;
                }
                
                $path = $this->handler->applyFormat($path, $format);
                
                // FIXME is it the right place to do this ?
                return substr($path, strpos($path, '/var/'));
            }),
        );
    }
}