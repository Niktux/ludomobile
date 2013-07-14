<?php

namespace Ludo\Search\Patterns;

class AndPattern extends Composite
{
    public function matches($result)
    {
        return $this->p1->matches($result)
            && $this->p2->matches($result);
    }
    
    public function sql($field)
    {
        return sprintf(
            "( %s AND %s)",
            $this->p1->sql($field),
            $this->p2->sql($field)
        );
    }
}