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
            $this->problemset_id = (int)$data['problemset_id'];
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = (int)$data['problem_id'];
        }
        if (isset($data['commit'])) {
            $this->commit = $data['commit'];
        }
        if (isset($data['version'])) {
            $this->version = $data['version'];
        }
        if (isset($data['points'])) {
            $this->points = (float)$data['points'];
        }
        if (isset($data['order'])) {
            $this->order = (int)$data['order'];
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
      * El hash SHA1 del commit en la rama master del problema.
      * @access public
      * @var char(40)
      */
    public $commit;

    /**
      * El hash SHA1 del árbol de la rama private.
      * @access public
      * @var char(40)
      */
    public $version;

    /**
      *  [Campo no documentado]
      * @access public
      * @var double
      */
    public $points;

    /**
      * Define el orden de aparición de los problemas en una lista de problemas
      * @access public
      * @var int(11)
      */
    public $order;
}
