<?php

namespace Ludo\Search\Patterns;

interface Pattern
{
    public function matches($result);
    public function sql($field);
    
    public function or_(Pattern $p);
    public function and_(Pattern $p);
}