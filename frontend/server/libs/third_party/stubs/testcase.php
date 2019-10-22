<?php // phpcs:ignoreFile
namespace PHPUnit\Framework;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\MockBuilder;
use Prophecy\Prophecy\ObjectProphecy;
abstract class TestCase extends Assert implements Test, SelfDescribing
{
    /**
     * @template T
     * @param class-string<T> $class
     * @return MockObject&T
     */
    public function createMock($class) {}
    /**
     * Returns a builder object to create mock objects using a fluent interface.
     *
     * @template T
     * @param class-string<T> $className
     *
     * @return MockBuilder<T>
     */
    public function getMockBuilder(string $className) {}
    /**
     * @template T
     * @param class-string<T> $classOrInterface
     * @return ObjectProphecy<T>
     */
    public function prophesize($classOrInterface): ObjectProphecy {}
    /**
     * @param class-string<\Throwable> $exception
     * @return void
     */
    public function expectException(string $exception) {}
}