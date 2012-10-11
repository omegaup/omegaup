<?php
/** ContestProblemOpened Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link ContestProblemOpened }. 
  * @author alanboy
  * @access private
  * @abstract
  * @package docs
  * 
  */
abstract class ContestProblemOpenedDAOBase extends DAO
{

		private static $loadedRecords = array();

		private static function recordExists(  $contest_id, $problem_id, $user_id ){
			$pk = "";
			$pk .= $contest_id . "-";
			$pk .= $problem_id . "-";
			$pk .= $user_id . "-";
			return array_key_exists ( $pk , self::$loadedRecords );
		}
		private static function pushRecord( $inventario,  $contest_id, $problem_id, $user_id){
			$pk = "";
			$pk .= $contest_id . "-";
			$pk .= $problem_id . "-";
			$pk .= $user_id . "-";
			self::$loadedRecords [$pk] = $inventario;
		}
		private static function getRecord(  $contest_id, $problem_id, $user_id ){
			$pk = "";
			$pk .= $contest_id . "-";
			$pk .= $problem_id . "-";
			$pk .= $user_id . "-";
			return self::$loadedRecords[$pk];
		}
	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link ContestProblemOpened} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param ContestProblemOpened [$Contest_Problem_Opened] El objeto de tipo ContestProblemOpened
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Contest_Problem_Opened )
	{
		if(  self::getByPK(  $Contest_Problem_Opened->getContestId() , $Contest_Problem_Opened->getProblemId() , $Contest_Problem_Opened->getUserId() ) !== NULL )
		{
			try{ return ContestProblemOpenedDAOBase::update( $Contest_Problem_Opened) ; } catch(Exception $e){ throw $e; }
		}else{
			try{ return ContestProblemOpenedDAOBase::create( $Contest_Problem_Opened) ; } catch(Exception $e){ throw $e; }
		}
	}


	/**
	  *	Obtener {@link ContestProblemOpened} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link ContestProblemOpened} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link ContestProblemOpened Un objeto del tipo {@link ContestProblemOpened}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $contest_id, $problem_id, $user_id )
	{
		if(self::recordExists(  $contest_id, $problem_id, $user_id)){
			return self::getRecord( $contest_id, $problem_id, $user_id );
		}
		$sql = "SELECT * FROM Contest_Problem_Opened WHERE (contest_id = ? AND problem_id = ? AND user_id = ? ) LIMIT 1;";
		$params = array(  $contest_id, $problem_id, $user_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
			$foo = new ContestProblemOpened( $rs );
			self::pushRecord( $foo,  $contest_id, $problem_id, $user_id );
			return $foo;
	}


	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link ContestProblemOpened}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link ContestProblemOpened}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Contest_Problem_Opened";
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
			$bar = new ContestProblemOpened($foo);
    		array_push( $allData, $bar);
			//contest_id
			//problem_id
			//user_id
    		self::pushRecord( $bar, $foo["contest_id"],$foo["problem_id"],$foo["user_id"] );
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ContestProblemOpened} de la base de datos. 
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
	  * @param ContestProblemOpened [$Contest_Problem_Opened] El objeto de tipo ContestProblemOpened
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Contest_Problem_Opened , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Contest_Problem_Opened WHERE ("; 
		$val = array();
		if( $Contest_Problem_Opened->getContestId() != NULL){
			$sql .= " contest_id = ? AND";
			array_push( $val, $Contest_Problem_Opened->getContestId() );
		}

		if( $Contest_Problem_Opened->getProblemId() != NULL){
			$sql .= " problem_id = ? AND";
			array_push( $val, $Contest_Problem_Opened->getProblemId() );
		}

		if( $Contest_Problem_Opened->getUserId() != NULL){
			$sql .= " user_id = ? AND";
			array_push( $val, $Contest_Problem_Opened->getUserId() );
		}

		if( $Contest_Problem_Opened->getOpenTime() != NULL){
			$sql .= " open_time = ? AND";
			array_push( $val, $Contest_Problem_Opened->getOpenTime() );
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
			$bar =  new ContestProblemOpened($foo);
    		array_push( $ar,$bar);
    		self::pushRecord( $bar, $foo["contest_id"],$foo["problem_id"],$foo["user_id"] );
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
	  * @param ContestProblemOpened [$Contest_Problem_Opened] El objeto de tipo ContestProblemOpened a actualizar.
	  **/
	private static final function update( $Contest_Problem_Opened )
	{
		$sql = "UPDATE Contest_Problem_Opened SET  open_time = ? WHERE  contest_id = ? AND problem_id = ? AND user_id = ?;";
		$params = array( 
			$Contest_Problem_Opened->getOpenTime(), 
			$Contest_Problem_Opened->getContestId(),$Contest_Problem_Opened->getProblemId(),$Contest_Problem_Opened->getUserId(), );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		return $conn->Affected_Rows();
	}


	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto ContestProblemOpened suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto ContestProblemOpened dentro de la misma transaccion.
	  *	
	  * @internal private information for advanced developers only
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param ContestProblemOpened [$Contest_Problem_Opened] El objeto de tipo ContestProblemOpened a crear.
	  **/
	private static final function create( &$Contest_Problem_Opened )
	{
		$sql = "INSERT INTO Contest_Problem_Opened ( contest_id, problem_id, user_id, open_time ) VALUES ( ?, ?, ?, ?);";
		$params = array( 
			$Contest_Problem_Opened->getContestId(), 
			$Contest_Problem_Opened->getProblemId(), 
			$Contest_Problem_Opened->getUserId(), 
			$Contest_Problem_Opened->getOpenTime(), 
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
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ContestProblemOpened} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link ContestProblemOpened}.
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
	  * @param ContestProblemOpened [$Contest_Problem_Opened] El objeto de tipo ContestProblemOpened
	  * @param ContestProblemOpened [$Contest_Problem_Opened] El objeto de tipo ContestProblemOpened
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $Contest_Problem_OpenedA , $Contest_Problem_OpenedB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Contest_Problem_Opened WHERE ("; 
		$val = array();
		if( (($a = $Contest_Problem_OpenedA->getContestId()) != NULL) & ( ($b = $Contest_Problem_OpenedB->getContestId()) != NULL) ){
				$sql .= " contest_id >= ? AND contest_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " contest_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $Contest_Problem_OpenedA->getProblemId()) != NULL) & ( ($b = $Contest_Problem_OpenedB->getProblemId()) != NULL) ){
				$sql .= " problem_id >= ? AND problem_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " problem_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $Contest_Problem_OpenedA->getUserId()) != NULL) & ( ($b = $Contest_Problem_OpenedB->getUserId()) != NULL) ){
				$sql .= " user_id >= ? AND user_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " user_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $Contest_Problem_OpenedA->getOpenTime()) != NULL) & ( ($b = $Contest_Problem_OpenedB->getOpenTime()) != NULL) ){
				$sql .= " open_time >= ? AND open_time <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " open_time = ? AND"; 
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
    		array_push( $ar, new ContestProblemOpened($foo));
		}
		return $ar;
	}


	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto ContestProblemOpened suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param ContestProblemOpened [$Contest_Problem_Opened] El objeto de tipo ContestProblemOpened a eliminar
	  **/
	public static final function delete( &$Contest_Problem_Opened )
	{
		if(self::getByPK($Contest_Problem_Opened->getContestId(), $Contest_Problem_Opened->getProblemId(), $Contest_Problem_Opened->getUserId()) === NULL) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Contest_Problem_Opened WHERE  contest_id = ? AND problem_id = ? AND user_id = ?;";
		$params = array( $Contest_Problem_Opened->getContestId(), $Contest_Problem_Opened->getProblemId(), $Contest_Problem_Opened->getUserId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
