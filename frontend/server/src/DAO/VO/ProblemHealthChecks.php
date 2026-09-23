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
 * Value Object class for table `Problem_Health_Checks`.
 *
 * @access public
 */
class ProblemHealthChecks extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'check_id' => true,
        'problem_id' => true,
        'check_type' => true,
        'severity' => true,
        'detail' => true,
        'first_detected_at' => true,
        'last_seen_at' => true,
        'resolved_at' => true,
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
        if (isset($data['check_id'])) {
            $this->check_id = intval(
                $data['check_id']
            );
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = intval(
                $data['problem_id']
            );
        }
        if (isset($data['check_type'])) {
            $this->check_type = is_scalar(
                $data['check_type']
            ) ? strval($data['check_type']) : '';
        }
        if (isset($data['severity'])) {
            $this->severity = is_scalar(
                $data['severity']
            ) ? strval($data['severity']) : '';
        }
        if (isset($data['detail'])) {
            $this->detail = is_scalar(
                $data['detail']
            ) ? strval($data['detail']) : '';
        }
        if (isset($data['first_detected_at'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['first_detected_at']
             * @var \OmegaUp\Timestamp $this->first_detected_at
             */
            $this->first_detected_at = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['first_detected_at']
                )
            );
        } else {
            $this->first_detected_at = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
        if (isset($data['last_seen_at'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['last_seen_at']
             * @var \OmegaUp\Timestamp $this->last_seen_at
             */
            $this->last_seen_at = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['last_seen_at']
                )
            );
        }
        if (isset($data['resolved_at'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['resolved_at']
             * @var \OmegaUp\Timestamp $this->resolved_at
             */
            $this->resolved_at = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['resolved_at']
                )
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
    public $check_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $problem_id = null;

    /**
     * El tipo de revisión que detectó el problema
     *
     * @var string|null
     */
    public $check_type = null;

    /**
     * [Campo no documentado]
     *
     * @var string
     */
    public $severity = 'warning';

    /**
     * Explicación legible de lo que se detectó
     *
     * @var string|null
     */
    public $detail = null;

    /**
     * La primera vez que se detectó, se conserva entre ejecuciones
     *
     * @var \OmegaUp\Timestamp
     */
    public $first_detected_at;  // CURRENT_TIMESTAMP

    /**
     * La última ejecución en la que se seguía detectando
     *
     * @var \OmegaUp\Timestamp|null
     */
    public $last_seen_at = null;

    /**
     * Cuando dejó de detectarse, NULL si sigue vigente
     *
     * @var \OmegaUp\Timestamp|null
     */
    public $resolved_at = null;
}
