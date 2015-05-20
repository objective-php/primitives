<?php

    namespace ObjectivePHP\Primitives;

    use ArrayObject;
    use ObjectivePHP\Primitives\Collection\Normalizer\CollectionNormalizer;
    use ObjectivePHP\Primitives\Collection\Normalizer\NumericNormalizer;
    use ObjectivePHP\Primitives\Collection\Normalizer\StringNormalizer;
    use ObjectivePHP\Primitives\Collection\Validator\CollectionValidator;
    use ObjectivePHP\Primitives\Collection\Validator\NumericValidator;
    use ObjectivePHP\Primitives\Collection\Validator\StringValidator;

    class Collection extends \ArrayObject implements PrimitiveInterface
    {

        const TYPE = 'collection';

        const NUMERIC    = 'numeric';
        const STRING     = 'string';
        const COLLECTION = 'collection';
        const MIXED      = 'mixed';

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
         * @var Collection
         */
        protected $normalizers;

        /**
         * @var Collection
         */
        protected $validators;

        /**
         * Set collection value
         *
         * @param $value
         *
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
         * @return $this
         */
        public function of($type)
        {
            // unset type
            if ($type == '' || $type == self::MIXED)
            {
                $this->type = false;

                return $this;
            }


            // set new type
            if (!is_null($this->type))
            {
                throw new Exception('Collection type cannot be modified once set', Exception::COLLECTION_TYPE_IS_INVALID);
            }

            //check type validity
            switch (strtolower($type))
            {
                case 'int':
                case 'integer':
                case 'float':
                case 'double':
                case 'numeric':
                    // numeric types are all the same primitive for now
                    $type = self::NUMERIC;
                    // add related Normalizer and Validator
                    $this->addNormalizer(new NumericNormalizer());
                    $this->addValidator(new NumericValidator());
                    break;

                case self::STRING:
                    $type = self::STRING;
                    // add related Normalizer and Validator
                    $this->addNormalizer(new StringNormalizer());
                    $this->addValidator(new StringValidator());
                    break;

                case self::COLLECTION:
                    $type = self::COLLECTION;
                    // add related Normalizer and Validator
                    $this->addNormalizer(new CollectionNormalizer());
                    $this->addValidator(new CollectionValidator());
                    break;

                default:
                    if (!class_exists($type) && !interface_exists($type))
                    {
                        throw new Exception('Unknown collection type', Exception::COLLECTION_TYPE_IS_INVALID);
                    }
            }

            $this->type = (string) $type;

            return $this;
        }

        /**
         * Returns collection type
         *
         * @see {Collection::of()}
         *
         * @return string
         */
        public function type()
        {
            return $this->type;
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
            // normalize value
            $this->getNormalizers()->each(function ($normalizer) use (&$value, &$index)
            {
                $normalizer($value, $index);
            });

            // check key validity
            if ($this->allowed && !in_array($index, $this->allowed))
            {
                throw new Exception('Illegal key: ' . $index, Exception::COLLECTION_FORBIDDEN_KEY);
            }


            // validate value
            $this->getValidators()->each(function ($validator) use ($value, &$isValid)
            {
                if (!$validator($value))
                {
                    throw new Exception('New value did not pass validation', Exception::COLLECTION_VALUE_IS_INVALID);
                }
            });

            parent::offsetSet($index, $value);
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
            if (!isset($this[$index]))
            {
                if (!in_array($index, $this->allowed))
                {
                    throw new Exception('Illegal key: ' . $index, Exception::COLLECTION_FORBIDDEN_KEY);
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
         * @param array|string $keys Key(s) allowed. Pass en empty array to remove restrictions on keys. If null,
         *                           current allowed keys are returned
         *
         * @return $this|array
         */
        public function allowed($keys = null)
        {
            if (is_null($keys))
            {
                return $this->allowed;
            }

            if (!is_array($keys)) $keys = [$keys];

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
            if (!is_callable($callable))
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
         * @param callable $callable A Optional callable
         * @param bool     $apply    Is the filter must be applied to the current collection or return a new collection
         *                           instance
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

            if (null !== $callable && !is_callable($callable))
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
         *
         * @todo loads of UT are missing yet!
         *
         * @return String
         */
        public function join($glue = ' ')
        {
            $joinedString = new String(implode($glue, $this->getInternalValue()));

            return $joinedString;
        }

        /**
         * Shunt native append() method to make it fluent
         *
         * @param mixed $value
         *
         * @return $this
         */
        public function append($value)
        {
            $this[] = $value;

            return $this;
        }

        /**
         * @return Collection
         */
        public function getNormalizers()
        {
            // initialize filters collection
            if (is_null($this->normalizers)) $this->normalizers = new Collection();

            return $this->normalizers;
        }

        /**
         * @return Collection
         */
        public function getValidators()
        {
            // initialize filters collection
            if (is_null($this->validators)) $this->validators = new Collection();

            return $this->validators;
        }

        /**
         * @param callable $normalizer
         *
         * @return $this
         */
        public function addNormalizer(callable $normalizer)
        {
            // applies normalizer to currently stored entries
            $data = $this->getInternalValue();
            $this->clear();

            foreach($data as $key => $value)
            {
                $normalizer($value, $key);
                $this[$key] = $value;
            }

            // stack the new normalizer
            $this->getNormalizers()[] = $normalizer;

            return $this;
        }

        /**
         * @param callable $validator
         *
         * @return $this
         */
        public function addValidator(callable $validator)
        {

            // match validator against currently stored entries
            foreach ($this->getInternalValue() as $key => $value)
            {
                if (!$validator($value))
                {
                    throw new Exception('Value #' . $key . ' did not pass validation', Exception::COLLECTION_VALUE_IS_INVALID);
                }
            }

            // stack the validator
            $this->getValidators()[] = $validator;

            return $this;
        }

        static public function cast($collection)
        {
            if($collection instanceof Collection)
            {
                return $collection;
            }
            return new Collection($collection);
        }

        /**
         * Clear content
         */
        public function clear()
        {
            $this->setInternalValue([]);
        }

        /**
         * @param $data
         */
        public function merge($data)
        {
            // force data conversion to array
            $data = Collection::cast($data)->getInternalValue();

            $this->setInternalValue(array_merge($this->getInternalValue(), $data));

            return $this;
        }

        /**
         * Return a new Collection with same data but without indices
         */
        public function getValues()
        {
            return new Collection(array_values($this->getInternalValue()));
        }

        /**
         * Return a new Collection with current indices as values
         */
        public function getKeys()
        {
            return new Collection(array_keys($this->getInternalValue()));
        }
    }

