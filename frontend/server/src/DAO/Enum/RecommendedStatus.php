<?php

namespace OmegaUp\DAO\Enum;

class RecommendedStatus extends \OmegaUp\DAO\Enum\StatusBase {
    public const ALL = 0;
    public const RECOMMENDED = 1;
    public const NOT_RECOMMENDED = 2;

    public const SQL_FOR_STATUS = [
        'TRUE',
        'recommended = 1',
        'recommended = 0',
    ];
}
