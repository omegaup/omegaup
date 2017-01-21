<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Problem_Viewed.
 *
 * VO does not have any behaviour.
 * @access public
 */
class ProblemViewed extends VO {
    /**
     * Constructor de ProblemViewed
     *
     * Para construir un objeto de tipo ProblemViewed debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = $data['problem_id'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = $data['user_id'];
        }
        if (isset($data['view_time'])) {
            $this->view_time = $data['view_time'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (count($fields) > 0) {
            parent::toUnixTime($fields);
        } else {
            parent::toUnixTime(['view_time']);
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var int(11)
      */
    public $problem_id;

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var int(11)
      */
    public $user_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var timestamp
      */
    public $view_time;
}
