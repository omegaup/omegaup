<?php
/** Value Object file for table Schools.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
  * 
  */

class Schools extends VO
{
	/**
	  * Constructor de Schools
	  * 
	  * Para construir un objeto de tipo Schools debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Schools
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['school_id']) ){
				$this->school_id = $data['school_id'];
			}
			if( isset($data['state_id']) ){
				$this->state_id = $data['state_id'];
			}
			if( isset($data['name']) ){
				$this->name = $data['name'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Schools en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"school_id" => $this->school_id,
			"state_id" => $this->state_id,
			"name" => $this->name
		); 
	return json_encode($vec); 
	}
	
	/**
	  * school_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $school_id;

	/**
	  * state_id
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $state_id;

	/**
	  * name
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(50)
	  */
	protected $name;

	/**
	  * getSchoolId
	  * 
	  * Get the <i>school_id</i> property for this object. Donde <i>school_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getSchoolId()
	{
		return $this->school_id;
	}

	/**
	  * setSchoolId( $school_id )
	  * 
	  * Set the <i>school_id</i> property for this object. Donde <i>school_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>school_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setSchoolId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setSchoolId( $school_id )
	{
		$this->school_id = $school_id;
	}

	/**
	  * getStateId
	  * 
	  * Get the <i>state_id</i> property for this object. Donde <i>state_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getStateId()
	{
		return $this->state_id;
	}

	/**
	  * setStateId( $state_id )
	  * 
	  * Set the <i>state_id</i> property for this object. Donde <i>state_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>state_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setStateId( $state_id )
	{
		$this->state_id = $state_id;
	}

	/**
	  * getName
	  * 
	  * Get the <i>name</i> property for this object. Donde <i>name</i> es  [Campo no documentado]
	  * @return varchar(50)
	  */
	final public function getName()
	{
		return $this->name;
	}

	/**
	  * setName( $name )
	  * 
	  * Set the <i>name</i> property for this object. Donde <i>name</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>name</i> es de tipo <i>varchar(50)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(50)
	  */
	final public function setName( $name )
	{
		$this->name = $name;
	}

}
