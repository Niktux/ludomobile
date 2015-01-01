<?php

namespace Ludo\Twig;

use Puzzle\Images\ImageHandler;

class Extension extends \Twig_Extension
{
    private
        $handler,
        $downloadedPath;
    
    public function __construct(ImageHandler $handler, $downloadedPath)
    {
        $this->handler = $handler;
        $this->downloadedPath = rtrim($downloadedPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
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
                
                if(stripos($path, 'deboo.fr') !== false)
                {
                    $path = $this->download($path);        
                }
                
                $path = $this->handler->applyFormat($path, $format);
                
                // FIXME is it the right place to do this ?
                return substr($path, strpos($path, '/var/'));
            }),
        );
    }
    
    private function download($path)
    {
        $newPath = $this->downloadedPath . str_replace('http://', '', $path);
        
        if(is_file($newPath))
        {
            return $newPath;
        }
        
        $content = @file_get_contents(str_replace(' ', '%20', $path));
        if($content !== false)
        {
            $this->ensureDirectoryExists(dirname($newPath));
            
            if(file_put_contents($newPath, $content) !== false)
            {
                $path = $newPath;
            }
        }
        
        return $path;
    }
    
    private function ensureDirectoryExists($directory)
    {
        if(!is_dir($directory))
        {
            if(!mkdir($directory, 0755, true))
            {
                throw new \Firenote\Exceptions\Filesystem("Cannot create directory $directory");
            }
        }
    }
}