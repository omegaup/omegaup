<?php

namespace OmegaUp\DAO\Enum;

class FilteredStatus extends \OmegaUp\DAO\Enum\StatusBase {
    const ALL = 0;
    const SIMPLE = 1;
    const FULLTEXT = 2;

    public const SQL_FOR_STATUS = [
        'TRUE',
        '(title LIKE CONCAT(\'%\', ?, \'%\') OR description LIKE CONCAT(\'%\', ?, \'%\'))',
        'MATCH(title, description) AGAINST(? IN BOOLEAN MODE)',
    ];
}
