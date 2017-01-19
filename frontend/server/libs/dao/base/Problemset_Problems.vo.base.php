<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Problemset_Problems.
 *
 * VO does not have any behaviour.
 * @access public
 */
class ProblemsetProblems extends VO {
    /**
     * Constructor de ProblemsetProblems
     *
     * Para construir un objeto de tipo ProblemsetProblems debera llamarse a el constructor
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
        if (isset($data['problem_id'])) {
            $this->problem_id = $data['problem_id'];
        }
        if (isset($data['points'])) {
            $this->points = $data['points'];
        }
        if (isset($data['order'])) {
            $this->order = $data['order'];
        }
    }

    /**
     * Obtener una representacion en String
     *
     * Este metodo permite tratar a un objeto ProblemsetProblems en forma de cadena.
     * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
     * @return String
     */
    public function __toString() {
        return json_encode([
            'problemset_id' => $this->problemset_id,
            'problem_id' => $this->problem_id,
            'points' => $this->points,
            'order' => $this->order,
        ]);
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
    public $problemset_id;

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var int(11)
      */
    public $problem_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var double
      */
    public $points;

    /**
      * Define el orden de aparición de los problemas en un concurso
      * @access public
      * @var int
      */
    public $order;
}
