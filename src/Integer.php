<?php

    namespace ObjectivePHP\Primitives;


    use ObjectivePHP\Primitives\Numeric;

    class Integer extends Numeric
    {
        const TYPE = 'int';

        public function setInternalValue($value)
        {
            $this->value = (int) $value;

            return $this;
        }
    }