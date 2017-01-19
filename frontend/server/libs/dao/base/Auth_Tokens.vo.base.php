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
        if (isset($data['token'])) {
            $this->token = $data['token'];
        }
        if (isset($data['create_time'])) {
            $this->create_time = $data['create_time'];
        }
    }

    /**
     * Obtener una representacion en String
     *
     * Este metodo permite tratar a un objeto AuthTokens en forma de cadena.
     * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
     * @return String
     */
    public function __toString() {
        return json_encode([
            'user_id' => $this->user_id,
            'token' => $this->token,
            'create_time' => $this->create_time,
        ]);
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
