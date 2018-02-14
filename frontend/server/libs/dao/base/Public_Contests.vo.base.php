<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Public_Contests.
 *
 * VO does not have any behaviour.
 * @access public
 */
class PublicContests extends VO {
    /**
     * Constructor de PublicContests
     *
     * Para construir un objeto de tipo PublicContests debera llamarse a el constructor
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
      * @var timestamp
      */
    public $time;
}
