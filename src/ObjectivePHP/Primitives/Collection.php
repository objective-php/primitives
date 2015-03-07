<?php

namespace ObjectivePHP\Primitives;

use ArrayObject;

class Collection extends \ArrayObject implements PrimitiveInterface
{

    const TYPE = 'collection';

    protected $type;

    // Allowed keys
    // empty means all keys are allowed
    protected $allowed = [];

    public function set($value)
    {
        $this->exchangeArray($value);

        return $this;
    }

    public function get()
    {
        return $this->getArrayCopy();
    }

    /**
     * @param mixed $type
     */
    public function of($type = null)
    {
        // return current type
        if(is_null($type))
        {
            return $this->type;
        }

        // unset type
        if($type === false || $type === 'mixed' || $type === null)
        {
            $this->type = false;
            return;
        }

        // set new type
        if(!is_null($this->type))
        {
            throw new Exception('Collection type cannot be modified once set', Exception::COLLECTION_TYPE_IS_INVALID);
        }

        //check type validity
        switch($type)
        {
            case 'int':
            case 'integer':
            case 'float':
            case 'numeric':
                $type = 'numeric';
                break;

            case 'string':
                break;

            default:
                if(!class_exists($type))
                {
                    throw new Exception('Unknown collection type', Exception::COLLECTION_TYPE_IS_INVALID);
                }
        }


        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->type;
    }

    public function offsetSet($index, $value)
    {
        // check key validity
        if($this->allowed && !in_array($index, $this->allowed))
        {
            throw new Exception('Illegal key: ' . $index, Exception::COLLECTION_ILLEGAL_KEY);
        }


        switch($this->type)
        {
            case null:
                $normalized = $value;
                break;

            case 'numeric':
                if(!is_int($value))
                {
                    throw new Exception('Collection expects member to be a number or a Primitive\Numeric instance', Exception::COLLECTION_VALUE_DOES_NOT_MATCH_TYPE);
                }
                $normalized = $value;
            break;

            case 'string':
                if(!is_string($value) && !$value instanceof String)
                {
                    throw new Exception('Collection expects member to be a string or a String object', Exception::COLLECTION_VALUE_DOES_NOT_MATCH_TYPE);
                }
                $normalized = $value;
                break;

            default:
                if(!is_object($value) || !$value instanceof $this->type)
                {
                    throw new Exception('Collection expects member to be "' . $this->type . '"', Exception::COLLECTION_VALUE_DOES_NOT_MATCH_TYPE);
                }
                $normalized = $value;
                break;
        }


        parent::offsetSet($index, $normalized);
    }

    public function offsetGet($index)
    {
        if(!isset($this[$index]))
        {
            if(!in_array($index, $this->allowed))
            {
                throw new Exception('Illegal key: ' . $index, Exception::COLLECTION_ILLEGAL_KEY);
            }
            else
            {
                return null;
            }
        }
        else
        {
            return parent::offsetGet($index);
        }
    }

    public function allowed($keys = null)
    {
        if(is_null($keys))
        {
            return $this->allowed;
        }

        if(!is_array($keys)) $keys = [$keys];

        $this->allowed = $keys;

        return $this;
    }

    /**
     * Iterates collection. Value is passed by reference in the callback.
     *
     * @param $callable
     *
     * @throws Exception
     * @return $this
     */
    public function each($callable)
    {
        if (! is_callable($callable))
        {
            throw new Exception(sprintf('Parameter of type  %s is not callable', gettype($callable)),
                      Exception::INVALID_CALLBACK
            );
        }
        foreach ($this as $key => &$val)
        {
            $callable($val, $key);
        }

        return $this;
    }

    /**
     * Returns a new filtered collection
     *
     * @param callable $callable    A Optional callable
     * @param bool     $apply       Is the filter must be applied to the current collection or return a new collection instance
     *
     * @throws Exception
     * @return Collection
     */
    public function filter($callable = null, $apply = false)
    {
        // Exchange arguments: filter with no callback by ref
        if (is_bool($callable))
        {
            $apply    = $callable;
            $callable = null;
        }

        if (null !== $callable && ! is_callable($callable))
        {
            throw new Exception(sprintf('Parameter of type  %s is not callable', gettype($callable)),
                      Exception::INVALID_CALLBACK
            );
        }

        $array = is_callable($callable)
               ? array_filter($this->getArrayCopy(), $callable)
               : array_filter($this->getArrayCopy());

        if ($apply === true)
        {
            $this->exchangeArray($array);

            return $this;
        }

        return new static($array);
    }

    public function jsonSerialize()
    {
        return $this->getArrayCopy();
    }
}
