<?php
/** Value Object file for table Problemas.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author Alan Gonzalez <alan@caffeina.mx> 
  * @access public
  * @package openjudge
  * 
  */

class Problemas extends VO
{
	/**
	  * Constructor de Problemas
	  * 
	  * Para construir un objeto de tipo Problemas debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Problemas
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['problemaID']) ){
				$this->problemaID = $data['problemaID'];
			}
			if( isset($data['publico']) ){
				$this->publico = $data['publico'];
			}
			if( isset($data['autor']) ){
				$this->autor = $data['autor'];
			}
			if( isset($data['titulo']) ){
				$this->titulo = $data['titulo'];
			}
			if( isset($data['alias']) ){
				$this->alias = $data['alias'];
			}
			if( isset($data['validador']) ){
				$this->validador = $data['validador'];
			}
			if( isset($data['servidor']) ){
				$this->servidor = $data['servidor'];
			}
			if( isset($data['id_remoto']) ){
				$this->id_remoto = $data['id_remoto'];
			}
			if( isset($data['tiempoLimite']) ){
				$this->tiempoLimite = $data['tiempoLimite'];
			}
			if( isset($data['memoriaLimite']) ){
				$this->memoriaLimite = $data['memoriaLimite'];
			}
			if( isset($data['vistas']) ){
				$this->vistas = $data['vistas'];
			}
			if( isset($data['envios']) ){
				$this->envios = $data['envios'];
			}
			if( isset($data['aceptados']) ){
				$this->aceptados = $data['aceptados'];
			}
			if( isset($data['dificultad']) ){
				$this->dificultad = $data['dificultad'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Problemas en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array();
		array_push($vec, array( 
		"problemaID" => $this->problemaID,
		"publico" => $this->publico,
		"autor" => $this->autor,
		"titulo" => $this->titulo,
		"alias" => $this->alias,
		"validador" => $this->validador,
		"servidor" => $this->servidor,
		"id_remoto" => $this->id_remoto,
		"tiempoLimite" => $this->tiempoLimite,
		"memoriaLimite" => $this->memoriaLimite,
		"vistas" => $this->vistas,
		"envios" => $this->envios,
		"aceptados" => $this->aceptados,
		"dificultad" => $this->dificultad
		)); 
	return json_encode($vec); 
	}
	
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
	  * publico
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var tinyint(1)
	  */
	protected $publico;

	/**
	  * autor
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $autor;

	/**
	  * titulo
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(256)
	  */
	protected $titulo;

	/**
	  * alias
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(10)
	  */
	protected $alias;

	/**
	  * validador
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var enum('remoto','literal','token','token-caseless','token-numeric')
	  */
	protected $validador;

	/**
	  * servidor
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var enum('uva','livearchive','pku','tju','spoj')
	  */
	protected $servidor;

	/**
	  * id_remoto
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(10)
	  */
	protected $id_remoto;

	/**
	  * tiempoLimite
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $tiempoLimite;

	/**
	  * memoriaLimite
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $memoriaLimite;

	/**
	  * vistas
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $vistas;

	/**
	  * envios
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $envios;

	/**
	  * aceptados
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $aceptados;

	/**
	  * dificultad
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var double
	  */
	protected $dificultad;

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
	  * getPublico
	  * 
	  * Get the <i>publico</i> property for this object. Donde <i>publico</i> es  [Campo no documentado]
	  * @return tinyint(1)
	  */
	final public function getPublico()
	{
		return $this->publico;
	}

	/**
	  * setPublico( $publico )
	  * 
	  * Set the <i>publico</i> property for this object. Donde <i>publico</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>publico</i> es de tipo <i>tinyint(1)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param tinyint(1)
	  */
	final public function setPublico( $publico )
	{
		$this->publico = $publico;
	}

	/**
	  * getAutor
	  * 
	  * Get the <i>autor</i> property for this object. Donde <i>autor</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getAutor()
	{
		return $this->autor;
	}

	/**
	  * setAutor( $autor )
	  * 
	  * Set the <i>autor</i> property for this object. Donde <i>autor</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>autor</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setAutor( $autor )
	{
		$this->autor = $autor;
	}

	/**
	  * getTitulo
	  * 
	  * Get the <i>titulo</i> property for this object. Donde <i>titulo</i> es  [Campo no documentado]
	  * @return varchar(256)
	  */
	final public function getTitulo()
	{
		return $this->titulo;
	}

	/**
	  * setTitulo( $titulo )
	  * 
	  * Set the <i>titulo</i> property for this object. Donde <i>titulo</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>titulo</i> es de tipo <i>varchar(256)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(256)
	  */
	final public function setTitulo( $titulo )
	{
		$this->titulo = $titulo;
	}

	/**
	  * getAlias
	  * 
	  * Get the <i>alias</i> property for this object. Donde <i>alias</i> es  [Campo no documentado]
	  * @return varchar(10)
	  */
	final public function getAlias()
	{
		return $this->alias;
	}

