<?php

    namespace ObjectivePHP\Primitives\tests\units;

    use mageekguy\atoum;
    use ObjectivePHP\AtoumExtension\AtoumTestCase;
    use ObjectivePHP\Primitives\Collection;
    use ObjectivePHP\Primitives\Exception;
    use ObjectivePHP\Primitives\Numeric;
    use ObjectivePHP\Primitives\String;

    class StringTest extends AtoumTestCase
    {

        public function __construct(atoum\adapter $adapter = null, atoum\annotations\extractor $annotationExtractor = null, atoum\asserter\generator $asserterGenerator = null, atoum\test\assertion\manager $assertionManager = null, \closure $reflectionClassFactory = null)
        {
            $this->setTestedClassName(String::class);
            parent::__construct($adapter, $annotationExtractor, $asserterGenerator, $assertionManager, $reflectionClassFactory);
        }

        public function testAccessor()
        {
            $string = new String('example string');
            $this->string($string->getInternalValue())->isEqualTo('example string');
        }

        public function testLowercase()
        {
            $string = new String('TEST STRING');
            $this->string((string) $string->lower())->isEqualTo('test string');

            // with accented charcaters
            $string = new String('CHAÎNE ACCENTUÉE');
            $this->string((string) $string->lower())->isEqualTo('chaîne accentuée');
        }

        public function testUppercase()
        {
            // default mode
            $string = new String('test string');
            $this->string($string->upper()->getInternalValue())->isEqualTo('TEST STRING');

            $otherString = new String('test string');
            $this->object($string)->isEqualTo($otherString->upper(String::UPPER_ALL));

            // first letter only
            $string = (new String('test string'))->upper(String::UPPER_FIRST);
            $this->string($string->getInternalValue())->isEqualTo('Test string');

            // every word
            $string = (new String('test string'))->upper(String::UPPER_WORDS);
            $this->string($string->getInternalValue())->isEqualTo('Test String');


            // with accented charcaters
            $string = new String('chaîne accentuée');
            $this->string($string->upper()->getInternalValue())->isEqualTo('CHAÎNE ACCENTUÉE');
        }

        public function testLength()
        {
            $string = new String('test string');
            $this->integer((int) $string->length())->isEqualTo(11);

            // with accented charcaters
            $string = new String('chaîne accentuée');
            $this->integer($string->length())->isEqualTo(16);

        }

        public function testMatches($subject, $pattern, $result)
        {
            $string = new String($subject);

            $this->boolean($string->matches($pattern))->isEqualTo($result);
        }

        protected function testMatchesDataProvider()
        {
            return
                [
                    ['hello world', '/world/', true],
                    ['hello world', '/WORLD/', false],
                    ['hello world', '/WORLD/i', true],
                ];
        }

        public function testTrim($string, $charlist, $ends, $expected)
        {
            $string = new String($string);
            $result = $string->trim($charlist, $ends);

            $this->string((string) $result)->isEqualTo($expected);
        }

        protected function testTrimDataProvider()
        {
            return
                [
                    [' test string ', null, null, 'test string'],
                    [' test string ', null,     String::LEFT, 'test string '],
                    [' test string ', null,     String::RIGHT, ' test string'],
                    [' test string ', null,     String::BOTH, 'test string'],
                    ['test string', 'tg',     String::LEFT, 'est string'],
                    ['test string', 'tg',     String::RIGHT, 'test strin'],
                    ['test string', 'tg',     String::BOTH, 'est strin'],
                    ['test string', 'tg', null, 'est strin'],
                ];
        }

        public function testReplace()
        {
            $string = new String('abcde');
            $this
                ->string((string) $string->replace('de', '_DE_de'))
                ->isEqualTo('abc_DE_de')
                ->string((string) $string->replace('_DE', '', String::CASE_SENSITIVE))
                ->isEqualTo('abc_de');


            // same thing using a regexp
            $string = new String('abcde');
            $this
                ->string((string) $string->replace('/d./', '_DE_de', String::REGEXP))
                ->isEqualTo('abc_DE_de');
        }

        public function testRegexplace()
        {

            $string = new String('abcde');
            $this
                ->string((string) $string->regexplace('/[aeiou]/', 'x'))
                ->isEqualTo('xbcdx');
        }


        public function testExtract()
        {
            $string = new String('abcdefgh');

            $this->object($sub = $string->extract(1))->isInstanceOf(String::class);
            $this->string((string) $sub)->isEqualTo('bcdefgh');

            $this->object($sub = $string->extract(1, 1))->isInstanceOf(String::class);
            $this->string((string) $sub)->isEqualTo('b');

            $this->object($sub = $string->extract(1, -1))->isInstanceOf(String::class);
            $this->string((string) $sub)->isEqualTo('bcdefg');

            // with accented charcaters
            $string = new String('chaîne accentuée');
            $this->object($sub = $string->extract(3, 1))->isInstanceOf(String::class);
            $this->string((string) $sub)->isEqualTo('î');

        }

        public function testCrop()
        {
            // same as extract(), but amend internal value instead of returning a new string
            $string = new String('abcdefgh');

            $this->object($sub = $string->crop(1))->isIdenticalTo($string);
            $this->string((string) $sub)->isEqualTo('bcdefgh');

            $this->object($sub = $string->crop(1, -1))->isIdenticalTo($string);
            $this->string((string) $sub)->isEqualTo('cdefg');

            $this->object($sub = $string->crop(1, 1))->isIdenticalTo($string);
            $this->string((string) $sub)->isEqualTo('d');

            // with accented charcaters
            $string = new String('chaîne accentuée');
            $this->object($sub = $string->crop(3, 1))->isIdenticalTo($string);
            $this->string((string) $sub)->isEqualTo('î');
        }

        public function testContains()
        {
            $string = new String("Hello World");

            $this
                ->boolean($string->contains('World'))
                ->isTrue()
                ->boolean($string->contains('foo'))
                ->isFalse()
                ->boolean($string->contains('world', String::CASE_SENSITIVE))
                ->isFalse()
                ->boolean($string->contains('world'))
                ->isTrue()
                ->boolean($string->contains(1))
                ->isFalse();;
        }

        public function testSplit($str, $pattern, $expected, $exception, $code)
        {
            $string = new String($str);

            if ($exception)
            {
                $this
                    ->exception(function () use ($string, $pattern)
                    {
                        $string->split($pattern, String::REGEXP);
                    })
                    ->isInstanceOf($exception)
                    ->hasCode($code);
            }
            else
            {
                // check returned object
                $this
                    ->object($result = $string->split($pattern, String::REGEXP))
                    ->isInstanceOf(Collection::class);

                $this->string($result->type())->isEqualTo(String::class);

                // check returned values
                $values = $result->getArrayCopy();

                $this->sizeOf($values)->isEqualTo(count($expected));

                foreach ($values as $i => $value)
                {
                    $this->string((string) $value)->isEqualTo((string) $expected[$i]);
                }

            }
        }

        protected function testSplitDataProvider()
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
            $string = new String('Keep Phocus');

            $this
                ->exception(function () use ($string)
                {
                    $string->insert('string', '30');
                })
                ->isInstanceOf(Exception::class)
                ->hasCode(Exception::INVALID_PARAMETER);

            $this
                ->exception(function () use ($string)
                {
                    $string->insert([], []);
                })
                ->isInstanceOf(Exception::class)
                ->hasCode(Exception::INVALID_PARAMETER);

            $this
                ->exception(function () use ($string)
                {
                    $string->insert(['array'], 30);
                })
                ->isInstanceOf(Exception::class)
                ->hasCode(Exception::INVALID_PARAMETER);

            $string = new String('Objective');
            $this
                ->object($extendedString = $string->insert('Keep', 0))
                ->isInstanceOf(String::class)
                ->string($extendedString->getInternalValue())
                ->isEqualTo('KeepObjective');

            $string = (new String('Keep'))->insert('Objective', 99);
            $this
                ->string($string->getInternalValue())
                ->isEqualTo('KeepObjective');

            $string = (new String('Keep'))->insert(new String('Objective'), -2);
            $this
                ->string($string->getInternalValue())
                ->isEqualTo('KeObjectiveep');

            $string = (new String('Keep'))->insert('Objective', 3);
            $this
                ->string($string->getInternalValue())
                ->isEqualTo('KeeObjectivep');
        }

        public function testPrepend()
        {
            $string = new String('Phocus');
            $this
                ->string($string->prepend('Keep')->getInternalValue())
                ->isEqualTo('KeepPhocus');
        }

        public function testAppend()
        {
            $string = new String('Keep');
            $this
                ->string($string->append('Phocus')->getInternalValue())
                ->isEqualTo('KeepPhocus');
        }

        public function testReverse()
        {
            $string = new String('abc');
            $this
                ->string($string->reverse()->getInternalValue())
                ->isEqualTo('cba');
        }

        public function testLocate()
        {
            $string = new String('Hello Php World');
            $this
                ->boolean($string->locate('AA'))
                ->isFalse()
                ->isFalse($string->locate('L', 0, String::CASE_SENSITIVE))
                ->isEqualTo(false)
                ->integer($string->locate('W')->getInternalValue())
                ->isEqualTo(10)
                ->integer($string->locate('l', new Numeric(5))->getInternalValue())
                ->isEqualTo(13)
                ->exception(function () use ($string)
                {
                    $string->locate([]);
                })
                ->isInstanceOf(Exception::class)
                ->hasCode(Exception::INVALID_PARAMETER)
                ->exception(function () use ($string)
                {
                    $string->locate('ss', 'aa');
                })
                ->isInstanceOf(Exception::class)
                ->hasCode(Exception::INVALID_PARAMETER)
                ->exception(function () use ($string)
                {
                    $string->locate('test', -2);
                })
                ->isInstanceOf(Exception::class)
                ->hasCode(Exception::INVALID_PARAMETER)
                ->integer($string->locate('l', 0, String::FROM_END)->getInternalValue())->isEqualTo(13)
                ->integer($string->locate('l', 0, String::FROM_END)->getInternalValue())->isEqualTo(13)
                ->integer($string->locate('P', 0, String::FROM_END)->getInternalValue())->isEqualTo(8)
                ->integer($string->locate('P', 0, String::FROM_END | String::CASE_SENSITIVE)->getInternalValue())
                ->isEqualTo(6);
        }

        public function testCrypt()
        {
            $string = new String('Hello Php World');

            $this->boolean($string->crypt()->challenge('Hello Php World'))->isTrue();
            $this->boolean($string->challenge('Hello World'))->isFalse();

            // same test with custom salt
            $string = new String('Hello Php World', md5(time()));

            $this->boolean($string->crypt()->challenge('Hello Php World'))->isTrue();
            $this->boolean($string->challenge('Hello World'))->isFalse();
        }

        public function testMd5()
        {
            $string = new String('Hello World');

            $this->string($string->md5())->isEqualTo(md5('Hello World'));
        }

        public function testVariableStringHandling()
        {
            // variables handling by constructor
            $string            = (new String('this is a string', ['those', 'are', 'variables']));
            $reflectedString   = new \ReflectionObject($string);
            $reflectedProperty = new \ReflectionProperty(String::class, 'variables');
            $reflectedProperty->setAccessible(true);
            $stringVariables = $reflectedProperty->getValue($string);
            $this->array($stringVariables)->isEqualTo(['those', 'are', 'variables']);

            // anonymous placeholders
            $string = new String('This string contains a %s');

            $string->addVariable('placeholder');

            $this->variable($string->build())->isEqualTo('This string contains a placeholder');

            $string->clear();
            $this->variable($string->build())->isEqualTo('This string contains a %s');

            $string->setVariable(0, 'placeholder (again!)');
            $this->variable($string->build())->isEqualTo('This string contains a placeholder (again!)');

            // named placeholders
            $string = new String('This string contains a :named-placeholder');
            $string->setVariable('named-placeholder', 'named placeholder (I tell you!)');

            $this->variable($string->build())->isEqualTo('This string contains a named placeholder (I tell you!)');

            // mixed
            //
            // named placeholders are handled apart from anonymous ones, so
            // they aren't taken in account for anonymous variables position
            $string = new String('This string contains both :named and %s placeholders!');
            $string->setVariable('named', 'a named');
            $string->addVariable('an anonymous');

            $this->variable($string->build())
                 ->isEqualTo('This string contains both a named and an anonymous placeholders!');

            $string->clear();

            $string->setVariables(['an anonymous', 'named' => 'a named']);
            $this->variable($string->build())
                 ->isEqualTo('This string contains both a named and an anonymous placeholders!');

            // finally check that __toString() calls build()
            $this->variable((string) $string)
                 ->isEqualTo('This string contains both a named and an anonymous placeholders!');
        }

}