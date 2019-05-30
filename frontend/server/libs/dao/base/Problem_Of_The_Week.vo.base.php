<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Problem_Of_The_Week.
 *
 * VO does not have any behaviour.
 * @access public
 */
class ProblemOfTheWeek extends VO {
    /**
     * Constructor de ProblemOfTheWeek
     *
     * Para construir un objeto de tipo ProblemOfTheWeek debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['problem_of_the_week_id'])) {
            $this->problem_of_the_week_id = (int)$data['problem_of_the_week_id'];
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = (int)$data['problem_id'];
        }
        if (isset($data['time'])) {
            $this->time = $data['time'];
        }
        if (isset($data['difficulty'])) {
            $this->difficulty = $data['difficulty'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (empty($fields)) {
            parent::toUnixTime([]);
            return;
        }
        parent::toUnixTime($fields);
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int(11)
      */
    public $problem_of_the_week_id;

    /**
      * El id del problema escogido como problema de la semana.
      * @access public
      * @var int(11)
      */
    public $problem_id;

    /**
      * El inicio de la semana de la cual este problema fue elegido como el mejor de la semana.
      * @access public
      * @var date
      */
    public $time;

    /**
      * En algún momento tendremos un problema fácil y uno difícil.
      * @access public
      * @var enum('easy','hard')
      */
    public $difficulty;
}
