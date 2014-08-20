<?php
/** Value Object file for table Groups.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
  * 
  */

class Groups extends VO
{
	/**
	  * Constructor de Groups
	  * 
	  * Para construir un objeto de tipo Groups debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Groups
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
                    if(is_string($data))
                        $data = self::object_to_array(json_decode($data));


			if( isset($data['group_id']) ){
				$this->group_id = $data['group_id'];
			}
			if( isset($data['owner_id']) ){
				$this->owner_id = $data['owner_id'];
			}
			if( isset($data['create_time']) ){
				$this->create_time = $data['create_time'];
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
	  * Este metodo permite tratar a un objeto Groups en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"group_id" => $this->group_id,
			"owner_id" => $this->owner_id,
			"create_time" => $this->create_time,
			"name" => $this->name,
			"description" => $this->description
		); 
	return json_encode($vec); 
	}
	
	/**
	  * group_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * <b>Auto Incremento</b><br>
	  * @access public
	  * @var int(11)
	  */
	public $group_id;

	/**
	  * owner_id
	  * 
	  *  [Campo no documentado]<br>
	  * @access public
	  * @var int(11)
	  */
	public $owner_id;

	/**
	  * create_time
	  * 
	  *  [Campo no documentado]<br>
	  * @access public
	  * @var timestamp
	  */
	public $create_time;

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
	  * <br><br>Esta propiedad se mapea con un campo que es de <b>Auto Incremento</b> !<br>
	  * No deberias usar setGroupId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setGroupId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setGroupId( $group_id )
	{
		$this->group_id = $group_id;
	}

	/**
	  * getOwnerId
	  * 
	  * Get the <i>owner_id</i> property for this object. Donde <i>owner_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getOwnerId()
	{
		return $this->owner_id;
	}

	/**
	  * setOwnerId( $owner_id )
	  * 
	  * Set the <i>owner_id</i> property for this object. Donde <i>owner_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>owner_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setOwnerId( $owner_id )
	{
		$this->owner_id = $owner_id;
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
