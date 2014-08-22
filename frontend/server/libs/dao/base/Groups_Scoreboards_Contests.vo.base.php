<?php
/** Value Object file for table Groups_Scoreboards_Contests.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
  * 
  */

class GroupsScoreboardsContests extends VO
{
	/**
	  * Constructor de GroupsScoreboardsContests
	  * 
	  * Para construir un objeto de tipo GroupsScoreboardsContests debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return GroupsScoreboardsContests
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
                    if(is_string($data))
                        $data = self::object_to_array(json_decode($data));


			if( isset($data['group_scoreboard_id']) ){
				$this->group_scoreboard_id = $data['group_scoreboard_id'];
			}
			if( isset($data['contest_id']) ){
				$this->contest_id = $data['contest_id'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto GroupsScoreboardsContests en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"group_scoreboard_id" => $this->group_scoreboard_id,
			"contest_id" => $this->contest_id
		); 
	return json_encode($vec); 
	}
	
	/**
	  * group_scoreboard_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access public
	  * @var int(11)
	  */
	public $group_scoreboard_id;

	/**
	  * contest_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access public
	  * @var int(11)
	  */
	public $contest_id;

	/**
	  * getGroupScoreboardId
	  * 
	  * Get the <i>group_scoreboard_id</i> property for this object. Donde <i>group_scoreboard_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getGroupScoreboardId()
	{
		return $this->group_scoreboard_id;
	}

	/**
	  * setGroupScoreboardId( $group_scoreboard_id )
	  * 
	  * Set the <i>group_scoreboard_id</i> property for this object. Donde <i>group_scoreboard_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>group_scoreboard_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setGroupScoreboardId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setGroupScoreboardId( $group_scoreboard_id )
	{
		$this->group_scoreboard_id = $group_scoreboard_id;
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

}
