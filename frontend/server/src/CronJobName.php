<?php

namespace OmegaUp;

/**
 * The cron jobs registered in `Cron_Jobs`.
 *
 * `Cron_Runs.name` stays a plain string so the history survives a job being
 * renamed or dropped from the registry, which means it can hold names that are
 * not listed here. This is the list for code that names a specific job.
 */
enum CronJobName: string {
    case AggregateFeedback = 'aggregate_feedback.py';
    case AssignBadges = 'assign_badges.py';
    case BuildProblemRecModel = 'build_problem_rec_model.py';
    case ProblemHealthCheck = 'problem_health_check.py';
    case UpdateRanks = 'update_ranks.py';
}
