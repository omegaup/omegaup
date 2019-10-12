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
 * Value Object class for table `Users_Badges`.
 *
 * @access public
 */
class UsersBadges extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
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
            throw new \Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['user_badge_id'])) {
            $this->user_badge_id = intval($data['user_badge_id']);
        }
        if (isset($data['user_id'])) {
            $this->user_id = intval($data['user_id']);
        }
        if (isset($data['badge_alias'])) {
            $this->badge_alias = strval($data['badge_alias']);
        }
        if (isset($data['assignation_time'])) {
            /**
             * @var string|int|float $data['assignation_time']
             * @var int $this->assignation_time
             */
            $this->assignation_time = \OmegaUp\DAO\DAO::fromMySQLTimestamp($data['assignation_time']);
        } else {
            $this->assignation_time = \OmegaUp\Time::get();
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
     * @var int
     */
    public $assignation_time;  // CURRENT_TIMESTAMP
}
