<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Announcement.
  *
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @access public
  *
  */

class Announcement extends VO
{
	/**
	  * Constructor de Announcement
	  *
	  * Para construir un objeto de tipo Announcement debera llamarse a el constructor
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  */
	function __construct($data = NULL)
	{
		if (isset($data))
		{
			if (is_string($data))
				$data = self::object_to_array(json_decode($data));

			if (isset($data['announcement_id'])) {
				$this->announcement_id = $data['announcement_id'];
			}
			if (isset($data['user_id'])) {
				$this->user_id = $data['user_id'];
			}
			if (isset($data['time'])) {
				$this->time = $data['time'];
			}
			if (isset($data['description'])) {
				$this->description = $data['description'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  *
	  * Este metodo permite tratar a un objeto Announcement en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String
	  */
	public function __toString( )
	{
		$vec = array(
			"announcement_id" => $this->announcement_id,
			"user_id" => $this->user_id,
			"time" => $this->time,
			"description" => $this->description
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
	  * Identificador del aviso
	  * Llave Primaria
	  * Auto Incremento
	  * @access public
	  * @var int(11)
	  */
	public $announcement_id;

	/**
	  * UserID del autor de este aviso
	  * @access public
	  * @var int(11)
	  */
	public $user_id;

	/**
	  * Fecha de creacion de este aviso
	  * @access public
	  * @var timestamp
	  */
	public $time;

	/**
	  * Mensaje de texto del aviso
	  * @access public
	  * @var text
	  */
	public $description;
}
