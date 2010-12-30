<?php
/** Value Object file for table Concursos.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author Alan Gonzalez <alan@caffeina.mx> 
  * @access public
  * @package openjudge
  * 
  */

class Concursos extends VO
{
	/**
	  * Constructor de Concursos
	  * 
	  * Para construir un objeto de tipo Concursos debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Concursos
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['concursoID']) ){
				$this->concursoID = $data['concursoID'];
			}
			if( isset($data['titulo']) ){
				$this->titulo = $data['titulo'];
			}
			if( isset($data['descripcion']) ){
				$this->descripcion = $data['descripcion'];
			}
			if( isset($data['estado']) ){
				$this->estado = $data['estado'];
			}
			if( isset($data['inicio']) ){
				$this->inicio = $data['inicio'];
			}
			if( isset($data['final']) ){
				$this->final = $data['final'];
			}
			if( isset($data['estilo']) ){
				$this->estilo = $data['estilo'];
			}
			if( isset($data['creador']) ){
				$this->creador = $data['creador'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Concursos en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array();
		array_push($vec, array( 
		"concursoID" => $this->concursoID,
		"titulo" => $this->titulo,
		"descripcion" => $this->descripcion,
		"estado" => $this->estado,
		"inicio" => $this->inicio,
		"final" => $this->final,
		"estilo" => $this->estilo,
		"creador" => $this->creador
		)); 
	return json_encode($vec); 
	}
	
	/**
	  * concursoID
	  * 
	  * El identificador unico para cada concurso<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $concursoID;

	/**
	  * titulo
	  * 
	  * El titulo que aparecera en cada concurso<br>
	  * @access protected
	  * @var varchar(256)
	  */
	protected $titulo;

	/**
	  * descripcion
	  * 
	  * Una breve descripcion de cada concurso<br>
	  * @access protected
	  * @var tinytext
	  */
	protected $descripcion;

	/**
	  * estado
	  * 
	  * Estado actual del concurso, tengo mis dudas<br>
	  * @access protected
	  * @var enum('foo')
	  */
	protected $estado;

	/**
	  * inicio
	  * 
	  * Hora de inicio de este concurso<br>
	  * @access protected
	  * @var timestamp
	  */
	protected $inicio;

	/**
	  * final
	  * 
	  * Hora de finalizacion de este concurso<br>
	  * @access protected
	  * @var timestamp
	  */
	protected $final;

	/**
	  * estilo
	  * 
	  * Estilo de este concurso<br>
	  * @access protected
	  * @var enum('icpc','anpa','topcoder','codejam')
	  */
	protected $estilo;

	/**
	  * creador
	  * 
	  * el userID del usuario que creo este concurso<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $creador;

	/**
	  * getConcursoID
	  * 
	  * Get the <i>concursoID</i> property for this object. Donde <i>concursoID</i> es El identificador unico para cada concurso
	  * @return int(11)
	  */
	final public function getConcursoID()
	{
		return $this->concursoID;
	}

	/**
	  * setConcursoID( $concursoID )
	  * 
	  * Set the <i>concursoID</i> property for this object. Donde <i>concursoID</i> es El identificador unico para cada concurso.
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
	  * getTitulo
	  * 
	  * Get the <i>titulo</i> property for this object. Donde <i>titulo</i> es El titulo que aparecera en cada concurso
	  * @return varchar(256)
	  */
	final public function getTitulo()
	{
		return $this->titulo;
	}

	/**
	  * setTitulo( $titulo )
	  * 
	  * Set the <i>titulo</i> property for this object. Donde <i>titulo</i> es El titulo que aparecera en cada concurso.
	  * Una validacion basica se hara aqui para comprobar que <i>titulo</i> es de tipo <i>varchar(256)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(256)
	  */
	final public function setTitulo( $titulo )
	{
		$this->titulo = $titulo;
	}

