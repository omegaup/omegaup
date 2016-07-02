<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Countries.
  *
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @access public
  *
  */

class Countries extends VO
{
	/**
	  * Constructor de Countries
	  *
	  * Para construir un objeto de tipo Countries debera llamarse a el constructor
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  */
	function __construct($data = NULL)
	{
		if (isset($data))
		{
			if (is_string($data))
				$data = self::object_to_array(json_decode($data));

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
	  * Este metodo permite tratar a un objeto Countries en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String
	  */
	public function __toString( )
	{
		$vec = array(
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
	  * @access public
	  * @var char(3)
	  */
	public $country_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var varchar(50)
	  */
	public $name;
}

