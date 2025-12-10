<?php

namespace OmegaUp\DAO\Enum;

class ContestTabStatus extends \OmegaUp\DAO\Enum\StatusBase {
    const ALL = 0;
    const CURRENT = 1;
    const PAST = 2;
    const FUTURE = 3;

    public const NAME_FOR_STATUS = [
        self::ALL => 'all',
        self::CURRENT => 'current',
        self::PAST => 'past',
        self::FUTURE => 'future',
    ];
}
