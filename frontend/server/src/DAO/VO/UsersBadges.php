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
 * Value Object class for table `Users_Badges`.
 *
 * @access public
 */
class UsersBadges extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'user_badge_id' => true,
        'user_id' => true,
        'badge_alias' => true,
        'assignation_time' => true,
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
        if (isset($data['user_badge_id'])) {
            $this->user_badge_id = intval(
                $data['user_badge_id']
            );
        }
        if (isset($data['user_id'])) {
            $this->user_id = intval(
                $data['user_id']
            );
        }
        if (isset($data['badge_alias'])) {
            $this->badge_alias = is_scalar(
                $data['badge_alias']
            ) ? strval($data['badge_alias']) : '';
        }
        if (isset($data['assignation_time'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['assignation_time']
             * @var \OmegaUp\Timestamp $this->assignation_time
             */
            $this->assignation_time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['assignation_time']
                )
            );
        } else {
            $this->assignation_time = new \OmegaUp\Timestamp(
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
    public $user_badge_id = 0;

    /**
     * Identificador de usuario
     *
     * @var int|null
     */
    public $user_id = null;

    /**
     * Identificador de badge
     *
     * @var string|null
     */
    public $badge_alias = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $assignation_time;  // CURRENT_TIMESTAMP
}
