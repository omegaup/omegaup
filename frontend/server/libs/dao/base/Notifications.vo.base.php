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
            /**
             * @var string|int|float $data['timestamp']
             * @var int $this->timestamp
             */
            $this->timestamp = DAO::fromMySQLTimestamp($data['timestamp']);
        } else {
            $this->timestamp = \OmegaUp\Time::get();
        }
        if (isset($data['read'])) {
            $this->read = boolval($data['read']);
        }
        if (isset($data['contents'])) {
            $this->contents = strval($data['contents']);
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $notification_id = 0;

    /**
     * Identificador de usuario
     *
     * @var int|null
     */
    public $user_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $timestamp;  // CURRENT_TIMESTAMP

    /**
     * [Campo no documentado]
     *
     * @var bool
     */
    public $read = false;

    /**
     * JSON con el contenido de la notificación
     *
     * @var string|null
     */
    public $contents = null;
}
