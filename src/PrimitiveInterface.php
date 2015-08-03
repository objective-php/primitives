<?php

    namespace ObjectivePHP\Primitives;

    /**
     * Temp implementation proposition
     * JsonSerializable: each primitive must be serializable by json_encode()
     *
     * Class PrimitiveInterface
     *
     * @package Phocus\Primitives
     */
    interface PrimitiveInterface extends \JsonSerializable
    {
        /**
         * Set the primitive object initial value
         *
         * @param $value
         *
         * @return $this
         */
        public function setInternalValue($value);

        /**
         * Return the primitive value in its native representation
         *
         * @return mixed
         */
        public function getInternalValue();


        /**
         * Return a cloned primitive
         *
         * @return PrimitiveInterface
         */
        public function copy();

        /**
         * Convert a value to Primitive
         *
         * @param $value
         *
         * @return static
         */
        static public function cast($value);

    }