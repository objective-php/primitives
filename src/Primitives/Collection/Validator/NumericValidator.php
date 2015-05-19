<?php

    namespace ObjectivePHP\Primitives\Collection\Validator;


    use ObjectivePHP\Primitives\Numeric;

    class NumericValidator
    {
        public function __invoke($value)
        {
            return ($value instanceof Numeric);
        }
    }