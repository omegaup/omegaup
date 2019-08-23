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
 * Value Object class for table `Roles_Permissions`.
 *
 * @access public
 */
class RolesPermissions extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'role_id' => true,
        'permission_id' => true,
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
        if (isset($data['permission_id'])) {
            $this->permission_id = (int)$data['permission_id'];
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var int|null
     */
    public $role_id = null;

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var int|null
     */
    public $permission_id = null;
}
