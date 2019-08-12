<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Roles_Permissions.
 *
 * VO does not have any behaviour.
 * @access public
 */
class RolesPermissions extends VO {
    const FIELD_NAMES = [
        'role_id' => true,
        'permission_id' => true,
    ];

    /**
     * Constructor de RolesPermissions
     *
     * Para construir un objeto de tipo RolesPermissions debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['role_id'])) {
            $this->role_id = (int)$data['role_id'];
        }
        if (isset($data['permission_id'])) {
            $this->permission_id = (int)$data['permission_id'];
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var int
     */
    public $role_id;

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var int
     */
    public $permission_id;
}
