<?php

namespace OmegaUp\DAO;

/**
 * SubmissionFeedback Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\SubmissionFeedback}.
 *
 * @psalm-type SubmissionFeedbackThread=array{author: string, authorClassname: string, submission_feedback_thread_id: int, text: string, timestamp: \OmegaUp\Timestamp}
 *
 * @access public
 */
class SubmissionFeedback extends \OmegaUp\DAO\Base\SubmissionFeedback {
    /**
     * Gets the feedback of a certain submission
     *
     * @return list<array{author: string, author_classname: string, date: \OmegaUp\Timestamp, feedback: string, feedback_thread?: list<SubmissionFeedbackThread>, range_bytes_end: int|null, range_bytes_start: int|null, submission_feedback_id: int}>
     */
    public static function getSubmissionFeedback(
        \OmegaUp\DAO\VO\Submissions $submission
    ) {
        $sql = '
            SELECT
                sf.submission_feedback_id,
                i.username as author,
                IFNULL(ur.classname, "user-rank-unranked") AS author_classname,
                sf.feedback,
                sf.range_bytes_start,
                sf.range_bytes_end,
                sf.date,
                i2.username as author_thread,
                IFNULL(ur2.classname, "user-rank-unranked") AS author_classname_thread,
                sft.submission_feedback_thread_id,
                sft.contents AS feedback_thread,
                sft.date AS date_thread
            FROM
                Submissions s
            INNER JOIN
                Submission_Feedback sf ON sf.submission_id = s.submission_id
            INNER JOIN
                Identities i ON i.identity_id = sf.identity_id
            LEFT JOIN
                User_Rank ur ON ur.user_id = i.user_id
            LEFT JOIN
                Submission_Feedback_Thread sft ON sft.submission_feedback_id = sf.submission_feedback_id
            LEFT JOIN
                Identities i2 ON i2.identity_id = sft.identity_id
            LEFT JOIN
                User_Rank ur2 ON ur2.user_id = i2.user_id
            WHERE
                s.submission_id = ?
        ';

        /** @var list<array{author: string, author_classname: string, author_classname_thread: string, author_thread: null|string, date: \OmegaUp\Timestamp, date_thread: \OmegaUp\Timestamp|null, feedback: string, feedback_thread: null|string, range_bytes_end: int|null, range_bytes_start: int|null, submission_feedback_id: int, submission_feedback_thread_id: int|null}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [
               $submission->submission_id
            ]
        );
        $feedback = [];
        foreach ($rs as $row) {
            if (!isset($feedback[$row['submission_feedback_id']])) {
                $feedback[$row['submission_feedback_id']] = [
                    'submission_feedback_id' => $row['submission_feedback_id'],
                    'author' => $row['author'],
                    'author_classname' => $row['author_classname'],
                    'feedback' => $row['feedback'],
                    'range_bytes_start' => $row['range_bytes_start'],
                    'range_bytes_end' => $row['range_bytes_end'],
                    'date' => $row['date'],
                ];
            }
            if (
                $row['feedback_thread'] !== null && $row['author_thread'] !== null && $row['date_thread'] !== null && $row['submission_feedback_thread_id'] !== null
            ) {
                $feedback[$row['submission_feedback_id']]['feedback_thread'][] = [
                    'author' => $row['author_thread'],
                    'authorClassname' => $row['author_classname_thread'],
                    'submission_feedback_thread_id' => $row['submission_feedback_thread_id'],
                    'text' => $row['feedback_thread'],
                    'timestamp' => $row['date_thread'],
                ];
            }
        }

        $feedbackWithThread = [];
        foreach ($feedback as $row) {
            $feedbackWithThread[] = $row;
        }

        return $feedbackWithThread;
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
        $params = [$guid];
        $clause = 'AND sf.range_bytes_start IS NULL';
        if ($rangeBytesStart !== null) {
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
        if ($rs === null) {
            return null;
        }
        return new \OmegaUp\DAO\VO\SubmissionFeedback($rs);
    }
}
