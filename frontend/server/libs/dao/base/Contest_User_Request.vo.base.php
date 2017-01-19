<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Contest_User_Request.
 *
 * VO does not have any behaviour.
 * @access public
 */
class ContestUserRequest extends VO {
    /**
     * Constructor de ContestUserRequest
     *
     * Para construir un objeto de tipo ContestUserRequest debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['user_id'])) {
            $this->user_id = $data['user_id'];
        }
        if (isset($data['contest_id'])) {
            $this->contest_id = $data['contest_id'];
        }
        if (isset($data['request_time'])) {
            $this->request_time = $data['request_time'];
        }
        if (isset($data['last_update'])) {
            $this->last_update = $data['last_update'];
        }
        if (isset($data['accepted'])) {
            $this->accepted = $data['accepted'];
        }
        if (isset($data['extra_note'])) {
            $this->extra_note = $data['extra_note'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (count($fields) > 0) {
            parent::toUnixTime($fields);
        } else {
            parent::toUnixTime(['request_time', 'last_update']);
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var int(11)
      */
    public $user_id;

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var int(11)
      */
    public $contest_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var timestamp
      */
    public $request_time;

    /**
      *  [Campo no documentado]
      * @access public
      * @var timestamp
      */
    public $last_update;

    /**
      *  [Campo no documentado]
      * @access public
      * @var tinyint(1)
      */
    public $accepted;

    /**
      *  [Campo no documentado]
      * @access public
      * @var text,
      */
    public $extra_note;
}
