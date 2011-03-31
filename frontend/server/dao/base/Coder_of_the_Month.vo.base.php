<?php
/** Value Object file for table Coder_of_the_Month.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alan@caffeina.mx
  * @access public
  * @package docs
  * 
  */

class CoderOfTheMonth extends VO
{
	/**
	  * Constructor de CoderOfTheMonth
	  * 
	  * Para construir un objeto de tipo CoderOfTheMonth debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return CoderOfTheMonth
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['coder_of_the_month_id']) ){
				$this->coder_of_the_month_id = $data['coder_of_the_month_id'];
			}
			if( isset($data['description']) ){
				$this->description = $data['description'];
			}
			if( isset($data['time']) ){
				$this->time = $data['time'];
			}
			if( isset($data['interview_url']) ){
				$this->interview_url = $data['interview_url'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto CoderOfTheMonth en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"coder_of_the_month_id" => $this->coder_of_the_month_id,
			"description" => $this->description,
			"time" => $this->time,
			"interview_url" => $this->interview_url
		); 
	return json_encode($vec); 
	}
	
	/**
	  * coder_of_the_month_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $coder_of_the_month_id;

	/**
	  * description
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var tinytext,
	  */
	protected $description;

	/**
	  * time
	  * 
	  * Fecha no es UNIQUE por si hay más de 1 coder de mes.<br>
	  * @access protected
	  * @var date
	  */
	protected $time;

	/**
	  * interview_url
	  * 
	  * Para linekar a un post del blog con entrevistas.<br>
	  * @access protected
	  * @var varchar(256)
	  */
	protected $interview_url;

	/**
	  * getCoderOfTheMonthId
	  * 
	  * Get the <i>coder_of_the_month_id</i> property for this object. Donde <i>coder_of_the_month_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getCoderOfTheMonthId()
	{
		return $this->coder_of_the_month_id;
	}

	/**
	  * setCoderOfTheMonthId( $coder_of_the_month_id )
	  * 
	  * Set the <i>coder_of_the_month_id</i> property for this object. Donde <i>coder_of_the_month_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>coder_of_the_month_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setCoderOfTheMonthId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setCoderOfTheMonthId( $coder_of_the_month_id )
	{
		$this->coder_of_the_month_id = $coder_of_the_month_id;
	}

	/**
	  * getDescription
	  * 
	  * Get the <i>description</i> property for this object. Donde <i>description</i> es  [Campo no documentado]
	  * @return tinytext,
	  */
	final public function getDescription()
	{
		return $this->description;
	}

	/**
	  * setDescription( $description )
	  * 
	  * Set the <i>description</i> property for this object. Donde <i>description</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>description</i> es de tipo <i>tinytext,</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param tinytext,
	  */
	final public function setDescription( $description )
	{
		$this->description = $description;
	}

	/**
	  * getTime
	  * 
	  * Get the <i>time</i> property for this object. Donde <i>time</i> es Fecha no es UNIQUE por si hay más de 1 coder de mes.
	  * @return date
	  */
	final public function getTime()
	{
		return $this->time;
	}

	/**
	  * setTime( $time )
	  * 
	  * Set the <i>time</i> property for this object. Donde <i>time</i> es Fecha no es UNIQUE por si hay más de 1 coder de mes..
	  * Una validacion basica se hara aqui para comprobar que <i>time</i> es de tipo <i>date</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param date
	  */
	final public function setTime( $time )
	{
		$this->time = $time;
	}

	/**
	  * getInterviewUrl
	  * 
	  * Get the <i>interview_url</i> property for this object. Donde <i>interview_url</i> es Para linekar a un post del blog con entrevistas.
	  * @return varchar(256)
	  */
	final public function getInterviewUrl()
	{
		return $this->interview_url;
	}

	/**
	  * setInterviewUrl( $interview_url )
	  * 
	  * Set the <i>interview_url</i> property for this object. Donde <i>interview_url</i> es Para linekar a un post del blog con entrevistas..
	  * Una validacion basica se hara aqui para comprobar que <i>interview_url</i> es de tipo <i>varchar(256)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(256)
	  */
	final public function setInterviewUrl( $interview_url )
	{
		$this->interview_url = $interview_url;
	}

}
