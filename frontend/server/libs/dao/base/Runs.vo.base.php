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
        if (isset($data['user_id'])) {
            $this->user_id = $data['user_id'];
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = $data['problem_id'];
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = $data['problemset_id'];
        }
        if (isset($data['guid'])) {
            $this->guid = $data['guid'];
        }
        if (isset($data['language'])) {
            $this->language = $data['language'];
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
        if (isset($data['submit_delay'])) {
            $this->submit_delay = $data['submit_delay'];
        }
        if (isset($data['test'])) {
            $this->test = $data['test'];
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
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $user_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $problem_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $problemset_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var char(32)
      */
    public $guid;

    /**
      *  [Campo no documentado]
      * @access public
      * @var enum('c','cpp','java','py','rb','pl','cs','pas','kp','kj','cat','hs','cpp11')
      */
    public $language;

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
      * @var int(11)
      */
    public $submit_delay;

    /**
      *  [Campo no documentado]
      * @access public
      * @var tinyint(1)
      */
    public $test;

    /**
      *  [Campo no documentado]
      * @access public
      * @var char(32)
      */
    public $judged_by;
}
