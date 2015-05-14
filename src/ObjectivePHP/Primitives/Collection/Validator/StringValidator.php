<?php

    namespace ObjectivePHP\Primitives\Collection\Validator;

    use ObjectivePHP\Primitives\String;

    class StringValidator
    {
        public function __invoke($value)
        {
            return ($value instanceof String);
        }
    }