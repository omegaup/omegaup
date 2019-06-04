<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Notifications.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Notifications extends VO {
    /**
     * Constructor de Notifications
     *
     * Para construir un objeto de tipo Notifications debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['notification_id'])) {
            $this->notification_id = (int)$data['notification_id'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = (int)$data['user_id'];
        }
        if (isset($data['timestamp'])) {
            $this->timestamp = $data['timestamp'];
        }
        if (isset($data['read'])) {
            $this->read = $data['read'] == '1';
        }
        if (isset($data['translation_string'])) {
            $this->translation_string = $data['translation_string'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (empty($fields)) {
            parent::toUnixTime(['timestamp']);
            return;
        }
        parent::toUnixTime($fields);
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int(11)
      */
    public $notification_id;

    /**
      * Identificador de usuario
      * @access public
      * @var int(11)
      */
    public $user_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var timestamp
      */
    public $timestamp;

    /**
      *  [Campo no documentado]
      * @access public
      * @var tinyint(1)
      */
    public $read;

    /**
      * JSON con el contenido de la notificacion
      * @access public
      * @var text
      */
    public $translation_string;
}
