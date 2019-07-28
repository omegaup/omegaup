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
    /**
     * Constructor de Messages
     *
     * Para construir un objeto de tipo Messages debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct(?array $data = null) {
        if (is_null($data)) {
            return;
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
            $this->message = $data['message'];
        }
        if (isset($data['date'])) {
            $this->date = $data['date'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(iterable $fields = []) : void {
        if (empty($fields)) {
            parent::toUnixTime(['date']);
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
    public $message_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var bool
     */
    public $read = false;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $sender_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $recipient_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $message;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $date = null;
}
