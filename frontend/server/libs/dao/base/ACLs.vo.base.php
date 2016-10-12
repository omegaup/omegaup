<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table ACLs.
  *
  * VO does not have any behaviour.
  * @access public
  *
  */

class ACLs extends VO
{
	/**
	  * Constructor de ACLs
	  *
	  * Para construir un objeto de tipo ACLs debera llamarse a el constructor
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  */
	function __construct($data = NULL)
	{
		if (isset($data))
		{
			if (is_string($data))
				$data = self::object_to_array(json_decode($data));


			if (isset($data['acl_id'])) {
				$this->acl_id = $data['acl_id'];
			}
			if (isset($data['owner_id'])) {
				$this->owner_id = $data['owner_id'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  *
	  * Este metodo permite tratar a un objeto ACLs en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String
	  */
	public function __toString( )
	{
		$vec = array(
			"acl_id" => $this->acl_id,
			"owner_id" => $this->owner_id
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
	public $acl_id;

	/**
	  * El usuario que creó el objeto y que tiene un rol de administrador implícito
	  * @access public
	  * @var int(11)
	  */
	public $owner_id;
}
