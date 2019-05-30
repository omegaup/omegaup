<?php

/**
 * SubmissionController
 */
class SubmissionController extends Controller {
    public static function getSource(string $guid) {
        return Grader::GetInstance()->getSource($guid);
    }
}
