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
 * Value Object class for table `Permissions`.
 *
 * @access public
 */
class Permissions extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'permission_id' => true,
        'name' => true,
        'resource_type' => true,
        'description' => true,
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
        if (isset($data['permission_id'])) {
            $this->permission_id = intval(
                $data['permission_id']
            );
        }
        if (isset($data['name'])) {
            $this->name = is_scalar(
                $data['name']
            ) ? strval($data['name']) : '';
        }
        if (isset($data['resource_type'])) {
            $this->resource_type = is_scalar(
                $data['resource_type']
            ) ? strval($data['resource_type']) : '';
        }
        if (isset($data['description'])) {
            $this->description = is_scalar(
                $data['description']
            ) ? strval($data['description']) : '';
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $permission_id = 0;

    /**
     * El nombre corto del permiso.
     *
     * @var string|null
     */
    public $name = null;

    /**
     * Tipo de recurso que protege el permiso: página, API o capacidad.
     *
     * @var string|null
     */
    public $resource_type = null;

    /**
     * La descripción humana del permiso.
     *
     * @var string|null
     */
    public $description = null;
}
