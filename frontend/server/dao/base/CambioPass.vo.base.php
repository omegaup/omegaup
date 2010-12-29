<?php
/** Value Object file for table CambioPass.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author Alan Gonzalez <alan@caffeina.mx> 
  * @access public
  * @package openjudge
  * 
  */

class CambioPass extends VO
{
	/**
	  * Constructor de CambioPass
	  * 
	  * Para construir un objeto de tipo CambioPass debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return CambioPass
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['userID']) ){
				$this->userID = $data['userID'];
			}
			if( isset($data['token']) ){
				$this->token = $data['token'];
			}
			if( isset($data['ip']) ){
				$this->ip = $data['ip'];
			}
			if( isset($data['expiracion']) ){
				$this->expiracion = $data['expiracion'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto CambioPass en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array();
		array_push($vec, array( 
		"userID" => $this->userID,
		"token" => $this->token,
		"ip" => $this->ip,
		"expiracion" => $this->expiracion
		)); 
	return json_encode($vec); 
	}
	
	/**
	  * userID
	  * 
	  * Identificador de a que usuario pertenece este token<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $userID;

	/**
	  * token
	  * 
	  * El token que se genera aleatoriamente para luego comparar cuando el usuario haga click en el link<br>
	  * @access protected
	  * @var char(64)
	  */
	protected $token;

	/**
	  * ip
	  * 
	  * El ip desde donde se genero este reseteo de password<br>
	  * @access protected
	  * @var char(15)
	  */
	protected $ip;

	/**
	  * expiracion
	  * 
	  * La fecha en que vence este token<br>
	  * @access protected
	  * @var timestamp
	  */
	protected $expiracion;

	/**
	  * getUserID
	  * 
	  * Get the <i>userID</i> property for this object. Donde <i>userID</i> es Identificador de a que usuario pertenece este token
	  * @return int(11)
	  */
	final public function getUserID()
	{
		return $this->userID;
	}

	/**
	  * setUserID( $userID )
	  * 
	  * Set the <i>userID</i> property for this object. Donde <i>userID</i> es Identificador de a que usuario pertenece este token.
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
	  * getToken
	  * 
	  * Get the <i>token</i> property for this object. Donde <i>token</i> es El token que se genera aleatoriamente para luego comparar cuando el usuario haga click en el link
	  * @return char(64)
	  */
	final public function getToken()
	{
		return $this->token;
	}

	/**
	  * setToken( $token )
	  * 
	  * Set the <i>token</i> property for this object. Donde <i>token</i> es El token que se genera aleatoriamente para luego comparar cuando el usuario haga click en el link.
	  * Una validacion basica se hara aqui para comprobar que <i>token</i> es de tipo <i>char(64)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param char(64)
	  */
	final public function setToken( $token )
	{
		$this->token = $token;
	}

	/**
	  * getIp
	  * 
	  * Get the <i>ip</i> property for this object. Donde <i>ip</i> es El ip desde donde se genero este reseteo de password
	  * @return char(15)
	  */
	final public function getIp()
	{
		return $this->ip;
	}

	/**
	  * setIp( $ip )
	  * 
	  * Set the <i>ip</i> property for this object. Donde <i>ip</i> es El ip desde donde se genero este reseteo de password.
	  * Una validacion basica se hara aqui para comprobar que <i>ip</i> es de tipo <i>char(15)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param char(15)
	  */
	final public function setIp( $ip )
	{
		$this->ip = $ip;
	}

	/**
	  * getExpiracion
	  * 
	  * Get the <i>expiracion</i> property for this object. Donde <i>expiracion</i> es La fecha en que vence este token
	  * @return timestamp
	  */
	final public function getExpiracion()
	{
		return $this->expiracion;
	}

	/**
	  * setExpiracion( $expiracion )
	  * 
	  * Set the <i>expiracion</i> property for this object. Donde <i>expiracion</i> es La fecha en que vence este token.
	  * Una validacion basica se hara aqui para comprobar que <i>expiracion</i> es de tipo <i>timestamp</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param timestamp
	  */
	final public function setExpiracion( $expiracion )
	{
		$this->expiracion = $expiracion;
	}

}
