<?php
/** Value Object file for table Languages.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
  * 
  */

class Languages extends VO
{
	/**
	  * Constructor de Languages
	  * 
	  * Para construir un objeto de tipo Languages debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Languages
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['language_id']) ){
				$this->language_id = $data['language_id'];
			}
			if( isset($data['name']) ){
				$this->name = $data['name'];
			}
			if( isset($data['country_id']) ){
				$this->country_id = $data['country_id'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Languages en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"language_id" => $this->language_id,
			"name" => $this->name,
			"country_id" => $this->country_id
		); 
	return json_encode($vec); 
	}
	
	/**
	  * language_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * <b>Auto Incremento</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $language_id;

	/**
	  * name
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(45)
	  */
	protected $name;

	/**
	  * country_id
	  * 
	  * Se guarda la relaciÃ³n con el paÃ­s para defaultear mÃ¡s rÃ¡pido.<br>
	  * @access protected
	  * @var char(3)
	  */
	protected $country_id;

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
	  * <br><br>Esta propiedad se mapea con un campo que es de <b>Auto Incremento</b> !<br>
	  * No deberias usar setLanguageId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setLanguageId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setLanguageId( $language_id )
	{
		$this->language_id = $language_id;
	}

	/**
	  * getName
	  * 
	  * Get the <i>name</i> property for this object. Donde <i>name</i> es  [Campo no documentado]
	  * @return varchar(45)
	  */
	final public function getName()
	{
		return $this->name;
	}

	/**
	  * setName( $name )
	  * 
	  * Set the <i>name</i> property for this object. Donde <i>name</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>name</i> es de tipo <i>varchar(45)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(45)
	  */
	final public function setName( $name )
	{
		$this->name = $name;
	}

	/**
	  * getCountryId
	  * 
	  * Get the <i>country_id</i> property for this object. Donde <i>country_id</i> es Se guarda la relaciÃ³n con el paÃ­s para defaultear mÃ¡s rÃ¡pido.
	  * @return char(3)
	  */
	final public function getCountryId()
	{
		return $this->country_id;
	}

	/**
	  * setCountryId( $country_id )
	  * 
	  * Set the <i>country_id</i> property for this object. Donde <i>country_id</i> es Se guarda la relaciÃ³n con el paÃ­s para defaultear mÃ¡s rÃ¡pido..
	  * Una validacion basica se hara aqui para comprobar que <i>country_id</i> es de tipo <i>char(3)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param char(3)
	  */
	final public function setCountryId( $country_id )
	{
		$this->country_id = $country_id;
	}

}
