<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Group_Roles.
 *
 * VO does not have any behaviour.
 * @access public
 */
class GroupRoles extends VO {
    /**
     * Constructor de GroupRoles
     *
     * Para construir un objeto de tipo GroupRoles debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['group_id'])) {
            $this->group_id = $data['group_id'];
        }
        if (isset($data['role_id'])) {
            $this->role_id = $data['role_id'];
        }
        if (isset($data['acl_id'])) {
            $this->acl_id = $data['acl_id'];
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
    public $group_id;

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
    public $acl_id;
}
