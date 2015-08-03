<?php
    /**
     * Created by PhpStorm.
     * User: gauthier
     * Date: 03/08/15
     * Time: 19:19
     */
    
    namespace Collection\Merger;
    
    
    use ObjectivePHP\Primitives\Collection\MergePolicy\ValueMergerInterface;

    class AbstractValueMerger implements ValueMergerInterface
    {
        /**
         * @var int
         */
        protected $policy;

        protected $keys;

        /**
         * @param $policy   int
         * @param $keys     mixed
         */
        public function __construct($keys, $policy)
        {
            $this->keys
        }

        /**
         * Merge two values according to the defined policy
         *
         * @param $key
         * @param $first
         * @param $second
         *
         * @return mixed
         */
        public function merge($first, $second)
        {
            // TODO: Implement merge() method.
        }
    }