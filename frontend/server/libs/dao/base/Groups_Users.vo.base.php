<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Groups_Users.
 *
 * VO does not have any behaviour.
 * @access public
 */
class GroupsUsers extends VO {
    /**
     * Constructor de GroupsUsers
     *
     * Para construir un objeto de tipo GroupsUsers debera llamarse a el constructor
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
        if (isset($data['user_id'])) {
            $this->user_id = $data['user_id'];
        }
        if (isset($data['share_user_information'])) {
            $this->share_user_information = $data['share_user_information'];
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
    public $user_id;

    /**
      * Almacena la respuesta del participante de un curso si está de acuerdo en divulgar su información.
      * @access public
      * @var tinyint(1)
      */
    public $share_user_information;
}
