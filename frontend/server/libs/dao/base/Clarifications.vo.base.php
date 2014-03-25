<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Clarifications.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @access public
  * 
  */

class Clarifications extends VO
{
	/**
	  * Constructor de Clarifications
	  * 
	  * Para construir un objeto de tipo Clarifications debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  */
	function __construct($data = NULL)
	{
		if (isset($data))
		{
			if (is_string($data))
				$data = self::object_to_array(json_decode($data));


			if (isset($data['clarification_id'])) {
				$this->clarification_id = $data['clarification_id'];
			}
			if (isset($data['author_id'])) {
				$this->author_id = $data['author_id'];
			}
			if (isset($data['message'])) {
				$this->message = $data['message'];
			}
			if (isset($data['answer'])) {
				$this->answer = $data['answer'];
			}
			if (isset($data['time'])) {
				$this->time = $data['time'];
			}
			if (isset($data['problem_id'])) {
				$this->problem_id = $data['problem_id'];
			}
			if (isset($data['contest_id'])) {
				$this->contest_id = $data['contest_id'];
			}
			if (isset($data['public'])) {
				$this->public = $data['public'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Clarifications en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"clarification_id" => $this->clarification_id,
			"author_id" => $this->author_id,
			"message" => $this->message,
			"answer" => $this->answer,
			"time" => $this->time,
			"problem_id" => $this->problem_id,
			"contest_id" => $this->contest_id,
			"public" => $this->public
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
	public $clarification_id;

	/**
	  * Autor de la clarificación.
	  * @access public
	  * @var int(11)
	  */
	public $author_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var text
	  */
	public $message;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var text,
	  */
	public $answer;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var timestamp
	  */
	public $time;

	/**
	  * Lo ideal es que la clarificacion le llegue al problemsetter que escribio el problema o al contest owner si no esta ligado a un problema.
	  * @access public
	  * @var int(11)
	  */
	public $problem_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $contest_id;

	/**
	  * Sólo las clarificaciones que el problemsetter marque como publicacbles apareceran en la lista que toda la banda puede ver. Sino, solo al usuario. 
	  * @access public
	  * @var tinyint(1)
	  */
	public $public;
}
