<?php

namespace OmegaUp;

/**
 * The checks that can flag a problem, mirroring the
 * `Problem_Health_Checks.check_type` column.
 */
enum ProblemHealthCheckType: string {
    case JudgeErrors = 'judge_errors';
    case NoLanguages = 'no_languages';
    case NeverSolved = 'never_solved';
    case DeprecatedPublic = 'deprecated_public';
}
