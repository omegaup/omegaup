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
    /**
     * Constructor de PrivacyStatementConsentLog
     *
     * Para construir un objeto de tipo PrivacyStatementConsentLog debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = $data['identity_id'];
        }
        if (isset($data['privacystatement_id'])) {
            $this->privacystatement_id = $data['privacystatement_id'];
        }
        if (isset($data['acl_id'])) {
            $this->acl_id = $data['acl_id'];
        }
        if (isset($data['share_user_information'])) {
            $this->share_user_information = $data['share_user_information'];
        }
        if (isset($data['timestamp'])) {
            $this->timestamp = $data['timestamp'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (count($fields) > 0) {
            parent::toUnixTime($fields);
        } else {
            parent::toUnixTime(['timestamp']);
        }
    }

    /**
      * Identidad del usuario
      * Llave Primaria
      * @access public
      * @var int(11)
      */
    public $identity_id;

    /**
      * Id del documento de privacidad
      * Llave Primaria
      * @access public
      * @var int(11)
      */
    public $privacystatement_id;

    /**
      * Id de la lista de acceso al que pertenece el usuario que acepta/deniega el consentimiento de compartir sus datos. Se obtiene si es un curso
      * @access public
      * @var int(11)
      */
    public $acl_id;

    /**
      * Almacena la respuesta del participante de un concurso / curso si está de acuerdo en divulgar su información.
      * @access public
      * @var tinyint(1)
      */
    public $share_user_information;

    /**
      * Fecha y hora en la que el usuario acepta las nuevas políticas
      * @access public
      * @var timestamp
      */
    public $timestamp;
}
