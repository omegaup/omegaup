<?php
/** Value Object file for table Users.
  * 
  * VO does not have any behaviour except for storage and retrieval of its own data (accessors and mutators).
  * @author alanboy
  * @access public
  * @package docs
  * 
  */

class Users extends VO
{
	/**
	  * Constructor de Users
	  * 
	  * Para construir un objeto de tipo Users debera llamarse a el constructor 
	  * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo 
	  * cuyos campos son iguales a las variables que constituyen a este objeto.
	  * @return Users
	  */
	function __construct( $data = NULL)
	{ 
		if(isset($data))
		{
			


			if( isset($data['facebook_user_id']) ){
				$this->facebook_user_id = $data['facebook_user_id'];
			}			
			if( isset($data['user_id']) ){
				$this->user_id = $data['user_id'];
			}
			if( isset($data['username']) ){
				$this->username = $data['username'];
			}
			if( isset($data['password']) ){
				$this->password = $data['password'];
			}
			if( isset($data['main_email_id']) ){
				$this->main_email_id = $data['main_email_id'];
			}
			if( isset($data['name']) ){
				$this->name = $data['name'];
			}
			if( isset($data['solved']) ){
				$this->solved = $data['solved'];
			}
			if( isset($data['submissions']) ){
				$this->submissions = $data['submissions'];
			}
			if( isset($data['country_id']) ){
				$this->country_id = $data['country_id'];
			}
			if( isset($data['state_id']) ){
				$this->state_id = $data['state_id'];
			}
			if( isset($data['school_id']) ){
				$this->school_id = $data['school_id'];
			}
			if( isset($data['scholar_degree']) ){
				$this->scholar_degree = $data['scholar_degree'];
			}
			if( isset($data['graduation_date']) ){
				$this->graduation_date = $data['graduation_date'];
			}
			if( isset($data['birth_date']) ){
				$this->birth_date = $data['birth_date'];
			}
			if( isset($data['last_access']) ){
				$this->last_access = $data['last_access'];
			}
			if( isset($data['verified']) ){
				$this->verified = $data['verified'];
			}
			if( isset($data['verification_id']) ){
				$this->verification_id = $data['verification_id'];
			}
		}
	}

	/**
	  * Obtener una representacion en String
	  * 
	  * Este metodo permite tratar a un objeto Users en forma de cadena.
	  * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
	  * @return String 
	  */
	public function __toString( )
	{ 
		$vec = array( 

			"user_id" => $this->user_id,
			"username" => $this->username,
			"facebook_user_id" => $this->facebook_user_id,			
			"password" => $this->password,
			"main_email_id" => $this->main_email_id,
			"name" => $this->name,
			"solved" => $this->solved,
			"submissions" => $this->submissions,
			"country_id" => $this->country_id,
			"state_id" => $this->state_id,
			"school_id" => $this->school_id,
			"scholar_degree" => $this->scholar_degree,
			"graduation_date" => $this->graduation_date,
			"birth_date" => $this->birth_date,
			"last_access" => $this->last_access
		); 
	return json_encode($vec); 
	}
	
	/**
	  * user_id
	  * 
	  *  [Campo no documentado]<br>
	  * <b>Llave Primaria</b><br>
	  * <b>Auto Incremento</b><br>
	  * @access protected
	  * @var int(11)
	  */
	protected $user_id;

	/**
	  * username
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(50)
	  */
	protected $username;

	protected $facebook_user_id;
	
	/**
	  * password
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var char(32)
	  */
	protected $password;

	/**
	  * main_email_id
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $main_email_id;

	/**
	  * name
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(256)
	  */
	protected $name;

	/**
	  * solved
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $solved;

	/**
	  * submissions
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $submissions;

	/**
	  * country_id
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var char(3)
	  */
	protected $country_id;

	/**
	  * state_id
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $state_id;

	/**
	  * school_id
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var int(11)
	  */
	protected $school_id;

	/**
	  * scholar_degree
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var varchar(64)
	  */
	protected $scholar_degree;

	/**
	  * graduation_date
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var date
	  */
	protected $graduation_date;

	/**
	  * birth_date
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var date
	  */
	protected $birth_date;

	/**
	  * last_access
	  * 
	  *  [Campo no documentado]<br>
	  * @access protected
	  * @var timestamp
	  */
	protected $last_access;
	
	protected $verified;
	protected $verification_id;




	final public function getFacebookUserId(){
		return $this->facebook_user_id;
	}

	final public function setFacebookUserId($facebook_user_id){
		$this->facebook_user_id = $facebook_user_id;
	}
	
	
	/**
	  * getUserId
	  * 
	  * Get the <i>user_id</i> property for this object. Donde <i>user_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getUserId()
	{
		return $this->user_id;
	}

	/**
	  * setUserId( $user_id )
	  * 
	  * Set the <i>user_id</i> property for this object. Donde <i>user_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>user_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * <br><br>Esta propiedad se mapea con un campo que es de <b>Auto Incremento</b> !<br>
	  * No deberias usar setUserId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * <br><br>Esta propiedad se mapea con un campo que es una <b>Llave Primaria</b> !<br>
	  * No deberias usar setUserId( ) a menos que sepas exactamente lo que estas haciendo.<br>
	  * @param int(11)
	  */
	final public function setUserId( $user_id )
	{
		$this->user_id = $user_id;
	}

	/**
	  * getUsername
	  * 
	  * Get the <i>username</i> property for this object. Donde <i>username</i> es  [Campo no documentado]
	  * @return varchar(50)
	  */
	final public function getUsername()
	{
		return $this->username;
	}

