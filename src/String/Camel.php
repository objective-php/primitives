<?php

namespace ObjectivePHP\Primitives\String;


class Camel
{
    const UPPER = 1;
    const LOWER = 2;
    
    static public function case($string, $flag = self::UPPER)
    {
        $parts = explode('_', $string);
        
        array_walk($parts, function(&$part) {
            $part = ucfirst(strtolower($part));
        });
        
        $result = implode('', $parts);
        
        if($flag & self::LOWER) {
            $result = lcfirst($result);
        }
        
        return $result;
    }
}
