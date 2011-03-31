<?php
/** Value Object file for table Tags.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alan@caffeina.mx
  * @access public
  * @package docs
  * 
  */

class Tags extends VO
{
	/**
	  * Constructor de Tags
	  * 
	  * Para construir un objeto de tipo Tags debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Tags
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['tag_id']) ){
				$this->tag_id = $data['tag_id'];
			}
			if( isset($data['name']) ){
				$this->name = $data['name'];
			}
			if( isset($data['description']) ){
				$this->description = $data['description'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Tags en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"tag_id" => $this->tag_id,
			"name" => $this->name,
			"description" => $this->description
		); 
	return json_encode($vec); 
	}
	
	/**
	  * tag_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $tag_id;

	/**
	  * name
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(45)
	  */
	protected $name;

	/**
	  * description
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var tinytext,
	  */
	protected $description;

	/**
	  * getTagId
	  * 
	  * Get the <i>tag_id</i> property for this object. Donde <i>tag_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getTagId()
	{
		return $this->tag_id;
	}

	/**
	  * setTagId( $tag_id )
	  * 
	  * Set the <i>tag_id</i> property for this object. Donde <i>tag_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>tag_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setTagId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setTagId( $tag_id )
	{
		$this->tag_id = $tag_id;
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
	  * getDescription
	  * 
	  * Get the <i>description</i> property for this object. Donde <i>description</i> es  [Campo no documentado]
	  * @return tinytext,
	  */
	final public function getDescription()
	{
		return $this->description;
	}

	/**
	  * setDescription( $description )
	  * 
	  * Set the <i>description</i> property for this object. Donde <i>description</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>description</i> es de tipo <i>tinytext,</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param tinytext,
	  */
	final public function setDescription( $description )
	{
		$this->description = $description;
	}

}
