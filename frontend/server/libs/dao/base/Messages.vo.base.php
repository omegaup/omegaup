<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Messages.
  *
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @access public
  *
  */

class Messages extends VO
{
	/**
	  * Constructor de Messages
	  *
	  * Para construir un objeto de tipo Messages debera llamarse a el constructor
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  */
	function __construct($data = NULL)
	{
		if (isset($data))
		{
			if (is_string($data))
				$data = self::object_to_array(json_decode($data));

			if (isset($data['message_id'])) {
				$this->message_id = $data['message_id'];
			}
			if (isset($data['read'])) {
				$this->read = $data['read'];
			}
			if (isset($data['sender_id'])) {
				$this->sender_id = $data['sender_id'];
			}
			if (isset($data['recipient_id'])) {
				$this->recipient_id = $data['recipient_id'];
			}
			if (isset($data['message'])) {
				$this->message = $data['message'];
			}
			if (isset($data['date'])) {
				$this->date = $data['date'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  *
	  * Este metodo permite tratar a un objeto Messages en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String
	  */
	public function __toString( )
	{
		$vec = array(
			"message_id" => $this->message_id,
			"read" => $this->read,
			"sender_id" => $this->sender_id,
			"recipient_id" => $this->recipient_id,
			"message" => $this->message,
			"date" => $this->date
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
			parent::toUnixTime(array("date"));
	}

	/**
	  *  [Campo no documentado]
	  * Llave Primaria
	  * Auto Incremento
	  * @access public
	  * @var int(11)
	  */
	public $message_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var tinyint(1)
	  */
	public $read;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $sender_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $recipient_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var tinytext
	  */
	public $message;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var timestamp
	  */
	public $date;
}
