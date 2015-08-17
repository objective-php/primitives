<?php

    namespace ObjectivePHP\Primitives;


    /**
     * Class AbstractPrimitive
     * @package ObjectivePHP\Primitives
     */
    abstract class AbstractPrimitive implements PrimitiveInterface
    {

        /**
         * Primitive type
         *
         * This should *always* be overridden in inherited classes!
         */
        const TYPE = 'ABSTRACT';

        /**
         * @var mixed
         */
        protected $value;

        /**
         * @param $value
         *
         * @return $this
         */
        public function setInternalValue($value)
        {
            $this->validateInternalValue($value);

            $this->value = $value;

            return $this;
        }

        /**
         * To be implemented in inherited classes to automate value validation
         *
         * In case the value is considered as invalid, this method should throw an exception
         *
         * @param mixed $value
         *
         * @return bool
         */
        public function validateInternalValue($value)
        {
        }

        /**
         * Return the internal value of the primitive (in its native form)
         *
         * @return mixed
         */
        public function getInternalValue()
        {
            return $this->value;
        }

        /**
         * Return value to serialize when calling json_encode on the primitive
         *
         * @return array
         */
        public function jsonSerialize()
        {
            return $this->value;
        }

        /**
         * Return primitive's type
         *
         * @return mixed
         */
        public function getType()
        {
            return static::TYPE;
        }

        /**
         * @see PrimitiveInterface::apply
         *
         * @param callable $callback
         *
         * @return $this
         */
        public function apply(callable $callback)
        {
            $this->setInternalValue($callback($this->getInternalValue()));

            return $this;
        }

        /**
         * Return a cloned primitive
         *
         * @return self
         */
        public function copy()
        {
            return clone $this;
        }

        /**
         * @param $class
         * @return bool
         */
        static public function isPrimitive($class)
        {
            // check that $primitive actually is a Primitive class name
            $reflectionClass = new \ReflectionClass($class);

            return $reflectionClass->implementsInterface(PrimitiveInterface::class);

        }
    }