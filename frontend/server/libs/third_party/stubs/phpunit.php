<?php

namespace PHPUnit\Framework;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\MockBuilder;
use Prophecy\Prophecy\ObjectProphecy;

abstract class Assert {
    /**
     * Asserts that a variable is of a given type.
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     *
     * @psalm-template ExpectedType of object
     * @psalm-param class-string<ExpectedType> $expected
     * @psalm-assert ExpectedType $actual
     */
    public static function assertInstanceOf($expected, $actual, $message = '');

    /**
     * Asserts that a variable is of a given type.
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     *
     * @psalm-template ExpectedType of object
     * @psalm-param class-string<ExpectedType> $expected
     * @psalm-assert !ExpectedType $actual
     */
    public static function assertNotInstanceOf($expected, $actual, $message = '');

    /**
     * Asserts that a condition is true.
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @psalm-assert true $condition
     */
    public static function assertTrue($condition, $message = '');

    /**
     * Asserts that a condition is not true.
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @psalm-assert !true $condition
     */
    public static function assertNotTrue($condition, $message = '');

    /**
     * Asserts that a condition is false.
     *
      * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @psalm-assert false $condition
     */
    public static function assertFalse($condition, $message = '');

    /**
     * Asserts that a condition is not false.
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @psalm-assert !false $condition
     */
    public static function assertNotFalse($condition, $message = '');

    /**
     * Asserts that a variable is null.
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @psalm-assert null $actual
     */
    public static function assertNull($actual, $message = '');

    /**
     * Asserts that a variable is not null.
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @psalm-assert !null $actual
     */
    public static function assertNotNull($actual, $message = '');

    /**
     * Asserts that two variables are the same.
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @psalm-template ExpectedType
     * @psalm-param ExpectedType $expected
     * @psalm-assert =ExpectedType $actual
     */
    function assertSame($expected, $actual, $message = '');

    /**
     * Asserts that two variables are not the same.
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    function assertNotSame($expected, $actual, $message = '');
}

interface Test extends \Countable {
}


/**
 * @internal
 */
interface SelfDescribing {
    /**
     * Returns a string representation of the object.
     */
    public function toString(): string;
}

abstract class TestCase extends Assert implements Test, SelfDescribing {
    /**
     * @template T
     * @param class-string<T> $class
     * @return MockObject&T
     */
    public function createMock($class);

    /**
     * Returns a builder object to create mock objects using a fluent interface.
     *
     * @template T
     * @param class-string<T> $className
     *
     * @return MockBuilder<T>
     */
    public function getMockBuilder(string $className);

    /**
     * @template T
     * @param class-string<T> $classOrInterface
     * @return ObjectProphecy<T>
     */
    public function prophesize($classOrInterface): ObjectProphecy;

    /**
     * @param class-string<\Throwable> $exception
     * @return void
     */
    public function expectException(string $exception);
}
