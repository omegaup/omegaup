<?php
/** Value Object file for table Usuarios.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author Alan Gonzalez <alan@caffeina.mx> 
  * @access public
  * @package openjudge
  * 
  */

class Usuarios extends VO
{
	/**
	  * Constructor de Usuarios
	  * 
	  * Para construir un objeto de tipo Usuarios debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Usuarios
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['userID']) ){
				$this->userID = $data['userID'];
			}
			if( isset($data['username']) ){
				$this->username = $data['username'];
			}
			if( isset($data['password']) ){
				$this->password = $data['password'];
			}
			if( isset($data['email']) ){
				$this->email = $data['email'];
			}
			if( isset($data['nombre']) ){
				$this->nombre = $data['nombre'];
			}
			if( isset($data['resueltos']) ){
				$this->resueltos = $data['resueltos'];
			}
			if( isset($data['intentados']) ){
				$this->intentados = $data['intentados'];
			}
			if( isset($data['pais']) ){
				$this->pais = $data['pais'];
			}
			if( isset($data['estado']) ){
				$this->estado = $data['estado'];
			}
			if( isset($data['escuela']) ){
				$this->escuela = $data['escuela'];
			}
			if( isset($data['gradoestudios']) ){
				$this->gradoestudios = $data['gradoestudios'];
			}
			if( isset($data['graduacion']) ){
				$this->graduacion = $data['graduacion'];
			}
			if( isset($data['fechaNacimiento']) ){
				$this->fechaNacimiento = $data['fechaNacimiento'];
			}
			if( isset($data['ultimoAcceso']) ){
				$this->ultimoAcceso = $data['ultimoAcceso'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Usuarios en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array();
		array_push($vec, array( 
		"userID" => $this->userID,
		"username" => $this->username,
		"password" => $this->password,
		"email" => $this->email,
		"nombre" => $this->nombre,
		"resueltos" => $this->resueltos,
		"intentados" => $this->intentados,
		"pais" => $this->pais,
		"estado" => $this->estado,
		"escuela" => $this->escuela,
		"gradoestudios" => $this->gradoestudios,
		"graduacion" => $this->graduacion,
		"fechaNacimiento" => $this->fechaNacimiento,
		"ultimoAcceso" => $this->ultimoAcceso
		)); 
	return json_encode($vec); 
	}
	
	/**
	  * userID
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $userID;

	/**
	  * username
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(50)
	  */
	protected $username;

	/**
	  * password
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var char(32)
	  */
	protected $password;

	/**
	  * email
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(256)
	  */
	protected $email;

	/**
	  * nombre
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(256)
	  */
	protected $nombre;

	/**
	  * resueltos
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $resueltos;

	/**
	  * intentados
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $intentados;

	/**
	  * pais
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(64)
	  */
	protected $pais;

	/**
	  * estado
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(64)
	  */
	protected $estado;

	/**
	  * escuela
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(64)
	  */
	protected $escuela;

	/**
	  * gradoestudios
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(64)
	  */
	protected $gradoestudios;

	/**
	  * graduacion
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var date
	  */
	protected $graduacion;

	/**
	  * fechaNacimiento
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var date
	  */
	protected $fechaNacimiento;

	/**
	  * ultimoAcceso
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var timestamp
	  */
	protected $ultimoAcceso;

	/**
	  * getUserID
	  * 
	  * Get the <i>userID</i> property for this object. Donde <i>userID</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getUserID()
	{
		return $this->userID;
	}

	/**
	  * setUserID( $userID )
	  * 
	  * Set the <i>userID</i> property for this object. Donde <i>userID</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>userID</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setUserID( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setUserID( $userID )
	{
		$this->userID = $userID;
	}

	/**
	  * getUsername
	  * 
	  * Get the <i>username</i> property for this object. Donde <i>username</i> es  [Campo no documentado]
	  * @return varchar(50)
	  */
	final public function getUsername()
	{
		return $this->username;
	}

	/**
	  * setUsername( $username )
	  * 
	  * Set the <i>username</i> property for this object. Donde <i>username</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>username</i> es de tipo <i>varchar(50)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(50)
	  */
	final public function setUsername( $username )
	{
		$this->username = $username;
	}

	/**
	  * getPassword
	  * 
	  * Get the <i>password</i> property for this object. Donde <i>password</i> es  [Campo no documentado]
	  * @return char(32)
	  */
	final public function getPassword()
	{
		return $this->password;
	}

	/**
	  * setPassword( $password )
	  * 
	  * Set the <i>password</i> property for this object. Donde <i>password</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>password</i> es de tipo <i>char(32)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param char(32)
	  */
	final public function setPassword( $password )
	{
		$this->password = $password;
	}

	/**
	  * getEmail
	  * 
	  * Get the <i>email</i> property for this object. Donde <i>email</i> es  [Campo no documentado]
	  * @return varchar(256)
	  */
	final public function getEmail()
	{
		return $this->email;
	}

	/**
	  * setEmail( $email )
	  * 
	  * Set the <i>email</i> property for this object. Donde <i>email</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>email</i> es de tipo <i>varchar(256)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(256)
	  */
	final public function setEmail( $email )
	{
		$this->email = $email;
	}

	/**
	  * getNombre
	  * 
	  * Get the <i>nombre</i> property for this object. Donde <i>nombre</i> es  [Campo no documentado]
	  * @return varchar(256)
	  */
	final public function getNombre()
	{
		return $this->nombre;
	}

