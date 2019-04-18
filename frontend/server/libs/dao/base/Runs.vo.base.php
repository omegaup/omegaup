<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Runs.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Runs extends VO {
    /**
     * Constructor de Runs
     *
     * Para construir un objeto de tipo Runs debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['run_id'])) {
            $this->run_id = $data['run_id'];
        }
        if (isset($data['submission_id'])) {
            $this->submission_id = $data['submission_id'];
        }
        if (isset($data['version'])) {
            $this->version = $data['version'];
        }
        if (isset($data['status'])) {
            $this->status = $data['status'];
        }
        if (isset($data['verdict'])) {
            $this->verdict = $data['verdict'];
        }
        if (isset($data['runtime'])) {
            $this->runtime = $data['runtime'];
        }
        if (isset($data['penalty'])) {
            $this->penalty = $data['penalty'];
        }
        if (isset($data['memory'])) {
            $this->memory = $data['memory'];
        }
        if (isset($data['score'])) {
            $this->score = $data['score'];
        }
        if (isset($data['contest_score'])) {
            $this->contest_score = $data['contest_score'];
        }
        if (isset($data['time'])) {
            $this->time = $data['time'];
        }
        if (isset($data['judged_by'])) {
            $this->judged_by = $data['judged_by'];
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
    public $run_id;

    /**
      * El envío
      * @access public
      * @var int(11)
      */
    public $submission_id;

    /**
      * El hash SHA1 del árbol de la rama private.
      * @access public
      * @var char(40)
      */
    public $version;

    /**
      *  [Campo no documentado]
      * @access public
      * @var enum('new','waiting','compiling','running','ready')
      */
    public $status;

    /**
      *  [Campo no documentado]
      * @access public
      * @var enum('ac','pa','pe','wa','tle','ole','mle','rte','rfe','ce','je')
      */
    public $verdict;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $runtime;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $penalty;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $memory;

    /**
      *  [Campo no documentado]
      * @access public
      * @var double
      */
    public $score;

    /**
      *  [Campo no documentado]
      * @access public
      * @var double
      */
    public $contest_score;

    /**
      *  [Campo no documentado]
      * @access public
      * @var timestamp
      */
    public $time;

    /**
      *  [Campo no documentado]
      * @access public
      * @var char(32)
      */
    public $judged_by;
}
