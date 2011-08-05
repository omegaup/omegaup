<?php
/** Value Object file for table Clarifications.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
  * 
  */

class Clarifications extends VO
{
	/**
	  * Constructor de Clarifications
	  * 
	  * Para construir un objeto de tipo Clarifications debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Clarifications
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['clarification_id']) ){
				$this->clarification_id = $data['clarification_id'];
			}
			if( isset($data['author_id']) ){
				$this->author_id = $data['author_id'];
			}
			if( isset($data['message']) ){
				$this->message = $data['message'];
			}
			if( isset($data['answer']) ){
				$this->answer = $data['answer'];
			}
			if( isset($data['time']) ){
				$this->time = $data['time'];
			}
			if( isset($data['problem_id']) ){
				$this->problem_id = $data['problem_id'];
			}
			if( isset($data['contest_id']) ){
				$this->contest_id = $data['contest_id'];
			}
			if( isset($data['public']) ){
				$this->public = $data['public'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Clarifications en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"clarification_id" => $this->clarification_id,
			"author_id" => $this->author_id,
			"message" => $this->message,
			"answer" => $this->answer,
			"time" => $this->time,
			"problem_id" => $this->problem_id,
			"contest_id" => $this->contest_id,
			"public" => $this->public
		); 
	return json_encode($vec); 
	}
	
	/**
	  * clarification_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * <b>Auto Incremento</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $clarification_id;

	/**
	  * author_id
	  * 
	  * Autor de la clarificaciÃ³n.<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $author_id;

	/**
	  * message
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var text
	  */
	protected $message;

	/**
	  * answer
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var text
	  */
	protected $answer;

	/**
	  * time
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var timestamp
	  */
	protected $time;

	/**
	  * problem_id
	  * 
	  * Lo ideal es que la clarificacion le llegue al problemsetter que escribio el problema.<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $problem_id;

	/**
	  * contest_id
	  * 
	  * Puede ser nulo si la clarificacion no se da en un concurso.<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $contest_id;

	/**
	  * public
	  * 
	  * SÃ³lo las clarificaciones que el problemsetter marque como publicacbles apareceran en la lista que toda la banda puede ver. Sino, solo al usuario. <br>
	  * @access protected
	  * @var tinyint(1)
	  */
	protected $public;

	/**
	  * getClarificationId
	  * 
	  * Get the <i>clarification_id</i> property for this object. Donde <i>clarification_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getClarificationId()
	{
		return $this->clarification_id;
	}

	/**
	  * setClarificationId( $clarification_id )
	  * 
	  * Set the <i>clarification_id</i> property for this object. Donde <i>clarification_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>clarification_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es de <b>Auto Incremento</b> !<br>
	  * No deberias usar setClarificationId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setClarificationId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setClarificationId( $clarification_id )
	{
		$this->clarification_id = $clarification_id;
	}

	/**
	  * getAuthorId
	  * 
	  * Get the <i>author_id</i> property for this object. Donde <i>author_id</i> es Autor de la clarificaciÃ³n.
	  * @return int(11)
	  */
	final public function getAuthorId()
	{
		return $this->author_id;
	}

	/**
	  * setAuthorId( $author_id )
	  * 
	  * Set the <i>author_id</i> property for this object. Donde <i>author_id</i> es Autor de la clarificaciÃ³n..
	  * Una validacion basica se hara aqui para comprobar que <i>author_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setAuthorId( $author_id )
	{
		$this->author_id = $author_id;
	}

	/**
	  * getMessage
	  * 
	  * Get the <i>message</i> property for this object. Donde <i>message</i> es  [Campo no documentado]
	  * @return text
	  */
	final public function getMessage()
	{
		return $this->message;
	}

	/**
	  * setMessage( $message )
	  * 
	  * Set the <i>message</i> property for this object. Donde <i>message</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>message</i> es de tipo <i>text</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param text
	  */
	final public function setMessage( $message )
	{
		$this->message = $message;
	}

	/**
	  * getAnswer
	  * 
	  * Get the <i>answer</i> property for this object. Donde <i>answer</i> es  [Campo no documentado]
	  * @return text
	  */
	final public function getAnswer()
	{
		return $this->answer;
	}

	/**
	  * setAnswer( $answer )
	  * 
	  * Set the <i>answer</i> property for this object. Donde <i>answer</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>answer</i> es de tipo <i>text</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param text
	  */
	final public function setAnswer( $answer )
	{
		$this->answer = $answer;
	}

	/**
	  * getTime
	  * 
	  * Get the <i>time</i> property for this object. Donde <i>time</i> es  [Campo no documentado]
	  * @return timestamp
	  */
	final public function getTime()
	{
		return $this->time;
	}

	/**
	  * setTime( $time )
	  * 
	  * Set the <i>time</i> property for this object. Donde <i>time</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>time</i> es de tipo <i>timestamp</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param timestamp
	  */
	final public function setTime( $time )
	{
		$this->time = $time;
	}

	/**
	  * getProblemId
	  * 
	  * Get the <i>problem_id</i> property for this object. Donde <i>problem_id</i> es Lo ideal es que la clarificacion le llegue al problemsetter que escribio el problema.
	  * @return int(11)
	  */
	final public function getProblemId()
	{
		return $this->problem_id;
	}

	/**
	  * setProblemId( $problem_id )
	  * 
	  * Set the <i>problem_id</i> property for this object. Donde <i>problem_id</i> es Lo ideal es que la clarificacion le llegue al problemsetter que escribio el problema..
	  * Una validacion basica se hara aqui para comprobar que <i>problem_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setProblemId( $problem_id )
	{
		$this->problem_id = $problem_id;
	}

	/**
	  * getContestId
	  * 
	  * Get the <i>contest_id</i> property for this object. Donde <i>contest_id</i> es Puede ser nulo si la clarificacion no se da en un concurso.
	  * @return int(11)
	  */
	final public function getContestId()
	{
		return $this->contest_id;
	}

	/**
	  * setContestId( $contest_id )
	  * 
	  * Set the <i>contest_id</i> property for this object. Donde <i>contest_id</i> es Puede ser nulo si la clarificacion no se da en un concurso..
	  * Una validacion basica se hara aqui para comprobar que <i>contest_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setContestId( $contest_id )
	{
		$this->contest_id = $contest_id;
	}

	/**
	  * getPublic
	  * 
	  * Get the <i>public</i> property for this object. Donde <i>public</i> es SÃ³lo las clarificaciones que el problemsetter marque como publicacbles apareceran en la lista que toda la banda puede ver. Sino, solo al usuario. 
	  * @return tinyint(1)
	  */
	final public function getPublic()
	{
		return $this->public;
	}

	/**
	  * setPublic( $public )
	  * 
	  * Set the <i>public</i> property for this object. Donde <i>public</i> es SÃ³lo las clarificaciones que el problemsetter marque como publicacbles apareceran en la lista que toda la banda puede ver. Sino, solo al usuario. .
	  * Una validacion basica se hara aqui para comprobar que <i>public</i> es de tipo <i>tinyint(1)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param tinyint(1)
	  */
	final public function setPublic( $public )
	{
		$this->public = $public;
	}

}
