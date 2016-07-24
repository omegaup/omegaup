<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Problems.
  *
  * VO does not have any behaviour.
  * @access public
  *
  */

class Problems extends VO
{
	/**
	  * Constructor de Problems
	  *
	  * Para construir un objeto de tipo Problems debera llamarse a el constructor
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
			if (isset($data['public'])) {
				$this->public = $data['public'];
			}
			if (isset($data['author_id'])) {
				$this->author_id = $data['author_id'];
			}
			if (isset($data['title'])) {
				$this->title = $data['title'];
			}
			if (isset($data['alias'])) {
				$this->alias = $data['alias'];
			}
			if (isset($data['validator'])) {
				$this->validator = $data['validator'];
			}
			if (isset($data['languages'])) {
				$this->languages = $data['languages'];
			}
			if (isset($data['server'])) {
				$this->server = $data['server'];
			}
			if (isset($data['remote_id'])) {
				$this->remote_id = $data['remote_id'];
			}
			if (isset($data['time_limit'])) {
				$this->time_limit = $data['time_limit'];
			}
			if (isset($data['validator_time_limit'])) {
				$this->validator_time_limit = $data['validator_time_limit'];
			}
			if (isset($data['overall_wall_time_limit'])) {
				$this->overall_wall_time_limit = $data['overall_wall_time_limit'];
			}
			if (isset($data['extra_wall_time'])) {
				$this->extra_wall_time = $data['extra_wall_time'];
			}
			if (isset($data['memory_limit'])) {
				$this->memory_limit = $data['memory_limit'];
			}
			if (isset($data['output_limit'])) {
				$this->output_limit = $data['output_limit'];
			}
			if (isset($data['stack_limit'])) {
				$this->stack_limit = $data['stack_limit'];
			}
			if (isset($data['visits'])) {
				$this->visits = $data['visits'];
			}
			if (isset($data['submissions'])) {
				$this->submissions = $data['submissions'];
			}
			if (isset($data['accepted'])) {
				$this->accepted = $data['accepted'];
			}
			if (isset($data['difficulty'])) {
				$this->difficulty = $data['difficulty'];
			}
			if (isset($data['creation_date'])) {
				$this->creation_date = $data['creation_date'];
			}
			if (isset($data['source'])) {
				$this->source = $data['source'];
			}
			if (isset($data['order'])) {
				$this->order = $data['order'];
			}
			if (isset($data['tolerance'])) {
				$this->tolerance = $data['tolerance'];
			}
			if (isset($data['slow'])) {
				$this->slow = $data['slow'];
			}
			if (isset($data['deprecated'])) {
				$this->deprecated = $data['deprecated'];
			}
			if (isset($data['email_clarifications'])) {
				$this->email_clarifications = $data['email_clarifications'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  *
	  * Este metodo permite tratar a un objeto Problems en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String
	  */
	public function __toString( )
	{
		$vec = array(
			"problem_id" => $this->problem_id,
			"public" => $this->public,
			"author_id" => $this->author_id,
			"title" => $this->title,
			"alias" => $this->alias,
			"validator" => $this->validator,
			"languages" => $this->languages,
			"server" => $this->server,
			"remote_id" => $this->remote_id,
			"time_limit" => $this->time_limit,
			"validator_time_limit" => $this->validator_time_limit,
			"overall_wall_time_limit" => $this->overall_wall_time_limit,
			"extra_wall_time" => $this->extra_wall_time,
			"memory_limit" => $this->memory_limit,
			"output_limit" => $this->output_limit,
			"stack_limit" => $this->stack_limit,
			"visits" => $this->visits,
			"submissions" => $this->submissions,
			"accepted" => $this->accepted,
			"difficulty" => $this->difficulty,
			"creation_date" => $this->creation_date,
			"source" => $this->source,
			"order" => $this->order,
			"tolerance" => $this->tolerance,
			"slow" => $this->slow,
			"deprecated" => $this->deprecated,
			"email_clarifications" => $this->email_clarifications
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
			parent::toUnixTime(array("creation_date"));
	}

	/**
	  *  [Campo no documentado]
	  * Llave Primaria
	  * Auto Incremento
	  * @access public
	  * @var int(11)
	  */
	public $problem_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var tinyint(1)
	  */
	public $public;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $author_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var varchar(256)
	  */
	public $title;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var varchar(32)
	  */
	public $alias;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var enum('token','token-caseless','token-numeric','custom','literal')
	  */
	public $validator;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var set('c','cpp','java','py','rb','pl','cs','pas','kp','kj','cat','hs','cpp11')
	  */
	public $languages;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var enum('uva','livearchive','pku','tju','spoj')
	  */
	public $server;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var varchar(10)
	  */
	public $remote_id;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $time_limit;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $validator_time_limit;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $overall_wall_time_limit;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $extra_wall_time;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $memory_limit;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $output_limit;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $stack_limit;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $visits;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $submissions;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var int(11)
	  */
	public $accepted;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var double
	  */
	public $difficulty;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var timestamp
	  */
	public $creation_date;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var varchar(256)
	  */
	public $source;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var enum('normal','inverse')
	  */
	public $order;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var double
	  */
	public $tolerance;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var tinyint(1)
	  */
	public $slow;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var tinyint(1)
	  */
	public $deprecated;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var tinyint(1)
	  */
	public $email_clarifications;
}
