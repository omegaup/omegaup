<?php
/** Value Object file for table Announcement.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alan@caffeina.mx
  * @access public
  * @package docs
  * 
  */

class Announcement extends VO
{
	/**
	  * Constructor de Announcement
	  * 
	  * Para construir un objeto de tipo Announcement debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Announcement
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['announcement_id']) ){
				$this->announcement_id = $data['announcement_id'];
			}
			if( isset($data['user_id']) ){
				$this->user_id = $data['user_id'];
			}
			if( isset($data['time']) ){
				$this->time = $data['time'];
			}
			if( isset($data['description']) ){
				$this->description = $data['description'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Announcement en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"announcement_id" => $this->announcement_id,
			"user_id" => $this->user_id,
			"time" => $this->time,
			"description" => $this->description
		); 
	return json_encode($vec); 
	}
	
	/**
	  * announcement_id
	  * 
	  * Identificador del aviso<br>
	  * <b>Llave Primaria</b><br>
	  * <b>Auto Incremento</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $announcement_id;

	/**
	  * user_id
	  * 
	  * UserID del autor de este aviso<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $user_id;

	/**
	  * time
	  * 
	  * Fecha de creacion de este aviso<br>
	  * @access protected
	  * @var timestamp
	  */
	protected $time;

	/**
	  * description
	  * 
	  * Mensaje de texto del aviso<br>
	  * @access protected
	  * @var text
	  */
	protected $description;

	/**
	  * getAnnouncementId
	  * 
	  * Get the <i>announcement_id</i> property for this object. Donde <i>announcement_id</i> es Identificador del aviso
	  * @return int(11)
	  */
	final public function getAnnouncementId()
	{
		return $this->announcement_id;
	}

	/**
	  * setAnnouncementId( $announcement_id )
	  * 
	  * Set the <i>announcement_id</i> property for this object. Donde <i>announcement_id</i> es Identificador del aviso.
	  * Una validacion basica se hara aqui para comprobar que <i>announcement_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es de <b>Auto Incremento</b> !<br>
	  * No deberias usar setAnnouncementId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setAnnouncementId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setAnnouncementId( $announcement_id )
	{
		$this->announcement_id = $announcement_id;
	}

	/**
	  * getUserId
	  * 
	  * Get the <i>user_id</i> property for this object. Donde <i>user_id</i> es UserID del autor de este aviso
	  * @return int(11)
	  */
	final public function getUserId()
	{
		return $this->user_id;
	}

	/**
	  * setUserId( $user_id )
	  * 
	  * Set the <i>user_id</i> property for this object. Donde <i>user_id</i> es UserID del autor de este aviso.
	  * Una validacion basica se hara aqui para comprobar que <i>user_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setUserId( $user_id )
	{
		$this->user_id = $user_id;
	}

	/**
	  * getTime
	  * 
	  * Get the <i>time</i> property for this object. Donde <i>time</i> es Fecha de creacion de este aviso
	  * @return timestamp
	  */
	final public function getTime()
	{
		return $this->time;
	}

	/**
	  * setTime( $time )
	  * 
	  * Set the <i>time</i> property for this object. Donde <i>time</i> es Fecha de creacion de este aviso.
	  * Una validacion basica se hara aqui para comprobar que <i>time</i> es de tipo <i>timestamp</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param timestamp
	  */
	final public function setTime( $time )
	{
		$this->time = $time;
	}

	/**
	  * getDescription
	  * 
	  * Get the <i>description</i> property for this object. Donde <i>description</i> es Mensaje de texto del aviso
	  * @return text
	  */
	final public function getDescription()
	{
		return $this->description;
	}

	/**
	  * setDescription( $description )
	  * 
	  * Set the <i>description</i> property for this object. Donde <i>description</i> es Mensaje de texto del aviso.
	  * Una validacion basica se hara aqui para comprobar que <i>description</i> es de tipo <i>text</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param text
	  */
	final public function setDescription( $description )
	{
		$this->description = $description;
	}

}
