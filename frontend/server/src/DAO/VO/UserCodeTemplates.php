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
 * Value Object class for table `User_Code_Templates`.
 *
 * @access public
 */
class UserCodeTemplates extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'template_id' => true,
        'user_id' => true,
        'language' => true,
        'template_name' => true,
        'code' => true,
        'created_at' => true,
        'updated_at' => true,
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
        if (isset($data['template_id'])) {
            $this->template_id = intval(
                $data['template_id']
            );
        }
        if (isset($data['user_id'])) {
            $this->user_id = intval(
                $data['user_id']
            );
        }
        if (isset($data['language'])) {
            $this->language = is_scalar(
                $data['language']
            ) ? strval($data['language']) : '';
        }
        if (isset($data['template_name'])) {
            $this->template_name = is_scalar(
                $data['template_name']
            ) ? strval($data['template_name']) : '';
        }
        if (isset($data['code'])) {
            $this->code = is_scalar(
                $data['code']
            ) ? strval($data['code']) : '';
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
        if (isset($data['updated_at'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['updated_at']
             * @var \OmegaUp\Timestamp $this->updated_at
             */
            $this->updated_at = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['updated_at']
                )
            );
        } else {
            $this->updated_at = new \OmegaUp\Timestamp(
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
    public $template_id = 0;

    /**
     * Identificador del usuario
     *
     * @var int|null
     */
    public $user_id = null;

    /**
     * Lenguaje de programación del template
     *
     * @var string|null
     */
    public $language = null;

    /**
     * Nombre del template definido por el usuario
     *
     * @var string|null
     */
    public $template_name = null;

    /**
     * Código del template
     *
     * @var string|null
     */
    public $code = null;

    /**
     * Fecha de creación del template
     *
     * @var \OmegaUp\Timestamp
     */
    public $created_at;  // CURRENT_TIMESTAMP

    /**
     * Fecha de última actualización
     *
     * @var \OmegaUp\Timestamp
     */
    public $updated_at;  // CURRENT_TIMESTAMP
}
