<?php
/** Value Object file for table Permissions.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
  * 
  */

class Permissions extends VO
{
	/**
	  * Constructor de Permissions
	  * 
	  * Para construir un objeto de tipo Permissions debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Permissions
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['permission_id']) ){
				$this->permission_id = $data['permission_id'];
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
	  * Este metodo permite tratar a un objeto Permissions en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"permission_id" => $this->permission_id,
			"name" => $this->name,
			"description" => $this->description
		); 
	return json_encode($vec); 
	}
	
	/**
	  * permission_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * <b>Auto Incremento</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $permission_id;

	/**
	  * name
	  * 
	  * El nombre corto del permiso.<br>
	  * @access protected
	  * @var varchar(50)
	  */
	protected $name;

	/**
	  * description
	  * 
	  * La descripciÃ³n humana del permiso.<br>
	  * @access protected
	  * @var varchar(100)
	  */
	protected $description;

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
	  * <br><br>Esta propiedad se mapea con un campo que es de <b>Auto Incremento</b> !<br>
	  * No deberias usar setPermissionId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setPermissionId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setPermissionId( $permission_id )
	{
		$this->permission_id = $permission_id;
	}

	/**
	  * getName
	  * 
	  * Get the <i>name</i> property for this object. Donde <i>name</i> es El nombre corto del permiso.
	  * @return varchar(50)
	  */
	final public function getName()
	{
		return $this->name;
	}

	/**
	  * setName( $name )
	  * 
	  * Set the <i>name</i> property for this object. Donde <i>name</i> es El nombre corto del permiso..
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
	  * Get the <i>description</i> property for this object. Donde <i>description</i> es La descripciÃ³n humana del permiso.
	  * @return varchar(100)
	  */
	final public function getDescription()
	{
		return $this->description;
	}

	/**
	  * setDescription( $description )
	  * 
	  * Set the <i>description</i> property for this object. Donde <i>description</i> es La descripciÃ³n humana del permiso..
	  * Una validacion basica se hara aqui para comprobar que <i>description</i> es de tipo <i>varchar(100)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(100)
	  */
	final public function setDescription( $description )
	{
		$this->description = $description;
	}

}
