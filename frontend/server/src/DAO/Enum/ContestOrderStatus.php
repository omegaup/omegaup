<?php

namespace OmegaUp\DAO\Enum;

/**
 * This class represents an enumeration of possible sorting orders for contests.
 * It provides constants for different sorting criteria. It also includes
 * mappings for SQL column names and human-readable names corresponding to each
 * sorting order.
 */
class ContestOrderStatus extends \OmegaUp\DAO\Enum\StatusBase {
    public const NONE = 0;
    public const TITLE = 1;
    public const ENDS = 2;
    public const DURATION = 3;
    public const ORGANIZER = 4;
    public const CONTESTANTS = 5;
    public const SIGNED_UP = 6;

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
