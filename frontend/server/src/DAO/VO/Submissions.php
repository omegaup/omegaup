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
 * Value Object class for table `Submissions`.
 *
 * @access public
 */
class Submissions extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'submission_id' => true,
        'current_run_id' => true,
        'identity_id' => true,
        'problem_id' => true,
        'problemset_id' => true,
        'guid' => true,
        'language' => true,
        'time' => true,
        'status' => true,
        'verdict' => true,
        'submit_delay' => true,
        'type' => true,
        'school_id' => true,
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
        if (isset($data['submission_id'])) {
            $this->submission_id = intval(
                $data['submission_id']
            );
        }
        if (isset($data['current_run_id'])) {
            $this->current_run_id = intval(
                $data['current_run_id']
            );
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = intval(
                $data['identity_id']
            );
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = intval(
                $data['problem_id']
            );
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = intval(
                $data['problemset_id']
            );
        }
        if (isset($data['guid'])) {
            $this->guid = is_scalar(
                $data['guid']
            ) ? strval($data['guid']) : '';
        }
        if (isset($data['language'])) {
            $this->language = is_scalar(
                $data['language']
            ) ? strval($data['language']) : '';
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
        if (isset($data['submit_delay'])) {
            $this->submit_delay = intval(
                $data['submit_delay']
            );
        }
        if (isset($data['type'])) {
            $this->type = is_scalar(
                $data['type']
            ) ? strval($data['type']) : '';
        }
        if (isset($data['school_id'])) {
            $this->school_id = intval(
                $data['school_id']
            );
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $submission_id = 0;

    /**
     * La evaluación actual del envío
     *
     * @var int|null
     */
    public $current_run_id = null;

    /**
     * Identidad del usuario
     *
     * @var int|null
     */
    public $identity_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $problem_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $problemset_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $guid = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $language = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $time;  // CURRENT_TIMESTAMP

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
    public $submit_delay = 0;

    /**
     * [Campo no documentado]
     *
     * @var string
     */
    public $type = 'normal';

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $school_id = null;
}
