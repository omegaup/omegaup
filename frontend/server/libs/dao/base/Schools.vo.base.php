<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Schools.
  *
  * VO does not have any behaviour.
  * @access public
  *
  */

class Schools extends VO
{
	/**
	  * Constructor de Schools
	  *
	  * Para construir un objeto de tipo Schools debera llamarse a el constructor
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  */
	function __construct($data = NULL)
	{
		if (isset($data))
		{
			if (is_string($data))
				$data = self::object_to_array(json_decode($data));

			if (isset($data['school_id'])) {
				$this->school_id = $data['school_id'];
			}
			if (isset($data['state_id'])) {
				$this->state_id = $data['state_id'];
			}
			if (isset($data['country_id'])) {
				$this->country_id = $data['country_id'];
			}
			if (isset($data['name'])) {
				$this->name = $data['name'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  *
	  * Este metodo permite tratar a un objeto Schools en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String
	  */
	public function __toString( )
	{
		$vec = array(
			"school_id" => $this->school_id,
			"state_id" => $this->state_id,
			"country_id" => $this->country_id,
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
	public $school_id;

	/**
	  *  [Campo no documentado]
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
	  * @var varchar(128)
	  */
	public $name;
}
