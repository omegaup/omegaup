<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Interview_Users.
  *
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @access public
  *
  */

class InterviewUsers extends VO
{
	/**
	  * Constructor de InterviewUsers
	  *
	  * Para construir un objeto de tipo InterviewUsers debera llamarse a el constructor
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
			if (isset($data['interview_id'])) {
				$this->interview_id = $data['interview_id'];
			}
			if (isset($data['invitation_sent'])) {
				$this->invitation_sent = $data['invitation_sent'];
			}
			if (isset($data['access_time'])) {
				$this->access_time = $data['access_time'];
			}
			if (isset($data['score'])) {
				$this->score = $data['score'];
			}
			if (isset($data['time'])) {
				$this->time = $data['time'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  *
	  * Este metodo permite tratar a un objeto InterviewUsers en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String
	  */
	public function __toString( )
	{
		$vec = array(
			"user_id" => $this->user_id,
			"interview_id" => $this->interview_id,
			"invitation_sent" => $this->invitation_sent,
			"access_time" => $this->access_time,
			"score" => $this->score,
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
			parent::toUnixTime(array());
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
	  * @access public
	  * @var int(11)
	  */
	public $interview_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var BOOLEAN
	  */
	public $invitation_sent;

	/**
	  * Hora a la que entró el usuario al concurso
	  * @access public
	  * @var datetime
	  */
	public $access_time;

	/**
	  * Indica el puntaje que obtuvo el usuario en el concurso
	  * @access public
	  * @var int(11)
	  */
	public $score;

	/**
	  * Indica el tiempo que acumulo en usuario en el concurso
	  * @access public
	  * @var int(11)
	  */
	public $time;
}
