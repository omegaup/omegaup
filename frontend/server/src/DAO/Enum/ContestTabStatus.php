<?php

namespace OmegaUp\DAO\Enum;

class ContestTabStatus extends \OmegaUp\DAO\Enum\StatusBase {
    const CURRENT = 0;
    const PAST = 1;
    const FUTURE = 2;

    public const NAME_FOR_STATUS = [
        self::CURRENT => 'current',
        self::PAST => 'past',
        self::FUTURE => 'future',
    ];
}
