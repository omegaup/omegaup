<?php

namespace OmegaUp\DAO;

/**
 * SubmissionFeedback Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\SubmissionFeedback}.
 *
 * @author carlosabcs
 * @access public
 */
class SubmissionFeedback extends \OmegaUp\DAO\Base\SubmissionFeedback {
    /**
     * Get the submission feedback of a given submission
     *
     * @return null|\OmegaUp\DAO\VO\SubmissionFeedback
     */
    public static function getBySubmission(
        \OmegaUp\DAO\VO\Submissions $submission
    ): ?\OmegaUp\DAO\VO\SubmissionFeedback {
        $sql = '
            SELECT
                sf.*
            FROM
                Submission_Feedback sf
            WHERE
                sf.submission_id = ?';

        /** @var array{date: \OmegaUp\Timestamp, feedback: string, identity_id: int, submission_feedback_id: int, submission_id: int}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->getRow(
            $sql,
            [ $submission->submission_id ]
        );
        if (is_null($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\SubmissionFeedback($rs);
    }
}
