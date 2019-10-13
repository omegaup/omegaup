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
 * Value Object class for table `Auth_Tokens`.
 *
 * @access public
 */
class AuthTokens extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'user_id' => true,
        'identity_id' => true,
        'token' => true,
        'create_time' => true,
    ];

    public function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['user_id'])) {
            $this->user_id = intval($data['user_id']);
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = intval($data['identity_id']);
        }
        if (isset($data['token'])) {
            $this->token = strval($data['token']);
        }
        if (isset($data['create_time'])) {
            /**
             * @var string|int|float $data['create_time']
             * @var int $this->create_time
             */
            $this->create_time = \OmegaUp\DAO\DAO::fromMySQLTimestamp($data['create_time']);
        } else {
            $this->create_time = \OmegaUp\Time::get();
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
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var string|null
     */
    public $token = null;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $create_time;  // CURRENT_TIMESTAMP
}
