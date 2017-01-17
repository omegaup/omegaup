<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Problemset_User_Request.
  *
  * VO does not have any behaviour.
  * @access public
  *
  */

class ProblemsetUserRequest extends VO
{
	/**
	  * Constructor de ProblemsetUserRequest
	  *
	  * Para construir un objeto de tipo ProblemsetUserRequest debera llamarse a el constructor
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
			if (isset($data['problemset_id'])) {
				$this->problemset_id = $data['problemset_id'];
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
		}
	}

	/**
	  * Obtener una representacion en String
	  *
	  * Este metodo permite tratar a un objeto ProblemsetUserRequest en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String
	  */
	public function __toString( )
	{
		$vec = array(
			"user_id" => $this->user_id,
			"problemset_id" => $this->problemset_id,
			"request_time" => $this->request_time,
			"last_update" => $this->last_update,
			"accepted" => $this->accepted,
			"extra_note" => $this->extra_note
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
	public $problemset_id;

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
}
