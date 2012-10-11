<?php
/** Value Object file for table Password_Change.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
  * 
  */

class PasswordChange extends VO
{
	/**
	  * Constructor de PasswordChange
	  * 
	  * Para construir un objeto de tipo PasswordChange debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return PasswordChange
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['user_id']) ){
				$this->user_id = $data['user_id'];
			}
			if( isset($data['token']) ){
				$this->token = $data['token'];
			}
			if( isset($data['ip']) ){
				$this->ip = $data['ip'];
			}
			if( isset($data['expiration_date']) ){
				$this->expiration_date = $data['expiration_date'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto PasswordChange en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"user_id" => $this->user_id,
			"token" => $this->token,
			"ip" => $this->ip,
			"expiration_date" => $this->expiration_date
		); 
	return json_encode($vec); 
	}
	
	/**
	  * user_id
	  * 
	  * Identificador de a que usuario pertenece este token<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $user_id;

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
	  * expiration_date
	  * 
	  * La fecha en que vence este token<br>
	  * @access protected
	  * @var timestamp
	  */
	protected $expiration_date;

	/**
	  * getUserId
	  * 
	  * Get the <i>user_id</i> property for this object. Donde <i>user_id</i> es Identificador de a que usuario pertenece este token
	  * @return int(11)
	  */
	final public function getUserId()
	{
		return $this->user_id;
	}

	/**
	  * setUserId( $user_id )
	  * 
	  * Set the <i>user_id</i> property for this object. Donde <i>user_id</i> es Identificador de a que usuario pertenece este token.
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
	  * getExpirationDate
	  * 
	  * Get the <i>expiration_date</i> property for this object. Donde <i>expiration_date</i> es La fecha en que vence este token
	  * @return timestamp
	  */
	final public function getExpirationDate()
	{
		return $this->expiration_date;
	}

	/**
	  * setExpirationDate( $expiration_date )
	  * 
	  * Set the <i>expiration_date</i> property for this object. Donde <i>expiration_date</i> es La fecha en que vence este token.
	  * Una validacion basica se hara aqui para comprobar que <i>expiration_date</i> es de tipo <i>timestamp</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param timestamp
	  */
	final public function setExpirationDate( $expiration_date )
	{
		$this->expiration_date = $expiration_date;
	}



	/**
	  * Converts date fields to timestamps
	  * 
	  **/
	public function toUnixTime( array $fields = array() ){
		if(count($fields) > 0 )
			parent::toUnixTime( $fields );
		else
			parent::toUnixTime( array( "expiration_date" ) );
	}
}
