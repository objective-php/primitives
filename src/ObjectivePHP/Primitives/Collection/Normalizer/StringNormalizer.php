<?php

    namespace ObjectivePHP\Primitives\Collection\Normalizer;


    use ObjectivePHP\Primitives\String;

    class StringNormalizer
    {
        public function __invoke(&$string)
        {
            if (is_string($string))
            {
                $string = new String($string);
            }
        }
    }