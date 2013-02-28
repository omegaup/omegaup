<?php

/** Value Object file for table Contest_Problems.
 * 
 * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
 * @author alanboy
 * @access public
 * @package docs
 * 
 */
class ContestProblems extends VO {

	/**
	 * Constructor de ContestProblems
	 * 
	 * Para construir un objeto de tipo ContestProblems debera llamarse a el constructor 
	 * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	 * cuyos campos son iguales a las variables que constituyen a este objeto.
	 * @return ContestProblems
	 */
	function __construct($data = NULL) {
		if (isset($data)) {
			if (isset($data['contest_id'])) {
				$this->contest_id = $data['contest_id'];
			}
			if (isset($data['problem_id'])) {
				$this->problem_id = $data['problem_id'];
			}
			if (isset($data['points'])) {
				$this->points = $data['points'];
			}
			if (isset($data['order'])) {
				$this->order = $data['order'];
			}
		}
	}

	/**
	 * Obtener una representacion en String
	 * 
	 * Este metodo permite tratar a un objeto ContestProblems en forma de cadena.
	 * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	 * @return String 
	 */
	public function __toString() {
		$vec = array(
			"contest_id" => $this->contest_id,
			"problem_id" => $this->problem_id,
			"points" => $this->points
		);
		return json_encode($vec);
	}

	/**
	 * contest_id
	 * 
	 *  [Campo no documentado]<br>
	 * <b>Llave Primaria</b><br>
	 * @access protected
	 * @var int(11)
	 */
	protected $contest_id;

	/**
	 * problem_id
	 * 
	 *  [Campo no documentado]<br>
	 * <b>Llave Primaria</b><br>
	 * @access protected
	 * @var int(11)
	 */
	protected $problem_id;

	/**
	 * points
	 * 
	 *  [Campo no documentado]<br>
	 * @access protected
	 * @var double
	 */
	protected $points;
	protected $order;

	/**
	 * getContestId
	 * 
	 * Get the <i>contest_id</i> property for this object. Donde <i>contest_id</i> es  [Campo no documentado]
	 * @return int(11)
	 */
	final public function getContestId() {
		return $this->contest_id;
	}

	/**
	 * setContestId( $contest_id )
	 * 
	 * Set the <i>contest_id</i> property for this object. Donde <i>contest_id</i> es  [Campo no documentado].
	 * Una validacion basica se hara aqui para comprobar que <i>contest_id</i> es de tipo <i>int(11)</i>. 
	 * Si esta validacion falla, se arrojara... algo. 
	 * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	 * No deberias usar setContestId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	 * @param int(11)
	 */
	final public function setContestId($contest_id) {
		$this->contest_id = $contest_id;
	}

	/**
	 * getProblemId
	 * 
	 * Get the <i>problem_id</i> property for this object. Donde <i>problem_id</i> es  [Campo no documentado]
	 * @return int(11)
	 */
	final public function getProblemId() {
		return $this->problem_id;
	}

	/**
	 * setProblemId( $problem_id )
	 * 
	 * Set the <i>problem_id</i> property for this object. Donde <i>problem_id</i> es  [Campo no documentado].
	 * Una validacion basica se hara aqui para comprobar que <i>problem_id</i> es de tipo <i>int(11)</i>. 
	 * Si esta validacion falla, se arrojara... algo. 
	 * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	 * No deberias usar setProblemId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	 * @param int(11)
	 */
	final public function setProblemId($problem_id) {
		$this->problem_id = $problem_id;
	}

	/**
	 * getPoints
	 * 
	 * Get the <i>points</i> property for this object. Donde <i>points</i> es  [Campo no documentado]
	 * @return double
	 */
	final public function getPoints() {
		return $this->points;
	}

	/**
	 * setPoints( $points )
	 * 
	 * Set the <i>points</i> property for this object. Donde <i>points</i> es  [Campo no documentado].
	 * Una validacion basica se hara aqui para comprobar que <i>points</i> es de tipo <i>double</i>. 
	 * Si esta validacion falla, se arrojara... algo. 
	 * @param double
	 */
	final public function setPoints($points) {
		$this->points = $points;
	}

	final public function getOrder() {
		return $this->order;
	}

	final public function serOrder($order) {
		$this->order = $order;
	}

}
