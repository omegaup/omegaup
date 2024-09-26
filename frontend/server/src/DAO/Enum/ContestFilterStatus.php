<?php

namespace OmegaUp\DAO\Enum;

class ContestFilterStatus extends \OmegaUp\DAO\Enum\StatusBase {
    const SIGNED_UP = 0;
    const ONLY_RECOMMENDED = 1;
    const ALL = 2;

    public const NAME_FOR_STATUS = [
        self::SIGNED_UP => 'signedup',
        self::ONLY_RECOMMENDED => 'recommended',
        self::ALL => 'all',
    ];

    public static function convertToInt(?string $tab): int {
        if (is_null($tab)) {
            return self::ALL;
        }
        $index = array_search($tab, self::NAME_FOR_STATUS);
        if ($index === false) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'filter'
            );
        }
        $filter = self::getIntValue($index);
        if (is_null($filter)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'filter'
            );
        }
        return $filter;
    }
}
