<?php

/**
 * Description of TimeTest
 *
 * @author joemmanuel
 */
class TimeTest extends OmegaupTestCase {
	
	public function testTimeApi() {
						
		// Call API
		$response = TimeController::apiList();
		
		// Validate result
		$time = time();
		$this->assertLessThanOrEqual($time, $response['time']);		
	}
}

