<?php
/** Value Object file for table Roles.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alan@caffeina.mx
  * @access public
  * @package docs
  * 
  */

class Roles extends VO
{
	/**
	  * Constructor de Roles
	  * 
	  * Para construir un objeto de tipo Roles debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Roles
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['role_id']) ){
				$this->role_id = $data['role_id'];
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
	  * Este metodo permite tratar a un objeto Roles en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"role_id" => $this->role_id,
			"name" => $this->name,
			"description" => $this->description
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
	  * name
	  * 
	  * El nombre corto del rol.<br>
	  * @access protected
	  * @var varchar(50)
	  */
	protected $name;

	/**
	  * description
	  * 
	  * La descripción humana del rol.<br>
	  * @access protected
	  * @var varchar(100)
	  */
	protected $description;

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
	  * getName
	  * 
	  * Get the <i>name</i> property for this object. Donde <i>name</i> es El nombre corto del rol.
	  * @return varchar(50)
	  */
	final public function getName()
	{
		return $this->name;
	}

	/**
	  * setName( $name )
	  * 
	  * Set the <i>name</i> property for this object. Donde <i>name</i> es El nombre corto del rol..
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
	  * Get the <i>description</i> property for this object. Donde <i>description</i> es La descripción humana del rol.
	  * @return varchar(100)
	  */
	final public function getDescription()
	{
		return $this->description;
	}

	/**
	  * setDescription( $description )
	  * 
	  * Set the <i>description</i> property for this object. Donde <i>description</i> es La descripción humana del rol..
	  * Una validacion basica se hara aqui para comprobar que <i>description</i> es de tipo <i>varchar(100)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(100)
	  */
	final public function setDescription( $description )
	{
		$this->description = $description;
	}

}
