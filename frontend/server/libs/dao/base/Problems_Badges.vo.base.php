<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Problems_Badges.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @access public
  * 
  */

class ProblemsBadges extends VO
{
	/**
	  * Constructor de ProblemsBadges
	  * 
	  * Para construir un objeto de tipo ProblemsBadges debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  */
	function __construct($data = NULL)
	{
		if (isset($data))
		{
			if (is_string($data))
				$data = self::object_to_array(json_decode($data));


			if (isset($data['badge_id'])) {
				$this->badge_id = $data['badge_id'];
			}
			if (isset($data['problem_id'])) {
				$this->problem_id = $data['problem_id'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto ProblemsBadges en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"badge_id" => $this->badge_id,
			"problem_id" => $this->problem_id
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
	public $badge_id;

	/**
	  *  [Campo no documentado]
	  * Llave Primaria
	  * @access public
	  * @var int(11)
	  */
	public $problem_id;
}