	/**
	  * setAlias( $alias )
	  * 
	  * Set the <i>alias</i> property for this object. Donde <i>alias</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>alias</i> es de tipo <i>varchar(10)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(10)
	  */
	final public function setAlias( $alias )
	{
		$this->alias = $alias;
	}

	/**
	  * getValidador
	  * 
	  * Get the <i>validador</i> property for this object. Donde <i>validador</i> es  [Campo no documentado]
	  * @return enum('remoto','literal','token','token-caseless','token-numeric')
	  */
	final public function getValidador()
	{
		return $this->validador;
	}

	/**
	  * setValidador( $validador )
	  * 
	  * Set the <i>validador</i> property for this object. Donde <i>validador</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>validador</i> es de tipo <i>enum('remoto','literal','token','token-caseless','token-numeric')</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param enum('remoto','literal','token','token-caseless','token-numeric')
	  */
	final public function setValidador( $validador )
	{
		$this->validador = $validador;
	}

	/**
	  * getServidor
	  * 
	  * Get the <i>servidor</i> property for this object. Donde <i>servidor</i> es  [Campo no documentado]
	  * @return enum('uva','livearchive','pku','tju','spoj')
	  */
	final public function getServidor()
	{
		return $this->servidor;
	}

	/**
	  * setServidor( $servidor )
	  * 
	  * Set the <i>servidor</i> property for this object. Donde <i>servidor</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>servidor</i> es de tipo <i>enum('uva','livearchive','pku','tju','spoj')</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param enum('uva','livearchive','pku','tju','spoj')
	  */
	final public function setServidor( $servidor )
	{
		$this->servidor = $servidor;
	}

	/**
	  * getIdRemoto
	  * 
	  * Get the <i>id_remoto</i> property for this object. Donde <i>id_remoto</i> es  [Campo no documentado]
	  * @return varchar(10)
	  */
	final public function getIdRemoto()
	{
		return $this->id_remoto;
	}

	/**
	  * setIdRemoto( $id_remoto )
	  * 
	  * Set the <i>id_remoto</i> property for this object. Donde <i>id_remoto</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>id_remoto</i> es de tipo <i>varchar(10)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(10)
	  */
	final public function setIdRemoto( $id_remoto )
	{
		$this->id_remoto = $id_remoto;
	}

	/**
	  * getTiempoLimite
	  * 
	  * Get the <i>tiempoLimite</i> property for this object. Donde <i>tiempoLimite</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getTiempoLimite()
	{
		return $this->tiempoLimite;
	}

	/**
	  * setTiempoLimite( $tiempoLimite )
	  * 
	  * Set the <i>tiempoLimite</i> property for this object. Donde <i>tiempoLimite</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>tiempoLimite</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setTiempoLimite( $tiempoLimite )
	{
		$this->tiempoLimite = $tiempoLimite;
	}

	/**
	  * getMemoriaLimite
	  * 
	  * Get the <i>memoriaLimite</i> property for this object. Donde <i>memoriaLimite</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getMemoriaLimite()
	{
		return $this->memoriaLimite;
	}

	/**
	  * setMemoriaLimite( $memoriaLimite )
	  * 
	  * Set the <i>memoriaLimite</i> property for this object. Donde <i>memoriaLimite</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>memoriaLimite</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setMemoriaLimite( $memoriaLimite )
	{
		$this->memoriaLimite = $memoriaLimite;
	}

	/**
	  * getVistas
	  * 
	  * Get the <i>vistas</i> property for this object. Donde <i>vistas</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getVistas()
	{
		return $this->vistas;
	}

	/**
	  * setVistas( $vistas )
	  * 
	  * Set the <i>vistas</i> property for this object. Donde <i>vistas</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>vistas</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setVistas( $vistas )
	{
		$this->vistas = $vistas;
	}

	/**
	  * getEnvios
	  * 
	  * Get the <i>envios</i> property for this object. Donde <i>envios</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getEnvios()
	{
		return $this->envios;
	}

	/**
	  * setEnvios( $envios )
	  * 
	  * Set the <i>envios</i> property for this object. Donde <i>envios</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>envios</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setEnvios( $envios )
	{
		$this->envios = $envios;
	}

	/**
	  * getAceptados
	  * 
	  * Get the <i>aceptados</i> property for this object. Donde <i>aceptados</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getAceptados()
	{
		return $this->aceptados;
	}

	/**
	  * setAceptados( $aceptados )
	  * 
	  * Set the <i>aceptados</i> property for this object. Donde <i>aceptados</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>aceptados</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setAceptados( $aceptados )
	{
		$this->aceptados = $aceptados;
	}

	/**
	  * getDificultad
	  * 
	  * Get the <i>dificultad</i> property for this object. Donde <i>dificultad</i> es  [Campo no documentado]
	  * @return double
	  */
	final public function getDificultad()
	{
		return $this->dificultad;
	}

	/**
	  * setDificultad( $dificultad )
	  * 
	  * Set the <i>dificultad</i> property for this object. Donde <i>dificultad</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>dificultad</i> es de tipo <i>double</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param double
	  */
	final public function setDificultad( $dificultad )
	{
		$this->dificultad = $dificultad;
	}

}
