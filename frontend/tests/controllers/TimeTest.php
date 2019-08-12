<?php

/**
 * Description of TimeTest
 *
 * @author joemmanuel
 */
class TimeTest extends OmegaupTestCase {
    public function testTimeApi() {
        // Call API
        $response = TimeController::apiGet();

        // Validate result
        $time = Time::get();
        $this->assertLessThanOrEqual($time, $response['time']);
    }
}
