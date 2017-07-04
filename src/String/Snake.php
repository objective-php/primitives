<?php

namespace ObjectivePHP\Primitives\String;


class Snake
{
    
    static public function case($string)
    {
        $string = preg_replace('/(?!^)[[:upper:]][[:lower:]]/', '$0',
            preg_replace('/(?!^)[[:upper:]]+/', '_' . '$0', $string));
    
        return strtolower($string);
    }
}
