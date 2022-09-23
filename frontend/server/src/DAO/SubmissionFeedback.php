<?php

namespace OmegaUp\DAO;

/**
 * SubmissionFeedback Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\SubmissionFeedback}.
 *
 * @access public
 */
class SubmissionFeedback extends \OmegaUp\DAO\Base\SubmissionFeedback {
    /**
     * Gets the feedback of a certain submission
     *
     * @return array{author: string, author_classname: string, date: \OmegaUp\Timestamp, feedback: string}|null
     */
    public static function getSubmissionFeedback(
        \OmegaUp\DAO\VO\Submissions $submission
    ): ?array {
        $sql = '
            SELECT
                i.username as author,
                IFNULL(ur.classname, "user-rank-unranked") AS author_classname,
                sf.feedback,
                sf.date
            FROM
                Submissions s
            INNER JOIN
                Submission_Feedback sf ON sf.submission_id = s.submission_id
            INNER JOIN
                Identities i ON i.identity_id = sf.identity_id
            LEFT JOIN
                User_Rank ur ON ur.user_id = i.user_id
            WHERE
                s.submission_id = ?
        ';

        /** @var array{author: string, author_classname: string, date: \OmegaUp\Timestamp, feedback: string}|null */
        return \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [
               $submission->submission_id
            ]
        );
    }

    /**
     * Gets the SubmissionFeedback object of a certain submission
     *
     * @return \OmegaUp\DAO\VO\SubmissionFeedback|null
     */
    public static function getFeedbackBySubmission(
        \OmegaUp\DAO\VO\Submissions $submission
    ): ?\OmegaUp\DAO\VO\SubmissionFeedback {
        $fields = join(
            ', ',
            array_map(
                fn (string $field): string => "sf.{$field}",
                array_keys(
                    \OmegaUp\DAO\VO\SubmissionFeedback::FIELD_NAMES
                )
            )
        );
        $sql = "SELECT
                    {$fields}
                FROM
                    Submission_Feedback sf
                INNER JOIN
                    Submissions s ON s.submission_id = sf.submission_id
                WHERE
                    s.submission_id = ?
                FOR UPDATE;
        ";

        /** @var array{date: \OmegaUp\Timestamp, feedback: string, identity_id: int, range_bytes_end: int, range_bytes_start: int, submission_feedback_id: int, submission_id: int}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [
               $submission->submission_id
            ]
        );
        if (is_null($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\SubmissionFeedback($rs);
    }
}
