<?php
/** Value Object file for table Ejecuciones.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author Alan Gonzalez <alan@caffeina.mx> 
  * @access public
  * @package openjudge
  * 
  */

class Ejecuciones extends VO
{
	/**
	  * Constructor de Ejecuciones
	  * 
	  * Para construir un objeto de tipo Ejecuciones debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Ejecuciones
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['ejecucionID']) ){
				$this->ejecucionID = $data['ejecucionID'];
			}
			if( isset($data['usuarioID']) ){
				$this->usuarioID = $data['usuarioID'];
			}
			if( isset($data['problemaID']) ){
				$this->problemaID = $data['problemaID'];
			}
			if( isset($data['concursoID']) ){
				$this->concursoID = $data['concursoID'];
			}
			if( isset($data['guid']) ){
				$this->guid = $data['guid'];
			}
			if( isset($data['lenguaje']) ){
				$this->lenguaje = $data['lenguaje'];
			}
			if( isset($data['estado']) ){
				$this->estado = $data['estado'];
			}
			if( isset($data['veredicto']) ){
				$this->veredicto = $data['veredicto'];
			}
			if( isset($data['tiempo']) ){
				$this->tiempo = $data['tiempo'];
			}
			if( isset($data['memoria']) ){
				$this->memoria = $data['memoria'];
			}
			if( isset($data['puntuacion']) ){
				$this->puntuacion = $data['puntuacion'];
			}
			if( isset($data['ip']) ){
				$this->ip = $data['ip'];
			}
			if( isset($data['fecha']) ){
				$this->fecha = $data['fecha'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Ejecuciones en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array();
		array_push($vec, array( 
		"ejecucionID" => $this->ejecucionID,
		"usuarioID" => $this->usuarioID,
		"problemaID" => $this->problemaID,
		"concursoID" => $this->concursoID,
		"guid" => $this->guid,
		"lenguaje" => $this->lenguaje,
		"estado" => $this->estado,
		"veredicto" => $this->veredicto,
		"tiempo" => $this->tiempo,
		"memoria" => $this->memoria,
		"puntuacion" => $this->puntuacion,
		"ip" => $this->ip,
		"fecha" => $this->fecha
		)); 
	return json_encode($vec); 
	}
	
	/**
	  * ejecucionID
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $ejecucionID;

	/**
	  * usuarioID
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $usuarioID;

	/**
	  * problemaID
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $problemaID;

	/**
	  * concursoID
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $concursoID;

	/**
	  * guid
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var char(32)
	  */
	protected $guid;

	/**
	  * lenguaje
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var enum('c','cpp','java','py','rb','pl','cs')
	  */
	protected $lenguaje;

	/**
	  * estado
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var enum('nuevo','espera','compilando','ejecutando','listo')
	  */
	protected $estado;

	/**
	  * veredicto
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var enum('AC','WA','PE','RTE','MLE','TLE','RFE','JE')
	  */
	protected $veredicto;

	/**
	  * tiempo
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $tiempo;

	/**
	  * memoria
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $memoria;

	/**
	  * puntuacion
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var double
	  */
	protected $puntuacion;

	/**
	  * ip
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var char(15)
	  */
	protected $ip;

	/**
	  * fecha
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var timestamp
	  */
	protected $fecha;

	/**
	  * getEjecucionID
	  * 
	  * Get the <i>ejecucionID</i> property for this object. Donde <i>ejecucionID</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getEjecucionID()
	{
		return $this->ejecucionID;
	}

	/**
	  * setEjecucionID( $ejecucionID )
	  * 
	  * Set the <i>ejecucionID</i> property for this object. Donde <i>ejecucionID</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>ejecucionID</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setEjecucionID( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setEjecucionID( $ejecucionID )
	{
		$this->ejecucionID = $ejecucionID;
	}

	/**
	  * getUsuarioID
	  * 
	  * Get the <i>usuarioID</i> property for this object. Donde <i>usuarioID</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getUsuarioID()
	{
		return $this->usuarioID;
	}

	/**
	  * setUsuarioID( $usuarioID )
	  * 
	  * Set the <i>usuarioID</i> property for this object. Donde <i>usuarioID</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>usuarioID</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setUsuarioID( $usuarioID )
	{
		$this->usuarioID = $usuarioID;
	}

	/**
	  * getProblemaID
	  * 
	  * Get the <i>problemaID</i> property for this object. Donde <i>problemaID</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getProblemaID()
	{
		return $this->problemaID;
	}

	/**
	  * setProblemaID( $problemaID )
	  * 
	  * Set the <i>problemaID</i> property for this object. Donde <i>problemaID</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>problemaID</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setProblemaID( $problemaID )
	{
		$this->problemaID = $problemaID;
	}

	/**
	  * getConcursoID
	  * 
	  * Get the <i>concursoID</i> property for this object. Donde <i>concursoID</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getConcursoID()
	{
		return $this->concursoID;
	}

	/**
	  * setConcursoID( $concursoID )
	  * 
	  * Set the <i>concursoID</i> property for this object. Donde <i>concursoID</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>concursoID</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setConcursoID( $concursoID )
	{
		$this->concursoID = $concursoID;
	}

	/**
	  * getGuid
	  * 
	  * Get the <i>guid</i> property for this object. Donde <i>guid</i> es  [Campo no documentado]
	  * @return char(32)
	  */
	final public function getGuid()
	{
		return $this->guid;
	}

