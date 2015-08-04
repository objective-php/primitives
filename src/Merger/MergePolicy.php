<?php

    namespace ObjectivePHP\Primitives\Merger;
    
    
    class MergePolicy
    {
        /**
         * replace existing value with new one
         */
        const REPLACE = 1;

        /**
         * takes both values to create a new Collection with both values
         */
        const COMBINE = 2;

        // VALUES ABOVE 10 ARE FOR COLLECTIONS ONLY

        /**
         * merge values from two collections
         */
        const NATIVE = 16;

        /**
         * add values from merged value to existing collection
         */
        const ADD = 32;
    }