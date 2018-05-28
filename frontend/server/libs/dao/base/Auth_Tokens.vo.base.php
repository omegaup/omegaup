<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Auth_Tokens.
 *
 * VO does not have any behaviour.
 * @access public
 */
class AuthTokens extends VO {
    /**
     * Constructor de AuthTokens
     *
     * Para construir un objeto de tipo AuthTokens debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['user_id'])) {
            $this->user_id = $data['user_id'];
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = $data['identity_id'];
        }
        if (isset($data['token'])) {
            $this->token = $data['token'];
        }
        if (isset($data['create_time'])) {
            $this->create_time = $data['create_time'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (count($fields) > 0) {
            parent::toUnixTime($fields);
        } else {
            parent::toUnixTime(['create_time']);
        }
    }

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $user_id;

    /**
      * Identidad del usuario
      * @access public
      * @var int(11)
      */
    public $identity_id;

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var varchar(128)
      */
    public $token;

    /**
      *  [Campo no documentado]
      * @access public
      * @var timestamp
      */
    public $create_time;
}
