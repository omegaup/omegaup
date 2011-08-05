<?php
/** Value Object file for table Problems_Languages.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
  * 
  */

class ProblemsLanguages extends VO
{
	/**
	  * Constructor de ProblemsLanguages
	  * 
	  * Para construir un objeto de tipo ProblemsLanguages debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return ProblemsLanguages
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['problem_id']) ){
				$this->problem_id = $data['problem_id'];
			}
			if( isset($data['language_id']) ){
				$this->language_id = $data['language_id'];
			}
			if( isset($data['translator_id']) ){
				$this->translator_id = $data['translator_id'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto ProblemsLanguages en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"problem_id" => $this->problem_id,
			"language_id" => $this->language_id,
			"translator_id" => $this->translator_id
		); 
	return json_encode($vec); 
	}
	
	/**
	  * problem_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $problem_id;

	/**
	  * language_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $language_id;

	/**
	  * translator_id
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $translator_id;

	/**
	  * getProblemId
	  * 
	  * Get the <i>problem_id</i> property for this object. Donde <i>problem_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getProblemId()
	{
		return $this->problem_id;
	}

	/**
	  * setProblemId( $problem_id )
	  * 
	  * Set the <i>problem_id</i> property for this object. Donde <i>problem_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>problem_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setProblemId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setProblemId( $problem_id )
	{
		$this->problem_id = $problem_id;
	}

	/**
	  * getLanguageId
	  * 
	  * Get the <i>language_id</i> property for this object. Donde <i>language_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getLanguageId()
	{
		return $this->language_id;
	}

	/**
	  * setLanguageId( $language_id )
	  * 
	  * Set the <i>language_id</i> property for this object. Donde <i>language_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>language_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setLanguageId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setLanguageId( $language_id )
	{
		$this->language_id = $language_id;
	}

	/**
	  * getTranslatorId
	  * 
	  * Get the <i>translator_id</i> property for this object. Donde <i>translator_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getTranslatorId()
	{
		return $this->translator_id;
	}

	/**
	  * setTranslatorId( $translator_id )
	  * 
	  * Set the <i>translator_id</i> property for this object. Donde <i>translator_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>translator_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setTranslatorId( $translator_id )
	{
		$this->translator_id = $translator_id;
	}

}
