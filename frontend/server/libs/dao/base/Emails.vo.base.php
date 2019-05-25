<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Emails.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Emails extends VO {
    /**
     * Constructor de Emails
     *
     * Para construir un objeto de tipo Emails debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['email_id'])) {
            $this->email_id = (int)$data['email_id'];
        }
        if (isset($data['email'])) {
            $this->email = $data['email'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = (int)$data['user_id'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (empty($fields)) {
            parent::toUnixTime([]);
            return;
        }
        parent::toUnixTime($fields);
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int(11)
      */
    public $email_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(100)
      */
    public $email;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $user_id;
}
