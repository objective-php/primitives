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
     * @return void
     */
    public function validateInput($value)
    {
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->value;
    }

    public function getType()
    {
        return static::TYPE;
    }
}