<?php
/** Value Object file for table Runs.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
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
	  * @return Runs
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['run_id']) ){
				$this->run_id = $data['run_id'];
			}
			if( isset($data['user_id']) ){
				$this->user_id = $data['user_id'];
			}
			if( isset($data['problem_id']) ){
				$this->problem_id = $data['problem_id'];
			}
			if( isset($data['contest_id']) ){
				$this->contest_id = $data['contest_id'];
			}
			if( isset($data['guid']) ){
				$this->guid = $data['guid'];
			}
			if( isset($data['language']) ){
				$this->language = $data['language'];
			}
			if( isset($data['status']) ){
				$this->status = $data['status'];
			}
			if( isset($data['veredict']) ){
				$this->veredict = $data['veredict'];
			}
			if( isset($data['runtime']) ){
				$this->runtime = $data['runtime'];
			}
			if( isset($data['memory']) ){
				$this->memory = $data['memory'];
			}
			if( isset($data['score']) ){
				$this->score = $data['score'];
			}
			if( isset($data['contest_score']) ){
				$this->contest_score = $data['contest_score'];
			}
			if( isset($data['ip']) ){
				$this->ip = $data['ip'];
			}
			if( isset($data['time']) ){
				$this->time = $data['time'];
			}
                        if( isset($data['submit_delay']) ){
				$this->submit_delay = $data['submit_delay'];
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
			"veredict" => $this->veredict,
			"runtime" => $this->runtime,
			"memory" => $this->memory,
			"score" => $this->score,
			"contest_score" => $this->contest_score,
			"ip" => $this->ip,
			"time" => $this->time
		); 
	return json_encode($vec); 
	}
	
	/**
	  * run_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * <b>Auto Incremento</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $run_id;

	/**
	  * user_id
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $user_id;

	/**
	  * problem_id
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $problem_id;

	/**
	  * contest_id
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $contest_id;

	/**
	  * guid
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var char(32)
	  */
	protected $guid;

	/**
	  * language
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var enum('c','cpp','java','py','rb','pl','cs','p')
	  */
	protected $language;

	/**
	  * status
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var enum('new','waiting','compiling','running','ready')
	  */
	protected $status;

	/**
	  * veredict
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var enum('AC','PA','PE','WA','TLE','OLE','MLE','RTE','RFE','CE','JE')
	  */
	protected $veredict;

	/**
	  * runtime
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $runtime;

	/**
	  * memory
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $memory;

	/**
	  * score
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var double
	  */
	protected $score;

	/**
	  * contest_score
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var double
	  */
	protected $contest_score;

	/**
	  * ip
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var char(15)
	  */
	protected $ip;

	/**
	  * time
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var timestamp
	  */
	protected $time;
        
        protected $submit_delay;

	/**
	  * getRunId
	  * 
	  * Get the <i>run_id</i> property for this object. Donde <i>run_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getRunId()
	{
		return $this->run_id;
	}

	/**
	  * setRunId( $run_id )
	  * 
	  * Set the <i>run_id</i> property for this object. Donde <i>run_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>run_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es de <b>Auto Incremento</b> !<br>
	  * No deberias usar setRunId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setRunId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setRunId( $run_id )
	{
		$this->run_id = $run_id;
	}

	/**
	  * getUserId
	  * 
	  * Get the <i>user_id</i> property for this object. Donde <i>user_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getUserId()
	{
		return $this->user_id;
	}

	/**
	  * setUserId( $user_id )
	  * 
	  * Set the <i>user_id</i> property for this object. Donde <i>user_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>user_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setUserId( $user_id )
	{
		$this->user_id = $user_id;
	}

	/**
	  * getProblemId
	  * 
	  * Get the <i>problem_id</i> property for this object. Donde <i>problem_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getProblemId()
	{
		return $this->problem_id;
	}

	/**
	  * setProblemId( $problem_id )
	  * 
	  * Set the <i>problem_id</i> property for this object. Donde <i>problem_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>problem_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setProblemId( $problem_id )
	{
		$this->problem_id = $problem_id;
	}

	/**
	  * getContestId
	  * 
	  * Get the <i>contest_id</i> property for this object. Donde <i>contest_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getContestId()
	{
		return $this->contest_id;
	}

	/**
	  * setContestId( $contest_id )
	  * 
	  * Set the <i>contest_id</i> property for this object. Donde <i>contest_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>contest_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setContestId( $contest_id )
	{
		$this->contest_id = $contest_id;
	}

	/**
	  * getGuid
	  * 
	  * Get the <i>guid</i> property for this object. Donde <i>guid</i> es  [Campo no documentado]
	  * @return char(32)
	  */
	final public function getGuid()
	{
		return $this->guid;
	}

	/**
	  * setGuid( $guid )
	  * 
	  * Set the <i>guid</i> property for this object. Donde <i>guid</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>guid</i> es de tipo <i>char(32)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param char(32)
	  */
	final public function setGuid( $guid )
	{
		$this->guid = $guid;
	}

	/**
	  * getLanguage
	  * 
	  * Get the <i>language</i> property for this object. Donde <i>language</i> es  [Campo no documentado]
	  * @return enum('c','cpp','java','py','rb','pl','cs','p')
	  */
	final public function getLanguage()
	{
		return $this->language;
	}

	/**
	  * setLanguage( $language )
	  * 
	  * Set the <i>language</i> property for this object. Donde <i>language</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>language</i> es de tipo <i>enum('c','cpp','java','py','rb','pl','cs','p')</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param enum('c','cpp','java','py','rb','pl','cs','p')
	  */
	final public function setLanguage( $language )
	{
		$this->language = $language;
	}

	/**
	  * getStatus
	  * 
	  * Get the <i>status</i> property for this object. Donde <i>status</i> es  [Campo no documentado]
	  * @return enum('new','waiting','compiling','running','ready')
	  */
	final public function getStatus()
	{
		return $this->status;
	}

	/**
	  * setStatus( $status )
	  * 
	  * Set the <i>status</i> property for this object. Donde <i>status</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>status</i> es de tipo <i>enum('new','waiting','compiling','running','ready')</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param enum('new','waiting','compiling','running','ready')
	  */
	final public function setStatus( $status )
	{
		$this->status = $status;
	}

	/**
	  * getVeredict
	  * 
	  * Get the <i>veredict</i> property for this object. Donde <i>veredict</i> es  [Campo no documentado]
	  * @return enum('AC','PA','PE','WA','TLE','OLE','MLE','RTE','RFE','CE','JE')
	  */
	final public function getVeredict()
	{
		return $this->veredict;
	}

	/**
	  * setVeredict( $veredict )
	  * 
	  * Set the <i>veredict</i> property for this object. Donde <i>veredict</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>veredict</i> es de tipo <i>enum('AC','PA','PE','WA','TLE','OLE','MLE','RTE','RFE','CE','JE')</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param enum('AC','PA','PE','WA','TLE','OLE','MLE','RTE','RFE','CE','JE')
	  */
	final public function setVeredict( $veredict )
	{
		$this->veredict = $veredict;
	}

	/**
	  * getRuntime
	  * 
	  * Get the <i>runtime</i> property for this object. Donde <i>runtime</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getRuntime()
	{
		return $this->runtime;
	}

	/**
	  * setRuntime( $runtime )
	  * 
	  * Set the <i>runtime</i> property for this object. Donde <i>runtime</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>runtime</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setRuntime( $runtime )
	{
		$this->runtime = $runtime;
	}

	/**
	  * getMemory
	  * 
	  * Get the <i>memory</i> property for this object. Donde <i>memory</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getMemory()
	{
		return $this->memory;
	}

	/**
	  * setMemory( $memory )
	  * 
	  * Set the <i>memory</i> property for this object. Donde <i>memory</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>memory</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setMemory( $memory )
	{
		$this->memory = $memory;
	}

	/**
	  * getScore
	  * 
	  * Get the <i>score</i> property for this object. Donde <i>score</i> es  [Campo no documentado]
	  * @return double
	  */
	final public function getScore()
	{
		return $this->score;
	}

	/**
	  * setScore( $score )
	  * 
	  * Set the <i>score</i> property for this object. Donde <i>score</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>score</i> es de tipo <i>double</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param double
	  */
	final public function setScore( $score )
	{
		$this->score = $score;
	}

	/**
	  * getContestScore
	  * 
	  * Get the <i>contest_score</i> property for this object. Donde <i>contest_score</i> es  [Campo no documentado]
	  * @return double
	  */
	final public function getContestScore()
	{
		return $this->contest_score;
	}

	/**
	  * setContestScore( $contest_score )
	  * 
	  * Set the <i>contest_score</i> property for this object. Donde <i>contest_score</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>contest_score</i> es de tipo <i>double</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param double
	  */
	final public function setContestScore( $contest_score )
	{
		$this->contest_score = $contest_score;
	}

	/**
	  * getIp
	  * 
	  * Get the <i>ip</i> property for this object. Donde <i>ip</i> es  [Campo no documentado]
	  * @return char(15)
	  */
	final public function getIp()
	{
		return $this->ip;
	}

	/**
	  * setIp( $ip )
	  * 
	  * Set the <i>ip</i> property for this object. Donde <i>ip</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>ip</i> es de tipo <i>char(15)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param char(15)
	  */
	final public function setIp( $ip )
	{
		$this->ip = $ip;
	}

	/**
	  * getTime
	  * 
	  * Get the <i>time</i> property for this object. Donde <i>time</i> es  [Campo no documentado]
	  * @return timestamp
	  */
	final public function getTime()
	{
		return $this->time;
	}

	/**
	  * setTime( $time )
	  * 
	  * Set the <i>time</i> property for this object. Donde <i>time</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>time</i> es de tipo <i>timestamp</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param timestamp
	  */
	final public function setTime( $time )
	{
		$this->time = $time;
	}
        
        final public function getSubmitDelay()
	{
		return $this->submit_delay;
	}
        
        final public function setSubmitDelay( $submit_delay )
	{
		$this->time = $submit_delay;
	}
        

}
