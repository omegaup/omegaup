<?php

namespace OmegaUp\DAO;

/**
 * PrivacyStatementConsentLog Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\PrivacyStatementConsentLog}.
 *
 * @access public
 */
class PrivacyStatementConsentLog extends \OmegaUp\DAO\Base\PrivacyStatementConsentLog {
    public static function hasAcceptedPrivacyStatement($identity_id, $privacystatement_id) {
        $sql = 'SELECT
                  COUNT(1)
                FROM
                  `PrivacyStatement_Consent_Log` pscl
                WHERE
                  pscl.identity_id = ?
                  AND pscl.privacystatement_id = ?
               ';
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, [$identity_id, $privacystatement_id]) > 0;
    }

    /**
     * Saves the user's consent into the database.
     *
     * @param int $identityId the identity of the user giving consent.
     * @param int $privacyStatementId the id of the privacy statement.
     * @return the ID of the newly inserted consent.
     */
    public static function saveLog(int $identityId, int $privacyStatementId) : int {
        $sql = 'INSERT INTO
                  PrivacyStatement_Consent_Log (
                    `identity_id`,
                    `privacystatement_id`
                  )
                VALUES
                  (?, ?)';
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, [$identityId, $privacyStatementId]);
        return \OmegaUp\MySQLConnection::getInstance()->Insert_ID();
    }

    /**
     * Gets the user's consent ID from the database.
     *
     * @param int $identityId the identity of the user giving consent.
     * @param int $privacyStatementId the id of the privacy statement.
     * @return the ID of the consent, null if missing.
     */
    public static function getId(int $identityId, int $privacyStatementId) : ?int {
        $sql = 'SELECT
                  `privacystatement_consent_id`
                FROM
                  `PrivacyStatement_Consent_Log`
                WHERE
                  identity_id = ?
                  AND privacystatement_id = ?
                ORDER BY
                  privacystatement_id DESC
                LIMIT 1';
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, [$identityId, $privacyStatementId]);
    }
}
