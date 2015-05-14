<?php

    namespace ObjectivePHP\Primitives\Collection\Validator;


    class ClassValidator
    {

        protected $class;

        public function __construct($class)
        {
            $this->class = (string) $class;
        }

        public function __invoke($instance)
        {
            $expectedClass = $this->class;
            return ($instance instanceof $expectedClass);
        }
    }