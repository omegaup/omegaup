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
 * Value Object class for table `Problemset_Identity_Request_History`.
 *
 * @access public
 */
class ProblemsetIdentityRequestHistory extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'history_id' => true,
        'identity_id' => true,
        'problemset_id' => true,
        'time' => true,
        'accepted' => true,
        'admin_id' => true,
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
        if (isset($data['history_id'])) {
            $this->history_id = intval(
                $data['history_id']
            );
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = intval(
                $data['identity_id']
            );
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = intval(
                $data['problemset_id']
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
        if (isset($data['accepted'])) {
            $this->accepted = boolval(
                $data['accepted']
            );
        }
        if (isset($data['admin_id'])) {
            $this->admin_id = intval(
                $data['admin_id']
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
    public $history_id = 0;

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
    public $problemset_id = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $time;  // CURRENT_TIMESTAMP

    /**
     * [Campo no documentado]
     *
     * @var bool|null
     */
    public $accepted = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $admin_id = null;
}
