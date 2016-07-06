<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Contest_User_Request_History.
  *
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @access public
  *
  */

class ContestUserRequestHistory extends VO
{
	/**
	  * Constructor de ContestUserRequestHistory
	  *
	  * Para construir un objeto de tipo ContestUserRequestHistory debera llamarse a el constructor
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  */
	function __construct($data = NULL)
	{
		if (isset($data))
		{
			if (is_string($data))
				$data = self::object_to_array(json_decode($data));

			if (isset($data['history_id'])) {
				$this->history_id = $data['history_id'];
			}
			if (isset($data['user_id'])) {
				$this->user_id = $data['user_id'];
			}
			if (isset($data['contest_id'])) {
				$this->contest_id = $data['contest_id'];
			}
			if (isset($data['time'])) {
				$this->time = $data['time'];
			}
			if (isset($data['accepted'])) {
				$this->accepted = $data['accepted'];
			}
			if (isset($data['admin_id'])) {
				$this->admin_id = $data['admin_id'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  *
	  * Este metodo permite tratar a un objeto ContestUserRequestHistory en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String
	  */
	public function __toString( )
	{
		$vec = array(
			"history_id" => $this->history_id,
			"user_id" => $this->user_id,
			"contest_id" => $this->contest_id,
			"time" => $this->time,
			"accepted" => $this->accepted,
			"admin_id" => $this->admin_id
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
	  * Llave Primaria
	  * Auto Incremento
	  * @access public
	  * @var int(11)
	  */
	public $history_id;

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
	public $contest_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var timestamp
	  */
	public $time;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var tinyint(4)
	  */
	public $accepted;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $admin_id;
}
