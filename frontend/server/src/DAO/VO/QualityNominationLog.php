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
 * Value Object class for table `QualityNomination_Log`.
 *
 * @access public
 */
class QualityNominationLog extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'qualitynomination_log_id' => true,
        'qualitynomination_id' => true,
        'time' => true,
        'user_id' => true,
        'from_status' => true,
        'to_status' => true,
        'rationale' => true,
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
        if (isset($data['qualitynomination_log_id'])) {
            $this->qualitynomination_log_id = intval(
                $data['qualitynomination_log_id']
            );
        }
        if (isset($data['qualitynomination_id'])) {
            $this->qualitynomination_id = intval(
                $data['qualitynomination_id']
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
        if (isset($data['user_id'])) {
            $this->user_id = intval(
                $data['user_id']
            );
        }
        if (isset($data['from_status'])) {
            $this->from_status = is_scalar(
                $data['from_status']
            ) ? strval($data['from_status']) : '';
        }
        if (isset($data['to_status'])) {
            $this->to_status = is_scalar(
                $data['to_status']
            ) ? strval($data['to_status']) : '';
        }
        if (isset($data['rationale'])) {
            $this->rationale = is_scalar(
                $data['rationale']
            ) ? strval($data['rationale']) : '';
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $qualitynomination_log_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $qualitynomination_id = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $time;  // CURRENT_TIMESTAMP

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $user_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string
     */
    public $from_status = 'open';

    /**
     * [Campo no documentado]
     *
     * @var string
     */
    public $to_status = 'open';

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $rationale = null;
}
