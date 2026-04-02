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
 * Value Object class for table `Emails`.
 *
 * @access public
 */
class Emails extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'email_id' => true,
        'email' => true,
        'user_id' => true,
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
        if (isset($data['email_id'])) {
            $this->email_id = intval(
                $data['email_id']
            );
        }
        if (isset($data['email'])) {
            $this->email = is_scalar(
                $data['email']
            ) ? strval($data['email']) : '';
        }
        if (isset($data['user_id'])) {
            $this->user_id = intval(
                $data['user_id']
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
    public $email_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $email = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $user_id = null;
}
