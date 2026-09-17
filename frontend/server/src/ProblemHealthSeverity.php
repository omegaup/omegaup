<?php

namespace OmegaUp;

/**
 * How bad a problem health finding is, mirroring the
 * `Problem_Health_Checks.severity` column. Declared worst first.
 */
enum ProblemHealthSeverity: string {
    case Error = 'error';
    case Warning = 'warning';
}