	/**
	  * setNombre( $nombre )
	  * 
	  * Set the <i>nombre</i> property for this object. Donde <i>nombre</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>nombre</i> es de tipo <i>varchar(256)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(256)
	  */
	final public function setNombre( $nombre )
	{
		$this->nombre = $nombre;
	}

	/**
	  * getResueltos
	  * 
	  * Get the <i>resueltos</i> property for this object. Donde <i>resueltos</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getResueltos()
	{
		return $this->resueltos;
	}

	/**
	  * setResueltos( $resueltos )
	  * 
	  * Set the <i>resueltos</i> property for this object. Donde <i>resueltos</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>resueltos</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setResueltos( $resueltos )
	{
		$this->resueltos = $resueltos;
	}

	/**
	  * getIntentados
	  * 
	  * Get the <i>intentados</i> property for this object. Donde <i>intentados</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getIntentados()
	{
		return $this->intentados;
	}

	/**
	  * setIntentados( $intentados )
	  * 
	  * Set the <i>intentados</i> property for this object. Donde <i>intentados</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>intentados</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setIntentados( $intentados )
	{
		$this->intentados = $intentados;
	}

	/**
	  * getPais
	  * 
	  * Get the <i>pais</i> property for this object. Donde <i>pais</i> es  [Campo no documentado]
	  * @return varchar(64)
	  */
	final public function getPais()
	{
		return $this->pais;
	}

	/**
	  * setPais( $pais )
	  * 
	  * Set the <i>pais</i> property for this object. Donde <i>pais</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>pais</i> es de tipo <i>varchar(64)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(64)
	  */
	final public function setPais( $pais )
	{
		$this->pais = $pais;
	}

	/**
	  * getEstado
	  * 
	  * Get the <i>estado</i> property for this object. Donde <i>estado</i> es  [Campo no documentado]
	  * @return varchar(64)
	  */
	final public function getEstado()
	{
		return $this->estado;
	}

	/**
	  * setEstado( $estado )
	  * 
	  * Set the <i>estado</i> property for this object. Donde <i>estado</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>estado</i> es de tipo <i>varchar(64)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(64)
	  */
	final public function setEstado( $estado )
	{
		$this->estado = $estado;
	}

	/**
	  * getEscuela
	  * 
	  * Get the <i>escuela</i> property for this object. Donde <i>escuela</i> es  [Campo no documentado]
	  * @return varchar(64)
	  */
	final public function getEscuela()
	{
		return $this->escuela;
	}

	/**
	  * setEscuela( $escuela )
	  * 
	  * Set the <i>escuela</i> property for this object. Donde <i>escuela</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>escuela</i> es de tipo <i>varchar(64)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(64)
	  */
	final public function setEscuela( $escuela )
	{
		$this->escuela = $escuela;
	}

	/**
	  * getGradoestudios
	  * 
	  * Get the <i>gradoestudios</i> property for this object. Donde <i>gradoestudios</i> es  [Campo no documentado]
	  * @return varchar(64)
	  */
	final public function getGradoestudios()
	{
		return $this->gradoestudios;
	}

	/**
	  * setGradoestudios( $gradoestudios )
	  * 
	  * Set the <i>gradoestudios</i> property for this object. Donde <i>gradoestudios</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>gradoestudios</i> es de tipo <i>varchar(64)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(64)
	  */
	final public function setGradoestudios( $gradoestudios )
	{
		$this->gradoestudios = $gradoestudios;
	}

	/**
	  * getGraduacion
	  * 
	  * Get the <i>graduacion</i> property for this object. Donde <i>graduacion</i> es  [Campo no documentado]
	  * @return date
	  */
	final public function getGraduacion()
	{
		return $this->graduacion;
	}

	/**
	  * setGraduacion( $graduacion )
	  * 
	  * Set the <i>graduacion</i> property for this object. Donde <i>graduacion</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>graduacion</i> es de tipo <i>date</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param date
	  */
	final public function setGraduacion( $graduacion )
	{
		$this->graduacion = $graduacion;
	}

	/**
	  * getFechaNacimiento
	  * 
	  * Get the <i>fechaNacimiento</i> property for this object. Donde <i>fechaNacimiento</i> es  [Campo no documentado]
	  * @return date
	  */
	final public function getFechaNacimiento()
	{
		return $this->fechaNacimiento;
	}

	/**
	  * setFechaNacimiento( $fechaNacimiento )
	  * 
	  * Set the <i>fechaNacimiento</i> property for this object. Donde <i>fechaNacimiento</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>fechaNacimiento</i> es de tipo <i>date</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param date
	  */
	final public function setFechaNacimiento( $fechaNacimiento )
	{
		$this->fechaNacimiento = $fechaNacimiento;
	}

	/**
	  * getUltimoAcceso
	  * 
	  * Get the <i>ultimoAcceso</i> property for this object. Donde <i>ultimoAcceso</i> es  [Campo no documentado]
	  * @return timestamp
	  */
	final public function getUltimoAcceso()
	{
		return $this->ultimoAcceso;
	}

	/**
	  * setUltimoAcceso( $ultimoAcceso )
	  * 
	  * Set the <i>ultimoAcceso</i> property for this object. Donde <i>ultimoAcceso</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>ultimoAcceso</i> es de tipo <i>timestamp</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param timestamp
	  */
	final public function setUltimoAcceso( $ultimoAcceso )
	{
		$this->ultimoAcceso = $ultimoAcceso;
	}

}
