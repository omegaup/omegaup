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
 * Value Object class for table `Group_Roles`.
 *
 * @access public
 */
class GroupRoles extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'group_id' => true,
        'role_id' => true,
        'acl_id' => true,
    ];

    public function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['group_id'])) {
            $this->group_id = intval($data['group_id']);
        }
        if (isset($data['role_id'])) {
            $this->role_id = intval($data['role_id']);
        }
        if (isset($data['acl_id'])) {
            $this->acl_id = intval($data['acl_id']);
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var int|null
     */
    public $group_id = null;

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
    public $acl_id = null;
}
