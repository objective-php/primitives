<?php

    namespace ObjectivePHP\Primitives\Collection\Normalizer;

    use ObjectivePHP\Primitives\Numeric;

    class NumericNormalizer
    {
        public function __invoke(&$numeric)
        {
            if (is_numeric($numeric))
            {
                $numeric = new Numeric($numeric);
            }
        }
    }