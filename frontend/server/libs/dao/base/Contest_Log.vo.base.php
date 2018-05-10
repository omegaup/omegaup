<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Contest_Log.
 *
 * VO does not have any behaviour.
 * @access public
 */
class ContestLog extends VO {
    /**
     * Constructor de ContestLog
     *
     * Para construir un objeto de tipo ContestLog debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['public_contest_id'])) {
            $this->public_contest_id = $data['public_contest_id'];
        }
        if (isset($data['contest_id'])) {
            $this->contest_id = $data['contest_id'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = $data['user_id'];
        }
        if (isset($data['from_admission_mode'])) {
            $this->from_admission_mode = $data['from_admission_mode'];
        }
        if (isset($data['to_admission_mode'])) {
            $this->to_admission_mode = $data['to_admission_mode'];
        }
        if (isset($data['time'])) {
            $this->time = $data['time'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (count($fields) > 0) {
            parent::toUnixTime($fields);
        } else {
            parent::toUnixTime(['time']);
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int(11)
      */
    public $public_contest_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $contest_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $user_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(20)
      */
    public $from_admission_mode;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(20)
      */
    public $to_admission_mode;

    /**
      *  [Campo no documentado]
      * @access public
      * @var timestamp
      */
    public $time;
}
