<?php

namespace Ludo\Search\Patterns;

class String extends AbstractPattern
{
    protected
        $pattern;
    
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }
    
    public function matches($result)
    {
        return stripos($result, $this->pattern) !== false;
    }
    
    public function sql($field)
    {
        return sprintf(
            "%s LIKE '%%%s%%'",
            $field,
            $this->pattern
        );
    }
}