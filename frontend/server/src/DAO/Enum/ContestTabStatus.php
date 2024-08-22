<?php

namespace OmegaUp\DAO\Enum;

class ContestTabStatus extends \OmegaUp\DAO\Enum\StatusBase {
    const CURRENT = 0;
    const FUTURE = 1;
    const PAST = 2;

    public const NAME_FOR_STATUS = [
        self::CURRENT => 'current',
        self::FUTURE => 'future',
        self::PAST => 'past',
    ];
}
