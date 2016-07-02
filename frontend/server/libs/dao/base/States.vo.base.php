<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table States.
  *
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @access public
  *
  */

class States extends VO
{
	/**
	  * Constructor de States
	  *
	  * Para construir un objeto de tipo States debera llamarse a el constructor
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  */
	function __construct($data = NULL)
	{
		if (isset($data))
		{
			if (is_string($data))
				$data = self::object_to_array(json_decode($data));

			if (isset($data['state_id'])) {
				$this->state_id = $data['state_id'];
			}
			if (isset($data['country_id'])) {
				$this->country_id = $data['country_id'];
			}
			if (isset($data['state_code'])) {
				$this->state_code = $data['state_code'];
			}
			if (isset($data['name'])) {
				$this->name = $data['name'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  *
	  * Este metodo permite tratar a un objeto States en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String
	  */
	public function __toString( )
	{
		$vec = array(
			"state_id" => $this->state_id,
			"country_id" => $this->country_id,
			"state_code" => $this->state_code,
			"name" => $this->name
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
	public $state_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var char(3)
	  */
	public $country_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var CHAR(
	  */
	public $state_code;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var varchar(50)
	  */
	public $name;
}

