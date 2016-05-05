<?php

    namespace ObjectivePHP\Primitives\Merger;
    
    
    abstract class AbstractMerger implements MergerInterface
    {
        /**
         * @var int
         */
        protected $policy;

        /**
         * @param $policy   mixed
         */
        public function __construct($policy = MergePolicy::AUTO)
        {
            $this->setPolicy($policy);
        }

        /**
         * @return mixed
         */
        public function getPolicy()
        {
            return $this->policy;
        }

        /**
         * @param mixed $policy
         *
         * @return $this
         */
        public function setPolicy($policy)
        {
            $this->policy = $policy;

            return $this;
        }


    }
