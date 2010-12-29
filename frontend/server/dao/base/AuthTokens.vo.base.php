<?php
/** Value Object file for table AuthTokens.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author Alan Gonzalez <alan@caffeina.mx> 
  * @access public
  * @package openjudge
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
			if( isset($data['userID']) ){
				$this->userID = $data['userID'];
			}
			if( isset($data['authToken']) ){
				$this->authToken = $data['authToken'];
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
		$vec = array();
		array_push($vec, array( 
		"userID" => $this->userID,
		"authToken" => $this->authToken
		)); 
	return json_encode($vec); 
	}
	
	/**
	  * userID
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $userID;

	/**
	  * authToken
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var varchar(256)
	  */
	protected $authToken;

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
	  * @param int(11)
	  */
	final public function setUserID( $userID )
	{
		$this->userID = $userID;
	}

	/**
	  * getAuthToken
	  * 
	  * Get the <i>authToken</i> property for this object. Donde <i>authToken</i> es  [Campo no documentado]
	  * @return varchar(256)
	  */
	final public function getAuthToken()
	{
		return $this->authToken;
	}

	/**
	  * setAuthToken( $authToken )
	  * 
	  * Set the <i>authToken</i> property for this object. Donde <i>authToken</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>authToken</i> es de tipo <i>varchar(256)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setAuthToken( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param varchar(256)
	  */
	final public function setAuthToken( $authToken )
	{
		$this->authToken = $authToken;
	}

}
