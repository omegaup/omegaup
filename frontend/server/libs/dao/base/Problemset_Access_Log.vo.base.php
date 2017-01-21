<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Problemset_Access_Log.
 *
 * VO does not have any behaviour.
 * @access public
 */
class ProblemsetAccessLog extends VO {
    /**
     * Constructor de ProblemsetAccessLog
     *
     * Para construir un objeto de tipo ProblemsetAccessLog debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = $data['problemset_id'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = $data['user_id'];
        }
        if (isset($data['ip'])) {
            $this->ip = $data['ip'];
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
      * @access public
      * @var int(11)
      */
    public $problemset_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $user_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
      */
    public $ip;

    /**
      *  [Campo no documentado]
      * @access public
      * @var timestamp
      */
    public $time;
}
