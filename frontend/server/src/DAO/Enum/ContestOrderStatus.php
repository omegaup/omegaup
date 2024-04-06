<?php

namespace OmegaUp\DAO\Enum;

class ContestOrderStatus extends \OmegaUp\DAO\Enum\StatusBase {
    const NONE = 0;
    const TITLE = 1;
    const ENDS = 2;
    const DURATION = 3;
    const ORGANIZER = 4;
    const CONTESTANTS = 5;
    const SIGNED_UP = 6;

    public const SQL_FOR_STATUS = [
        self::NONE => '',
        self::TITLE => 'title',
        self::ENDS => 'finish_time',
        self::DURATION => 'duration',
        self::ORGANIZER => 'organizer',
        self::CONTESTANTS => 'contestants',
        self::SIGNED_UP => 'participating',
    ];

    public const NAME_FOR_STATUS = [
        self::NONE => 'none',
        self::TITLE => 'title',
        self::ENDS => 'ends',
        self::DURATION => 'duration',
        self::ORGANIZER => 'organizer',
        self::CONTESTANTS => 'contestants',
        self::SIGNED_UP => 'signedup',
    ];
}
