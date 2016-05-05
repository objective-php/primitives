<?php
    /**
     * Created by PhpStorm.
     * User: gauthier
     * Date: 03/08/15
     * Time: 21:47
     */
    
    namespace Tests\Primitives\Merger;

    use ObjectivePHP\PHPUnit\TestCase;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\Exception;
    use ObjectivePHP\Primitives\Merger\ValueMerger;
    use ObjectivePHP\Primitives\Merger\MergePolicy;

    class ValueMergerTest extends TestCase
    {
        public function testMerge()
        {
            $merger = new ValueMerger(-1);

            $this->expectsException(function () use ($merger)
            {
                return $merger->merge('a', 'b');
            }, Exception::class, null, Exception::INVALID_PARAMETER);
        }

        public function testCombining()
        {
            $merger = new ValueMerger(MergePolicy::COMBINE);

            $mergedValue = $merger->merge('a', 'b');
            $appendValue = $merger->merge(Collection::cast(['a', 'b']), 'c');

            $this->assertEquals(['a', 'b'], $mergedValue);
            $this->assertEquals(Collection::cast(['a', 'b', 'c']), $appendValue);
        }

        public function testReplacing()
        {
            $merger = new ValueMerger(MergePolicy::REPLACE);

            $mergedValue = $merger->merge('a', 'b');

            $this->assertEquals('b', $mergedValue);
        }

        public function testAdding()
        {
            $merger = new ValueMerger(MergePolicy::ADD);

            $firstCollection = new Collection(['x' => 'a', 'y' => 'b']);
            $secondCollection = new Collection(['x' => 'skipped', 'z' => 'c']);


            $mergedValue = $merger->merge($firstCollection, $secondCollection);

            $this->assertEquals(Collection::cast(['x' => 'a', 'y' => 'b', 'z' => 'c']), $mergedValue);
        }

        public function testNativeMerging()
        {
            $merger = new ValueMerger(MergePolicy::NATIVE);

            $firstCollection = new Collection(['x' => 'a', 'y' => 'b']);
            $secondCollection = new Collection(['x' => 'a was replaced', 'z' => 'c']);


            $mergedValue = $merger->merge($firstCollection, $secondCollection);

            $this->assertEquals(Collection::cast(['x' => 'a was replaced', 'y' => 'b', 'z' => 'c']), $mergedValue);
        }

        public function testGetPolicy()
        {
            $merger = new ValueMerger(MergePolicy::ADD);

            $policy = $merger->getPolicy();

            $this->assertEquals(MergePolicy::ADD, $policy);

        }

    }