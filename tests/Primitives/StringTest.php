<?php

    namespace Tests\ObjectivePHP\Primitives;

    use ObjectivePHP\PHPUnit\TestCase;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\Exception;
    use ObjectivePHP\Primitives\Numeric\Numeric;
    use ObjectivePHP\Primitives\String\String;

    class StringTest extends TestCase
    {

        public function testAccessor()
        {
            $string = new String('example string');
            $this->assertEquals('example string', $string->getInternalValue());
        }

        public function testLowercase()
        {
            $string = new String('TEST STRING');
            $this->assertEquals('test string', $string->lower()->getInternalValue());

            // with accented charcaters
            $string = new String('CHAÎNE ACCENTUÉE');
            $this->assertEquals('chaîne accentuée', $string->lower()->getInternalValue());
        }

        public function testUppercase()
        {
            // default mode
            $string = (new String('test string'))->upper();
            $this->assertEquals('TEST STRING', $string->getInternalValue());

            $otherString = (new String('test string'))->upper(String::UPPER_ALL);
            $this->assertEquals($string, $otherString);

            // first letter only
            $string = (new String('test string'))->upper(String::UPPER_FIRST);
            $this->assertEquals('Test string', $string->getInternalValue());

            // every word
            $string = (new String('test string'))->upper(String::UPPER_WORDS);
            $this->assertEquals('Test String', $string->getInternalValue());


            // with accented charcaters
            $string = (new String('chaîne accentuée'))->upper();
            $this->assertEquals('CHAÎNE ACCENTUÉE', $string->getInternalValue());
        }

        public function testLength()
        {
            $string = new String('test string');
            $this->assertEquals(11, $string->length());

            // with accented charcaters
            $string = new String('chaîne accentuée');
            $this->assertEquals(16, $string->length());

        }

        /**
         * @dataProvider dataProviderForTestMatches
         */
        public function testMatches($subject, $pattern, $result)
        {
            $string = new String($subject);

            $this->assertEquals($result, $string->matches($pattern));
        }

        public function dataProviderForTestMatches()
        {
            return
                [
                    ['hello world', '/world/', true],
                    ['hello world', '/WORLD/', false],
                    ['hello world', '/WORLD/i', true],
                ];
        }

        /**
         * @dataProvider dataProviderForTestTrim
         */
        public function testTrim($string, $charlist, $ends, $expected)
        {
            $string = new String($string);
            $result = $string->trim($charlist, $ends);

            $this->assertEquals($expected, $result->getInternalValue());
        }

        public function dataProviderForTestTrim()
        {
            return
                [
                    [' test string ', null, null, 'test string'],
                    [' test string ', null, String::LEFT, 'test string '],
                    [' test string ', null, String::RIGHT, ' test string'],
                    [' test string ', null, String::BOTH, 'test string'],
                    ['test string', 'tg', String::LEFT, 'est string'],
                    ['test string', 'tg', String::RIGHT, 'test strin'],
                    ['test string', 'tg', String::BOTH, 'est strin'],
                    ['test string', 'tg', null, 'est strin'],
                ];
        }

        public function testReplace()
        {
            $string = new String('abcde');
            $this->assertEquals('abc_DE_de', $string->replace('de', '_DE_de')->getInternalValue());
            $this->assertEquals('abc_de', $string->replace('_DE', '', String::CASE_SENSITIVE)->getInternalValue());


            // same thing using a regexp
            $string = new String('abcde');
            $this->assertEquals('abc_DE_de', $string->replace('/d./', '_DE_de', String::REGEXP)->getInternalValue());
            // $string->replace('/d./', '_DE_de', String::REGEXP)->getInternalValue()
        }

        public function testRegexplace()
        {

            $string = new String('abcde');
            $this->assertEquals('xbcdx', $string->regexplace('/[aeiou]/', 'x')->getInternalValue());
        }


        public function testExtract()
        {
            $string = new String('abcdefgh');

            $sub = $string->extract(1);
            $this->isInstanceOf(String::class, $sub);
            $this->assertEquals('bcdefgh', $sub->getInternalValue());


            $sub = $string->extract(1, 1);
            $this->isInstanceOf(String::class, $sub);
            $this->assertEquals('b', $sub->getInternalValue());


            $sub = $string->extract(1, -1);
            $this->isInstanceOf(String::class, $sub);
            $this->assertEquals('bcdefg', $sub->getInternalValue());

            // with accented charcaters
            $string = new String('chaîne accentuée');
            $sub    = $string->extract(3, 1);
            $this->isInstanceOf(String::class, $sub);
            $this->assertEquals('î', $sub->getInternalValue());

        }

        public function testCrop()
        {
            // same as extract(), but amend internal value instead of returning a new string
            $string = new String('abcdefgh');
            $fluent = $string->crop(1);
            $this->assertSame($string, $fluent);
            $this->assertEquals('bcdefgh', $string);

            $string->crop(1, -1);
            $this->assertEquals('cdefg', $string);

            $string->crop(1, 1);
            $this->assertEquals('d', $string);

            // with accented charcaters
            $string = new String('chaîne accentuée');
            $string->crop(3, 1);
            $this->assertEquals('î', $string);
        }

        public function testContains()
        {
            $string = new String("Hello World");

            $this->assertTrue($string->contains('World'));
            $this->assertFalse($string->contains('foo'));
            $this->assertFalse($string->contains('world', String::CASE_SENSITIVE));
            $this->assertTrue($string->contains('world'));
            $this->assertFalse($string->contains(1));

        }

        /**
         * @dataProvider dataProviderForTestSplit
         */
        public function testSplit($str, $pattern, $expected, $exception, $code)
        {
            $string = new String($str);

            if ($exception)
            {
                $this
                    ->expectsException(function () use ($string, $pattern)
                    {
                        $string->split($pattern, String::REGEXP);
                    }, $exception, null, $code);
            }
            else
            {
                // check returned object
                $result = $string->split($pattern, String::REGEXP);
                $this->isInstanceOf(Collection::class);

                $this->assertEquals(String::class, $result->getType());

                // check returned values
                $values = $result->getArrayCopy();

                $this->assertCount(count($expected), $values);

                foreach ($values as $i => $value)
                {
                    $this->assertEquals($expected[$i], $value);
                }

            }
        }

        public function dataProviderForTestSplit()
        {
            return
                [
                    ['Hello,World', '/,/', ['Hello', 'World'], null, null],
                    ['Hello,World', '/[\,]/', ['Hello', 'World'], null, null],
                    ['Hello`World', '/`/', ['Hello', 'World'], null, null],
                    ['Hello,World', ['this is no a string'], null, Exception::class, Exception::INVALID_PARAMETER],
                    ['Hello,World', 'this is not a valid regexp pattern', null, Exception::class, Exception::INVALID_REGEXP],
                ];
        }

        public function testInsert()
        {
            $string = new String('Keep Objective');

            $this
                ->expectsException(function () use ($string)
                {
                    $string->insert('string', '30');
                }, Exception::class, null, Exception::INVALID_PARAMETER);

            $this
                ->expectsException(function () use ($string)
                {
                    $string->insert([], []);
                }, Exception::class, null, Exception::INVALID_PARAMETER);

            $this
                ->expectsException(function () use ($string)
                {
                    $string->insert(['array'], 30);
                }, Exception::class, null, Exception::INVALID_PARAMETER);

            $string         = new String('Objective');
            $extendedString = $string->insert('Keep', 0);
            $this->isInstanceOf(String::class, $extendedString);
            $this->assertEquals('KeepObjective', $extendedString->getInternalValue());

            $string = (new String('Keep'))->insert('Objective', 99);
            $this->assertEquals('KeepObjective', $string->getInternalValue());

            $string = (new String('Keep'))->insert(new String('Objective'), -2);
            $this
                ->assertEquals('KeObjectiveep', $string->getInternalValue());

            $string = (new String('Keep'))->insert('Objective', 3);
            $this->assertEquals('KeeObjectivep', $string->getInternalValue());
        }

        public function testPrepend()
        {
            $string = new String('Objective');
            $this->assertEquals('KeepObjective', $string->prepend('Keep')->getInternalValue());
        }

        public function testAppend()
        {
            $string = new String('Keep');
            $this->assertEquals('KeepObjective', $string->append('Objective')->getInternalValue());
        }

        public function testReverse()
        {
            $string = new String('abc');
            $this->assertEquals('cba', $string->reverse()->getInternalValue());
        }

        public function testLocate()
        {
            $string = new String('Hello Php World');
            $this->assertFalse($string->locate('AA'));
            $this->assertFalse($string->locate('L', 0, String::CASE_SENSITIVE));
            $this->assertEquals(10, $string->locate('W')->getInternalValue());
            $this->assertEquals(13, $string->locate('l', new Numeric(5))->getInternalValue());

            $this->expectsException(function () use ($string)
            {
                $string->locate([]);
            }, Exception::class, null, Exception::INVALID_PARAMETER);

            $this->expectsException(function () use ($string)
            {
                $string->locate('ss', 'aa');
            }, Exception::class, null, Exception::INVALID_PARAMETER);

            $this->expectsException(function () use ($string)
            {
                $string->locate('test', -2);
            }, Exception::class, null, Exception::INVALID_PARAMETER);

            $this->assertEquals(13, $string->locate('l', 0, String::FROM_END)->getInternalValue());
            $this->assertEquals(8, $string->locate('P', 0, String::FROM_END)->getInternalValue());
            $this->assertEquals(6, $string->locate('P', 0, String::FROM_END | String::CASE_SENSITIVE)->getInternalValue());
        }

        public function testCrypt()
        {
            $string = new String('Hello Php World');

            $this->assertTrue($string->crypt()->challenge('Hello Php World'));
            $this->assertFalse($string->challenge('Hello World'));

            // same test with custom salt
            $string = new String('Hello Php World', md5(time()));

            $this->assertTrue($string->crypt()->challenge('Hello Php World'));
            $this->assertFalse($string->challenge('Hello World'));
        }

        public function testMd5()
        {
            $string = new String('Hello World');

            $this->assertEquals(md5('Hello World'), $string->md5());
        }

        public function testVariableStringHandling()
        {
            // variables handling by constructor
            $string            = (new String('this is a string', ['those', 'are', 'variables']));
            $reflectedProperty = new \ReflectionProperty(String::class, 'variables');
            $reflectedProperty->setAccessible(true);
            $stringVariables = $reflectedProperty->getValue($string);
            $this->assertEquals(['those', 'are', 'variables'], $stringVariables);

            // anonymous placeholders
            $string = new String('This string contains a %s');

            $string->addVariable('placeholder');

            $this->assertEquals('This string contains a placeholder', $string->build());

            $string->clear();
            $this->assertEquals('This string contains a %s', $string->build());

            $string->setVariable(0, 'placeholder (again!)');
            $this->assertEquals('This string contains a placeholder (again!)', $string->build());

            // named placeholders
            $string = new String('This string contains a :named-placeholder');
            $string->setVariable('named-placeholder', 'named placeholder (I tell you!)');

            $this->assertEquals('This string contains a named placeholder (I tell you!)', $string->build());

            // mixed
            //
            // named placeholders are handled apart from anonymous ones, so
            // they aren't taken in account for anonymous variables position
            $string = new String('This string contains both :named and %s placeholders!');
            $string->setVariable('named', 'a named');
            $string->addVariable('an anonymous');

            $this->assertEquals('This string contains both a named and an anonymous placeholders!', $string->build());

            $string->clear();

            $string->setVariables(['an anonymous', 'named' => 'a named']);
            $this->assertEquals('This string contains both a named and an anonymous placeholders!', $string->build());

            // finally check that __toString() calls build()
            $this->assertEquals('This string contains both a named and an anonymous placeholders!', (string) $string);
        }

        public function testCast()
        {
            $value = 'this is a string';
            $castedString = String::cast($value);
            $this->assertInstanceOf(String::class, $castedString);
            $this->assertEquals($value, $castedString->getInternalValue());

            // check that if value is already a String, it is returned as is
            $this->assertSame($castedString, String::cast($castedString));

        }

    }