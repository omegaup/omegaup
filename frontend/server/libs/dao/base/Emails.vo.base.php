<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Emails.
  *
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @access public
  *
  */

class Emails extends VO
{
	/**
	  * Constructor de Emails
	  *
	  * Para construir un objeto de tipo Emails debera llamarse a el constructor
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  */
	function __construct($data = NULL)
	{
		if (isset($data))
		{
			if (is_string($data))
				$data = self::object_to_array(json_decode($data));

			if (isset($data['email_id'])) {
				$this->email_id = $data['email_id'];
			}
			if (isset($data['email'])) {
				$this->email = $data['email'];
			}
			if (isset($data['user_id'])) {
				$this->user_id = $data['user_id'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  *
	  * Este metodo permite tratar a un objeto Emails en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String
	  */
	public function __toString( )
	{
		$vec = array(
			"email_id" => $this->email_id,
			"email" => $this->email,
			"user_id" => $this->user_id
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
			parent::toUnixTime(array());
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

