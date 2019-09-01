<?php

/**
 * SubmissionController
 */
class SubmissionController extends \OmegaUp\Controllers\Controller {
    public static function getSource(string $guid) {
        return \OmegaUp\Grader::GetInstance()->getSource($guid);
    }
}
