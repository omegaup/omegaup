<?php
/** Value Object file for table Contests_Users.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
  * 
  */

class ContestsUsers extends VO
{
	/**
	  * Constructor de ContestsUsers
	  * 
	  * Para construir un objeto de tipo ContestsUsers debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return ContestsUsers
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['user_id']) ){
				$this->user_id = $data['user_id'];
			}
			if( isset($data['contest_id']) ){
				$this->contest_id = $data['contest_id'];
			}
			if( isset($data['access_time']) ){
				$this->access_time = $data['access_time'];
			}
			if( isset($data['score']) ){
				$this->score = $data['score'];
			}
			if( isset($data['time']) ){
				$this->time = $data['time'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto ContestsUsers en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"user_id" => $this->user_id,
			"contest_id" => $this->contest_id,
			"access_time" => $this->access_time,
			"score" => $this->score,
			"time" => $this->time
		); 
	return json_encode($vec); 
	}
	
	/**
	  * user_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $user_id;

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
	  * access_time
	  * 
	  * Hora a la que entrÃ³ el usuario al concurso<br>
	  * @access protected
	  * @var timestamp
	  */
	protected $access_time;

	/**
	  * score
	  * 
	  * Ãndica el puntaje que obtuvo el usuario en el concurso<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $score;

	/**
	  * time
	  * 
	  * Ãndica el tiempo que acumulo en usuario en el concurso<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $time;

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
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setUserId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setUserId( $user_id )
	{
		$this->user_id = $user_id;
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
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setContestId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setContestId( $contest_id )
	{
		$this->contest_id = $contest_id;
	}

	/**
	  * getAccessTime
	  * 
	  * Get the <i>access_time</i> property for this object. Donde <i>access_time</i> es Hora a la que entrÃ³ el usuario al concurso
	  * @return timestamp
	  */
	final public function getAccessTime()
	{
		return $this->access_time;
	}

	/**
	  * setAccessTime( $access_time )
	  * 
	  * Set the <i>access_time</i> property for this object. Donde <i>access_time</i> es Hora a la que entrÃ³ el usuario al concurso.
	  * Una validacion basica se hara aqui para comprobar que <i>access_time</i> es de tipo <i>timestamp</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param timestamp
	  */
	final public function setAccessTime( $access_time )
	{
		$this->access_time = $access_time;
	}

	/**
	  * getScore
	  * 
	  * Get the <i>score</i> property for this object. Donde <i>score</i> es Ãndica el puntaje que obtuvo el usuario en el concurso
	  * @return int(11)
	  */
	final public function getScore()
	{
		return $this->score;
	}

	/**
	  * setScore( $score )
	  * 
	  * Set the <i>score</i> property for this object. Donde <i>score</i> es Ãndica el puntaje que obtuvo el usuario en el concurso.
	  * Una validacion basica se hara aqui para comprobar que <i>score</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setScore( $score )
	{
		$this->score = $score;
	}

	/**
	  * getTime
	  * 
	  * Get the <i>time</i> property for this object. Donde <i>time</i> es Ãndica el tiempo que acumulo en usuario en el concurso
	  * @return int(11)
	  */
	final public function getTime()
	{
		return $this->time;
	}

	/**
	  * setTime( $time )
	  * 
	  * Set the <i>time</i> property for this object. Donde <i>time</i> es Ãndica el tiempo que acumulo en usuario en el concurso.
	  * Una validacion basica se hara aqui para comprobar que <i>time</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setTime( $time )
	{
		$this->time = $time;
	}



	/**
	  * Converts date fields to timestamps
	  * 
	  **/
	public function toUnixTime( array $fields = array() ){
		if(count($fields) > 0 )
			parent::toUnixTime( $fields );
		else
			parent::toUnixTime( array( "access_time" ) );
	}
}
