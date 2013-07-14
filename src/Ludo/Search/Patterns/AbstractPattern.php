<?php

namespace Ludo\Search\Patterns;

abstract class AbstractPattern implements Pattern
{
    public function or_(Pattern $p)
    {
        return new OrPattern($this, $p);
    }
    
    public function and_(Pattern $p)
    {
        return new AndPattern($this, $p);
    }
}