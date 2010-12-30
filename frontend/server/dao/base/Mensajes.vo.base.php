<?php
/** Value Object file for table Mensajes.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author Alan Gonzalez <alan@caffeina.mx> 
  * @access public
  * @package openjudge
  * 
  */

class Mensajes extends VO
{
	/**
	  * Constructor de Mensajes
	  * 
	  * Para construir un objeto de tipo Mensajes debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Mensajes
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['mensajeID']) ){
				$this->mensajeID = $data['mensajeID'];
			}
			if( isset($data['leido']) ){
				$this->leido = $data['leido'];
			}
			if( isset($data['de']) ){
				$this->de = $data['de'];
			}
			if( isset($data['para']) ){
				$this->para = $data['para'];
			}
			if( isset($data['mensaje']) ){
				$this->mensaje = $data['mensaje'];
			}
			if( isset($data['fecha']) ){
				$this->fecha = $data['fecha'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Mensajes en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array();
		array_push($vec, array( 
		"mensajeID" => $this->mensajeID,
		"leido" => $this->leido,
		"de" => $this->de,
		"para" => $this->para,
		"mensaje" => $this->mensaje,
		"fecha" => $this->fecha
		)); 
	return json_encode($vec); 
	}
	
	/**
	  * mensajeID
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $mensajeID;

	/**
	  * leido
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var tinyint(1)
	  */
	protected $leido;

	/**
	  * de
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $de;

	/**
	  * para
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $para;

	/**
	  * mensaje
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var tinytext
	  */
	protected $mensaje;

	/**
	  * fecha
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var timestamp
	  */
	protected $fecha;

	/**
	  * getMensajeID
	  * 
	  * Get the <i>mensajeID</i> property for this object. Donde <i>mensajeID</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getMensajeID()
	{
		return $this->mensajeID;
	}

	/**
	  * setMensajeID( $mensajeID )
	  * 
	  * Set the <i>mensajeID</i> property for this object. Donde <i>mensajeID</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>mensajeID</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setMensajeID( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setMensajeID( $mensajeID )
	{
		$this->mensajeID = $mensajeID;
	}

	/**
	  * getLeido
	  * 
	  * Get the <i>leido</i> property for this object. Donde <i>leido</i> es  [Campo no documentado]
	  * @return tinyint(1)
	  */
	final public function getLeido()
	{
		return $this->leido;
	}

	/**
	  * setLeido( $leido )
	  * 
	  * Set the <i>leido</i> property for this object. Donde <i>leido</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>leido</i> es de tipo <i>tinyint(1)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param tinyint(1)
	  */
	final public function setLeido( $leido )
	{
		$this->leido = $leido;
	}

	/**
	  * getDe
	  * 
	  * Get the <i>de</i> property for this object. Donde <i>de</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getDe()
	{
		return $this->de;
	}

	/**
	  * setDe( $de )
	  * 
	  * Set the <i>de</i> property for this object. Donde <i>de</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>de</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setDe( $de )
	{
		$this->de = $de;
	}

	/**
	  * getPara
	  * 
	  * Get the <i>para</i> property for this object. Donde <i>para</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getPara()
	{
		return $this->para;
	}

	/**
	  * setPara( $para )
	  * 
	  * Set the <i>para</i> property for this object. Donde <i>para</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>para</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setPara( $para )
	{
		$this->para = $para;
	}

	/**
	  * getMensaje
	  * 
	  * Get the <i>mensaje</i> property for this object. Donde <i>mensaje</i> es  [Campo no documentado]
	  * @return tinytext
	  */
	final public function getMensaje()
	{
		return $this->mensaje;
	}

	/**
	  * setMensaje( $mensaje )
	  * 
	  * Set the <i>mensaje</i> property for this object. Donde <i>mensaje</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>mensaje</i> es de tipo <i>tinytext</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param tinytext
	  */
	final public function setMensaje( $mensaje )
	{
		$this->mensaje = $mensaje;
	}

	/**
	  * getFecha
	  * 
	  * Get the <i>fecha</i> property for this object. Donde <i>fecha</i> es  [Campo no documentado]
	  * @return timestamp
	  */
	final public function getFecha()
	{
		return $this->fecha;
	}

	/**
	  * setFecha( $fecha )
	  * 
	  * Set the <i>fecha</i> property for this object. Donde <i>fecha</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>fecha</i> es de tipo <i>timestamp</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param timestamp
	  */
	final public function setFecha( $fecha )
	{
		$this->fecha = $fecha;
	}

}
