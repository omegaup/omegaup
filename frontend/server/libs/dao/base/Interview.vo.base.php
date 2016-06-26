<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Interview.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @access public
  * 
  */

class Interview extends VO
{
	/**
	  * Constructor de Interview
	  * 
	  * Para construir un objeto de tipo Interview debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  */
	function __construct($data = NULL)
	{
		if (isset($data))
		{
			if (is_string($data))
				$data = self::object_to_array(json_decode($data));


			if (isset($data['interview_id'])) {
				$this->interview_id = $data['interview_id'];
			}
			if (isset($data['title'])) {
				$this->title = $data['title'];
			}
			if (isset($data['user_id'])) {
				$this->user_id = $data['user_id'];
			}
			if (isset($data['duration'])) {
				$this->duration = $data['duration'];
			}
			if (isset($data['time'])) {
				$this->time = $data['time'];
			}
			if (isset($data['contest_id'])) {
				$this->contest_id = $data['contest_id'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Interview en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"interview_id" => $this->interview_id,
			"title" => $this->title,
			"user_id" => $this->user_id,
			"duration" => $this->duration,
			"time" => $this->time,
			"contest_id" => $this->contest_id
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
	public $interview_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var varchar(256)
	  */
	public $title;

	/**
	  * UserID del autor de este aviso
	  * @access public
	  * @var int(11)
	  */
	public $user_id;

	/**
	  * Duration in minutes of this interview
	  * @access public
	  * @var int(11)
	  */
	public $duration;

	/**
	  * Fecha de creacion de este aviso
	  * @access public
	  * @var timestamp
	  */
	public $time;

	/**
	  * Una entrevista esta implementada con un concurso
	  * @access public
	  * @var text
	  */
	public $contest_id;
}
