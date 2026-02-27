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
 * Value Object class for table `Languages`.
 *
 * @access public
 */
class Languages extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'language_id' => true,
        'name' => true,
        'country_id' => true,
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
        if (isset($data['language_id'])) {
            $this->language_id = intval(
                $data['language_id']
            );
        }
        if (isset($data['name'])) {
            $this->name = is_scalar(
                $data['name']
            ) ? strval($data['name']) : '';
        }
        if (isset($data['country_id'])) {
            $this->country_id = is_scalar(
                $data['country_id']
            ) ? strval($data['country_id']) : '';
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $language_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $name = null;

    /**
     * Se guarda la relación con el país para defaultear más rápido.
     *
     * @var string|null
     */
    public $country_id = null;
}
