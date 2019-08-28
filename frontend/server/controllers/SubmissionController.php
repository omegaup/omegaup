<?php

/**
 * SubmissionController
 */
class SubmissionController extends Controller {
    public static function getSource(string $guid) {
        return \OmegaUp\Grader::GetInstance()->getSource($guid);
    }
}
