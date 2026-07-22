<?php

namespace OmegaUp\DAO;

/**
 * GSoCIdeaEdition Data Access Object (DAO).
 */
class GSoCIdeaEdition extends \OmegaUp\DAO\Base\GSoCIdeaEdition {
    public static function getByIdeaAndEdition(
        int $ideaId,
        int $editionId
    ): ?\OmegaUp\DAO\VO\GSoCIdeaEdition {
        $sql = '
            SELECT
                idea_edition_id
            FROM
                GSoC_Idea_Edition
            WHERE
                idea_id = ? AND edition_id = ?
            LIMIT 1;
        ';
        /** @var array{idea_edition_id: int|string}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$ideaId, $editionId]
        );
        if (empty($row)) {
            return null;
        }
        return self::getByPK(intval($row['idea_edition_id']));
    }
}
