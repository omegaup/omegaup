<?php

namespace OmegaUp\DAO;

/**
 * SubmissionFeedbackThread Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\SubmissionFeedbackThread}.
 *
 * @access public
 */
class SubmissionFeedbackThread extends \OmegaUp\DAO\Base\SubmissionFeedbackThread {
    /**
     * Gets the participants in a submission feedback thread, ignoring the
     * author of the current comment
     *
     * @return list<array{author_id: int}>
     */
    public static function getSubmissionFeedbackThreadParticipants(
        int $submissionFeedbackId,
    ) {
        $sql = 'SELECT
                    u.user_id AS author_id
                FROM
                    Submission_Feedback_Thread sft
                INNER JOIN
                    Identities i ON sft.identity_id = i.identity_id
                INNER JOIN
                    Users u ON u.user_id = i.user_id
                WHERE
                    sft.submission_feedback_id = ?
                UNION DISTINCT
                SELECT
                    u.user_id AS author_id
                FROM
                    Submission_Feedback sf
                INNER JOIN
                    Identities i
                ON
                    sf.identity_id = i.identity_id
                INNER JOIN
                    Users u
                ON
                    u.user_id = i.user_id
                WHERE
                    sf.submission_feedback_id = ?;

        ';

        /** @var list<array{author_id: int}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [ $submissionFeedbackId, $submissionFeedbackId ]
        );
    }
}
