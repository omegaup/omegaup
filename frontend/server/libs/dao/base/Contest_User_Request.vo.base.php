<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Contest_User_Request.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @access public
  * 
  */

class ContestUserRequest extends VO
{
	/**
	  * Constructor de ContestUserRequest
	  * 
	  * Para construir un objeto de tipo ContestUserRequest debera llamarse a el constructor 
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
			if (isset($data['contest_id'])) {
				$this->contest_id = $data['contest_id'];
			}
			if (isset($data['request_time'])) {
				$this->request_time = $data['request_time'];
			}
			if (isset($data['last_update'])) {
				$this->last_update = $data['last_update'];
			}
			if (isset($data['accepted'])) {
				$this->accepted = $data['accepted'];
			}
			if (isset($data['extra_note'])) {
				$this->extra_note = $data['extra_note'];
			}
			if (isset($data['reason'])) {
				$this->reason = $data['reason'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto ContestUserRequest en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"user_id" => $this->user_id,
			"contest_id" => $this->contest_id,
			"request_time" => $this->request_time,
			"last_update" => $this->last_update,
			"accepted" => $this->accepted,
			"extra_note" => $this->extra_note,
			"reason" => $this->reason
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
			parent::toUnixTime(array("request_time", "last_update"));
	}

	/**
	  *  [Campo no documentado]
	  * Llave Primaria
	  * @access public
	  * @var int(11)
	  */
	public $user_id;

	/**
	  *  [Campo no documentado]
	  * Llave Primaria
	  * @access public
	  * @var int(11)
	  */
	public $contest_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var timestamp
	  */
	public $request_time;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var timestamp
	  */
	public $last_update;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var tinyint(1)
	  */
	public $accepted;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var text,
	  */
	public $extra_note;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var enum('PRIVATE_CONTEST','PENDING')
	  */
	public $reason;
}
