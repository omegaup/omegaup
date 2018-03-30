<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Problemset_Identity_Request_History.
 *
 * VO does not have any behaviour.
 * @access public
 */
class ProblemsetIdentityRequestHistory extends VO {
    /**
     * Constructor de ProblemsetIdentityRequestHistory
     *
     * Para construir un objeto de tipo ProblemsetIdentityRequestHistory debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['history_id'])) {
            $this->history_id = $data['history_id'];
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = $data['identity_id'];
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = $data['problemset_id'];
        }
        if (isset($data['time'])) {
            $this->time = $data['time'];
        }
        if (isset($data['accepted'])) {
            $this->accepted = $data['accepted'];
        }
        if (isset($data['admin_id'])) {
            $this->admin_id = $data['admin_id'];
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
    public $history_id;

    /**
      * Identidad del usuario
      * @access public
      * @var int(11)
      */
    public $identity_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $problemset_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var timestamp
      */
    public $time;

    /**
      *  [Campo no documentado]
      * @access public
      * @var tinyint(4)
      */
    public $accepted;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $admin_id;
}