	/**
	  * setUsername( $username )
	  * 
	  * Set the <i>username</i> property for this object. Donde <i>username</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>username</i> es de tipo <i>varchar(50)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(50)
	  */
	final public function setUsername( $username )
	{
		$this->username = $username;
	}

	/**
	  * getPassword
	  * 
	  * Get the <i>password</i> property for this object. Donde <i>password</i> es  [Campo no documentado]
	  * @return char(32)
	  */
	final public function getPassword()
	{
		return $this->password;
	}

	/**
	  * setPassword( $password )
	  * 
	  * Set the <i>password</i> property for this object. Donde <i>password</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>password</i> es de tipo <i>char(32)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param char(32)
	  */
	final public function setPassword( $password )
	{
		$this->password = $password;
	}

	/**
	  * getMainEmailId
	  * 
	  * Get the <i>main_email_id</i> property for this object. Donde <i>main_email_id</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getMainEmailId()
	{
		return $this->main_email_id;
	}

	/**
	  * setMainEmailId( $main_email_id )
	  * 
	  * Set the <i>main_email_id</i> property for this object. Donde <i>main_email_id</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>main_email_id</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setMainEmailId( $main_email_id )
	{
		$this->main_email_id = $main_email_id;
	}

	/**
	  * getName
	  * 
	  * Get the <i>name</i> property for this object. Donde <i>name</i> es  [Campo no documentado]
	  * @return varchar(256)
	  */
	final public function getName()
	{
		return $this->name;
	}

	/**
	  * setName( $name )
	  * 
	  * Set the <i>name</i> property for this object. Donde <i>name</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>name</i> es de tipo <i>varchar(256)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(256)
	  */
	final public function setName( $name )
	{
		$this->name = $name;
	}

	/**
	  * getSolved
	  * 
	  * Get the <i>solved</i> property for this object. Donde <i>solved</i> es  [Campo no documentado]
	  * @return int(11)
	  */
	final public function getSolved()
	{
		return $this->solved;
	}

	/**
	  * setSolved( $solved )
	  * 
	  * Set the <i>solved</i> property for this object. Donde <i>solved</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>solved</i> es de tipo <i>int(11)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param int(11)
	  */
	final public function setSolved( $solved )
	{
		$this->solved = $solved;
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
	  * @param int(11)
	  */
	final public function setSchoolId( $school_id )
	{
		$this->school_id = $school_id;
	}

	/**
	  * getScholarDegree
	  * 
	  * Get the <i>scholar_degree</i> property for this object. Donde <i>scholar_degree</i> es  [Campo no documentado]
	  * @return varchar(64)
	  */
	final public function getScholarDegree()
	{
		return $this->scholar_degree;
	}

	/**
	  * setScholarDegree( $scholar_degree )
	  * 
	  * Set the <i>scholar_degree</i> property for this object. Donde <i>scholar_degree</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>scholar_degree</i> es de tipo <i>varchar(64)</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param varchar(64)
	  */
	final public function setScholarDegree( $scholar_degree )
	{
		$this->scholar_degree = $scholar_degree;
	}

	/**
	  * getGraduationDate
	  * 
	  * Get the <i>graduation_date</i> property for this object. Donde <i>graduation_date</i> es  [Campo no documentado]
	  * @return date
	  */
	final public function getGraduationDate()
	{
		return $this->graduation_date;
	}

	/**
	  * setGraduationDate( $graduation_date )
	  * 
	  * Set the <i>graduation_date</i> property for this object. Donde <i>graduation_date</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>graduation_date</i> es de tipo <i>date</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param date
	  */
	final public function setGraduationDate( $graduation_date )
	{
		$this->graduation_date = $graduation_date;
	}

	/**
	  * getBirthDate
	  * 
	  * Get the <i>birth_date</i> property for this object. Donde <i>birth_date</i> es  [Campo no documentado]
	  * @return date
	  */
	final public function getBirthDate()
	{
		return $this->birth_date;
	}

	/**
	  * setBirthDate( $birth_date )
	  * 
	  * Set the <i>birth_date</i> property for this object. Donde <i>birth_date</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>birth_date</i> es de tipo <i>date</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param date
	  */
	final public function setBirthDate( $birth_date )
	{
		$this->birth_date = $birth_date;
	}

	/**
	  * getLastAccess
	  * 
	  * Get the <i>last_access</i> property for this object. Donde <i>last_access</i> es  [Campo no documentado]
	  * @return timestamp
	  */
	final public function getLastAccess()
	{
		return $this->last_access;
	}

	/**
	  * setLastAccess( $last_access )
	  * 
	  * Set the <i>last_access</i> property for this object. Donde <i>last_access</i> es  [Campo no documentado].
	  * Una validacion basica se hara aqui para comprobar que <i>last_access</i> es de tipo <i>timestamp</i>. 
	  * Si esta validacion falla, se arrojara... algo. 
	  * @param timestamp
	  */
	final public function setLastAccess( $last_access )
	{
		$this->last_access = $last_access;
	}

	final public function setVerified($verified) {
		$this->verified = $verified;
	}
	
	final public function getVerified() {
		return $this->verified;
	}
	
	final public function setVerificationId($verification_id) {
		$this->verification_id = $verification_id;
	}
	
	final public function getVerificationId() {
		return $this->verification_id;
	}


	/**
	  * Converts date fields to timestamps
	  * 
	  **/
	public function toUnixTime( array $fields = array() ){
		if(count($fields) > 0 )
			parent::toUnixTime( $fields );
		else
			parent::toUnixTime( array( "last_access" ) );
	}
}
