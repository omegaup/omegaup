<?php
/** Value Object file for table Problems.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
  * 
  */

class Problems extends VO
{
	/**
	  * Constructor de Problems
	  * 
	  * Para construir un objeto de tipo Problems debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Problems
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['problem_id']) ){
				$this->problem_id = $data['problem_id'];
			}
			if( isset($data['public']) ){
				$this->public = $data['public'];
			}
			if( isset($data['author_id']) ){
				$this->author_id = $data['author_id'];
			}
			if( isset($data['title']) ){
				$this->title = $data['title'];
			}
			if( isset($data['alias']) ){
				$this->alias = $data['alias'];
			}
			if( isset($data['validator']) ){
				$this->validator = $data['validator'];
			}
			if( isset($data['server']) ){
				$this->server = $data['server'];
			}
			if( isset($data['remote_id']) ){
				$this->remote_id = $data['remote_id'];
			}
			if( isset($data['time_limit']) ){
				$this->time_limit = $data['time_limit'];
			}
			if( isset($data['memory_limit']) ){
				$this->memory_limit = $data['memory_limit'];
			}
			if( isset($data['visits']) ){
				$this->visits = $data['visits'];
			}
			if( isset($data['submissions']) ){
				$this->submissions = $data['submissions'];
			}
			if( isset($data['accepted']) ){
				$this->accepted = $data['accepted'];
			}
			if( isset($data['difficulty']) ){
				$this->difficulty = $data['difficulty'];
			}
			if( isset($data['creation_date']) ){
				$this->creation_date = $data['creation_date'];
			}
			if( isset($data['source']) ){
				$this->source = $data['source'];
			}
			if( isset($data['order']) ){
				$this->order = $data['order'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Problems en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"problem_id" => $this->problem_id,
			"public" => $this->public,
			"author_id" => $this->author_id,
			"title" => $this->title,
			"alias" => $this->alias,
			"validator" => $this->validator,
			"server" => $this->server,
			"remote_id" => $this->remote_id,
			"time_limit" => $this->time_limit,
			"memory_limit" => $this->memory_limit,
			"visits" => $this->visits,
			"submissions" => $this->submissions,
			"accepted" => $this->accepted,
			"difficulty" => $this->difficulty,
			"creation_date" => $this->creation_date,
			"source" => $this->source,
			"order" => $this->order
		); 
	return json_encode($vec); 
	}
	
	/**
	  * problem_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * <b>Auto Incremento</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $problem_id;

	/**
	  * public
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var tinyint(1)
	  */
	protected $public;

	/**
	  * author_id
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $author_id;

	/**
	  * title
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(256)
	  */
	protected $title;

	/**
	  * alias
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(10)
	  */
	protected $alias;

	/**
	  * validator
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var enum('remote','literal','token','token-caseless','token-numeric')
	  */
	protected $validator;

	/**
	  * server
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var enum('uva','livearchive','pku','tju','spoj')
	  */
	protected $server;

	/**
	  * remote_id
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(10)
	  */
	protected $remote_id;

	/**
	  * time_limit
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $time_limit;

	/**
	  * memory_limit
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $memory_limit;

	/**
	  * visits
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $visits;

	/**
	  * submissions
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $submissions;

	/**
	  * accepted
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $accepted;

	/**
	  * difficulty
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var double
	  */
	protected $difficulty;

	/**
	  * creation_date
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var timestamp
	  */
	protected $creation_date;

	/**
	  * source
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(256)
	  */
	protected $source;

	/**
	  * order
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var enum('normal','inverse')
	  */
	protected $order;

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
	  * <br><br>Esta propiedad se mapea con un campo que es de <b>Auto Incremento</b> !<br>
	  * No deberias usar setProblemId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setProblemId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setProblemId( $problem_id )
	{
		$this->problem_id = $problem_id;
	}

	/**
	  * getPublic
	  * 
	  * Get the <i>public</i> property for this object. Donde <i>public</i> es  [Campo no documentado]
	  * @return tinyint(1)
	  */
	final public function getPublic()
	{
		return $this->public;
	}

	/**
	  * setPublic( $public )
	  * 
	  * Set the <i>public</i> property for this object. Donde <i>public</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>public</i> es de tipo <i>tinyint(1)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param tinyint(1)
	  */
	final public function setPublic( $public )
	{
		$this->public = $public;
	}

	/**
	  * getAuthorId
	  * 
	  * Get the <i>author_id</i> property for this object. Donde <i>author_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getAuthorId()
	{
		return $this->author_id;
	}

	/**
	  * setAuthorId( $author_id )
	  * 
	  * Set the <i>author_id</i> property for this object. Donde <i>author_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>author_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setAuthorId( $author_id )
	{
		$this->author_id = $author_id;
	}

	/**
	  * getTitle
	  * 
	  * Get the <i>title</i> property for this object. Donde <i>title</i> es  [Campo no documentado]
	  * @return varchar(256)
	  */
	final public function getTitle()
	{
		return $this->title;
	}

