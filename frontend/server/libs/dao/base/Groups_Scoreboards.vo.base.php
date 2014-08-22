<?php
/** Value Object file for table Groups_Scoreboards.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
  * 
  */

class GroupsScoreboards extends VO
{
	/**
	  * Constructor de GroupsScoreboards
	  * 
	  * Para construir un objeto de tipo GroupsScoreboards debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return GroupsScoreboards
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
			if( isset($data['group_id']) ){
				$this->group_id = $data['group_id'];
			}
			if( isset($data['create_time']) ){
				$this->create_time = $data['create_time'];
			}
			if( isset($data['alias']) ){
				$this->alias = $data['alias'];
			}
			if( isset($data['name']) ){
				$this->name = $data['name'];
			}
			if( isset($data['description']) ){
				$this->description = $data['description'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto GroupsScoreboards en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"group_scoreboard_id" => $this->group_scoreboard_id,
			"group_id" => $this->group_id,
			"create_time" => $this->create_time,
			"alias" => $this->alias,
			"name" => $this->name,
			"description" => $this->description
		); 
	return json_encode($vec); 
	}
	
	/**
	  * group_scoreboard_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * <b>Auto Incremento</b><br>
	  * @access public
	  * @var int(11)
	  */
	public $group_scoreboard_id;

	/**
	  * group_id
	  * 
	  *  [Campo no documentado]<br>
	  * @access public
	  * @var int(11)
	  */
	public $group_id;

	/**
	  * create_time
	  * 
	  *  [Campo no documentado]<br>
	  * @access public
	  * @var timestamp
	  */
	public $create_time;

	/**
	  * alias
	  * 
	  *  [Campo no documentado]<br>
	  * @access public
	  * @var varchar(50)
	  */
	public $alias;

	/**
	  * name
	  * 
	  *  [Campo no documentado]<br>
	  * @access public
	  * @var varchar(50)
	  */
	public $name;

	/**
	  * description
	  * 
	  *  [Campo no documentado]<br>
	  * @access public
	  * @var varchar(256)
	  */
	public $description;

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
	  * <br><br>Esta propiedad se mapea con un campo que es de <b>Auto Incremento</b> !<br>
	  * No deberias usar setGroupScoreboardId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setGroupScoreboardId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setGroupScoreboardId( $group_scoreboard_id )
	{
		$this->group_scoreboard_id = $group_scoreboard_id;
	}

	/**
	  * getGroupId
	  * 
	  * Get the <i>group_id</i> property for this object. Donde <i>group_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getGroupId()
	{
		return $this->group_id;
	}

	/**
	  * setGroupId( $group_id )
	  * 
	  * Set the <i>group_id</i> property for this object. Donde <i>group_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>group_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setGroupId( $group_id )
	{
		$this->group_id = $group_id;
	}

	/**
	  * getCreateTime
	  * 
	  * Get the <i>create_time</i> property for this object. Donde <i>create_time</i> es  [Campo no documentado]
	  * @return timestamp
	  */
	final public function getCreateTime()
	{
		return $this->create_time;
	}

	/**
	  * setCreateTime( $create_time )
	  * 
	  * Set the <i>create_time</i> property for this object. Donde <i>create_time</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>create_time</i> es de tipo <i>timestamp</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param timestamp
	  */
	final public function setCreateTime( $create_time )
	{
		$this->create_time = $create_time;
	}

	/**
	  * getAlias
	  * 
	  * Get the <i>alias</i> property for this object. Donde <i>alias</i> es  [Campo no documentado]
	  * @return varchar(50)
	  */
	final public function getAlias()
	{
		return $this->alias;
	}

	/**
	  * setAlias( $alias )
	  * 
	  * Set the <i>alias</i> property for this object. Donde <i>alias</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>alias</i> es de tipo <i>varchar(50)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(50)
	  */
	final public function setAlias( $alias )
	{
		$this->alias = $alias;
	}

	/**
	  * getName
	  * 
	  * Get the <i>name</i> property for this object. Donde <i>name</i> es  [Campo no documentado]
	  * @return varchar(50)
	  */
	final public function getName()
	{
		return $this->name;
	}

	/**
	  * setName( $name )
	  * 
	  * Set the <i>name</i> property for this object. Donde <i>name</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>name</i> es de tipo <i>varchar(50)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(50)
	  */
	final public function setName( $name )
	{
		$this->name = $name;
	}

	/**
	  * getDescription
	  * 
	  * Get the <i>description</i> property for this object. Donde <i>description</i> es  [Campo no documentado]
	  * @return varchar(256)
	  */
	final public function getDescription()
	{
		return $this->description;
	}

	/**
	  * setDescription( $description )
	  * 
	  * Set the <i>description</i> property for this object. Donde <i>description</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>description</i> es de tipo <i>varchar(256)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(256)
	  */
	final public function setDescription( $description )
	{
		$this->description = $description;
	}

}
