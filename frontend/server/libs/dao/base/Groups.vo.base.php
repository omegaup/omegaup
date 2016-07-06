<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Groups.
  *
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @access public
  *
  */

class Groups extends VO
{
	/**
	  * Constructor de Groups
	  *
	  * Para construir un objeto de tipo Groups debera llamarse a el constructor
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  */
	function __construct($data = NULL)
	{
		if (isset($data))
		{
			if (is_string($data))
				$data = self::object_to_array(json_decode($data));

			if (isset($data['group_id'])) {
				$this->group_id = $data['group_id'];
			}
			if (isset($data['owner_id'])) {
				$this->owner_id = $data['owner_id'];
			}
			if (isset($data['create_time'])) {
				$this->create_time = $data['create_time'];
			}
			if (isset($data['alias'])) {
				$this->alias = $data['alias'];
			}
			if (isset($data['name'])) {
				$this->name = $data['name'];
			}
			if (isset($data['description'])) {
				$this->description = $data['description'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  *
	  * Este metodo permite tratar a un objeto Groups en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String
	  */
	public function __toString( )
	{
		$vec = array(
			"group_id" => $this->group_id,
			"owner_id" => $this->owner_id,
			"create_time" => $this->create_time,
			"alias" => $this->alias,
			"name" => $this->name,
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
			parent::toUnixTime(array("create_time"));
	}

	/**
	  *  [Campo no documentado]
	  * Llave Primaria
	  * Auto Incremento
	  * @access public
	  * @var int(11)
	  */
	public $group_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $owner_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var timestamp
	  */
	public $create_time;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var varchar(50)
	  */
	public $alias;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var varchar(50)
	  */
	public $name;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var varchar(256)
	  */
	public $description;
}
