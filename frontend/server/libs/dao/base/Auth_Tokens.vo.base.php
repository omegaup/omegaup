<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Auth_Tokens.
 *
 * VO does not have any behaviour.
 * @access public
 */
class AuthTokens extends VO {
    const FIELD_NAMES = [
        'user_id' => true,
        'identity_id' => true,
        'token' => true,
        'create_time' => true,
    ];

    /**
     * Constructor de AuthTokens
     *
     * Para construir un objeto de tipo AuthTokens debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['user_id'])) {
            $this->user_id = (int)$data['user_id'];
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = (int)$data['identity_id'];
        }
        if (isset($data['token'])) {
            $this->token = strval($data['token']);
        }
        if (isset($data['create_time'])) {
            /**
             * @var string|int|float $data['create_time']
             * @var int $this->create_time
             */
            $this->create_time = DAO::fromMySQLTimestamp($data['create_time']);
        } else {
            $this->create_time = Time::get();
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
