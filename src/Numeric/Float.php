<?php

    namespace ObjectivePHP\Primitives\Numeric;


    class Float extends Numeric
    {
        const TYPE = 'float';

        public function setInternalValue($value)
        {
            $this->value = (float) $value;

            return $this;
        }
    }