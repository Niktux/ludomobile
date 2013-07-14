<?php

namespace Ludo\Search\Patterns;

abstract class Composite extends AbstractPattern
{
    protected
        $p1,
        $p2;
    
    public function __construct(Pattern $p1, Pattern $p2)
    {
        $this->p1 = $p1;
        $this->p2 = $p2;
    }
}