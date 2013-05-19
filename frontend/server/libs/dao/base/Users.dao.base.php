<?php
/** Users Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link Users }. 
  * @author alanboy
  * @access private
  * @abstract
  * @package docs
  * 
  */
abstract class UsersDAOBase extends DAO
{

		private static $loadedRecords = array();

		private static function recordExists(  $user_id ){
			return false;
			$pk = "";
			$pk .= $user_id . "-";
			return array_key_exists ( $pk , self::$loadedRecords );
			
		}
		private static function pushRecord( $inventario,  $user_id){
			$pk = "";
			$pk .= $user_id . "-";
			self::$loadedRecords [$pk] = $inventario;
		}
		private static function getRecord(  $user_id ){
			$pk = "";
			$pk .= $user_id . "-";
			return self::$loadedRecords[$pk];
		}
	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link Users} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param Users [$Users] El objeto de tipo Users
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Users )
	{
		if(  self::getByPK(  $Users->getUserId() ) !== NULL )
		{
			try{ return UsersDAOBase::update( $Users) ; } catch(Exception $e){ throw $e; }
		}else{
			try{ return UsersDAOBase::create( $Users) ; } catch(Exception $e){ throw $e; }
		}
	}


	/**
	  *	Obtener {@link Users} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link Users} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link Users Un objeto del tipo {@link Users}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $user_id )
	{
		if(self::recordExists(  $user_id)){
			return self::getRecord( $user_id );
		}
		$sql = "SELECT * FROM Users WHERE (user_id = ? ) LIMIT 1;";
		$params = array(  $user_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
			$foo = new Users( $rs );
			self::pushRecord( $foo,  $user_id );
			return $foo;
	}


	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link Users}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link Users}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Users";
		if($orden != NULL)
		{ $sql .= " ORDER BY " . $orden . " " . $tipo_de_orden;	}
		if($pagina != NULL)
		{
			$sql .= " LIMIT " . (( $pagina - 1 )*$columnas_por_pagina) . "," . $columnas_por_pagina; 
		}
		global $conn;
		$rs = $conn->Execute($sql);
		$allData = array();
		foreach ($rs as $foo) {
			$bar = new Users($foo);
    		array_push( $allData, $bar);
			//user_id
    		self::pushRecord( $bar, $foo["user_id"] );
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Users} de la base de datos. 
	  * Consiste en buscar todos los objetos que coinciden con las variables permanentes instanciadas de objeto pasado como argumento. 
	  * Aquellas variables que tienen valores NULL seran excluidos en busca de criterios.
	  *	
	  * <code>
	  *  /**
	  *   * Ejemplo de uso - buscar todos los clientes que tengan limite de credito igual a 20000
	  *   {@*} 
	  *	  $cliente = new Cliente();
	  *	  $cliente->setLimiteCredito("20000");
	  *	  $resultados = ClienteDAO::search($cliente);
	  *	  
	  *	  foreach($resultados as $c ){
	  *	  	echo $c->getNombre() . "<br>";
	  *	  }
	  * </code>
	  *	@static
	  * @param Users [$Users] El objeto de tipo Users
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Users , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Users WHERE ("; 
		$val = array();
		if( $Users->getUserId() != NULL){
			$sql .= " user_id = ? AND";
			array_push( $val, $Users->getUserId() );
		}

		if( $Users->getUsername() != NULL){
			$sql .= " username = ? AND";
			array_push( $val, $Users->getUsername() );
		}

		if( $Users->getFacebookUserId() != NULL){
			$sql .= " facebook_user_id = ? AND";
			array_push( $val, $Users->getFacebookUserId() );
		}

		if( $Users->getPassword() != NULL){
			$sql .= " password = ? AND";
			array_push( $val, $Users->getPassword() );
		}

		if( $Users->getMainEmailId() != NULL){
			$sql .= " main_email_id = ? AND";
			array_push( $val, $Users->getMainEmailId() );
		}

		if( $Users->getName() != NULL){
			$sql .= " name = ? AND";
			array_push( $val, $Users->getName() );
		}

		if( $Users->getSolved() != NULL){
			$sql .= " solved = ? AND";
			array_push( $val, $Users->getSolved() );
		}

		if( $Users->getSubmissions() != NULL){
			$sql .= " submissions = ? AND";
			array_push( $val, $Users->getSubmissions() );
		}

		if( $Users->getCountryId() != NULL){
			$sql .= " country_id = ? AND";
			array_push( $val, $Users->getCountryId() );
		}

		if( $Users->getStateId() != NULL){
			$sql .= " state_id = ? AND";
			array_push( $val, $Users->getStateId() );
		}

		if( $Users->getSchoolId() != NULL){
			$sql .= " school_id = ? AND";
			array_push( $val, $Users->getSchoolId() );
		}

		if( $Users->getScholarDegree() != NULL){
			$sql .= " scholar_degree = ? AND";
			array_push( $val, $Users->getScholarDegree() );
		}

		if( $Users->getGraduationDate() != NULL){
			$sql .= " graduation_date = ? AND";
			array_push( $val, $Users->getGraduationDate() );
		}

		if( $Users->getBirthDate() != NULL){
			$sql .= " birth_date = ? AND";
			array_push( $val, $Users->getBirthDate() );
		}

		if( $Users->getLastAccess() != NULL){
			$sql .= " last_access = ? AND";
			array_push( $val, $Users->getLastAccess() );
		}
		
		if ($Users->getVerificationId() != NULL) {
			$sql .= " verification_id = ? AND";
			array_push( $val, $Users->getVerificationId() );
		}

		if(sizeof($val) == 0){return array();}
		$sql = substr($sql, 0, -3) . " )";
		if( $orderBy !== null ){
		    $sql .= " order by " . $orderBy . " " . $orden ;
		
		}
		global $conn;

		$rs = $conn->Execute($sql, $val);
		$ar = array();
		foreach ($rs as $foo) {
			$bar =  new Users($foo);
    		array_push( $ar,$bar);
    		self::pushRecord( $bar, $foo["user_id"] );
		}
		return $ar;
	}


	/**
	  *	Actualizar registros.
	  *	
	  * Este metodo es un metodo de ayuda para uso interno. Se ejecutara todas las manipulaciones
	  * en la base de datos que estan dadas en el objeto pasado.No se haran consultas SELECT 
	  * aqui, sin embargo. El valor de retorno indica cu�ntas filas se vieron afectadas.
	  *	
	  * @internal private information for advanced developers only
	  * @return Filas afectadas o un string con la descripcion del error
	  * @param Users [$Users] El objeto de tipo Users a actualizar.
	  **/
	private static final function update( $Users )
	{ 
		$sql = "UPDATE Users SET  username = ?, password = ?, facebook_user_id = ?, main_email_id = ?, name = ?, solved = ?, submissions = ?, country_id = ?, state_id = ?, school_id = ?, scholar_degree = ?, graduation_date = ?, birth_date = ?, last_access = ?, verified = ?, verification_id = ? WHERE  user_id = ?;";
		$params = array( 
			$Users->getUsername(), 
			$Users->getPassword(), 
			$Users->getFacebookUserId(),
			$Users->getMainEmailId(), 
			$Users->getName(), 
			$Users->getSolved(), 
			$Users->getSubmissions(), 
			$Users->getCountryId(), 
			$Users->getStateId(), 
			$Users->getSchoolId(), 
			$Users->getScholarDegree(), 
			$Users->getGraduationDate(), 
			$Users->getBirthDate(), 
			$Users->getLastAccess(), 
			$Users->getVerified(),
			$Users->getVerificationId(),
			$Users->getUserId(),
			);
		global $conn;		
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		return $conn->Affected_Rows();
	}


	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto Users suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto Users dentro de la misma transaccion.
	  *	
	  * @internal private information for advanced developers only
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param Users [$Users] El objeto de tipo Users a crear.
	  **/
	private static final function create( &$Users )
	{ 
		$sql = "INSERT INTO Users ( user_id, username, facebook_user_id, password, main_email_id, name, solved, submissions, country_id, state_id, school_id, scholar_degree, graduation_date, birth_date, last_access, verified, verification_id ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
		$params = array( 
			$Users->getUserId(), 
			$Users->getUsername(), 
			$Users->getFacebookUserId(),
			$Users->getPassword(), 
			$Users->getMainEmailId(), 
			$Users->getName(), 
			$Users->getSolved(), 
			$Users->getSubmissions(), 
			$Users->getCountryId(), 
			$Users->getStateId(), 
			$Users->getSchoolId(), 
			$Users->getScholarDegree(), 
			$Users->getGraduationDate(), 
			$Users->getBirthDate(), 
			$Users->getLastAccess(), 
			$Users->getVerified(),
			$Users->getVerificationId(),
		 );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		$ar = $conn->Affected_Rows();
		if($ar == 0) return 0;
		/* save autoincremented value on obj */  $Users->setUserId( $conn->Insert_ID() ); /*  */ 
		return $ar;
	}


	/**
	  *	Buscar por rango.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Users} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Users}.
	  * 
	  * Aquellas variables que tienen valores NULL seran excluidos en la busqueda. 
	  * No es necesario ordenar los objetos criterio, asi como tambien es posible mezclar atributos.
	  * Si algun atributo solo esta especificado en solo uno de los objetos de criterio se buscara que los resultados conicidan exactamente en ese campo.
	  *	
	  * <code>
	  *  /**
	  *   * Ejemplo de uso - buscar todos los clientes que tengan limite de credito 
	  *   * mayor a 2000 y menor a 5000. Y que tengan un descuento del 50%.
	  *   {@*} 
	  *	  $cr1 = new Cliente();
	  *	  $cr1->setLimiteCredito("2000");
	  *	  $cr1->setDescuento("50");
	  *	  
	  *	  $cr2 = new Cliente();
	  *	  $cr2->setLimiteCredito("5000");
	  *	  $resultados = ClienteDAO::byRange($cr1, $cr2);
	  *	  
	  *	  foreach($resultados as $c ){
	  *	  	echo $c->getNombre() . "<br>";
	  *	  }
	  * </code>
	  *	@static
	  * @param Users [$Users] El objeto de tipo Users
	  * @param Users [$Users] El objeto de tipo Users
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $UsersA , $UsersB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Users WHERE ("; 
		$val = array();
		if( (($a = $UsersA->getUserId()) != NULL) & ( ($b = $UsersB->getUserId()) != NULL) ){
				$sql .= " user_id >= ? AND user_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " user_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsersA->getUsername()) != NULL) & ( ($b = $UsersB->getUsername()) != NULL) ){
				$sql .= " username >= ? AND username <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " username = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsersA->getPassword()) != NULL) & ( ($b = $UsersB->getPassword()) != NULL) ){
				$sql .= " password >= ? AND password <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " password = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsersA->getMainEmailId()) != NULL) & ( ($b = $UsersB->getMainEmailId()) != NULL) ){
				$sql .= " main_email_id >= ? AND main_email_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " main_email_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsersA->getName()) != NULL) & ( ($b = $UsersB->getName()) != NULL) ){
				$sql .= " name >= ? AND name <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " name = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsersA->getSolved()) != NULL) & ( ($b = $UsersB->getSolved()) != NULL) ){
				$sql .= " solved >= ? AND solved <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " solved = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsersA->getSubmissions()) != NULL) & ( ($b = $UsersB->getSubmissions()) != NULL) ){
				$sql .= " submissions >= ? AND submissions <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " submissions = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsersA->getCountryId()) != NULL) & ( ($b = $UsersB->getCountryId()) != NULL) ){
				$sql .= " country_id >= ? AND country_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " country_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsersA->getStateId()) != NULL) & ( ($b = $UsersB->getStateId()) != NULL) ){
				$sql .= " state_id >= ? AND state_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " state_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsersA->getSchoolId()) != NULL) & ( ($b = $UsersB->getSchoolId()) != NULL) ){
				$sql .= " school_id >= ? AND school_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " school_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsersA->getScholarDegree()) != NULL) & ( ($b = $UsersB->getScholarDegree()) != NULL) ){
				$sql .= " scholar_degree >= ? AND scholar_degree <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " scholar_degree = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsersA->getGraduationDate()) != NULL) & ( ($b = $UsersB->getGraduationDate()) != NULL) ){
				$sql .= " graduation_date >= ? AND graduation_date <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " graduation_date = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsersA->getBirthDate()) != NULL) & ( ($b = $UsersB->getBirthDate()) != NULL) ){
				$sql .= " birth_date >= ? AND birth_date <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " birth_date = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsersA->getLastAccess()) != NULL) & ( ($b = $UsersB->getLastAccess()) != NULL) ){
				$sql .= " last_access >= ? AND last_access <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " last_access = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		$sql = substr($sql, 0, -3) . " )";
		if( $orderBy !== null ){
		    $sql .= " order by " . $orderBy . " " . $orden ;
		
		}
		global $conn;
		$rs = $conn->Execute($sql, $val);
		$ar = array();
		foreach ($rs as $foo) {
    		array_push( $ar, new Users($foo));
		}
		return $ar;
	}


	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto Users suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param Users [$Users] El objeto de tipo Users a eliminar
	  **/
	public static final function delete( &$Users )
	{
		if(self::getByPK($Users->getUserId()) === NULL) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Users WHERE  user_id = ?;";
		$params = array( $Users->getUserId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