	/**
	  * getDescripcion
	  * 
	  * Get the <i>descripcion</i> property for this object. Donde <i>descripcion</i> es Una breve descripcion de cada concurso
	  * @return tinytext
	  */
	final public function getDescripcion()
	{
		return $this->descripcion;
	}

	/**
	  * setDescripcion( $descripcion )
	  * 
	  * Set the <i>descripcion</i> property for this object. Donde <i>descripcion</i> es Una breve descripcion de cada concurso.
	  * Una validacion basica se hara aqui para comprobar que <i>descripcion</i> es de tipo <i>tinytext</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param tinytext
	  */
	final public function setDescripcion( $descripcion )
	{
		$this->descripcion = $descripcion;
	}

	/**
	  * getEstado
	  * 
	  * Get the <i>estado</i> property for this object. Donde <i>estado</i> es Estado actual del concurso, tengo mis dudas
	  * @return enum('foo')
	  */
	final public function getEstado()
	{
		return $this->estado;
	}

	/**
	  * setEstado( $estado )
	  * 
	  * Set the <i>estado</i> property for this object. Donde <i>estado</i> es Estado actual del concurso, tengo mis dudas.
	  * Una validacion basica se hara aqui para comprobar que <i>estado</i> es de tipo <i>enum('foo')</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param enum('foo')
	  */
	final public function setEstado( $estado )
	{
		$this->estado = $estado;
	}

	/**
	  * getInicio
	  * 
	  * Get the <i>inicio</i> property for this object. Donde <i>inicio</i> es Hora de inicio de este concurso
	  * @return timestamp
	  */
	final public function getInicio()
	{
		return $this->inicio;
	}

	/**
	  * setInicio( $inicio )
	  * 
	  * Set the <i>inicio</i> property for this object. Donde <i>inicio</i> es Hora de inicio de este concurso.
	  * Una validacion basica se hara aqui para comprobar que <i>inicio</i> es de tipo <i>timestamp</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param timestamp
	  */
	final public function setInicio( $inicio )
	{
		$this->inicio = $inicio;
	}

	/**
	  * getFinal
	  * 
	  * Get the <i>final</i> property for this object. Donde <i>final</i> es Hora de finalizacion de este concurso
	  * @return timestamp
	  */
	final public function getFinal()
	{
		return $this->final;
	}

	/**
	  * setFinal( $final )
	  * 
	  * Set the <i>final</i> property for this object. Donde <i>final</i> es Hora de finalizacion de este concurso.
	  * Una validacion basica se hara aqui para comprobar que <i>final</i> es de tipo <i>timestamp</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param timestamp
	  */
	final public function setFinal( $final )
	{
		$this->final = $final;
	}

	/**
	  * getEstilo
	  * 
	  * Get the <i>estilo</i> property for this object. Donde <i>estilo</i> es Estilo de este concurso
	  * @return enum('icpc','anpa','topcoder','codejam')
	  */
	final public function getEstilo()
	{
		return $this->estilo;
	}

	/**
	  * setEstilo( $estilo )
	  * 
	  * Set the <i>estilo</i> property for this object. Donde <i>estilo</i> es Estilo de este concurso.
	  * Una validacion basica se hara aqui para comprobar que <i>estilo</i> es de tipo <i>enum('icpc','anpa','topcoder','codejam')</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param enum('icpc','anpa','topcoder','codejam')
	  */
	final public function setEstilo( $estilo )
	{
		$this->estilo = $estilo;
	}

	/**
	  * getCreador
	  * 
	  * Get the <i>creador</i> property for this object. Donde <i>creador</i> es el userID del usuario que creo este concurso
	  * @return int(11)
	  */
	final public function getCreador()
	{
		return $this->creador;
	}

	/**
	  * setCreador( $creador )
	  * 
	  * Set the <i>creador</i> property for this object. Donde <i>creador</i> es el userID del usuario que creo este concurso.
	  * Una validacion basica se hara aqui para comprobar que <i>creador</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setCreador( $creador )
	{
		$this->creador = $creador;
	}

}
