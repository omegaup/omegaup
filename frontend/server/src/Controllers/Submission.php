<?php

 namespace OmegaUp\Controllers;

/**
 * SubmissionController
 */
class Submission extends \OmegaUp\Controllers\Controller {
    public static function getSource(string $guid) {
        return \OmegaUp\Grader::GetInstance()->getSource($guid);
    }
}
