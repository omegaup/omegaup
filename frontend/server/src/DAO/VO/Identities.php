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
 * Value Object class for table `Identities`.
 *
 * @access public
 */
class Identities extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'identity_id' => true,
        'username' => true,
        'password' => true,
        'name' => true,
        'user_id' => true,
        'language_id' => true,
        'country_id' => true,
        'state_id' => true,
        'gender' => true,
        'current_identity_school_id' => true,
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
        if (isset($data['identity_id'])) {
            $this->identity_id = intval(
                $data['identity_id']
            );
        }
        if (isset($data['username'])) {
            $this->username = is_scalar(
                $data['username']
            ) ? strval($data['username']) : '';
        }
        if (isset($data['password'])) {
            $this->password = is_scalar(
                $data['password']
            ) ? strval($data['password']) : '';
        }
        if (isset($data['name'])) {
            $this->name = is_scalar(
                $data['name']
            ) ? strval($data['name']) : '';
        }
        if (isset($data['user_id'])) {
            $this->user_id = intval(
                $data['user_id']
            );
        }
        if (isset($data['language_id'])) {
            $this->language_id = intval(
                $data['language_id']
            );
        }
        if (isset($data['country_id'])) {
            $this->country_id = is_scalar(
                $data['country_id']
            ) ? strval($data['country_id']) : '';
        }
        if (isset($data['state_id'])) {
            $this->state_id = is_scalar(
                $data['state_id']
            ) ? strval($data['state_id']) : '';
        }
        if (isset($data['gender'])) {
            $this->gender = is_scalar(
                $data['gender']
            ) ? strval($data['gender']) : '';
        }
        if (isset($data['current_identity_school_id'])) {
            $this->current_identity_school_id = intval(
                $data['current_identity_school_id']
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
    public $identity_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $username = null;

    /**
     * Contraseña del usuario, usando Argon2i o Blowfish
     *
     * @var string|null
     */
    public $password = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $name = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $user_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $language_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $country_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $state_id = null;

    /**
     * Género de la identidad
     *
     * @var string|null
     */
    public $gender = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $current_identity_school_id = null;
}
