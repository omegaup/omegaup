<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Identities.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Identities extends VO {
    /**
     * Constructor de Identities
     *
     * Para construir un objeto de tipo Identities debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = $data['identity_id'];
        }
        if (isset($data['username'])) {
            $this->username = $data['username'];
        }
        if (isset($data['password'])) {
            $this->password = $data['password'];
        }
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = $data['user_id'];
        }
        if (isset($data['language_id'])) {
            $this->language_id = $data['language_id'];
        }
        if (isset($data['country_id'])) {
            $this->country_id = $data['country_id'];
        }
        if (isset($data['state_id'])) {
            $this->state_id = $data['state_id'];
        }
        if (isset($data['school_id'])) {
            $this->school_id = $data['school_id'];
        }
        if (isset($data['gender'])) {
            $this->gender = $data['gender'];
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
      * Auto Incremento
      * @access public
      * @var int(11)
      */
    public $identity_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(50)
      */
    public $username;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(100)
      */
    public $password;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(256)
      */
    public $name;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $user_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $language_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var char(3)
      */
    public $country_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var char(3)
      */
    public $state_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $school_id;

    /**
      * Género de la identidad
      * @access public
      * @var enum('female','male','other','decline')
      */
    public $gender;
}
