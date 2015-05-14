<?php

    namespace ObjectivePHP\Primitives\Collection\Validator;


    use ObjectivePHP\Primitives\Collection;

    class CollectionValidator
    {
        public function __invoke($value)
        {
            return ($value instanceof Collection);
        }
    }