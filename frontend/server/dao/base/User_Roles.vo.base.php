<?php
/** Value Object file for table User_Roles.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alan@caffeina.mx
  * @access public
  * @package docs
  * 
  */

class UserRoles extends VO
{
	/**
	  * Constructor de UserRoles
	  * 
	  * Para construir un objeto de tipo UserRoles debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return UserRoles
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['user_id']) ){
				$this->user_id = $data['user_id'];
			}
			if( isset($data['role_id']) ){
				$this->role_id = $data['role_id'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto UserRoles en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"user_id" => $this->user_id,
			"role_id" => $this->role_id
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
	  * role_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $role_id;

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
	  * getRoleId
	  * 
	  * Get the <i>role_id</i> property for this object. Donde <i>role_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getRoleId()
	{
		return $this->role_id;
	}

	/**
	  * setRoleId( $role_id )
	  * 
	  * Set the <i>role_id</i> property for this object. Donde <i>role_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>role_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setRoleId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setRoleId( $role_id )
	{
		$this->role_id = $role_id;
	}

}
