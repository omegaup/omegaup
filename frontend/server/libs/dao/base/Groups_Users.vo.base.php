<?php
/** Value Object file for table Groups_Users.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
  * 
  */

class GroupsUsers extends VO
{
	/**
	  * Constructor de GroupsUsers
	  * 
	  * Para construir un objeto de tipo GroupsUsers debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return GroupsUsers
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
			if( isset($data['user_id']) ){
				$this->user_id = $data['user_id'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto GroupsUsers en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"group_id" => $this->group_id,
			"user_id" => $this->user_id
		); 
	return json_encode($vec); 
	}
	
	/**
	  * group_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access public
	  * @var int(11)
	  */
	public $group_id;

	/**
	  * user_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access public
	  * @var int(11)
	  */
	public $user_id;

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
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setGroupId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setGroupId( $group_id )
	{
		$this->group_id = $group_id;
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

}
