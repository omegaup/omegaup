<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table PrivacyStatements.
 *
 * VO does not have any behaviour.
 * @access public
 */
class PrivacyStatements extends VO {
    /**
     * Constructor de PrivacyStatements
     *
     * Para construir un objeto de tipo PrivacyStatements debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['privacystatement_id'])) {
            $this->privacystatement_id = $data['privacystatement_id'];
        }
        if (isset($data['git_object_id'])) {
            $this->git_object_id = $data['git_object_id'];
        }
        if (isset($data['type'])) {
            $this->type = $data['type'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (count($fields) > 0) {
            parent::toUnixTime($fields);
        } else {
            parent::toUnixTime([]);
        }
    }

    /**
      * Id del documento de privacidad
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int(11)
      */
    public $privacystatement_id;

    /**
      * Id de la versión del documento en el que se almacena la nueva política
      * @access public
      * @var varchar(50)
      */
    public $git_object_id;

    /**
      * Tipo de documento de privacidad
      * @access public
      * @var enum('privacy_policy')
      */
    public $type;
}
