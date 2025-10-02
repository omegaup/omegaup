<?php

/**
 * Tests for Grader connection errors
 */
class GraderConnectionTest extends \OmegaUp\Test\ControllerTestCase {
    public function setUp(): void {
        parent::setUp();
        // Reset grader instance before each test
        \OmegaUp\Grader::setInstanceForTesting(null);
    }

    /**
     * Simple test to verify curlRequest method directly throws exceptions
     *
     * This test verifies that curlRequest method throws exception correctly
     * when there's a connection error (BEFORE improvements)
     */
    public function testCurlRequestThrowsExceptionOnTimeout() {
        // Create a real grader instance
        $grader = new \OmegaUp\Grader();

        // Try to make a request to a URL that doesn't exist or will timeout
        // This should throw a RuntimeException
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/curl_exec failed/');

        // Use reflection to access private curlRequest method
        $reflection = new \ReflectionClass($grader);
        $method = $reflection->getMethod('curlRequest');
        $method->setAccessible(true);

        // Invalid URL that will cause connection error
        $method->invoke(
            $grader,
            'http://invalid-grader-url:12345/timeout',
            1,
            null,
            false
        );
    }

    /**
     * Test to verify that our NewRelicHelper works correctly
     */
    public function testNewRelicHelperSafeExecution() {
        // Verify that NewRelicHelper doesn't cause errors even if NewRelic is not installed
        $this->assertFalse(\OmegaUp\NewRelicHelper::isAvailable());

        // These calls should not generate errors
        \OmegaUp\NewRelicHelper::noticeError('Test error message');
        \OmegaUp\NewRelicHelper::nameTransaction('/api/test');
        \OmegaUp\NewRelicHelper::addCustomAttribute('test_key', 'test_value');

        // If we reach here, the test passed
        $this->assertTrue(true);
    }

    /**
     * Test to verify that ArrayHelper prevents undefined key errors
     */
    public function testArrayHelperPreventsUndefinedKeyErrors() {
        $testArray = [
            'existing_key' => 'value',
            'score' => 100
        ];

        // This should NOT generate "Undefined array key" warning
        $result = \OmegaUp\ArrayHelper::get(
            $testArray,
            'nonexistent_key',
            'default'
        );
        $this->assertEquals('default', $result);

        $score = \OmegaUp\ArrayHelper::getInt($testArray, 'score', 0);
        $this->assertEquals(100, $score);

        $missing = \OmegaUp\ArrayHelper::getInt($testArray, 'missing_score', 0);
        $this->assertEquals(0, $missing);
    }
}
