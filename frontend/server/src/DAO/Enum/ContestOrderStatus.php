<?php

namespace OmegaUp\DAO\Enum;

/**
 * This class represents an enumeration of possible sorting orders for contests.
 * It provides constants for different sorting criteria. It also includes
 * mappings for SQL column names and human-readable names corresponding to each
 * sorting order.
 */
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
        self::DURATION => 'duration_minutes',
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
