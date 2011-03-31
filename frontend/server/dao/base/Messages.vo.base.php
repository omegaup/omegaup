<?php
/** Value Object file for table Messages.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alan@caffeina.mx
  * @access public
  * @package docs
  * 
  */

class Messages extends VO
{
	/**
	  * Constructor de Messages
	  * 
	  * Para construir un objeto de tipo Messages debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Messages
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['message_id']) ){
				$this->message_id = $data['message_id'];
			}
			if( isset($data['read']) ){
				$this->read = $data['read'];
			}
			if( isset($data['sender_id']) ){
				$this->sender_id = $data['sender_id'];
			}
			if( isset($data['recipient_id']) ){
				$this->recipient_id = $data['recipient_id'];
			}
			if( isset($data['message']) ){
				$this->message = $data['message'];
			}
			if( isset($data['date']) ){
				$this->date = $data['date'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Messages en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"message_id" => $this->message_id,
			"read" => $this->read,
			"sender_id" => $this->sender_id,
			"recipient_id" => $this->recipient_id,
			"message" => $this->message,
			"date" => $this->date
		); 
	return json_encode($vec); 
	}
	
	/**
	  * message_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $message_id;

	/**
	  * read
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var tinyint(1)
	  */
	protected $read;

	/**
	  * sender_id
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $sender_id;

	/**
	  * recipient_id
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $recipient_id;

	/**
	  * message
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var tinytext
	  */
	protected $message;

	/**
	  * date
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var timestamp
	  */
	protected $date;

	/**
	  * getMessageId
	  * 
	  * Get the <i>message_id</i> property for this object. Donde <i>message_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getMessageId()
	{
		return $this->message_id;
	}

	/**
	  * setMessageId( $message_id )
	  * 
	  * Set the <i>message_id</i> property for this object. Donde <i>message_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>message_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setMessageId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setMessageId( $message_id )
	{
		$this->message_id = $message_id;
	}

	/**
	  * getRead
	  * 
	  * Get the <i>read</i> property for this object. Donde <i>read</i> es  [Campo no documentado]
	  * @return tinyint(1)
	  */
	final public function getRead()
	{
		return $this->read;
	}

	/**
	  * setRead( $read )
	  * 
	  * Set the <i>read</i> property for this object. Donde <i>read</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>read</i> es de tipo <i>tinyint(1)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param tinyint(1)
	  */
	final public function setRead( $read )
	{
		$this->read = $read;
	}

	/**
	  * getSenderId
	  * 
	  * Get the <i>sender_id</i> property for this object. Donde <i>sender_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getSenderId()
	{
		return $this->sender_id;
	}

	/**
	  * setSenderId( $sender_id )
	  * 
	  * Set the <i>sender_id</i> property for this object. Donde <i>sender_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>sender_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setSenderId( $sender_id )
	{
		$this->sender_id = $sender_id;
	}

	/**
	  * getRecipientId
	  * 
	  * Get the <i>recipient_id</i> property for this object. Donde <i>recipient_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getRecipientId()
	{
		return $this->recipient_id;
	}

	/**
	  * setRecipientId( $recipient_id )
	  * 
	  * Set the <i>recipient_id</i> property for this object. Donde <i>recipient_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>recipient_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setRecipientId( $recipient_id )
	{
		$this->recipient_id = $recipient_id;
	}

	/**
	  * getMessage
	  * 
	  * Get the <i>message</i> property for this object. Donde <i>message</i> es  [Campo no documentado]
	  * @return tinytext
	  */
	final public function getMessage()
	{
		return $this->message;
	}

	/**
	  * setMessage( $message )
	  * 
	  * Set the <i>message</i> property for this object. Donde <i>message</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>message</i> es de tipo <i>tinytext</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param tinytext
	  */
	final public function setMessage( $message )
	{
		$this->message = $message;
	}

	/**
	  * getDate
	  * 
	  * Get the <i>date</i> property for this object. Donde <i>date</i> es  [Campo no documentado]
	  * @return timestamp
	  */
	final public function getDate()
	{
		return $this->date;
	}

	/**
	  * setDate( $date )
	  * 
	  * Set the <i>date</i> property for this object. Donde <i>date</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>date</i> es de tipo <i>timestamp</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param timestamp
	  */
	final public function setDate( $date )
	{
		$this->date = $date;
	}

}
