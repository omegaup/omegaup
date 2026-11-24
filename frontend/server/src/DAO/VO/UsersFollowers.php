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
 * Value Object class for table `Users_Followers`.
 *
 * @access public
 */
class UsersFollowers extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'users_follower_id' => true,
        'follower_user_id' => true,
        'followed_user_id' => true,
        'created_at' => true,
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
        if (isset($data['users_follower_id'])) {
            $this->users_follower_id = intval(
                $data['users_follower_id']
            );
        }
        if (isset($data['follower_user_id'])) {
            $this->follower_user_id = intval(
                $data['follower_user_id']
            );
        }
        if (isset($data['followed_user_id'])) {
            $this->followed_user_id = intval(
                $data['followed_user_id']
            );
        }
        if (isset($data['created_at'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['created_at']
             * @var \OmegaUp\Timestamp $this->created_at
             */
            $this->created_at = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['created_at']
                )
            );
        } else {
            $this->created_at = new \OmegaUp\Timestamp(
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
    public $users_follower_id = 0;

    /**
     * El usuario que sigue a alguien más
     *
     * @var int|null
     */
    public $follower_user_id = null;

    /**
     * El usuario que es seguido
     *
     * @var int|null
     */
    public $followed_user_id = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $created_at;  // CURRENT_TIMESTAMP
}
