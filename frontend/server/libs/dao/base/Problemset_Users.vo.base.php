<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Problemset_Users.
 *
 * VO does not have any behaviour.
 * @access public
 */
class ProblemsetUsers extends VO {
    /**
     * Constructor de ProblemsetUsers
     *
     * Para construir un objeto de tipo ProblemsetUsers debera llamarse a el constructor
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
        if (isset($data['problemset_id'])) {
            $this->problemset_id = $data['problemset_id'];
        }
        if (isset($data['access_time'])) {
            $this->access_time = $data['access_time'];
        }
        if (isset($data['score'])) {
            $this->score = $data['score'];
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
            parent::toUnixTime([]);
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
    public $problemset_id;

    /**
      * Hora a la que entró el usuario al concurso
      * @access public
      * @var datetime
      */
    public $access_time;

    /**
      * Indica el puntaje que obtuvo el usuario en el concurso
      * @access public
      * @var int(11)
      */
    public $score;

    /**
      * Indica el tiempo que acumulo en usuario en el concurso
      * @access public
      * @var int(11)
      */
    public $time;
}
