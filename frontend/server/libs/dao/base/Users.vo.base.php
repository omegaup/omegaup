<?php

/********************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Users.
  *
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @access public
  *
  */

class Users extends VO
{
	/**
	  * Constructor de Users
	  *
	  * Para construir un objeto de tipo Users debera llamarse a el constructor
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  */
	function __construct($data = NULL)
	{
		if (isset($data))
		{
			if (is_string($data))
				$data = self::object_to_array(json_decode($data));

			if (isset($data['user_id'])) {
				$this->user_id = $data['user_id'];
			}
			if (isset($data['username'])) {
				$this->username = $data['username'];
			}
			if (isset($data['facebook_user_id'])) {
				$this->facebook_user_id = $data['facebook_user_id'];
			}
			if (isset($data['password'])) {
				$this->password = $data['password'];
			}
			if (isset($data['main_email_id'])) {
				$this->main_email_id = $data['main_email_id'];
			}
			if (isset($data['name'])) {
				$this->name = $data['name'];
			}
			if (isset($data['solved'])) {
				$this->solved = $data['solved'];
			}
			if (isset($data['submissions'])) {
				$this->submissions = $data['submissions'];
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
			if (isset($data['scholar_degree'])) {
				$this->scholar_degree = $data['scholar_degree'];
			}
			if (isset($data['language_id'])) {
				$this->language_id = $data['language_id'];
			}
			if (isset($data['graduation_date'])) {
				$this->graduation_date = $data['graduation_date'];
			}
			if (isset($data['birth_date'])) {
				$this->birth_date = $data['birth_date'];
			}
			if (isset($data['last_access'])) {
				$this->last_access = $data['last_access'];
			}
			if (isset($data['verified'])) {
				$this->verified = $data['verified'];
			}
			if (isset($data['verification_id'])) {
				$this->verification_id = $data['verification_id'];
			}
			if (isset($data['reset_digest'])) {
				$this->reset_digest = $data['reset_digest'];
			}
			if (isset($data['reset_sent_at'])) {
				$this->reset_sent_at = $data['reset_sent_at'];
			}
			if (isset($data['reset_sent_at'])) {
				$this->reset_sent_at = $data['reset_sent_at'];
			}

			$this->recruitment_optin = isset($data['recruitment_optin']) && $data['recruitment_optin'];
		}
	}

	/**
	  * Obtener una representacion en String
	  *
	  * Este metodo permite tratar a un objeto Users en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String
	  */
	public function __toString( )
	{
		$vec = array(
			"user_id" => $this->user_id,
			"username" => $this->username,
			"facebook_user_id" => $this->facebook_user_id,
			"password" => $this->password,
			"main_email_id" => $this->main_email_id,
			"name" => $this->name,
			"solved" => $this->solved,
			"submissions" => $this->submissions,
			"country_id" => $this->country_id,
			"state_id" => $this->state_id,
			"school_id" => $this->school_id,
			"scholar_degree" => $this->scholar_degree,
			"language_id" => $this->language_id,
			"graduation_date" => $this->graduation_date,
			"birth_date" => $this->birth_date,
			"last_access" => $this->last_access,
			"verified" => $this->verified,
			"verification_id" => $this->verification_id,
			"reset_digest" => $this->reset_digest,
			"reset_sent_at" => $this->reset_sent_at,
			"recruitment_optin" => $this->recruitment_optin
		);
		return json_encode($vec);
	}

	/**
	 * Converts date fields to timestamps
	 **/
	public function toUnixTime(array $fields = array()) {
		if (count($fields) > 0)
			parent::toUnixTime($fields);
		else
			parent::toUnixTime(array("last_access"));
	}

	/**
	  *  [Campo no documentado]
	  * Llave Primaria
	  * Auto Incremento
	  * @access public
	  * @var int(11)
	  */
	public $user_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var varchar(50)
	  */
	public $username;

	/**
	  * Facebook ID for this user.
	  * @access public
	  * @var varchar(20)
	  */
	public $facebook_user_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var varchar(100)
	  */
	public $password;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $main_email_id;

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
	public $solved;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $submissions;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var char(3)
	  */
	public $country_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $state_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $school_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var varchar(64)
	  */
	public $scholar_degree;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $language_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var date
	  */
	public $graduation_date;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var date
	  */
	public $birth_date;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var timestamp
	  */
	public $last_access;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var BOOLEAN
	  */
	public $verified;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var VARCHAR(
	  */
	public $verification_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var VARCHAR(45)
	  */
	public $reset_digest;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var DATETIME
	  */
	public $reset_sent_at;

	/**
	  * Determina si el usuario puede ser contactado con fines de reclutamiento.
	  * @access public
	  * @var tinyint(1)
	  */
	public $recruitment_optin;
}
