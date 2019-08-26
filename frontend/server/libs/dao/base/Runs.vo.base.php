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
class Runs extends \OmegaUp\DAO\VO\VO {
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
            $this->version = strval($data['version']);
        }
        if (isset($data['status'])) {
            $this->status = strval($data['status']);
        }
        if (isset($data['verdict'])) {
            $this->verdict = strval($data['verdict']);
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
            /**
             * @var string|int|float $data['time']
             * @var int $this->time
             */
            $this->time = \OmegaUp\DAO\DAO::fromMySQLTimestamp($data['time']);
        } else {
            $this->time = \OmegaUp\Time::get();
        }
        if (isset($data['judged_by'])) {
            $this->judged_by = strval($data['judged_by']);
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $run_id = 0;

    /**
     * El envío
     *
     * @var int|null
     */
    public $submission_id = null;

    /**
     * El hash SHA1 del árbol de la rama private.
     *
     * @var string|null
     */
    public $version = null;

    /**
     * [Campo no documentado]
     *
     * @var string
     */
    public $status = 'new';

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $verdict = null;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $runtime = 0;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $penalty = 0;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $memory = 0;

    /**
     * [Campo no documentado]
     *
     * @var float
     */
    public $score = 0.00;

    /**
     * [Campo no documentado]
     *
     * @var float|null
     */
    public $contest_score = null;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $time;  // CURRENT_TIMESTAMP

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $judged_by = null;
}
