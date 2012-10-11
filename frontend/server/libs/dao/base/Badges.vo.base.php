<?php
/** Value Object file for table Badges.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
  * 
  */

class Badges extends VO
{
	/**
	  * Constructor de Badges
	  * 
	  * Para construir un objeto de tipo Badges debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Badges
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['badge_id']) ){
				$this->badge_id = $data['badge_id'];
			}
			if( isset($data['name']) ){
				$this->name = $data['name'];
			}
			if( isset($data['image_url']) ){
				$this->image_url = $data['image_url'];
			}
			if( isset($data['description']) ){
				$this->description = $data['description'];
			}
			if( isset($data['hint']) ){
				$this->hint = $data['hint'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Badges en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"badge_id" => $this->badge_id,
			"name" => $this->name,
			"image_url" => $this->image_url,
			"description" => $this->description,
			"hint" => $this->hint
		); 
	return json_encode($vec); 
	}
	
	/**
	  * badge_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * <b>Auto Incremento</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $badge_id;

	/**
	  * name
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(45)
	  */
	protected $name;

	/**
	  * image_url
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(45)
	  */
	protected $image_url;

	/**
	  * description
	  * 
	  * La descripcion habla de como se obtuvo el badge, de forma corta.<br>
	  * @access protected
	  * @var varchar(500)
	  */
	protected $description;

	/**
	  * hint
	  * 
	  * Tip de como desbloquear el badge.<br>
	  * @access protected
	  * @var varchar(100)
	  */
	protected $hint;

	/**
	  * getBadgeId
	  * 
	  * Get the <i>badge_id</i> property for this object. Donde <i>badge_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getBadgeId()
	{
		return $this->badge_id;
	}

	/**
	  * setBadgeId( $badge_id )
	  * 
	  * Set the <i>badge_id</i> property for this object. Donde <i>badge_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>badge_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es de <b>Auto Incremento</b> !<br>
	  * No deberias usar setBadgeId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setBadgeId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setBadgeId( $badge_id )
	{
		$this->badge_id = $badge_id;
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
	  * getImageUrl
	  * 
	  * Get the <i>image_url</i> property for this object. Donde <i>image_url</i> es  [Campo no documentado]
	  * @return varchar(45)
	  */
	final public function getImageUrl()
	{
		return $this->image_url;
	}

	/**
	  * setImageUrl( $image_url )
	  * 
	  * Set the <i>image_url</i> property for this object. Donde <i>image_url</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>image_url</i> es de tipo <i>varchar(45)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(45)
	  */
	final public function setImageUrl( $image_url )
	{
		$this->image_url = $image_url;
	}

	/**
	  * getDescription
	  * 
	  * Get the <i>description</i> property for this object. Donde <i>description</i> es La descripcion habla de como se obtuvo el badge, de forma corta.
	  * @return varchar(500)
	  */
	final public function getDescription()
	{
		return $this->description;
	}

	/**
	  * setDescription( $description )
	  * 
	  * Set the <i>description</i> property for this object. Donde <i>description</i> es La descripcion habla de como se obtuvo el badge, de forma corta..
	  * Una validacion basica se hara aqui para comprobar que <i>description</i> es de tipo <i>varchar(500)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(500)
	  */
	final public function setDescription( $description )
	{
		$this->description = $description;
	}

	/**
	  * getHint
	  * 
	  * Get the <i>hint</i> property for this object. Donde <i>hint</i> es Tip de como desbloquear el badge.
	  * @return varchar(100)
	  */
	final public function getHint()
	{
		return $this->hint;
	}

	/**
	  * setHint( $hint )
	  * 
	  * Set the <i>hint</i> property for this object. Donde <i>hint</i> es Tip de como desbloquear el badge..
	  * Una validacion basica se hara aqui para comprobar que <i>hint</i> es de tipo <i>varchar(100)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(100)
	  */
	final public function setHint( $hint )
	{
		$this->hint = $hint;
	}

}
