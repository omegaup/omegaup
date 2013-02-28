<?php
/** Value Object file for table States.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
  * 
  */

class States extends VO
{
	/**
	  * Constructor de States
	  * 
	  * Para construir un objeto de tipo States debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return States
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['state_id']) ){
				$this->state_id = $data['state_id'];
			}
			if( isset($data['country_id']) ){
				$this->country_id = $data['country_id'];
			}
			if( isset($data['name']) ){
				$this->name = $data['name'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto States en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"state_id" => $this->state_id,
			"country_id" => $this->country_id,
			"name" => $this->name
		); 
	return json_encode($vec); 
	}
	
	/**
	  * state_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $state_id;

	/**
	  * country_id
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var char(3)
	  */
	protected $country_id;

	/**
	  * name
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(50)
	  */
	protected $name;

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
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setStateId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setStateId( $state_id )
	{
		$this->state_id = $state_id;
	}

	/**
	  * getCountryId
	  * 
	  * Get the <i>country_id</i> property for this object. Donde <i>country_id</i> es  [Campo no documentado]
	  * @return char(3)
	  */
	final public function getCountryId()
	{
		return $this->country_id;
	}

	/**
	  * setCountryId( $country_id )
	  * 
	  * Set the <i>country_id</i> property for this object. Donde <i>country_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>country_id</i> es de tipo <i>char(3)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param char(3)
	  */
	final public function setCountryId( $country_id )
	{
		$this->country_id = $country_id;
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
