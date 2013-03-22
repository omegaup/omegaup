<?php

/**
 * Description of OmegaupUITestCase
 *
 * @author joemmanuel
 */

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class OmegaupUITestCase extends PHPUnit_Extensions_SeleniumTestCase {

	protected function setUp() {
		$this->setBrowser('*firefox');
		$this->setBrowserUrl(OMEGAUP_BASE_URL);
	}
}

