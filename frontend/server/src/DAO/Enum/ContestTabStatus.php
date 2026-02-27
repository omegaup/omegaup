<?php

namespace OmegaUp\DAO\Enum;

class ContestTabStatus extends \OmegaUp\DAO\Enum\StatusBase {
    public const ALL = 0;
    public const CURRENT = 1;
    public const PAST = 2;
    public const FUTURE = 3;

    public const NAME_FOR_STATUS = [
        self::ALL => 'all',
        self::CURRENT => 'current',
        self::PAST => 'past',
        self::FUTURE => 'future',
    ];
}
