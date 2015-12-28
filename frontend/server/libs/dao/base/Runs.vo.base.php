<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Runs.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @access public
  * 
  */

class Runs extends VO
{
	/**
	  * Constructor de Runs
	  * 
	  * Para construir un objeto de tipo Runs debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  */
	function __construct($data = NULL)
	{
		if (isset($data))
		{
			if (is_string($data))
				$data = self::object_to_array(json_decode($data));


			if (isset($data['run_id'])) {
				$this->run_id = $data['run_id'];
			}
			if (isset($data['user_id'])) {
				$this->user_id = $data['user_id'];
			}
			if (isset($data['problem_id'])) {
				$this->problem_id = $data['problem_id'];
			}
			if (isset($data['contest_id'])) {
				$this->contest_id = $data['contest_id'];
			}
			if (isset($data['guid'])) {
				$this->guid = $data['guid'];
			}
			if (isset($data['language'])) {
				$this->language = $data['language'];
			}
			if (isset($data['status'])) {
				$this->status = $data['status'];
			}
			if (isset($data['verdict'])) {
				$this->verdict = $data['verdict'];
			}
			if (isset($data['runtime'])) {
				$this->runtime = $data['runtime'];
			}
			if (isset($data['penalty'])) {
				$this->penalty = $data['penalty'];
			}
			if (isset($data['memory'])) {
				$this->memory = $data['memory'];
			}
			if (isset($data['score'])) {
				$this->score = $data['score'];
			}
			if (isset($data['contest_score'])) {
				$this->contest_score = $data['contest_score'];
			}
			if (isset($data['time'])) {
				$this->time = $data['time'];
			}
			if (isset($data['submit_delay'])) {
				$this->submit_delay = $data['submit_delay'];
			}
			if (isset($data['test'])) {
				$this->test = $data['test'];
			}
			if (isset($data['judged_by'])) {
				$this->judged_by = $data['judged_by'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Runs en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"run_id" => $this->run_id,
			"user_id" => $this->user_id,
			"problem_id" => $this->problem_id,
			"contest_id" => $this->contest_id,
			"guid" => $this->guid,
			"language" => $this->language,
			"status" => $this->status,
			"verdict" => $this->verdict,
			"runtime" => $this->runtime,
			"penalty" => $this->penalty,
			"memory" => $this->memory,
			"score" => $this->score,
			"contest_score" => $this->contest_score,
			"time" => $this->time,
			"submit_delay" => $this->submit_delay,
			"test" => $this->test,
			"judged_by" => $this->judged_by
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
			parent::toUnixTime(array("time"));
	}

	/**
	  *  [Campo no documentado]
	  * Llave Primaria
	  * Auto Incremento
	  * @access public
	  * @var int(11)
	  */
	public $run_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $user_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $problem_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $contest_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var char(32)
	  */
	public $guid;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var enum('c','cpp','java','py','rb','pl','cs','pas','kp','kj','cat','hs','cpp11')
	  */
	public $language;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var enum('new','waiting','compiling','running','ready')
	  */
	public $status;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var enum('AC','PA','PE','WA','TLE','OLE','MLE','RTE','RFE','CE','JE')
	  */
	public $verdict;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $runtime;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $penalty;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $memory;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var double
	  */
	public $score;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var double
	  */
	public $contest_score;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var timestamp
	  */
	public $time;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $submit_delay;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var tinyint(1)
	  */
	public $test;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var char(32)
	  */
	public $judged_by;
}
