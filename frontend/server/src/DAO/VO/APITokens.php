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
 * Value Object class for table `API_Tokens`.
 *
 * @access public
 */
class APITokens extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'apitoken_id' => true,
        'user_id' => true,
        'timestamp' => true,
        'name' => true,
        'token' => true,
        'last_used' => true,
        'use_count' => true,
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
        if (isset($data['apitoken_id'])) {
            $this->apitoken_id = intval(
                $data['apitoken_id']
            );
        }
        if (isset($data['user_id'])) {
            $this->user_id = intval(
                $data['user_id']
            );
        }
        if (isset($data['timestamp'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['timestamp']
             * @var \OmegaUp\Timestamp $this->timestamp
             */
            $this->timestamp = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['timestamp']
                )
            );
        } else {
            $this->timestamp = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
        if (isset($data['name'])) {
            $this->name = is_scalar(
                $data['name']
            ) ? strval($data['name']) : '';
        }
        if (isset($data['token'])) {
            $this->token = is_scalar(
                $data['token']
            ) ? strval($data['token']) : '';
        }
        if (isset($data['last_used'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['last_used']
             * @var \OmegaUp\Timestamp $this->last_used
             */
            $this->last_used = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['last_used']
                )
            );
        } else {
            $this->last_used = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
        if (isset($data['use_count'])) {
            $this->use_count = intval(
                $data['use_count']
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
    public $apitoken_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $user_id = null;

    /**
     * Momento de creación del token
     *
     * @var \OmegaUp\Timestamp
     */
    public $timestamp;  // CURRENT_TIMESTAMP

    /**
     * Nombre que el usuario le asigna al token
     *
     * @var string|null
     */
    public $name = null;

    /**
     * Contenido del token
     *
     * @var string|null
     */
    public $token = null;

    /**
     * Momento de último uso del token, redondeado a la última hora
     *
     * @var \OmegaUp\Timestamp
     */
    public $last_used;  // CURRENT_TIMESTAMP

    /**
     * Número de usos desde la última hora
     *
     * @var int
     */
    public $use_count = 0;
}
