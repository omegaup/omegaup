<?php
/** Value Object file for table Users_Badges.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
  * 
  */

class UsersBadges extends VO
{
	/**
	  * Constructor de UsersBadges
	  * 
	  * Para construir un objeto de tipo UsersBadges debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return UsersBadges
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['badge_id']) ){
				$this->badge_id = $data['badge_id'];
			}
			if( isset($data['user_id']) ){
				$this->user_id = $data['user_id'];
			}
			if( isset($data['time']) ){
				$this->time = $data['time'];
			}
			if( isset($data['last_problem_id']) ){
				$this->last_problem_id = $data['last_problem_id'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto UsersBadges en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"badge_id" => $this->badge_id,
			"user_id" => $this->user_id,
			"time" => $this->time,
			"last_problem_id" => $this->last_problem_id
		); 
	return json_encode($vec); 
	}
	
	/**
	  * badge_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $badge_id;

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
	  * time
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var timestamp
	  */
	protected $time;

	/**
	  * last_problem_id
	  * 
	  * Este campo guarda el ultimo problema que logro que se desbloqueara el badge, just for fun.<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $last_problem_id;

	/**
	  * getBadgeId
	  * 
	  * Get the <i>badge_id</i> property for this object. Donde <i>badge_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getBadgeId()
	{
		return $this->badge_id;
	}

	/**
	  * setBadgeId( $badge_id )
	  * 
	  * Set the <i>badge_id</i> property for this object. Donde <i>badge_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>badge_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setBadgeId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setBadgeId( $badge_id )
	{
		$this->badge_id = $badge_id;
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
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setUserId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setUserId( $user_id )
	{
		$this->user_id = $user_id;
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

	/**
	  * getLastProblemId
	  * 
	  * Get the <i>last_problem_id</i> property for this object. Donde <i>last_problem_id</i> es Este campo guarda el ultimo problema que logro que se desbloqueara el badge, just for fun.
	  * @return int(11)
	  */
	final public function getLastProblemId()
	{
		return $this->last_problem_id;
	}

	/**
	  * setLastProblemId( $last_problem_id )
	  * 
	  * Set the <i>last_problem_id</i> property for this object. Donde <i>last_problem_id</i> es Este campo guarda el ultimo problema que logro que se desbloqueara el badge, just for fun..
	  * Una validacion basica se hara aqui para comprobar que <i>last_problem_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setLastProblemId( $last_problem_id )
	{
		$this->last_problem_id = $last_problem_id;
	}


	/**
	  * Converts date fields to timestamps
	  * 
	  **/
	public function toUnixTime( array $fields = array() ){
		if(count($fields) > 0 )
			parent::toUnixTime( $fields );
		else
			parent::toUnixTime( array( "time" ) );
	}
}
