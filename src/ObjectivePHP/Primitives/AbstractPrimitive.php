<?php

namespace ObjectivePHP\Primitives;


abstract class AbstractPrimitive implements PrimitiveInterface
{

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param $value
     *
     * @return $this
     */
    public function set($value)
    {
        $this->validateInput($value);

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
    public function validateInput()
    {
    }

    /**
     * Return the internal value of the primitive (in its native form)
     *
     * @return mixed
     */
    public function get()
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
        $this->set($callback($this->get()));

        return $this;
    }
}