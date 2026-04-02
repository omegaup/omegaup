<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\VO;

/**
 * Value Object class for table `PrivacyStatement_Consent_Log`.
 *
 * @access public
 */
class PrivacyStatementConsentLog extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'privacystatement_consent_id' => true,
        'identity_id' => true,
        'privacystatement_id' => true,
        'timestamp' => true,
    ];

    public function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception(
                'Unknown columns: ' . join(', ', array_keys($unknownColumns))
            );
        }
        if (isset($data['privacystatement_consent_id'])) {
            $this->privacystatement_consent_id = intval(
                $data['privacystatement_consent_id']
            );
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = intval(
                $data['identity_id']
            );
        }
        if (isset($data['privacystatement_id'])) {
            $this->privacystatement_id = intval(
                $data['privacystatement_id']
            );
        }
        if (isset($data['timestamp'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['timestamp']
             * @var \OmegaUp\Timestamp $this->timestamp
             */
            $this->timestamp = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['timestamp']
                )
            );
        } else {
            $this->timestamp = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
    }

    /**
     * Id del consentimiento de privacidad almacenado en el log
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $privacystatement_consent_id = 0;

    /**
     * Identidad del usuario
     *
     * @var int|null
     */
    public $identity_id = null;

    /**
     * Id del documento de privacidad
     *
     * @var int|null
     */
    public $privacystatement_id = null;

    /**
     * Fecha y hora en la que el usuario acepta las nuevas políticas
     *
     * @var \OmegaUp\Timestamp
     */
    public $timestamp;  // CURRENT_TIMESTAMP
}
