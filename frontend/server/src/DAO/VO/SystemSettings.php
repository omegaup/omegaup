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
 * Value Object class for table `System_Settings`.
 *
 * @access public
 */
class SystemSettings extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'setting_id' => true,
        'setting_key' => true,
        'setting_value' => true,
        'setting_description' => true,
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
        if (isset($data['setting_id'])) {
            $this->setting_id = intval(
                $data['setting_id']
            );
        }
        if (isset($data['setting_key'])) {
            $this->setting_key = is_scalar(
                $data['setting_key']
            ) ? strval($data['setting_key']) : '';
        }
        if (isset($data['setting_value'])) {
            $this->setting_value = is_scalar(
                $data['setting_value']
            ) ? strval($data['setting_value']) : '';
        }
        if (isset($data['setting_description'])) {
            $this->setting_description = is_scalar(
                $data['setting_description']
            ) ? strval($data['setting_description']) : '';
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
    public $setting_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $setting_key = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $setting_value = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $setting_description = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $created_at;  // CURRENT_TIMESTAMP

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $updated_at;  // CURRENT_TIMESTAMP
}
