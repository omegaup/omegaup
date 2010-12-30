<?php
/** Value Object file for table ProblemasConcurso.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author Alan Gonzalez <alan@caffeina.mx> 
  * @access public
  * @package openjudge
  * 
  */

class ProblemasConcurso extends VO
{
	/**
	  * Constructor de ProblemasConcurso
	  * 
	  * Para construir un objeto de tipo ProblemasConcurso debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return ProblemasConcurso
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['concursoID']) ){
				$this->concursoID = $data['concursoID'];
			}
			if( isset($data['problemaID']) ){
				$this->problemaID = $data['problemaID'];
			}
			if( isset($data['puntos']) ){
				$this->puntos = $data['puntos'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto ProblemasConcurso en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array();
		array_push($vec, array( 
		"concursoID" => $this->concursoID,
		"problemaID" => $this->problemaID,
		"puntos" => $this->puntos
		)); 
	return json_encode($vec); 
	}
	
	/**
	  * concursoID
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $concursoID;

	/**
	  * problemaID
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $problemaID;

	/**
	  * puntos
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var double
	  */
	protected $puntos;

	/**
	  * getConcursoID
	  * 
	  * Get the <i>concursoID</i> property for this object. Donde <i>concursoID</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getConcursoID()
	{
		return $this->concursoID;
	}

	/**
	  * setConcursoID( $concursoID )
	  * 
	  * Set the <i>concursoID</i> property for this object. Donde <i>concursoID</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>concursoID</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setConcursoID( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setConcursoID( $concursoID )
	{
		$this->concursoID = $concursoID;
	}

	/**
	  * getProblemaID
	  * 
	  * Get the <i>problemaID</i> property for this object. Donde <i>problemaID</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getProblemaID()
	{
		return $this->problemaID;
	}

	/**
	  * setProblemaID( $problemaID )
	  * 
	  * Set the <i>problemaID</i> property for this object. Donde <i>problemaID</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>problemaID</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setProblemaID( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setProblemaID( $problemaID )
	{
		$this->problemaID = $problemaID;
	}

	/**
	  * getPuntos
	  * 
	  * Get the <i>puntos</i> property for this object. Donde <i>puntos</i> es  [Campo no documentado]
	  * @return double
	  */
	final public function getPuntos()
	{
		return $this->puntos;
	}

	/**
	  * setPuntos( $puntos )
	  * 
	  * Set the <i>puntos</i> property for this object. Donde <i>puntos</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>puntos</i> es de tipo <i>double</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param double
	  */
	final public function setPuntos( $puntos )
	{
		$this->puntos = $puntos;
	}

}
