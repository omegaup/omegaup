<?php

/**
 * Parent class of all Test cases for omegaUp badges
 *
 * @author carlosabcs
 */
class BadgesTestCase extends OmegaupTestCase {
    public function setUp() {
        parent::setUp();
        Utils::CleanupDb();
    }
}
