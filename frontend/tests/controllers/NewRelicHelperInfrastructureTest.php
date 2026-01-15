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
}
