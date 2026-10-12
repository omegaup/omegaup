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
 * Value Object class for table `Contest_Problem_Change_Log`.
 *
 * @access public
 */
class ContestProblemChangeLog extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'change_id' => true,
        'contest_id' => true,
        'problem_id' => true,
        'user_id' => true,
        'change_type' => true,
        'timestamp' => true,
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
        if (isset($data['change_id'])) {
            $this->change_id = intval(
                $data['change_id']
            );
        }
        if (isset($data['contest_id'])) {
            $this->contest_id = intval(
                $data['contest_id']
            );
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = intval(
                $data['problem_id']
            );
        }
        if (isset($data['user_id'])) {
            $this->user_id = intval(
                $data['user_id']
            );
        }
        if (isset($data['change_type'])) {
            $this->change_type = is_scalar(
                $data['change_type']
            ) ? strval($data['change_type']) : '';
        }
        if (isset($data['timestamp'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['timestamp']
             * @var \OmegaUp\Timestamp $this->timestamp
             */
            $this->timestamp = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['timestamp']
                )
            );
        } else {
            $this->timestamp = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
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
    public $change_id = 0;

    /**
     * Concurso donde ocurrió el cambio de problema
     *
     * @var int|null
     */
    public $contest_id = null;

    /**
     * Problema que fue cambiado
     *
     * @var int|null
     */
    public $problem_id = null;

    /**
     * Usuario que realizó el cambio (auditoría)
     *
     * @var int|null
     */
    public $user_id = null;

    /**
     * Tipo de cambio
     *
     * @var string|null
     */
    public $change_type = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $timestamp;  // CURRENT_TIMESTAMP
}
