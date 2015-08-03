<?php

    namespace ObjectivePHP\Primitives\Collection\Validator;


    class ObjectValidator
    {

        protected $class;

        public function __construct($class = null)
        {
            $this->class = (string) $class;
        }

        public function __invoke($instance)
        {
            $expectedClass = $this->class;

            return ($instance instanceof $expectedClass);
        }
    }