<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table ACLs.
 *
 * VO does not have any behaviour.
 * @access public
 */
class ACLs extends VO {
    const FIELD_NAMES = [
        'acl_id' => true,
        'owner_id' => true,
    ];

    /**
     * Constructor de ACLs
     *
     * Para construir un objeto de tipo ACLs debera llamarse a el constructor
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
        if (isset($data['acl_id'])) {
            $this->acl_id = (int)$data['acl_id'];
        }
        if (isset($data['owner_id'])) {
            $this->owner_id = (int)$data['owner_id'];
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int
     */
    public $acl_id;

    /**
      * El usuario que creó el objeto y que tiene un rol de administrador implícito
      * @access public
      * @var int
     */
    public $owner_id;
}
