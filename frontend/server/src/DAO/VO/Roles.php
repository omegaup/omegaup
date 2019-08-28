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
 * Value Object class for table `Roles`.
 *
 * @access public
 */
class Roles extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'role_id' => true,
        'name' => true,
        'description' => true,
    ];

    function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['role_id'])) {
            $this->role_id = (int)$data['role_id'];
        }
        if (isset($data['name'])) {
            $this->name = strval($data['name']);
        }
        if (isset($data['description'])) {
            $this->description = strval($data['description']);
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $role_id = 0;

    /**
     * El nombre corto del rol.
     *
     * @var string|null
     */
    public $name = null;

    /**
     * La descripción humana del rol.
     *
     * @var string|null
     */
    public $description = null;
}
