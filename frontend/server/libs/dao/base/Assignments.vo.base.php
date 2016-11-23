<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Assignments.
  *
  * VO does not have any behaviour.
  * @access public
  *
  */

class Assignments extends VO
{
	/**
	  * Constructor de Assignments
	  *
	  * Para construir un objeto de tipo Assignments debera llamarse a el constructor
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  */
	function __construct($data = NULL)
	{
		if (isset($data))
		{
			if (is_string($data))
				$data = self::object_to_array(json_decode($data));

			if (isset($data['assignment_id'])) {
				$this->assignment_id = $data['assignment_id'];
			}
			if (isset($data['course_id'])) {
				$this->course_id = $data['course_id'];
			}
			if (isset($data['problemset_id'])) {
				$this->problemset_id = $data['problemset_id'];
			}
			if (isset($data['name'])) {
				$this->name = $data['name'];
			}
			if (isset($data['description'])) {
				$this->description = $data['description'];
			}
			if (isset($data['alias'])) {
				$this->alias = $data['alias'];
			}
			if (isset($data['publish_time_delay'])) {
				$this->publish_time_delay = $data['publish_time_delay'];
			}
			if (isset($data['assignment_type'])) {
				$this->assignment_type = $data['assignment_type'];
			}
			if (isset($data['start_time'])) {
				$this->start_time = $data['start_time'];
			}
			if (isset($data['finish_time'])) {
				$this->finish_time = $data['finish_time'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  *
	  * Este metodo permite tratar a un objeto Assignments en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String
	  */
	public function __toString( )
	{
		$vec = array(
			"assignment_id" => $this->assignment_id,
			"course_id" => $this->course_id,
			"problemset_id" => $this->problemset_id,
			"name" => $this->name,
			"description" => $this->description,
			"alias" => $this->alias,
			"publish_time_delay" => $this->publish_time_delay,
			"assignment_type" => $this->assignment_type,
			"start_time" => $this->start_time,
			"finish_time" => $this->finish_time
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
			parent::toUnixTime(array("start_time", "finish_time"));
	}

	/**
	  *  [Campo no documentado]
	  * Llave Primaria
	  * Auto Incremento
	  * @access public
	  * @var int(11)
	  */
	public $assignment_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $course_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $problemset_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var varchar(100)
	  */
	public $name;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var tinytext
	  */
	public $description;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var varchar(32)
	  */
	public $alias;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11),
	  */
	public $publish_time_delay;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var enum('homework',
	  */
	public $assignment_type;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var timestamp
	  */
	public $start_time;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var timestamp
	  */
	public $finish_time;
}
