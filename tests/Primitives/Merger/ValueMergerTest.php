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
    use ObjectivePHP\Primitives\Merger\ValueMerger;
    use ObjectivePHP\Primitives\Merger\MergePolicy;

    class ValueMergerTest extends TestCase
    {
        public function testCombining()
        {
            $merger = new ValueMerger(MergePolicy::COMBINE);

            $mergedValue = $merger->merge('a', 'b');

            $this->assertEquals(Collection::cast(['a', 'b']), $mergedValue);
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
    }