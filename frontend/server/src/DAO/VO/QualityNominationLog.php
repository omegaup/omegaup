<?php
/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

namespace OmegaUp\DAO\VO;

/**
 * Value Object class for table `QualityNomination_Log`.
 *
 * @access public
 */
class QualityNominationLog extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'qualitynomination_log_id' => true,
        'qualitynomination_id' => true,
        'time' => true,
        'user_id' => true,
        'from_status' => true,
        'to_status' => true,
        'rationale' => true,
    ];

    function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['qualitynomination_log_id'])) {
            $this->qualitynomination_log_id = (int)$data['qualitynomination_log_id'];
        }
        if (isset($data['qualitynomination_id'])) {
            $this->qualitynomination_id = (int)$data['qualitynomination_id'];
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
        if (isset($data['user_id'])) {
            $this->user_id = (int)$data['user_id'];
        }
        if (isset($data['from_status'])) {
            $this->from_status = strval($data['from_status']);
        }
        if (isset($data['to_status'])) {
            $this->to_status = strval($data['to_status']);
        }
        if (isset($data['rationale'])) {
            $this->rationale = strval($data['rationale']);
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
     * @var int
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
