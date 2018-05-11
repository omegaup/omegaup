<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Audit_Log.
 *
 * VO does not have any behaviour.
 * @access public
 */
class AuditLog extends VO {
    /**
     * Constructor de AuditLog
     *
     * Para construir un objeto de tipo AuditLog debera llamarse a el constructor
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
        if (isset($data['git_object_id'])) {
            $this->git_object_id = $data['git_object_id'];
        }
        if (isset($data['date'])) {
            $this->date = $data['date'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (count($fields) > 0) {
            parent::toUnixTime($fields);
        } else {
            parent::toUnixTime(['date']);
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
      * Id de la versión del documento en el que se almacena la nueva política
      * Llave Primaria
      * @access public
      * @var varchar(50)
      */
    public $git_object_id;

    /**
      * Fecha y hora en la que el usuario acepta las nuevas políticas
      * @access public
      * @var timestamp
      */
    public $date;
}
