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
    const FIELD_NAMES = [
        'identity_id' => true,
        'username' => true,
        'password' => true,
        'name' => true,
        'user_id' => true,
        'language_id' => true,
        'country_id' => true,
        'state_id' => true,
        'school_id' => true,
        'gender' => true,
    ];

    /**
     * Constructor de Identities
     *
     * Para construir un objeto de tipo Identities debera llamarse a el constructor
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
        if (isset($data['identity_id'])) {
            $this->identity_id = (int)$data['identity_id'];
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
            $this->user_id = (int)$data['user_id'];
        }
        if (isset($data['language_id'])) {
            $this->language_id = (int)$data['language_id'];
        }
        if (isset($data['country_id'])) {
            $this->country_id = $data['country_id'];
        }
        if (isset($data['state_id'])) {
            $this->state_id = $data['state_id'];
        }
        if (isset($data['school_id'])) {
            $this->school_id = (int)$data['school_id'];
        }
        if (isset($data['gender'])) {
            $this->gender = $data['gender'];
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int
     */
    public $identity_id = 0;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $username;

    /**
      * Contraseña del usuario, usando Argon2i o Blowfish
      * @access public
      * @var ?string
     */
    public $password;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?string
     */
    public $name;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?int
     */
    public $user_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?int
     */
    public $language_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?string
     */
    public $country_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?string
     */
    public $state_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?int
     */
    public $school_id;

    /**
      * Género de la identidad
      * @access public
      * @var ?string
     */
    public $gender;
}
