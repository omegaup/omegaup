<?php

/**
 * SubmissionController
 */
class SubmissionController extends Controller {
    public static function getSource(Submissions $submission) {
        return Grader::GetInstance()->getSource($submission->guid);
    }
}
