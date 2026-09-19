<?php

namespace OmegaUp;

/**
 * The states a manual rerun request can be in, mirroring the
 * `Cron_Run_Requests.status` column.
 */
enum CronRunRequestStatus: string {
    case Pending = 'pending';
    case Picked = 'picked';
    case Done = 'done';
    case Failed = 'failed';
}
