<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Value Object file for table Contests.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @access public
  * 
  */

class Contests extends VO
{
	/**
	  * Constructor de Contests
	  * 
	  * Para construir un objeto de tipo Contests debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  */
	function __construct($data = NULL)
	{
		if (isset($data))
		{
			if (is_string($data))
				$data = self::object_to_array(json_decode($data));


			if (isset($data['contest_id'])) {
				$this->contest_id = $data['contest_id'];
			}
			if (isset($data['title'])) {
				$this->title = $data['title'];
			}
			if (isset($data['description'])) {
				$this->description = $data['description'];
			}
			if (isset($data['start_time'])) {
				$this->start_time = $data['start_time'];
			}
			if (isset($data['finish_time'])) {
				$this->finish_time = $data['finish_time'];
			}
			if (isset($data['window_length'])) {
				$this->window_length = $data['window_length'];
			}
			if (isset($data['director_id'])) {
				$this->director_id = $data['director_id'];
			}
			if (isset($data['rerun_id'])) {
				$this->rerun_id = $data['rerun_id'];
			}
			if (isset($data['public'])) {
				$this->public = $data['public'];
			}
			if (isset($data['alias'])) {
				$this->alias = $data['alias'];
			}
			if (isset($data['scoreboard'])) {
				$this->scoreboard = $data['scoreboard'];
			}
			if (isset($data['points_decay_factor'])) {
				$this->points_decay_factor = $data['points_decay_factor'];
			}
			if (isset($data['partial_score'])) {
				$this->partial_score = $data['partial_score'];
			}
			if (isset($data['submissions_gap'])) {
				$this->submissions_gap = $data['submissions_gap'];
			}
			if (isset($data['feedback'])) {
				$this->feedback = $data['feedback'];
			}
			if (isset($data['penalty'])) {
				$this->penalty = $data['penalty'];
			}
			if (isset($data['penalty_time_start'])) {
				$this->penalty_time_start = $data['penalty_time_start'];
			}
			if (isset($data['penalty_calc_policy'])) {
				$this->penalty_calc_policy = $data['penalty_calc_policy'];
			}
			if (isset($data['show_scoreboard_after'])) {
				$this->show_scoreboard_after = $data['show_scoreboard_after'];
			}
			if (isset($data['scoreboard_url'])) {
				$this->scoreboard_url = $data['scoreboard_url'];
			}
			if (isset($data['scoreboard_url_admin'])) {
				$this->scoreboard_url_admin = $data['scoreboard_url_admin'];
			}
			if (isset($data['urgent'])) {
				$this->urgent = $data['urgent'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Contests en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"contest_id" => $this->contest_id,
			"title" => $this->title,
			"description" => $this->description,
			"start_time" => $this->start_time,
			"finish_time" => $this->finish_time,
			"window_length" => $this->window_length,
			"director_id" => $this->director_id,
			"rerun_id" => $this->rerun_id,
			"public" => $this->public,
			"alias" => $this->alias,
			"scoreboard" => $this->scoreboard,
			"points_decay_factor" => $this->points_decay_factor,
			"partial_score" => $this->partial_score,
			"submissions_gap" => $this->submissions_gap,
			"feedback" => $this->feedback,
			"penalty" => $this->penalty,
			"penalty_time_start" => $this->penalty_time_start,
			"penalty_calc_policy" => $this->penalty_calc_policy,
			"show_scoreboard_after" => $this->show_scoreboard_after,
			"scoreboard_url" => $this->scoreboard_url,
			"scoreboard_url_admin" => $this->scoreboard_url_admin,
			"urgent" => $this->urgent
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
			parent::toUnixTime(array("start_time", "finish_time"));
	}

	/**
	  * El identificador unico para cada concurso
	  * Llave Primaria
	  * Auto Incremento
	  * @access public
	  * @var int(11)
	  */
	public $contest_id;

	/**
	  * El titulo que aparecera en cada concurso
	  * @access public
	  * @var varchar(256)
	  */
	public $title;

	/**
	  * Una breve descripcion de cada concurso.
	  * @access public
	  * @var tinytext
	  */
	public $description;

	/**
	  * Hora de inicio de este concurso
	  * @access public
	  * @var timestamp
	  */
	public $start_time;

	/**
	  * Hora de finalizacion de este concurso
	  * @access public
	  * @var timestamp
	  */
	public $finish_time;

	/**
	  * Indica el tiempo que tiene el usuario para envíar solución, si es NULL entonces será durante todo el tiempo del concurso
	  * @access public
	  * @var int(11)
	  */
	public $window_length;

	/**
	  * el userID del usuario que creo este concurso
	  * @access public
	  * @var int(11)
	  */
	public $director_id;

	/**
	  * Este campo es para las repeticiones de algún concurso
	  * @access public
	  * @var int(11)
	  */
	public $rerun_id;

	/**
	  * False implica concurso cerrado, ver la tabla ConcursantesConcurso
	  * @access public
	  * @var tinyint(1)
	  */
	public $public;

	/**
	  * Almacenará el token necesario para acceder al concurso
	  * @access public
	  * @var varchar(32)
	  */
	public $alias;

	/**
	  * Entero del 0 al 100, indicando el porcentaje de tiempo que el scoreboard será visible
	  * @access public
	  * @var int(11)
	  */
	public $scoreboard;

	/**
	  * El factor de decaimiento de los puntos de este concurso. El default es 0 (no decae). TopCoder es 0.7
	  * @access public
	  * @var double
	  */
	public $points_decay_factor;

	/**
	  * Verdadero si el usuario recibirá puntaje parcial para problemas no resueltos en todos los casos
	  * @access public
	  * @var tinyint(1)
	  */
	public $partial_score;

	/**
	  * Tiempo mínimo en segundos que debe de esperar un usuario despues de realizar un envío para hacer otro
	  * @access public
	  * @var int(11)
	  */
	public $submissions_gap;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var enum('no','yes','partial')
	  */
	public $feedback;

	/**
	  * Entero indicando el número de minutos con que se penaliza por recibir un no-accepted
	  * @access public
	  * @var int(11)
	  */
	public $penalty;

	/**
	  * Indica el momento cuando se inicia a contar el timpo: cuando inicia el concurso o cuando se abre el problema
	  * @access public
	  * @var enum('contest','problem',
	  */
	public $penalty_time_start;

	/**
	  * Indica como afecta el penalty al score.
	  * @access public
	  * @var enum('sum',
	  */
	public $penalty_calc_policy;

	/**
	  * 'Mostrar el scoreboard automáticamente después del concurso
	  * @access public
	  * @var BOOL
	  */
	public $show_scoreboard_after;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var VARCHAR(
	  */
	public $scoreboard_url;

	/**
	  *  [Campo no documentado]
	  * @access public
	  * @var VARCHAR(
	  */
	public $scoreboard_url_admin;

	/**
	  * Indica si el concurso es de alta prioridad y requiere mejor QoS.
	  * @access public
	  * @var tinyint(1)
	  */
	public $urgent;
}
