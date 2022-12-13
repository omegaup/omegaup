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
     * @return list<array{author: string, author_classname: string, date: \OmegaUp\Timestamp, feedback: string, range_bytes_end: int|null, range_bytes_start: int|null}>
     */
    public static function getSubmissionFeedback(
        \OmegaUp\DAO\VO\Submissions $submission
    ) {
        $sql = '
            SELECT
                i.username as author,
                IFNULL(ur.classname, "user-rank-unranked") AS author_classname,
                sf.feedback,
                sf.range_bytes_start,
                sf.range_bytes_end,
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

        /** @var list<array{author: string, author_classname: string, date: \OmegaUp\Timestamp, feedback: string, range_bytes_end: int|null, range_bytes_start: int|null}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
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
        string $guid,
        ?int $rangeBytesStart
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
        $clause = 'AND sf.range_bytes_start IS NULL';
        $params = [$guid];
        if (!is_null($rangeBytesStart)) {
            $clause = 'AND sf.range_bytes_start = ?';
            $params[] = $rangeBytesStart;
        }
        $sql = "SELECT
                    {$fields}
                FROM
                    Submission_Feedback sf
                INNER JOIN
                    Submissions s ON s.submission_id = sf.submission_id
                WHERE
                    s.guid = ?
                    {$clause}
                FOR UPDATE;
        ";

        /** @var array{date: \OmegaUp\Timestamp, feedback: string, identity_id: int, range_bytes_end: int|null, range_bytes_start: int|null, submission_feedback_id: int, submission_id: int}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (is_null($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\SubmissionFeedback($rs);
    }

    /**
     * Gets the SubmissionFeedback objects of a certain submission
     *
     * @return list<\OmegaUp\DAO\VO\SubmissionFeedback>
     */
    public static function getAllFeedbackBySubmission(
        \OmegaUp\DAO\VO\Submissions $submission
    ) {
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

        /** @var list<array{date: \OmegaUp\Timestamp, feedback: string, identity_id: int, range_bytes_end: int|null, range_bytes_start: int|null, submission_feedback_id: int, submission_id: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [
               $submission->submission_id
            ]
        );

        $result = [];
        foreach ($rs as $record) {
            $result[] = new \OmegaUp\DAO\VO\SubmissionFeedback($record);
        }
        return $result;
    }
}
