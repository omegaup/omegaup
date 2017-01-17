<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Interviews.
  *
  * VO does not have any behaviour.
  * @access public
  *
  */

class Interviews extends VO
{
	/**
	  * Constructor de Interviews
	  *
	  * Para construir un objeto de tipo Interviews debera llamarse a el constructor
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
			if (isset($data['problemset_id'])) {
				$this->problemset_id = $data['problemset_id'];
			}
			if (isset($data['acl_id'])) {
				$this->acl_id = $data['acl_id'];
			}
			if (isset($data['alias'])) {
				$this->alias = $data['alias'];
			}
			if (isset($data['title'])) {
				$this->title = $data['title'];
			}
			if (isset($data['description'])) {
				$this->description = $data['description'];
			}
			if (isset($data['window_length'])) {
				$this->window_length = $data['window_length'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  *
	  * Este metodo permite tratar a un objeto Interviews en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String
	  */
	public function __toString( )
	{
		$vec = array(
			"interview_id" => $this->interview_id,
			"problemset_id" => $this->problemset_id,
			"acl_id" => $this->acl_id,
			"alias" => $this->alias,
			"title" => $this->title,
			"description" => $this->description,
			"window_length" => $this->window_length
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
	public $interview_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $problemset_id;

	/**
	  * La lista de control de acceso del problema
	  * @access public
	  * @var int(11)
	  */
	public $acl_id;

	/**
	  * El alias de la entrevista
	  * @access public
	  * @var varchar(32)
	  */
	public $alias;

	/**
	  * El titulo de la entrevista.
	  * @access public
	  * @var varchar(256)
	  */
	public $title;

	/**
	  * Una breve descripcion de la entrevista.
	  * @access public
	  * @var tinytext
	  */
	public $description;

	/**
	  * Indica el tiempo que tiene el usuario para envíar soluciones.
	  * @access public
	  * @var int(11)
	  */
	public $window_length;
}
