<?php
/** Value Object file for table Avisos.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author Alan Gonzalez <alan@caffeina.mx> 
  * @access public
  * @package openjudge
  * 
  */

class Avisos extends VO
{
	/**
	  * Constructor de Avisos
	  * 
	  * Para construir un objeto de tipo Avisos debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Avisos
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['avisoID']) ){
				$this->avisoID = $data['avisoID'];
			}
			if( isset($data['userID']) ){
				$this->userID = $data['userID'];
			}
			if( isset($data['fecha']) ){
				$this->fecha = $data['fecha'];
			}
			if( isset($data['aviso']) ){
				$this->aviso = $data['aviso'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Avisos en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array();
		array_push($vec, array( 
		"avisoID" => $this->avisoID,
		"userID" => $this->userID,
		"fecha" => $this->fecha,
		"aviso" => $this->aviso
		)); 
	return json_encode($vec); 
	}
	
	/**
	  * avisoID
	  * 
	  * Identificador del aviso<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $avisoID;

	/**
	  * userID
	  * 
	  * UserID del autor de este aviso<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $userID;

	/**
	  * fecha
	  * 
	  * Fecha de creacion de este aviso<br>
	  * @access protected
	  * @var timestamp
	  */
	protected $fecha;

	/**
	  * aviso
	  * 
	  * Mensaje de texto del aviso<br>
	  * @access protected
	  * @var text
	  */
	protected $aviso;

	/**
	  * getAvisoID
	  * 
	  * Get the <i>avisoID</i> property for this object. Donde <i>avisoID</i> es Identificador del aviso
	  * @return int(11)
	  */
	final public function getAvisoID()
	{
		return $this->avisoID;
	}

	/**
	  * setAvisoID( $avisoID )
	  * 
	  * Set the <i>avisoID</i> property for this object. Donde <i>avisoID</i> es Identificador del aviso.
	  * Una validacion basica se hara aqui para comprobar que <i>avisoID</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setAvisoID( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setAvisoID( $avisoID )
	{
		$this->avisoID = $avisoID;
	}

	/**
	  * getUserID
	  * 
	  * Get the <i>userID</i> property for this object. Donde <i>userID</i> es UserID del autor de este aviso
	  * @return int(11)
	  */
	final public function getUserID()
	{
		return $this->userID;
	}

	/**
	  * setUserID( $userID )
	  * 
	  * Set the <i>userID</i> property for this object. Donde <i>userID</i> es UserID del autor de este aviso.
	  * Una validacion basica se hara aqui para comprobar que <i>userID</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setUserID( $userID )
	{
		$this->userID = $userID;
	}

	/**
	  * getFecha
	  * 
	  * Get the <i>fecha</i> property for this object. Donde <i>fecha</i> es Fecha de creacion de este aviso
	  * @return timestamp
	  */
	final public function getFecha()
	{
		return $this->fecha;
	}

	/**
	  * setFecha( $fecha )
	  * 
	  * Set the <i>fecha</i> property for this object. Donde <i>fecha</i> es Fecha de creacion de este aviso.
	  * Una validacion basica se hara aqui para comprobar que <i>fecha</i> es de tipo <i>timestamp</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param timestamp
	  */
	final public function setFecha( $fecha )
	{
		$this->fecha = $fecha;
	}

	/**
	  * getAviso
	  * 
	  * Get the <i>aviso</i> property for this object. Donde <i>aviso</i> es Mensaje de texto del aviso
	  * @return text
	  */
	final public function getAviso()
	{
		return $this->aviso;
	}

	/**
	  * setAviso( $aviso )
	  * 
	  * Set the <i>aviso</i> property for this object. Donde <i>aviso</i> es Mensaje de texto del aviso.
	  * Una validacion basica se hara aqui para comprobar que <i>aviso</i> es de tipo <i>text</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param text
	  */
	final public function setAviso( $aviso )
	{
		$this->aviso = $aviso;
	}

}
