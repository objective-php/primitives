<?php

    namespace ObjectivePHP\Primitives\Merger;


    interface MergerInterface
    {

        /**
         * @param $policy   mixed
         * @param $keys     mixed
         */
        public function __construct($policy);

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