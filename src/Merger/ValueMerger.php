<?php

    namespace ObjectivePHP\Primitives\Merger;
    
    
    use ObjectivePHP\Primitives\Collection\Collection;

    class ValueMerger extends AbstractMerger
    {
        /**
         * Merge two values according to the defined policy
         *
         * @param $key
         * @param $first
         * @param $second
         *
         * @return mixed
         */
        public function merge($first, $second)
        {

            switch ($this->policy)
            {
                case MergePolicy::COMBINE:
                    if ($first instanceof Collection)
                    {
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
            }

        }

    }