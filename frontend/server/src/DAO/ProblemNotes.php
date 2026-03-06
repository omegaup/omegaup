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
     * Get all notes for a specific identity
     *
     * @return list<ProblemNoteItem>
     */
    public static function getAllNotesForIdentity(int $identityId): array {
        $query = '
            SELECT
                ' . \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Problems::FIELD_NAMES,
            'Problems'
        ) . ',
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
        /** @var list<array{accepted: int, acl_id: int, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, current_version: string, deprecated: bool, difficulty: float|null, difficulty_histogram: null|string, email_clarifications: bool, input_limit: int, languages: string, note_text: string, order: string, problem_id: int, quality: float|null, quality_histogram: null|string, quality_seal: bool, show_diff: string, source: null|string, submissions: int, title: string, visibility: int, visits: int}> */
        $resultRows = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $query,
            $queryParams
        );

        $notedProblems = [];
        foreach ($resultRows as $rowData) {
            $problem = new \OmegaUp\DAO\VO\Problems($rowData);
            if (\OmegaUp\DAO\Problems::isVisible($problem)) {
                $notedProblems[] = [
                    'alias' => $rowData['alias'],
                    'title' => $rowData['title'],
                    'note_text' => $rowData['note_text'],
                    'problem_id' => $rowData['problem_id'],
                ];
            }
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
