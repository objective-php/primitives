<?php

namespace ObjectivePHP\Primitives\String;

/**
 * Class Snake
 *
 * @package ObjectivePHP\Primitives\String
 */
class Snake
{
    public static function case($string, $glue = '_')
    {
        $string = preg_replace('/[a-z]+(?=[A-Z])|[A-Z]+(?=[A-Z][a-z])/', '\0' . $glue, $string);
        return strtolower($string);
    }
}
