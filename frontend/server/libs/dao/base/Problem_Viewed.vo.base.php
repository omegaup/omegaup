<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Problem_Viewed.
  *
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @access public
  *
  */

class ProblemViewed extends VO
{
	/**
	  * Constructor de ProblemViewed
	  *
	  * Para construir un objeto de tipo ProblemViewed debera llamarse a el constructor
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  */
	function __construct($data = NULL)
	{
		if (isset($data))
		{
			if (is_string($data))
				$data = self::object_to_array(json_decode($data));

			if (isset($data['problem_id'])) {
				$this->problem_id = $data['problem_id'];
			}
			if (isset($data['user_id'])) {
				$this->user_id = $data['user_id'];
			}
			if (isset($data['view_time'])) {
				$this->view_time = $data['view_time'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  *
	  * Este metodo permite tratar a un objeto ProblemViewed en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String
	  */
	public function __toString( )
	{
		$vec = array(
			"problem_id" => $this->problem_id,
			"user_id" => $this->user_id,
			"view_time" => $this->view_time
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
			parent::toUnixTime(array("view_time"));
	}

	/**
	  *  [Campo no documentado]
	  * Llave Primaria
	  * @access public
	  * @var int(11)
	  */
	public $problem_id;

	/**
	  *  [Campo no documentado]
	  * Llave Primaria
	  * @access public
	  * @var int(11)
	  */
	public $user_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var timestamp
	  */
	public $view_time;
}

