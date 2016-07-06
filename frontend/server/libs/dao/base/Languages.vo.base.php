<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Languages.
  *
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @access public
  *
  */

class Languages extends VO
{
	/**
	  * Constructor de Languages
	  *
	  * Para construir un objeto de tipo Languages debera llamarse a el constructor
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  */
	function __construct($data = NULL)
	{
		if (isset($data))
		{
			if (is_string($data))
				$data = self::object_to_array(json_decode($data));

			if (isset($data['language_id'])) {
				$this->language_id = $data['language_id'];
			}
			if (isset($data['name'])) {
				$this->name = $data['name'];
			}
			if (isset($data['country_id'])) {
				$this->country_id = $data['country_id'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  *
	  * Este metodo permite tratar a un objeto Languages en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String
	  */
	public function __toString( )
	{
		$vec = array(
			"language_id" => $this->language_id,
			"name" => $this->name,
			"country_id" => $this->country_id
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
	public $language_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var varchar(45)
	  */
	public $name;

	/**
	  * Se guarda la relación con el país para defaultear más rápido.
	  * @access public
	  * @var char(3)
	  */
	public $country_id;
}
