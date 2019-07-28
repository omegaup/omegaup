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
    const FIELD_NAMES = [
        'notification_id' => true,
        'user_id' => true,
        'timestamp' => true,
        'read' => true,
        'contents' => true,
    ];

    /**
     * Constructor de Notifications
     *
     * Para construir un objeto de tipo Notifications debera llamarse a el constructor
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
            $this->read = boolval($data['read']);
        }
        if (isset($data['contents'])) {
            $this->contents = $data['contents'];
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
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int
     */
    public $notification_id;

    /**
      * Identificador de usuario
      * @access public
      * @var int
     */
    public $user_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $timestamp = null;

    /**
      *  [Campo no documentado]
      * @access public
      * @var bool
     */
    public $read = false;

    /**
      * JSON con el contenido de la notificación
      * @access public
      * @var string
     */
    public $contents;
}
