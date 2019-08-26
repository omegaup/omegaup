<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Messages.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Messages extends VO {
    const FIELD_NAMES = [
        'message_id' => true,
        'read' => true,
        'sender_id' => true,
        'recipient_id' => true,
        'message' => true,
        'date' => true,
    ];

    /**
     * Constructor de Messages
     *
     * Para construir un objeto de tipo Messages debera llamarse a el constructor
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
        if (isset($data['message_id'])) {
            $this->message_id = (int)$data['message_id'];
        }
        if (isset($data['read'])) {
            $this->read = boolval($data['read']);
        }
        if (isset($data['sender_id'])) {
            $this->sender_id = (int)$data['sender_id'];
        }
        if (isset($data['recipient_id'])) {
            $this->recipient_id = (int)$data['recipient_id'];
        }
        if (isset($data['message'])) {
            $this->message = strval($data['message']);
        }
        if (isset($data['date'])) {
            /**
             * @var string|int|float $data['date']
             * @var int $this->date
             */
            $this->date = DAO::fromMySQLTimestamp($data['date']);
        } else {
            $this->date = \OmegaUp\Time::get();
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $message_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var bool
     */
    public $read = false;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $sender_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $recipient_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $message = null;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $date;  // CURRENT_TIMESTAMP
}
