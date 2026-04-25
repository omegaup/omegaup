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
 * Value Object class for table `Auth_Tokens`.
 *
 * @access public
 */
class AuthTokens extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'user_id' => true,
        'identity_id' => true,
        'acting_identity_id' => true,
        'token' => true,
        'create_time' => true,
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
        if (isset($data['user_id'])) {
            $this->user_id = intval(
                $data['user_id']
            );
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = intval(
                $data['identity_id']
            );
        }
        if (isset($data['acting_identity_id'])) {
            $this->acting_identity_id = intval(
                $data['acting_identity_id']
            );
        }
        if (isset($data['token'])) {
            $this->token = is_scalar(
                $data['token']
            ) ? strval($data['token']) : '';
        }
        if (isset($data['create_time'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['create_time']
             * @var \OmegaUp\Timestamp $this->create_time
             */
            $this->create_time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['create_time']
                )
            );
        } else {
            $this->create_time = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
    }

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $user_id = null;

    /**
     * Identidad del usuario
     *
     * @var int|null
     */
    public $identity_id = null;

    /**
     * Identidad del usuario que indica que no está actuando como identidad principal
     *
     * @var int|null
     */
    public $acting_identity_id = null;

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var string|null
     */
    public $token = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $create_time;  // CURRENT_TIMESTAMP
}
