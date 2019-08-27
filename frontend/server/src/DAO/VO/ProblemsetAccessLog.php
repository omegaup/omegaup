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
 * Value Object class for table `Problemset_Access_Log`.
 *
 * @access public
 */
class ProblemsetAccessLog extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'problemset_id' => true,
        'identity_id' => true,
        'ip' => true,
        'time' => true,
    ];

    function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = (int)$data['problemset_id'];
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = (int)$data['identity_id'];
        }
        if (isset($data['ip'])) {
            $this->ip = (int)$data['ip'];
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
     *
     * @var int|null
     */
    public $problemset_id = null;

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
    public $ip = null;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $time;  // CURRENT_TIMESTAMP
}
