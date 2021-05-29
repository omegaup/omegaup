<?php

/**
 * Description of TimeTest
 */
class TimeTest extends \OmegaUp\Test\ControllerTestCase {
    public function testTimeApi() {
        // Call API
        $response = \OmegaUp\Controllers\Time::apiGet();

        // Validate result
        $time = \OmegaUp\Time::get();
        $this->assertLessThanOrEqual($time, $response['time']);
    }
}