	/**
	  * setTitle( $title )
	  * 
	  * Set the <i>title</i> property for this object. Donde <i>title</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>title</i> es de tipo <i>varchar(256)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(256)
	  */
	final public function setTitle( $title )
	{
		$this->title = $title;
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
	  * getValidator
	  * 
	  * Get the <i>validator</i> property for this object. Donde <i>validator</i> es  [Campo no documentado]
	  * @return enum('remote','literal','token','token-caseless','token-numeric')
	  */
	final public function getValidator()
	{
		return $this->validator;
	}

	/**
	  * setValidator( $validator )
	  * 
	  * Set the <i>validator</i> property for this object. Donde <i>validator</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>validator</i> es de tipo <i>enum('remote','literal','token','token-caseless','token-numeric')</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param enum('remote','literal','token','token-caseless','token-numeric')
	  */
	final public function setValidator( $validator )
	{
		$this->validator = $validator;
	}

	/**
	  * getServer
	  * 
	  * Get the <i>server</i> property for this object. Donde <i>server</i> es  [Campo no documentado]
	  * @return enum('uva','livearchive','pku','tju','spoj')
	  */
	final public function getServer()
	{
		return $this->server;
	}

	/**
	  * setServer( $server )
	  * 
	  * Set the <i>server</i> property for this object. Donde <i>server</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>server</i> es de tipo <i>enum('uva','livearchive','pku','tju','spoj')</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param enum('uva','livearchive','pku','tju','spoj')
	  */
	final public function setServer( $server )
	{
		$this->server = $server;
	}

	/**
	  * getRemoteId
	  * 
	  * Get the <i>remote_id</i> property for this object. Donde <i>remote_id</i> es  [Campo no documentado]
	  * @return varchar(10)
	  */
	final public function getRemoteId()
	{
		return $this->remote_id;
	}

	/**
	  * setRemoteId( $remote_id )
	  * 
	  * Set the <i>remote_id</i> property for this object. Donde <i>remote_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>remote_id</i> es de tipo <i>varchar(10)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(10)
	  */
	final public function setRemoteId( $remote_id )
	{
		$this->remote_id = $remote_id;
	}

	/**
	  * getTimeLimit
	  * 
	  * Get the <i>time_limit</i> property for this object. Donde <i>time_limit</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getTimeLimit()
	{
		return $this->time_limit;
	}

	/**
	  * setTimeLimit( $time_limit )
	  * 
	  * Set the <i>time_limit</i> property for this object. Donde <i>time_limit</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>time_limit</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setTimeLimit( $time_limit )
	{
		$this->time_limit = $time_limit;
	}

	/**
	  * getMemoryLimit
	  * 
	  * Get the <i>memory_limit</i> property for this object. Donde <i>memory_limit</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getMemoryLimit()
	{
		return $this->memory_limit;
	}

	/**
	  * setMemoryLimit( $memory_limit )
	  * 
	  * Set the <i>memory_limit</i> property for this object. Donde <i>memory_limit</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>memory_limit</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setMemoryLimit( $memory_limit )
	{
		$this->memory_limit = $memory_limit;
	}

	/**
	  * getVisits
	  * 
	  * Get the <i>visits</i> property for this object. Donde <i>visits</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getVisits()
	{
		return $this->visits;
	}

	/**
	  * setVisits( $visits )
	  * 
	  * Set the <i>visits</i> property for this object. Donde <i>visits</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>visits</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setVisits( $visits )
	{
		$this->visits = $visits;
	}

	/**
	  * getSubmissions
	  * 
	  * Get the <i>submissions</i> property for this object. Donde <i>submissions</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getSubmissions()
	{
		return $this->submissions;
	}

	/**
	  * setSubmissions( $submissions )
	  * 
	  * Set the <i>submissions</i> property for this object. Donde <i>submissions</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>submissions</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setSubmissions( $submissions )
	{
		$this->submissions = $submissions;
	}

	/**
	  * getAccepted
	  * 
	  * Get the <i>accepted</i> property for this object. Donde <i>accepted</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getAccepted()
	{
		return $this->accepted;
	}

	/**
	  * setAccepted( $accepted )
	  * 
	  * Set the <i>accepted</i> property for this object. Donde <i>accepted</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>accepted</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setAccepted( $accepted )
	{
		$this->accepted = $accepted;
	}

	/**
	  * getDifficulty
	  * 
	  * Get the <i>difficulty</i> property for this object. Donde <i>difficulty</i> es  [Campo no documentado]
	  * @return double
	  */
	final public function getDifficulty()
	{
		return $this->difficulty;
	}

	/**
	  * setDifficulty( $difficulty )
	  * 
	  * Set the <i>difficulty</i> property for this object. Donde <i>difficulty</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>difficulty</i> es de tipo <i>double</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param double
	  */
	final public function setDifficulty( $difficulty )
	{
		$this->difficulty = $difficulty;
	}

	/**
	  * getCreationDate
	  * 
	  * Get the <i>creation_date</i> property for this object. Donde <i>creation_date</i> es  [Campo no documentado]
	  * @return timestamp
	  */
	final public function getCreationDate()
	{
		return $this->creation_date;
	}

	/**
	  * setCreationDate( $creation_date )
	  * 
	  * Set the <i>creation_date</i> property for this object. Donde <i>creation_date</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>creation_date</i> es de tipo <i>timestamp</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param timestamp
	  */
	final public function setCreationDate( $creation_date )
	{
		$this->creation_date = $creation_date;
	}

	/**
	  * getSource
	  * 
	  * Get the <i>source</i> property for this object. Donde <i>source</i> es  [Campo no documentado]
	  * @return varchar(256)
	  */
	final public function getSource()
	{
		return $this->source;
	}

	/**
	  * setSource( $source )
	  * 
	  * Set the <i>source</i> property for this object. Donde <i>source</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>source</i> es de tipo <i>varchar(256)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(256)
	  */
	final public function setSource( $source )
	{
		$this->source = $source;
	}

	/**
	  * getOrder
	  * 
	  * Get the <i>order</i> property for this object. Donde <i>order</i> es  [Campo no documentado]
	  * @return enum('normal','inverse')
	  */
	final public function getOrder()
	{
		return $this->order;
	}

	/**
	  * setOrder( $order )
	  * 
	  * Set the <i>order</i> property for this object. Donde <i>order</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>order</i> es de tipo <i>enum('normal','inverse')</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param enum('normal','inverse')
	  */
	final public function setOrder( $order )
	{
		$this->order = $order;
	}

}
