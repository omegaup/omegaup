<?php
/** Value Object file for table Auth_Tokens.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alan@caffeina.mx
  * @access public
  * @package docs
  * 
  */

class AuthTokens extends VO
{
	/**
	  * Constructor de AuthTokens
	  * 
	  * Para construir un objeto de tipo AuthTokens debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return AuthTokens
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
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto AuthTokens en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"user_id" => $this->user_id,
			"token" => $this->token
		); 
	return json_encode($vec); 
	}
	
	/**
	  * user_id
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $user_id;

	/**
	  * token
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var varchar(128)
	  */
	protected $token;

	/**
	  * getUserId
	  * 
	  * Get the <i>user_id</i> property for this object. Donde <i>user_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getUserId()
	{
		return $this->user_id;
	}

	/**
	  * setUserId( $user_id )
	  * 
	  * Set the <i>user_id</i> property for this object. Donde <i>user_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>user_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setUserId( $user_id )
	{
		$this->user_id = $user_id;
	}

	/**
	  * getToken
	  * 
	  * Get the <i>token</i> property for this object. Donde <i>token</i> es  [Campo no documentado]
	  * @return varchar(128)
	  */
	final public function getToken()
	{
		return $this->token;
	}

	/**
	  * setToken( $token )
	  * 
	  * Set the <i>token</i> property for this object. Donde <i>token</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>token</i> es de tipo <i>varchar(128)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setToken( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param varchar(128)
	  */
	final public function setToken( $token )
	{
		$this->token = $token;
	}

}
