<?php

    namespace ObjectivePHP\Primitives\Merger;
    
    
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\Exception;

    /**
     * Class ValueMerger
     *
     * @package ObjectivePHP\Primitives\Merger
     */
    class ValueMerger extends AbstractMerger
    {
        /**
         * Merge two values according to the defined policy
         *
         * @param $first
         * @param $second
         *
         * @return mixed
         * @throws Exception
         */
        public function merge($first, $second)
        {

            $policy = $this->policy;

            if ($policy == MergePolicy::AUTO)
            {
                if (is_array($first) || $first instanceof \ArrayObject)
                {
                    $policy = MergePolicy::NATIVE;
                }
                else $policy = MergePolicy::REPLACE;
            }

            switch ($policy)
            {
                case MergePolicy::SKIP:
                    return is_null($first) ? $second : $first;
                    break;

                case MergePolicy::COMBINE:
                    if ($first instanceof Collection)
                    {
                        if ($second instanceof Collection)
                        {
                            return $first->merge($second);
                        }
                        elseif (is_array($second))
                        {
                            return $first->append(...$second);
                        }
                        else
                        {
                            // Modify the first collection
                            return $first->append($second);
                        }
                    }
                    else if ($second instanceof Collection)
                    {
                        if (is_array($first))
                        {
                            return $second->append(...$first);
                        }
                        else
                        {
                            // Modify the first collection
                            return $second->append($first);
                        }
                    }
                    else
                    {
                        // neither are Collection instance
                        $mergedValue = new Collection(array_filter(array_merge((array) $first, (array) $second), function ($value)
                        {
                            return !is_null($value);
                        }));

                        return $mergedValue->toArray();
                    }
                    break;

                case MergePolicy::REPLACE:
                    return $second;
                    break;

                case MergePolicy::ADD:
                    $mergedValue = Collection::cast($first)->add(Collection::cast($second));
                    if (!$first instanceof Collection && !$second instanceof Collection)
                    {
                        $mergedValue = $mergedValue->toArray();
                    }

                    return $mergedValue;
                    break;

                case MergePolicy::NATIVE:
                    $mergedValue = Collection::cast($first)->merge(Collection::cast($second));
                    if (!$first instanceof Collection && !$second instanceof Collection)
                    {
                        $mergedValue = $mergedValue->toArray();
                    }

                    return $mergedValue;
                    break;

                default:
                    throw new Exception(sprintf('Policy "%s" does not exist', $this->policy), Exception::INVALID_PARAMETER);
            }

        }

    }
