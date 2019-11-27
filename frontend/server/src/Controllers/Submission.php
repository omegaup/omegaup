<?php

 namespace OmegaUp\Controllers;

/**
 * SubmissionController
 */
class Submission extends \OmegaUp\Controllers\Controller {
    public static function getSource(string $guid): string {
        return \OmegaUp\Grader::GetInstance()->getSource($guid);
    }
}
