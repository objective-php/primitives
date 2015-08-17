<?php

    namespace ObjectivePHP\Primitives\Merger;
    
    
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\Exception;

    /**
     * Class ValueMerger
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

            switch ($this->policy)
            {
                case MergePolicy::COMBINE:
                    if ($first instanceof Collection)
                    {
                        // Modify the first collection
                        return $first->append($second);
                    }
                    else return new Collection([$first, $second]);
                    break;

                case MergePolicy::REPLACE:
                    return $second;
                    break;

                case MergePolicy::ADD:
                    return Collection::cast($first)->add(Collection::cast($second));
                    break;

                case MergePolicy::NATIVE:
                    return Collection::cast($first)->merge(Collection::cast($second));
                    break;

                default:
                    throw new Exception(sprintf('Policy "%s" does not exist', $this->policy), Exception::INVALID_PARAMETER);
            }

        }

    }