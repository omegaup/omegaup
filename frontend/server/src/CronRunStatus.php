<?php

namespace OmegaUp;

/**
 * The states a cron run can be in, mirroring the `Cron_Runs.status` column.
 */
enum CronRunStatus: string {
    case Running = 'running';
    case Success = 'success';
    case Failure = 'failure';
}
