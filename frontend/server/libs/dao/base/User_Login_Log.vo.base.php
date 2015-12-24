<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table User_Login_Log.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @access public
  * 
  */

class UserLoginLog extends VO
{
	/**
	  * Constructor de UserLoginLog
	  * 
	  * Para construir un objeto de tipo UserLoginLog debera llamarse a el constructor 
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
			if (isset($data['ip'])) {
				$this->ip = $data['ip'];
			}
			if (isset($data['time'])) {
				$this->time = $data['time'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto UserLoginLog en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"user_id" => $this->user_id,
			"ip" => $this->ip,
			"time" => $this->time
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
			parent::toUnixTime(array("time"));
	}

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $user_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int
	  */
	public $ip;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var timestamp
	  */
	public $time;
}
