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
        $sql = 'WITH participants AS (
                    SELECT identity_id
                    FROM Submission_Feedback_Thread
                    WHERE submission_feedback_id = ?

                    UNION ALL

                    SELECT identity_id
                    FROM Submission_Feedback
                    WHERE submission_feedback_id = ?
                )
                SELECT DISTINCT u.user_id AS author_id
                FROM participants p
                INNER JOIN Identities i ON i.identity_id = p.identity_id
                INNER JOIN Users u ON u.user_id = i.user_id;';

        /** @var list<array{author_id: int}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [ $submissionFeedbackId, $submissionFeedbackId ]
        );
    }
}
