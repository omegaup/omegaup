<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Courses.
  *
  * VO does not have any behaviour.
  * @access public
  *
  */

class Courses extends VO
{
	/**
	  * Constructor de Courses
	  *
	  * Para construir un objeto de tipo Courses debera llamarse a el constructor
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  */
	function __construct($data = NULL)
	{
		if (isset($data))
		{
			if (is_string($data))
				$data = self::object_to_array(json_decode($data));

			if (isset($data['course_id'])) {
				$this->course_id = $data['course_id'];
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
			if (isset($data['group_id'])) {
				$this->group_id = $data['group_id'];
			}
			if (isset($data['acl_id'])) {
				$this->acl_id = $data['acl_id'];
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
	  * Este metodo permite tratar a un objeto Courses en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String
	  */
	public function __toString( )
	{
		$vec = array(
			"course_id" => $this->course_id,
			"name" => $this->name,
			"description" => $this->description,
			"alias" => $this->alias,
			"group_id" => $this->group_id,
			"acl_id" => $this->acl_id,
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
	public $course_id;

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
	  * @var int(11)
	  */
	public $group_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $acl_id;

	/**
	  * Hora de inicio de este curso
	  * @access public
	  * @var timestamp
	  */
	public $start_time;

	/**
	  * Hora de finalizacion de este curso
	  * @access public
	  * @var timestamp
	  */
	public $finish_time;
}
