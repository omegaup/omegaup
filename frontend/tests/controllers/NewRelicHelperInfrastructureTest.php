<?php

namespace OmegaUp\Test\Controllers;

/**
 * Test cases for NewRelic Helper Infrastructure
 * This test validates the foundation helper classes work correctly
 */
class NewRelicHelperInfrastructureTest extends \OmegaUp\Test\ControllerTestCase {
    public function setUp(): void {
        parent::setUp();
    }

    /**
     * Test NewRelicHelper basic functionality
     */
    public function testNewRelicHelperBasics() {
        // Test availability check
        $isAvailable = \OmegaUp\NewRelicHelper::isAvailable();
        $this->assertIsBool($isAvailable);

        // Test error reporting (should not throw exceptions)
        $result = \OmegaUp\NewRelicHelper::noticeError(
            new \Exception(
                'Test error'
            )
        );
        $this->assertIsBool($result);

        // Test transaction naming (should not throw exceptions)
        \OmegaUp\NewRelicHelper::nameTransaction('test-transaction');
        $this->assertTrue(true); // If we get here, no exception was thrown

        // Test custom attributes (should not throw exceptions)
        \OmegaUp\NewRelicHelper::addCustomAttribute('test-key', 'test-value');
        $this->assertTrue(true); // If we get here, no exception was thrown

        // Test status method
        $status = \OmegaUp\NewRelicHelper::getStatus();
        $this->assertIsArray($status);
        $this->assertArrayHasKey('extension_loaded', $status);
        $this->assertArrayHasKey('notice_error_exists', $status);
        $this->assertArrayHasKey('name_transaction_exists', $status);
        $this->assertArrayHasKey('add_custom_attribute_exists', $status);
    }

    /**
     * Test ArrayHelper basic functionality
     */
    public function testArrayHelperBasics() {
        $testArray = [
            'string_key' => 'test_value',
            'int_key' => 42,
            'float_key' => 3.14,
            'bool_key' => true,
            'nested' => [
                'deep' => 'nested_value'
            ]
        ];

        // Test basic get
        $this->assertEquals(
            'test_value',
            \OmegaUp\ArrayHelper::get(
                $testArray,
                'string_key'
            )
        );
        $this->assertEquals(
            'default',
            \OmegaUp\ArrayHelper::get(
                $testArray,
                'missing_key',
                'default'
            )
        );

        // Test typed getters
        $this->assertEquals(
            'test_value',
            \OmegaUp\ArrayHelper::getString(
                $testArray,
                'string_key'
            )
        );
        $this->assertEquals(
            42,
            \OmegaUp\ArrayHelper::getInt(
                $testArray,
                'int_key'
            )
        );
        $this->assertEquals(
            3.14,
            \OmegaUp\ArrayHelper::getFloat(
                $testArray,
                'float_key'
            )
        );
        $this->assertEquals(
            true,
            \OmegaUp\ArrayHelper::getBool(
                $testArray,
                'bool_key'
            )
        );

        // Test hasKeys
        $this->assertTrue(
            \OmegaUp\ArrayHelper::hasKeys(
                $testArray,
                ['string_key', 'int_key']
            )
        );
        $this->assertFalse(
            \OmegaUp\ArrayHelper::hasKeys(
                $testArray,
                ['string_key', 'missing_key']
            )
        );

        // Test getPath for nested access
        $this->assertEquals(
            'nested_value',
            \OmegaUp\ArrayHelper::getPath(
                $testArray,
                ['nested', 'deep']
            )
        );
        $this->assertEquals(
            'default',
            \OmegaUp\ArrayHelper::getPath(
                $testArray,
                ['nested', 'missing'],
                'default'
            )
        );
    }

    /**
     * Test ArrayHelper edge cases and safety
     */
    public function testArrayHelperSafety() {
        $emptyArray = [];

        // All methods should handle empty arrays gracefully
        $this->assertNull(\OmegaUp\ArrayHelper::get($emptyArray, 'missing'));
        $this->assertEquals(
            'default',
            \OmegaUp\ArrayHelper::getString(
                $emptyArray,
                'missing',
                'default'
            )
        );
        $this->assertEquals(
            0,
            \OmegaUp\ArrayHelper::getInt(
                $emptyArray,
                'missing',
                0
            )
        );
        $this->assertEquals(
            0.0,
            \OmegaUp\ArrayHelper::getFloat(
                $emptyArray,
                'missing',
                0.0
            )
        );
        $this->assertEquals(
            false,
            \OmegaUp\ArrayHelper::getBool(
                $emptyArray,
                'missing',
                false
            )
        );
        $this->assertFalse(
            \OmegaUp\ArrayHelper::hasKeys(
                $emptyArray,
                ['any_key']
            )
        );
        $this->assertEquals(
            'default',
            \OmegaUp\ArrayHelper::getPath(
                $emptyArray,
                ['any', 'path'],
                'default'
            )
        );
    }

    /**
     * Test that helper classes don't interfere with each other
     */
    public function testHelperClassesIndependence() {
        // Use both helpers in the same test
        $testArray = ['test' => 'value'];
        $arrayResult = \OmegaUp\ArrayHelper::get($testArray, 'test');
        $newrelicResult = \OmegaUp\NewRelicHelper::isAvailable();

        // Both should work independently
        $this->assertEquals('value', $arrayResult);
        $this->assertIsBool($newrelicResult);
    }
}
