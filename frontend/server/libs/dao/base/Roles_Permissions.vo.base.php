<?php
/** Value Object file for table Roles_Permissions.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
  * 
  */

class RolesPermissions extends VO
{
	/**
	  * Constructor de RolesPermissions
	  * 
	  * Para construir un objeto de tipo RolesPermissions debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return RolesPermissions
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['role_id']) ){
				$this->role_id = $data['role_id'];
			}
			if( isset($data['permission_id']) ){
				$this->permission_id = $data['permission_id'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto RolesPermissions en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"role_id" => $this->role_id,
			"permission_id" => $this->permission_id
		); 
	return json_encode($vec); 
	}
	
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
	  * permission_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $permission_id;

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

	/**
	  * getPermissionId
	  * 
	  * Get the <i>permission_id</i> property for this object. Donde <i>permission_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getPermissionId()
	{
		return $this->permission_id;
	}

	/**
	  * setPermissionId( $permission_id )
	  * 
	  * Set the <i>permission_id</i> property for this object. Donde <i>permission_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>permission_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setPermissionId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setPermissionId( $permission_id )
	{
		$this->permission_id = $permission_id;
	}

}
