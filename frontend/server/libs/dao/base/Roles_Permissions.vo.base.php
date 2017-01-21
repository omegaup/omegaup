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
    /**
     * Constructor de RolesPermissions
     *
     * Para construir un objeto de tipo RolesPermissions debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['role_id'])) {
            $this->role_id = $data['role_id'];
        }
        if (isset($data['permission_id'])) {
            $this->permission_id = $data['permission_id'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (count($fields) > 0) {
            parent::toUnixTime($fields);
        } else {
            parent::toUnixTime([]);
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var int(11)
      */
    public $role_id;

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var int(11)
      */
    public $permission_id;
}
