<?php
/** Value Object file for table Emails.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
  * 
  */

class Emails extends VO
{
	/**
	  * Constructor de Emails
	  * 
	  * Para construir un objeto de tipo Emails debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Emails
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['email_id']) ){
				$this->email_id = $data['email_id'];
			}
			if( isset($data['email']) ){
				$this->email = $data['email'];
			}
			if( isset($data['user_id']) ){
				$this->user_id = $data['user_id'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Emails en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"email_id" => $this->email_id,
			"email" => $this->email,
			"user_id" => $this->user_id
		); 
	return json_encode($vec); 
	}
	
	/**
	  * email_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $email_id;

	/**
	  * email
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(256)
	  */
	protected $email;

	/**
	  * user_id
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $user_id;

	/**
	  * getEmailId
	  * 
	  * Get the <i>email_id</i> property for this object. Donde <i>email_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getEmailId()
	{
		return $this->email_id;
	}

	/**
	  * setEmailId( $email_id )
	  * 
	  * Set the <i>email_id</i> property for this object. Donde <i>email_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>email_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setEmailId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setEmailId( $email_id )
	{
		$this->email_id = $email_id;
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

}
