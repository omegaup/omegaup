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
    const FIELD_NAMES = [
        'run_id' => true,
        'submission_id' => true,
        'version' => true,
        'status' => true,
        'verdict' => true,
        'runtime' => true,
        'penalty' => true,
        'memory' => true,
        'score' => true,
        'contest_score' => true,
        'time' => true,
        'judged_by' => true,
    ];

    /**
     * Constructor de Runs
     *
     * Para construir un objeto de tipo Runs debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['run_id'])) {
            $this->run_id = (int)$data['run_id'];
        }
        if (isset($data['submission_id'])) {
            $this->submission_id = (int)$data['submission_id'];
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
            $this->runtime = (int)$data['runtime'];
        }
        if (isset($data['penalty'])) {
            $this->penalty = (int)$data['penalty'];
        }
        if (isset($data['memory'])) {
            $this->memory = (int)$data['memory'];
        }
        if (isset($data['score'])) {
            $this->score = (float)$data['score'];
        }
        if (isset($data['contest_score'])) {
            $this->contest_score = (float)$data['contest_score'];
        }
        if (isset($data['time'])) {
            $this->time = DAO::fromMySQLTimestamp($data['time']);
        }
        if (isset($data['judged_by'])) {
            $this->judged_by = $data['judged_by'];
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int
     */
    public $run_id;

    /**
      * El envío
      * @access public
      * @var int
     */
    public $submission_id;

    /**
      * El hash SHA1 del árbol de la rama private.
      * @access public
      * @var string
     */
    public $version;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $status = 'new';

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $verdict;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $runtime = 0;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $penalty = 0;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $memory = 0;

    /**
      *  [Campo no documentado]
      * @access public
      * @var float
     */
    public $score = 0.00;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?float
     */
    public $contest_score;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $time = null;  // CURRENT_TIMESTAMP

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?string
     */
    public $judged_by;
}
