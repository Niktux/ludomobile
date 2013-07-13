<?php

namespace Ludo\Search\Patterns;

class BeginsWith extends String
{
    public function matches($result)
    {
        return substr($result, 0, strlen($this->pattern)) === $this->pattern;
    }
    
    public function sql($field)
    {
        return sprintf(
            "%s LIKE '%s%%'",
            $field,
            $this->pattern
        );
    }
}