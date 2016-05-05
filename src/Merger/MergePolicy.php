<?php

    namespace ObjectivePHP\Primitives\Merger;
    
    
    class MergePolicy
    {
        /**
         * let the merger define the best way to handle data merging
         */
        const AUTO = 1;

        /**
         * replace existing value with new one
         */
        const REPLACE = 2;

        /**
         * takes both values to create a new Collection with both values
         */
        const COMBINE = 4;

        /**
         * if an existing value already exist, skip the new one
         */
        const SKIP = 8;

        /**
         * merge values from two collections
         */
        const NATIVE = 16;

        /**
         * add values from merged value to existing collection
         */
        const ADD = 32;

        /**
         * recursively merge arrays
         */
        const RECURSIVE = 32;




    }
