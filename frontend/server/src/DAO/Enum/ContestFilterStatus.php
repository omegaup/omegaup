<?php

namespace OmegaUp\DAO\Enum;

class ContestFilterStatus extends \OmegaUp\DAO\Enum\StatusBase {
    public const SIGNED_UP = 0;
    public const ONLY_RECOMMENDED = 1;
    public const ALL = 2;

    public const NAME_FOR_STATUS = [
        self::SIGNED_UP => 'signedup',
        self::ONLY_RECOMMENDED => 'recommended',
        self::ALL => 'all',
    ];
}
