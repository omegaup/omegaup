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
 * Value Object class for table `Contest_Log`.
 *
 * @access public
 */
class ContestLog extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'public_contest_id' => true,
        'contest_id' => true,
        'user_id' => true,
        'from_admission_mode' => true,
        'to_admission_mode' => true,
        'time' => true,
    ];

    public function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['public_contest_id'])) {
            $this->public_contest_id = intval($data['public_contest_id']);
        }
        if (isset($data['contest_id'])) {
            $this->contest_id = intval($data['contest_id']);
        }
        if (isset($data['user_id'])) {
            $this->user_id = intval($data['user_id']);
        }
        if (isset($data['from_admission_mode'])) {
            $this->from_admission_mode = strval($data['from_admission_mode']);
        }
        if (isset($data['to_admission_mode'])) {
            $this->to_admission_mode = strval($data['to_admission_mode']);
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
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $public_contest_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $contest_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $user_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $from_admission_mode = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $to_admission_mode = null;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $time;  // CURRENT_TIMESTAMP
}
