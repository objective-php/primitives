<?php
    namespace ObjectivePHP\Primitives\Collection\Normalizer;

    use ObjectivePHP\Primitives\Collection;

    class CollectionNormalizer
    {
        public function __invoke(&$collection)
        {
            if (is_array($collection))
            {
                $collection = new Collection($collection);
            }
        }
    }