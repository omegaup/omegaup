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
            $this->problemset_id = (int)$data['problemset_id'];
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = (int)$data['identity_id'];
        }
        if (isset($data['ip'])) {
            $this->ip = (int)$data['ip'];
        }
        if (isset($data['time'])) {
            $this->time = $data['time'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (empty($fields)) {
            parent::toUnixTime(['time']);
            return;
        }
        parent::toUnixTime($fields);
    }

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $problemset_id;

    /**
      * Identidad del usuario
      * @access public
      * @var int(11)
      */
    public $identity_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(10)
      */
    public $ip;

    /**
      *  [Campo no documentado]
      * @access public
      * @var timestamp
      */
    public $time;
}
