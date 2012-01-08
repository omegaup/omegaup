<?php
/** ContestsUsers Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link ContestsUsers }. 
  * @author alanboy
  * @access private
  * @abstract
  * @package docs
  * 
  */
abstract class ContestsUsersDAOBase extends DAO
{

		private static $loadedRecords = array();

		private static function recordExists(  $user_id, $contest_id ){
			return false;
			$pk = "";
			$pk .= $user_id . "-";
			$pk .= $contest_id . "-";
			return array_key_exists ( $pk , self::$loadedRecords );
		}
		private static function pushRecord( $inventario,  $user_id, $contest_id){
			$pk = "";
			$pk .= $user_id . "-";
			$pk .= $contest_id . "-";
			self::$loadedRecords [$pk] = $inventario;
		}
		private static function getRecord(  $user_id, $contest_id ){
			$pk = "";
			$pk .= $user_id . "-";
			$pk .= $contest_id . "-";
			return self::$loadedRecords[$pk];
		}
	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link ContestsUsers} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param ContestsUsers [$Contests_Users] El objeto de tipo ContestsUsers
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Contests_Users )
	{
		if(  self::getByPK(  $Contests_Users->getUserId() , $Contests_Users->getContestId() ) !== NULL )
		{
			try{ return ContestsUsersDAOBase::update( $Contests_Users) ; } catch(Exception $e){ throw $e; }
		}else{
			try{ return ContestsUsersDAOBase::create( $Contests_Users) ; } catch(Exception $e){ throw $e; }
		}
	}


	/**
	  *	Obtener {@link ContestsUsers} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link ContestsUsers} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link ContestsUsers Un objeto del tipo {@link ContestsUsers}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $user_id, $contest_id )
	{
		if(self::recordExists(  $user_id, $contest_id)){
			return self::getRecord( $user_id, $contest_id );
		}
		$sql = "SELECT * FROM Contests_Users WHERE (user_id = ? AND contest_id = ? ) LIMIT 1;";
		$params = array(  $user_id, $contest_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
			$foo = new ContestsUsers( $rs );
			self::pushRecord( $foo,  $user_id, $contest_id );
			return $foo;
	}


	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link ContestsUsers}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link ContestsUsers}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Contests_Users";
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
			$bar = new ContestsUsers($foo);
    		array_push( $allData, $bar);
			//user_id
			//contest_id
    		self::pushRecord( $bar, $foo["user_id"],$foo["contest_id"] );
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ContestsUsers} de la base de datos. 
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
	  * @param ContestsUsers [$Contests_Users] El objeto de tipo ContestsUsers
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Contests_Users , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Contests_Users WHERE ("; 
		$val = array();
		if( $Contests_Users->getUserId() != NULL){
			$sql .= " user_id = ? AND";
			array_push( $val, $Contests_Users->getUserId() );
		}

		if( $Contests_Users->getContestId() != NULL){
			$sql .= " contest_id = ? AND";
			array_push( $val, $Contests_Users->getContestId() );
		}

		if( $Contests_Users->getAccessTime() != NULL){
			$sql .= " access_time = ? AND";
			array_push( $val, $Contests_Users->getAccessTime() );
		}

		if( $Contests_Users->getScore() != NULL){
			$sql .= " score = ? AND";
			array_push( $val, $Contests_Users->getScore() );
		}

		if( $Contests_Users->getTime() != NULL){
			$sql .= " time = ? AND";
			array_push( $val, $Contests_Users->getTime() );
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
			$bar =  new ContestsUsers($foo);
    		array_push( $ar,$bar);
    		self::pushRecord( $bar, $foo["user_id"],$foo["contest_id"] );
		}
		return $ar;
	}


	/**
	  *	Actualizar registros.
	  *	
	  * Este metodo es un metodo de ayuda para uso interno. Se ejecutara todas las manipulaciones
	  * en la base de datos que estan dadas en el objeto pasado.No se haran consultas SELECT 
	  * aqui, sin embargo. El valor de retorno indica cu‡ntas filas se vieron afectadas.
	  *	
	  * @internal private information for advanced developers only
	  * @return Filas afectadas o un string con la descripcion del error
	  * @param ContestsUsers [$Contests_Users] El objeto de tipo ContestsUsers a actualizar.
	  **/
	private static final function update( $Contests_Users )
	{
		$sql = "UPDATE Contests_Users SET  access_time = ?, score = ?, time = ? WHERE  user_id = ? AND contest_id = ?;";
		$params = array( 
			$Contests_Users->getAccessTime(), 
			$Contests_Users->getScore(), 
			$Contests_Users->getTime(), 
			$Contests_Users->getUserId(),$Contests_Users->getContestId(), );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		return $conn->Affected_Rows();
	}


	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto ContestsUsers suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto ContestsUsers dentro de la misma transaccion.
	  *	
	  * @internal private information for advanced developers only
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param ContestsUsers [$Contests_Users] El objeto de tipo ContestsUsers a crear.
	  **/
	private static final function create( &$Contests_Users )
	{
		$sql = "INSERT INTO Contests_Users ( user_id, contest_id, access_time, score, time ) VALUES ( ?, ?, ?, ?, ?);";
		$params = array( 
			$Contests_Users->getUserId(), 
			$Contests_Users->getContestId(), 
			$Contests_Users->getAccessTime(), 
			$Contests_Users->getScore(), 
			$Contests_Users->getTime(), 
		 );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		$ar = $conn->Affected_Rows();
		if($ar == 0) return 0;
		/* save autoincremented value on obj */   /*  */ 
		return $ar;
	}


	/**
	  *	Buscar por rango.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ContestsUsers} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link ContestsUsers}.
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
	  * @param ContestsUsers [$Contests_Users] El objeto de tipo ContestsUsers
	  * @param ContestsUsers [$Contests_Users] El objeto de tipo ContestsUsers
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $Contests_UsersA , $Contests_UsersB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Contests_Users WHERE ("; 
		$val = array();
		if( (($a = $Contests_UsersA->getUserId()) != NULL) & ( ($b = $Contests_UsersB->getUserId()) != NULL) ){
				$sql .= " user_id >= ? AND user_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " user_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $Contests_UsersA->getContestId()) != NULL) & ( ($b = $Contests_UsersB->getContestId()) != NULL) ){
				$sql .= " contest_id >= ? AND contest_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " contest_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $Contests_UsersA->getAccessTime()) != NULL) & ( ($b = $Contests_UsersB->getAccessTime()) != NULL) ){
				$sql .= " access_time >= ? AND access_time <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " access_time = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $Contests_UsersA->getScore()) != NULL) & ( ($b = $Contests_UsersB->getScore()) != NULL) ){
				$sql .= " score >= ? AND score <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " score = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $Contests_UsersA->getTime()) != NULL) & ( ($b = $Contests_UsersB->getTime()) != NULL) ){
				$sql .= " time >= ? AND time <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " time = ? AND"; 
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
    		array_push( $ar, new ContestsUsers($foo));
		}
		return $ar;
	}


	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto ContestsUsers suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param ContestsUsers [$Contests_Users] El objeto de tipo ContestsUsers a eliminar
	  **/
	public static final function delete( &$Contests_Users )
	{
		if(self::getByPK($Contests_Users->getUserId(), $Contests_Users->getContestId()) === NULL) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Contests_Users WHERE  user_id = ? AND contest_id = ?;";
		$params = array( $Contests_Users->getUserId(), $Contests_Users->getContestId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
