<?php

namespace OmegaUp\DAO\Enum;

class ActiveStatus extends \OmegaUp\DAO\Enum\StatusBase {
    public const ALL = 0;
    public const ACTIVE = 1;
    public const PAST = 2;
    public const FUTURE = 3;

    public const SQL_FOR_STATUS = [
        'TRUE',
        'finish_time >= NOW() AND start_time <= NOW()',
        'finish_time < NOW()',
        'start_time > NOW()',
    ];
}
