<?php

namespace ObjectivePHP\Primitives;

/**
 * Class Numeric
 * @package Phocus\PrimitiveInterface
 */
class Numeric extends AbstractPrimitive
{

    const TYPE = 'numeric';

    /**
     * @param int $value
     */
    public function __construct($value = 0)
    {
        $this->set($value);
    }

    /**
     * @return float|int
     */
    public function __invoke()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->value;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->value = 0;
        return $this;
    }

    /**
     * Set|Get internal value
     *
     * @param string|float|int $value
     *
     * @throws Exception
     *
     * @return $this|int
     */
    public function set($value)
    {

        if ($value instanceof PrimitiveInterface)
        {
            $value = $value->get();
        }

        if (! is_scalar($value) || (is_float($value) && abs($value) > PHP_INT_MAX))
        {
            throw new Exception(
                gettype($value) . ': cannot properly handle value as numeric', Exception::INVALID_PARAMETER
            );
        }

        $this->value = $value;

        return $this;
    }

    /**
     * Set current value as negative or positive
     *
     * @return mixed
     */
    public function opposite()
    {
        $this->value = $this->value > 0 ? $this->value * -1 : abs($this->value);
        return $this;
    }

    /**
     * @return $this
     */
    public function increment()
    {
        $this->value++;
        return $this;
    }

    /**
     * @return $this
     */
    public function decrement()
    {
        $this->value--;
        return $this;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function add($value)
    {
        $this->value += $value;
        return $this;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function subtract($value)
    {
        $this->value -= $value;
        return $this;
    }

    /**
     * @param $value
     *
     * @throws Exception
     * @return $this
     */
    public function divideBy($value)
    {
        if (($value =  (int) $value) === 0)
        {
            throw new Exception('Division by zero', Exception::INVALID_PARAMETER);
        }

        $this->value = $this->value / $value;
        return $this;
    }

    /**
     * @param $value
     *
     * @throws Exception
     * @return $this
     */
    public function multiplyBy($value)
    {
        $this->value = $this->value *  $value;
        return $this;
    }

    /**
     * @return int
     */
    public function odd()
    {
        return ! ($this->value & 1);
    }

    /**
     * @return int
     */
    public function even()
    {
        return (bool) ($this->value & 1);
    }

    /**
     * @param      $from
     * @param      $to
     * @param bool $inclusive
     *
     * @return bool
     */
    public function isBetween($from, $to, $inclusive = true)
    {
        return $inclusive === true
               ? $this->value >= $from && $this->value <= $to
               : $this->value >  $from && $this->value <  $to;
    }

    /**
     * @param $haystack
     *
     * @throws Exception
     *
     * @return bool
     */
    public function isIn($haystack)
    {
        if ($haystack instanceof \ArrayObject)
        {
            $haystack = $haystack->getArrayCopy();
        }

        if (is_array($haystack))
        {
            return in_array($this->value, $haystack);
        }

        throw new Exception(
            gettype($haystack) . ': Cannot manage type', Exception::INVALID_PARAMETER
        );
    }

    /**
     * Returns the length of value
     *
     * @return int
     */
    public function length()
    {
        return strlen($this->value);
    }

    /**
     * Is the value correspond of the size  of $data
     *
     * @param string|int|array $haystack
     *
     * @throws Exception
     * @return bool
     */
    public function lengthOf($haystack)
    {
        if (is_scalar($haystack))
        {
            return $this->value == strlen($haystack);
        }

        if ($haystack instanceof \Countable || is_array($haystack))
        {
            return $this->value === count($haystack);
        }

        throw new Exception(
            'Can retrieve the length of type ' . gettype($haystack), Exception::INVALID_PARAMETER
        );
    }

    /**
     * Apply a range to int, from its baseline
     * functions are allowed as first arg, which call the walk callback
     *
     * @param mixed $stepOrCallable
     *
     * @return Collection
     */
    public function split($stepOrCallable = 1)
    {
        $range = function($step)
        {
            switch (true)
            {
                case $this->value >= 1:
                    return range(1, $this->value, $step);

                case $this->value <= -1:
                    return range($this->value, -1, $step);

                default:
                    return range($this->value, 0, $step);
            }
        };

        if (! is_callable($stepOrCallable))
        {
            $array = $range($stepOrCallable);
        }
        else
        {
            foreach (($array = $range(1)) as $key => &$val)
            {
                null === ($output = $stepOrCallable($key, $val)) or $val = $output;
            }
        }
        return new Collection($array);
    }

    /**
     * @TODO Supports encoding parameter for chars representation
     *
     * @return string
     * Convert the actual value into charlist,
     */
    public function char()
    {
        $r = '';
        $n = $this->value;
        for ($i = 1; $n >= 0 && $i < 10; $i++) {
            $r = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
            $n -= pow(26, $i);
        }
        return $r;

        //return strtr(base_convert($this->value, 10, 26), '0123456789', 'qrstuvxwyz');
    }

    /**
     * Returns a primitive string
     *
     * @return String
     */
    public function string()
    {
        return new String($this->__toString());
    }
}