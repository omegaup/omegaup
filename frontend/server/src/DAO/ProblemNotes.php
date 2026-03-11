<?php

namespace OmegaUp\DAO;

/**
 * ProblemNotes Data Access Object (DAO).
 *
 * @psalm-type ProblemNoteItem=array{alias: string, title: string, note_text: string, problem_id: int}
 *
 * @access public
 * @package docs
 */
class ProblemNotes extends \OmegaUp\DAO\Base\ProblemNotes {
    /**
     * Create or update a note, preserving created_at on update.
     *
     * Uses INSERT ... AS new ON DUPLICATE KEY UPDATE (MySQL 8.0.19+)
     * instead of REPLACE INTO, which would reset created_at.
     */
    public static function upsert(
        \OmegaUp\DAO\VO\ProblemNotes $Problem_Notes
    ): int {
        if (
            empty($Problem_Notes->identity_id) ||
            empty($Problem_Notes->problem_id)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }
        $sql = '
            INSERT INTO
                Problem_Notes (
                    `identity_id`,
                    `problem_id`,
                    `note_text`,
                    `created_at`,
                    `updated_at`
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                ) AS `new`
            ON DUPLICATE KEY UPDATE
                `note_text` = `new`.`note_text`,
                `updated_at` = `new`.`updated_at`;';
        $params = [
            $Problem_Notes->identity_id,
            $Problem_Notes->problem_id,
            $Problem_Notes->note_text,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Problem_Notes->created_at
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Problem_Notes->updated_at
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Get all notes for a specific identity
     *
     * @return list<ProblemNoteItem>
     */
    public static function getAllNotesForIdentity(int $identityId): array {
        $query = '
            SELECT
                Problems.problem_id,
                Problems.alias,
                Problems.title,
                Problems.visibility,
                pn.note_text
            FROM
                Problems
            INNER JOIN
                Problem_Notes pn
            ON
                Problems.problem_id = pn.problem_id
            WHERE
                pn.identity_id = ?
            ORDER BY
                pn.updated_at DESC;';
        $queryParams = [$identityId];
        /** @var list<array{alias: string, note_text: string, problem_id: int, title: string, visibility: int}> */
        $resultRows = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $query,
            $queryParams
        );

        $notedProblems = [];
        foreach ($resultRows as $rowData) {
            $visibility = intval($rowData['visibility']);
            if (
                $visibility < \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_WARNING
                && $visibility !== \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED
            ) {
                continue;
            }
            $notedProblems[] = [
                'alias' => $rowData['alias'],
                'title' => $rowData['title'],
                'note_text' => $rowData['note_text'],
                'problem_id' => $rowData['problem_id'],
            ];
        }
        return $notedProblems;
    }

    /**
     * Batch-fetch notes for a set of problems for a given identity.
     * Returns a map keyed by problem_id => note_text.
     *
     * @param list<int> $problemIds
     * @return array<int, string>
     */
    public static function getNotesByIdentityAndProblemIds(
        int $identityId,
        array $problemIds
    ): array {
        if (empty($problemIds)) {
            return [];
        }

        $placeholders = implode(
            ',',
            array_fill(0, count($problemIds), '?')
        );
        $query = "
            SELECT
                problem_id,
                note_text
            FROM
                Problem_Notes
            WHERE
                identity_id = ?
                AND problem_id IN ({$placeholders});";

        $queryParams = array_merge([$identityId], $problemIds);
        /** @var list<array{note_text: string, problem_id: int}> */
        $resultRows = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $query,
            $queryParams
        );

        $notes = [];
        foreach ($resultRows as $row) {
            $notes[intval($row['problem_id'])] = $row['note_text'];
        }
        return $notes;
    }
}
