<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table PrivacyStatement_Consent_Log.
 *
 * VO does not have any behaviour.
 * @access public
 */
class PrivacyStatementConsentLog extends VO {
    const FIELD_NAMES = [
        'privacystatement_consent_id' => true,
        'identity_id' => true,
        'privacystatement_id' => true,
        'timestamp' => true,
    ];

    /**
     * Constructor de PrivacyStatementConsentLog
     *
     * Para construir un objeto de tipo PrivacyStatementConsentLog debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['privacystatement_consent_id'])) {
            $this->privacystatement_consent_id = (int)$data['privacystatement_consent_id'];
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = (int)$data['identity_id'];
        }
        if (isset($data['privacystatement_id'])) {
            $this->privacystatement_id = (int)$data['privacystatement_id'];
        }
        if (isset($data['timestamp'])) {
            $this->timestamp = $data['timestamp'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(iterable $fields = []) : void {
        if (empty($fields)) {
            parent::toUnixTime(['timestamp']);
            return;
        }
        parent::toUnixTime($fields);
    }

    /**
      * Id del consentimiento de privacidad almacenado en el log
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int
     */
    public $privacystatement_consent_id;

    /**
      * Identidad del usuario
      * @access public
      * @var int
     */
    public $identity_id;

    /**
      * Id del documento de privacidad
      * @access public
      * @var int
     */
    public $privacystatement_id;

    /**
      * Fecha y hora en la que el usuario acepta las nuevas políticas
      * @access public
      * @var string
     */
    public $timestamp = null;
}
