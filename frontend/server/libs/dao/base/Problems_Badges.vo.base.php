<?php
/** Value Object file for table Problems_Badges.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
  * 
  */

class ProblemsBadges extends VO
{
	/**
	  * Constructor de ProblemsBadges
	  * 
	  * Para construir un objeto de tipo ProblemsBadges debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return ProblemsBadges
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			if( isset($data['badge_id']) ){
				$this->badge_id = $data['badge_id'];
			}
			if( isset($data['problem_id']) ){
				$this->problem_id = $data['problem_id'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto ProblemsBadges en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 
			"badge_id" => $this->badge_id,
			"problem_id" => $this->problem_id
		); 
	return json_encode($vec); 
	}
	
	/**
	  * badge_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $badge_id;

	/**
	  * problem_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $problem_id;

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
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setBadgeId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setBadgeId( $badge_id )
	{
		$this->badge_id = $badge_id;
	}

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
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setProblemId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setProblemId( $problem_id )
	{
		$this->problem_id = $problem_id;
	}

}
