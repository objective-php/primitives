<?php

namespace ObjectivePHP\Primitives;

use ArrayObject;

class Collection extends \ArrayObject implements PrimitiveInterface
{

    const TYPE = 'collection';

    const MIXED = 'mixed';

    /**
     * Collections content's type
     *
     * @var string $type
     */
    protected $type;

    /**
     * Allowed keys
     *
     * @var $allowed array An empty array means all keys are allowed
     */
    protected $allowed = [];

    /**
     * Set collection value
     *
     * @param $value
     * @todo check value type ; only allow array, Iterator and Collection
     *
     * @return $this
     */
    public function setInternalValue($value)
    {
        $this->exchangeArray($value);

        return $this;
    }

    /**
     * Get collection value (as an array)
     *
     * @return array
     */
    public function getInternalValue()
    {
        return $this->getArrayCopy();
    }

    /**
     * Set or retrieve collection type
     *
     * @param string $type Type of the collection. If null, current type is returned
     *
     * @return $this|string
     */
    public function of($type = null)
    {
        // return current type
        if(is_null($type))
        {
            return $this->type;
        }

        // unset type
        if($type === false || $type === 'mixed')
        {
            $this->type = false;
            return $this;
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
     * ArrayAccess implementation
     *
     * @param mixed $index
     * @param mixed $value
     *
     * @throws Exception
     */
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

    /**
     * ArrayAccess implementation
     *
     * @param mixed $index
     *
     * @return mixed|null
     * @throws Exception
     */
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

    /**
     * Set or retrieve allowed keys
     *
     * @param array|string $keys Key(s) allowed. Pass en empty array to remove restrictions on keys. If null, current allowed keys are returned
     *
     * @return $this|array
     */
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

    /**
     * Return value to serialize on json_encode calls
     *
     * @see {@\JsonSerializable}
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->getArrayCopy();
    }

    /**
     * Apply a callback to primitive's internal value
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
     * @return PrimitiveInterface
     */
    public function copy()
    {
        return clone $this;
    }

    /**
     * Returns a String generated from items concatenation
     *
     * @param string $glue
     * @todo loads of UT are missing yet!
     *
     * @return String
     */
    public function join($glue = ' ')
    {
        $joinedString = new String();

        $this->each(function($value) use ($glue, $joinedString) {
           $joinedString->append((string) $value)->append($glue);
        });

        // remove last $glue occurence
        $joinedString->trim($glue, String::RIGHT);

        return $joinedString;
    }

}
