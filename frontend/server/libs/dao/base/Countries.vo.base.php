<?php
/** Value Object file for table Countries.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
  * 
  */

class Countries extends VO
{
	/**
	  * Constructor de Countries
	  * 
	  * Para construir un objeto de tipo Countries debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Countries
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
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
	  * Este metodo permite tratar a un objeto Countries en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"country_id" => $this->country_id,
			"name" => $this->name
		); 
	return json_encode($vec); 
	}
	
	/**
	  * country_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
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
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setCountryId( ) a menos que sepas exactamente lo que estas haciendo.<br>
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
