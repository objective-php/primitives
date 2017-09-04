<?php

namespace ObjectivePHP\Primitives\String;


class Camel
{
    const UPPER = 1;
    const LOWER = 2;
    
    static public function case($string, $flag = self::UPPER, $delimiter = '_')
    {

        if(is_string($delimiter)) {
            $parts = explode($delimiter, $string);
        } elseif(is_array($delimiter))
        {
            $parts = preg_split('/[' . implode('', $delimiter) .']/', $string);
        }
        
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