	/**
	  * setGuid( $guid )
	  * 
	  * Set the <i>guid</i> property for this object. Donde <i>guid</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>guid</i> es de tipo <i>char(32)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param char(32)
	  */
	final public function setGuid( $guid )
	{
		$this->guid = $guid;
	}

	/**
	  * getLenguaje
	  * 
	  * Get the <i>lenguaje</i> property for this object. Donde <i>lenguaje</i> es  [Campo no documentado]
	  * @return enum('c','cpp','java','py','rb','pl','cs')
	  */
	final public function getLenguaje()
	{
		return $this->lenguaje;
	}

	/**
	  * setLenguaje( $lenguaje )
	  * 
	  * Set the <i>lenguaje</i> property for this object. Donde <i>lenguaje</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>lenguaje</i> es de tipo <i>enum('c','cpp','java','py','rb','pl','cs')</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param enum('c','cpp','java','py','rb','pl','cs')
	  */
	final public function setLenguaje( $lenguaje )
	{
		$this->lenguaje = $lenguaje;
	}

	/**
	  * getEstado
	  * 
	  * Get the <i>estado</i> property for this object. Donde <i>estado</i> es  [Campo no documentado]
	  * @return enum('nuevo','espera','compilando','ejecutando','listo')
	  */
	final public function getEstado()
	{
		return $this->estado;
	}

	/**
	  * setEstado( $estado )
	  * 
	  * Set the <i>estado</i> property for this object. Donde <i>estado</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>estado</i> es de tipo <i>enum('nuevo','espera','compilando','ejecutando','listo')</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param enum('nuevo','espera','compilando','ejecutando','listo')
	  */
	final public function setEstado( $estado )
	{
		$this->estado = $estado;
	}

	/**
	  * getVeredicto
	  * 
	  * Get the <i>veredicto</i> property for this object. Donde <i>veredicto</i> es  [Campo no documentado]
	  * @return enum('AC','WA','PE','RTE','MLE','TLE','RFE','JE')
	  */
	final public function getVeredicto()
	{
		return $this->veredicto;
	}

	/**
	  * setVeredicto( $veredicto )
	  * 
	  * Set the <i>veredicto</i> property for this object. Donde <i>veredicto</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>veredicto</i> es de tipo <i>enum('AC','WA','PE','RTE','MLE','TLE','RFE','JE')</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param enum('AC','WA','PE','RTE','MLE','TLE','RFE','JE')
	  */
	final public function setVeredicto( $veredicto )
	{
		$this->veredicto = $veredicto;
	}

	/**
	  * getTiempo
	  * 
	  * Get the <i>tiempo</i> property for this object. Donde <i>tiempo</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getTiempo()
	{
		return $this->tiempo;
	}

	/**
	  * setTiempo( $tiempo )
	  * 
	  * Set the <i>tiempo</i> property for this object. Donde <i>tiempo</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>tiempo</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setTiempo( $tiempo )
	{
		$this->tiempo = $tiempo;
	}

	/**
	  * getMemoria
	  * 
	  * Get the <i>memoria</i> property for this object. Donde <i>memoria</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getMemoria()
	{
		return $this->memoria;
	}

	/**
	  * setMemoria( $memoria )
	  * 
	  * Set the <i>memoria</i> property for this object. Donde <i>memoria</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>memoria</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setMemoria( $memoria )
	{
		$this->memoria = $memoria;
	}

	/**
	  * getPuntuacion
	  * 
	  * Get the <i>puntuacion</i> property for this object. Donde <i>puntuacion</i> es  [Campo no documentado]
	  * @return double
	  */
	final public function getPuntuacion()
	{
		return $this->puntuacion;
	}

	/**
	  * setPuntuacion( $puntuacion )
	  * 
	  * Set the <i>puntuacion</i> property for this object. Donde <i>puntuacion</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>puntuacion</i> es de tipo <i>double</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param double
	  */
	final public function setPuntuacion( $puntuacion )
	{
		$this->puntuacion = $puntuacion;
	}

	/**
	  * getIp
	  * 
	  * Get the <i>ip</i> property for this object. Donde <i>ip</i> es  [Campo no documentado]
	  * @return char(15)
	  */
	final public function getIp()
	{
		return $this->ip;
	}

	/**
	  * setIp( $ip )
	  * 
	  * Set the <i>ip</i> property for this object. Donde <i>ip</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>ip</i> es de tipo <i>char(15)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param char(15)
	  */
	final public function setIp( $ip )
	{
		$this->ip = $ip;
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
