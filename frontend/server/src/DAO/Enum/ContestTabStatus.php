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

    public static function convertToInt(?string $tab): int {
        if (is_null($tab)) {
            return self::CURRENT;
        }
        $index = array_search($tab, self::NAME_FOR_STATUS);
        if ($index === false) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'tab_name'
            );
        }
        $activeTab = self::getIntValue($index);
        if (is_null($activeTab)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'tab_name'
            );
        }
        return $activeTab;
    }
}
