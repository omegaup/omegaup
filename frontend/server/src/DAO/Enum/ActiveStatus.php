<?php

namespace OmegaUp\DAO\Enum;

class ActiveStatus extends \OmegaUp\DAO\Enum\StatusBase {
    const ALL = 0;
    const ACTIVE = 1;
    const PAST = 2;
    const FUTURE = 3;

    public const SQL_FOR_STATUS = [
        'TRUE',
        'finish_time >= NOW() AND start_time <= NOW()',
        'finish_time < NOW()',
        'start_time > NOW()',
    ];
}
