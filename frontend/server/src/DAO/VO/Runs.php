<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\VO;

/**
 * Value Object class for table `Runs`.
 *
 * @access public
 */
class Runs extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'run_id' => true,
        'submission_id' => true,
        'version' => true,
        'commit' => true,
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

    public function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception(
                'Unknown columns: ' . join(', ', array_keys($unknownColumns))
            );
        }
        if (isset($data['run_id'])) {
            $this->run_id = intval(
                $data['run_id']
            );
        }
        if (isset($data['submission_id'])) {
            $this->submission_id = intval(
                $data['submission_id']
            );
        }
        if (isset($data['version'])) {
            $this->version = is_scalar(
                $data['version']
            ) ? strval($data['version']) : '';
        }
        if (isset($data['commit'])) {
            $this->commit = is_scalar(
                $data['commit']
            ) ? strval($data['commit']) : '';
        }
        if (isset($data['status'])) {
            $this->status = is_scalar(
                $data['status']
            ) ? strval($data['status']) : '';
        }
        if (isset($data['verdict'])) {
            $this->verdict = is_scalar(
                $data['verdict']
            ) ? strval($data['verdict']) : '';
        }
        if (isset($data['runtime'])) {
            $this->runtime = intval(
                $data['runtime']
            );
        }
        if (isset($data['penalty'])) {
            $this->penalty = intval(
                $data['penalty']
            );
        }
        if (isset($data['memory'])) {
            $this->memory = intval(
                $data['memory']
            );
        }
        if (isset($data['score'])) {
            $this->score = floatval(
                $data['score']
            );
        }
        if (isset($data['contest_score'])) {
            $this->contest_score = floatval(
                $data['contest_score']
            );
        }
        if (isset($data['time'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['time']
             * @var \OmegaUp\Timestamp $this->time
             */
            $this->time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['time']
                )
            );
        } else {
            $this->time = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
        if (isset($data['judged_by'])) {
            $this->judged_by = is_scalar(
                $data['judged_by']
            ) ? strval($data['judged_by']) : '';
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
     * El hash SHA1 del commit en la rama master del problema con el que se realizó el envío.
     *
     * @var string|null
     */
    public $commit = null;

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
     * @var \OmegaUp\Timestamp
     */
    public $time;  // CURRENT_TIMESTAMP

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $judged_by = null;
}
