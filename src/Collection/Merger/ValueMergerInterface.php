<?php

    namespace ObjectivePHP\Primitives\Collection\MergePolicy;


    interface ValueMergerInterface
    {

        /**
         * @param $policy   int
         * @param $keys     mixed
         */
        public function __construct($keys, $policy);

        /**
         * Merge two values according to the defined policy
         *
         * @param $key
         * @param $first
         * @param $second
         *
         * @return mixed
         */
        public function merge($first, $second);


    }