<?php
/** Value Object file for table Estados.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author Alan Gonzalez <alan@caffeina.mx> 
  * @access public
  * @package openjudge
  * 
  */

class Estados extends VO
{
	/**
	  * Constructor de Estados
	  * 
	  * Para construir un objeto de tipo Estados debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Estados
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['estadoID']) ){
				$this->estadoID = $data['estadoID'];
			}
			if( isset($data['paisID']) ){
				$this->paisID = $data['paisID'];
			}
			if( isset($data['nombre']) ){
				$this->nombre = $data['nombre'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Estados en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array();
		array_push($vec, array( 
		"estadoID" => $this->estadoID,
		"paisID" => $this->paisID,
		"nombre" => $this->nombre
		)); 
	return json_encode($vec); 
	}
	
	/**
	  * estadoID
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $estadoID;

	/**
	  * paisID
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $paisID;

	/**
	  * nombre
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(50)
	  */
	protected $nombre;

	/**
	  * getEstadoID
	  * 
	  * Get the <i>estadoID</i> property for this object. Donde <i>estadoID</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getEstadoID()
	{
		return $this->estadoID;
	}

	/**
	  * setEstadoID( $estadoID )
	  * 
	  * Set the <i>estadoID</i> property for this object. Donde <i>estadoID</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>estadoID</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setEstadoID( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setEstadoID( $estadoID )
	{
		$this->estadoID = $estadoID;
	}

	/**
	  * getPaisID
	  * 
	  * Get the <i>paisID</i> property for this object. Donde <i>paisID</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getPaisID()
	{
		return $this->paisID;
	}

	/**
	  * setPaisID( $paisID )
	  * 
	  * Set the <i>paisID</i> property for this object. Donde <i>paisID</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>paisID</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setPaisID( $paisID )
	{
		$this->paisID = $paisID;
	}

	/**
	  * getNombre
	  * 
	  * Get the <i>nombre</i> property for this object. Donde <i>nombre</i> es  [Campo no documentado]
	  * @return varchar(50)
	  */
	final public function getNombre()
	{
		return $this->nombre;
	}

	/**
	  * setNombre( $nombre )
	  * 
	  * Set the <i>nombre</i> property for this object. Donde <i>nombre</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>nombre</i> es de tipo <i>varchar(50)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(50)
	  */
	final public function setNombre( $nombre )
	{
		$this->nombre = $nombre;
	}

}
