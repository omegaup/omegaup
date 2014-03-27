<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Password_Change.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @access public
  * 
  */

class PasswordChange extends VO
{
	/**
	  * Constructor de PasswordChange
	  * 
	  * Para construir un objeto de tipo PasswordChange debera llamarse a el constructor 
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
			if (isset($data['token'])) {
				$this->token = $data['token'];
			}
			if (isset($data['ip'])) {
				$this->ip = $data['ip'];
			}
			if (isset($data['expiration_date'])) {
				$this->expiration_date = $data['expiration_date'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto PasswordChange en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"user_id" => $this->user_id,
			"token" => $this->token,
			"ip" => $this->ip,
			"expiration_date" => $this->expiration_date
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
			parent::toUnixTime(array("expiration_date"));
	}

	/**
	  * Identificador de a que usuario pertenece este token
	  * Llave Primaria
	  * @access public
	  * @var int(11)
	  */
	public $user_id;

	/**
	  * El token que se genera aleatoriamente para luego comparar cuando el usuario haga click en el link
	  * @access public
	  * @var char(64)
	  */
	public $token;

	/**
	  * El ip desde donde se genero este reseteo de password
	  * @access public
	  * @var char(15)
	  */
	public $ip;

	/**
	  * La fecha en que vence este token
	  * @access public
	  * @var timestamp
	  */
	public $expiration_date;
}
