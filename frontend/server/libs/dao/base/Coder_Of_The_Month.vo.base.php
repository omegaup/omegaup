<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Coder_Of_The_Month.
  *
  * VO does not have any behaviour.
  * @access public
  *
  */

class CoderOfTheMonth extends VO
{
	/**
	  * Constructor de CoderOfTheMonth
	  *
	  * Para construir un objeto de tipo CoderOfTheMonth debera llamarse a el constructor
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  */
	function __construct($data = NULL)
	{
		if (isset($data))
		{
			if (is_string($data))
				$data = self::object_to_array(json_decode($data));

			if (isset($data['coder_of_the_month_id'])) {
				$this->coder_of_the_month_id = $data['coder_of_the_month_id'];
			}
			if (isset($data['user_id'])) {
				$this->user_id = $data['user_id'];
			}
			if (isset($data['description'])) {
				$this->description = $data['description'];
			}
			if (isset($data['time'])) {
				$this->time = $data['time'];
			}
			if (isset($data['interview_url'])) {
				$this->interview_url = $data['interview_url'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  *
	  * Este metodo permite tratar a un objeto CoderOfTheMonth en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String
	  */
	public function __toString( )
	{
		$vec = array(
			"coder_of_the_month_id" => $this->coder_of_the_month_id,
			"user_id" => $this->user_id,
			"description" => $this->description,
			"time" => $this->time,
			"interview_url" => $this->interview_url
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
	public $coder_of_the_month_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $user_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var tinytext,
	  */
	public $description;

	/**
	  * Fecha no es UNIQUE por si hay más de 1 coder de mes.
	  * @access public
	  * @var date
	  */
	public $time;

	/**
	  * Para linekar a un post del blog con entrevistas.
	  * @access public
	  * @var varchar(256)
	  */
	public $interview_url;
}
