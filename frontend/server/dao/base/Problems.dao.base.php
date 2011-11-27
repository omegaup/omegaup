<?php
/** Problems Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link Problems }. 
  * @author alanboy
  * @access private
  * @abstract
  * @package docs
  * 
  */
abstract class ProblemsDAOBase extends DAO
{

		private static $loadedRecords = array();

		private static function recordExists(  $problem_id ){
			$pk = "";
			$pk .= $problem_id . "-";
			return array_key_exists ( $pk , self::$loadedRecords );
		}
		private static function pushRecord( $inventario,  $problem_id){
			$pk = "";
			$pk .= $problem_id . "-";
			self::$loadedRecords [$pk] = $inventario;
		}
		private static function getRecord(  $problem_id ){
			$pk = "";
			$pk .= $problem_id . "-";
			return self::$loadedRecords[$pk];
		}
	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link Problems} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param Problems [$Problems] El objeto de tipo Problems
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Problems )
	{
		if(  self::getByPK(  $Problems->getProblemId() ) !== NULL )
		{
			try{ return ProblemsDAOBase::update( $Problems) ; } catch(Exception $e){ throw $e; }
		}else{
			try{ return ProblemsDAOBase::create( $Problems) ; } catch(Exception $e){ throw $e; }
		}
	}


	/**
	  *	Obtener {@link Problems} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link Problems} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link Problems Un objeto del tipo {@link Problems}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $problem_id )
	{
		if(self::recordExists(  $problem_id)){                        
			return self::getRecord( $problem_id );
		}
		$sql = "SELECT * FROM Problems WHERE (problem_id = ? ) LIMIT 1;";
		$params = array(  $problem_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
			$foo = new Problems( $rs );
			self::pushRecord( $foo,  $problem_id );
			return $foo;
	}


	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link Problems}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link Problems}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Problems";
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
			$bar = new Problems($foo);
    		array_push( $allData, $bar);
			//problem_id
    		self::pushRecord( $bar, $foo["problem_id"] );
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Problems} de la base de datos. 
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
	  * @param Problems [$Problems] El objeto de tipo Problems
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Problems , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Problems WHERE ("; 
		$val = array();
		if( $Problems->getProblemId() != NULL){
			$sql .= " problem_id = ? AND";
			array_push( $val, $Problems->getProblemId() );
		}

		if( $Problems->getPublic() != NULL){
			$sql .= " public = ? AND";
			array_push( $val, $Problems->getPublic() );
		}

		if( $Problems->getAuthorId() != NULL){
			$sql .= " author_id = ? AND";
			array_push( $val, $Problems->getAuthorId() );
		}

		if( $Problems->getTitle() != NULL){
			$sql .= " title = ? AND";
			array_push( $val, $Problems->getTitle() );
		}

		if( $Problems->getAlias() != NULL){
			$sql .= " alias = ? AND";
			array_push( $val, $Problems->getAlias() );
		}

		if( $Problems->getValidator() != NULL){
			$sql .= " validator = ? AND";
			array_push( $val, $Problems->getValidator() );
		}

		if( $Problems->getServer() != NULL){
			$sql .= " server = ? AND";
			array_push( $val, $Problems->getServer() );
		}

		if( $Problems->getRemoteId() != NULL){
			$sql .= " remote_id = ? AND";
			array_push( $val, $Problems->getRemoteId() );
		}

		if( $Problems->getTimeLimit() != NULL){
			$sql .= " time_limit = ? AND";
			array_push( $val, $Problems->getTimeLimit() );
		}

		if( $Problems->getMemoryLimit() != NULL){
			$sql .= " memory_limit = ? AND";
			array_push( $val, $Problems->getMemoryLimit() );
		}

		if( $Problems->getVisits() != NULL){
			$sql .= " visits = ? AND";
			array_push( $val, $Problems->getVisits() );
		}

		if( $Problems->getSubmissions() != NULL){
			$sql .= " submissions = ? AND";
			array_push( $val, $Problems->getSubmissions() );
		}

		if( $Problems->getAccepted() != NULL){
			$sql .= " accepted = ? AND";
			array_push( $val, $Problems->getAccepted() );
		}

		if( $Problems->getDifficulty() != NULL){
			$sql .= " difficulty = ? AND";
			array_push( $val, $Problems->getDifficulty() );
		}

		if( $Problems->getCreationDate() != NULL){
			$sql .= " creation_date = ? AND";
			array_push( $val, $Problems->getCreationDate() );
		}

		if( $Problems->getSource() != NULL){
			$sql .= " source = ? AND";
			array_push( $val, $Problems->getSource() );
		}

		if( $Problems->getOrder() != NULL){
			$sql .= " order = ? AND";
			array_push( $val, $Problems->getOrder() );
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
			$bar =  new Problems($foo);
    		array_push( $ar,$bar);
    		self::pushRecord( $bar, $foo["problem_id"] );
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
	  * @param Problems [$Problems] El objeto de tipo Problems a actualizar.
	  **/
	private static final function update( $Problems )
	{
		$sql = "UPDATE Problems SET  public = ?, author_id = ?, title = ?, alias = ?, validator = ?, server = ?, remote_id = ?, time_limit = ?, memory_limit = ?, visits = ?, submissions = ?, accepted = ?, difficulty = ?, creation_date = ?, source = ?, order = ? WHERE  problem_id = ?;";
		$params = array( 
			$Problems->getPublic(), 
			$Problems->getAuthorId(), 
			$Problems->getTitle(), 
			$Problems->getAlias(), 
			$Problems->getValidator(), 
			$Problems->getServer(), 
			$Problems->getRemoteId(), 
			$Problems->getTimeLimit(), 
			$Problems->getMemoryLimit(), 
			$Problems->getVisits(), 
			$Problems->getSubmissions(), 
			$Problems->getAccepted(), 
			$Problems->getDifficulty(), 
			$Problems->getCreationDate(), 
			$Problems->getSource(), 
			$Problems->getOrder(), 
			$Problems->getProblemId(), );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		return $conn->Affected_Rows();
	}


	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto Problems suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto Problems dentro de la misma transaccion.
	  *	
	  * @internal private information for advanced developers only
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param Problems [$Problems] El objeto de tipo Problems a crear.
	  **/
	private static final function create( &$Problems )
	{
		$sql = "INSERT INTO Problems ( problem_id, public, author_id, title, alias, validator, server, remote_id, time_limit, memory_limit, visits, submissions, accepted, difficulty, creation_date, source, `order` ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
		$params = array( 
			$Problems->getProblemId(), 
			$Problems->getPublic(), 
			$Problems->getAuthorId(), 
			$Problems->getTitle(), 
			$Problems->getAlias(), 
			$Problems->getValidator(), 
			$Problems->getServer(), 
			$Problems->getRemoteId(), 
			$Problems->getTimeLimit(), 
			$Problems->getMemoryLimit(), 
			$Problems->getVisits(), 
			$Problems->getSubmissions(), 
			$Problems->getAccepted(), 
			$Problems->getDifficulty(), 
			$Problems->getCreationDate(), 
			$Problems->getSource(), 
			$Problems->getOrder(), 
		 );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		$ar = $conn->Affected_Rows();
		if($ar == 0) return 0;
		/* save autoincremented value on obj */  $Problems->setProblemId( $conn->Insert_ID() ); /*  */ 
		return $ar;
	}


	/**
	  *	Buscar por rango.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Problems} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Problems}.
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
	  * @param Problems [$Problems] El objeto de tipo Problems
	  * @param Problems [$Problems] El objeto de tipo Problems
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $ProblemsA , $ProblemsB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Problems WHERE ("; 
		$val = array();
		if( (($a = $ProblemsA->getProblemId()) != NULL) & ( ($b = $ProblemsB->getProblemId()) != NULL) ){
				$sql .= " problem_id >= ? AND problem_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " problem_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemsA->getPublic()) != NULL) & ( ($b = $ProblemsB->getPublic()) != NULL) ){
				$sql .= " public >= ? AND public <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " public = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemsA->getAuthorId()) != NULL) & ( ($b = $ProblemsB->getAuthorId()) != NULL) ){
				$sql .= " author_id >= ? AND author_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " author_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemsA->getTitle()) != NULL) & ( ($b = $ProblemsB->getTitle()) != NULL) ){
				$sql .= " title >= ? AND title <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " title = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemsA->getAlias()) != NULL) & ( ($b = $ProblemsB->getAlias()) != NULL) ){
				$sql .= " alias >= ? AND alias <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " alias = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemsA->getValidator()) != NULL) & ( ($b = $ProblemsB->getValidator()) != NULL) ){
				$sql .= " validator >= ? AND validator <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " validator = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemsA->getServer()) != NULL) & ( ($b = $ProblemsB->getServer()) != NULL) ){
				$sql .= " server >= ? AND server <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " server = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemsA->getRemoteId()) != NULL) & ( ($b = $ProblemsB->getRemoteId()) != NULL) ){
				$sql .= " remote_id >= ? AND remote_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " remote_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemsA->getTimeLimit()) != NULL) & ( ($b = $ProblemsB->getTimeLimit()) != NULL) ){
				$sql .= " time_limit >= ? AND time_limit <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " time_limit = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemsA->getMemoryLimit()) != NULL) & ( ($b = $ProblemsB->getMemoryLimit()) != NULL) ){
				$sql .= " memory_limit >= ? AND memory_limit <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " memory_limit = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemsA->getVisits()) != NULL) & ( ($b = $ProblemsB->getVisits()) != NULL) ){
				$sql .= " visits >= ? AND visits <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " visits = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemsA->getSubmissions()) != NULL) & ( ($b = $ProblemsB->getSubmissions()) != NULL) ){
				$sql .= " submissions >= ? AND submissions <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " submissions = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemsA->getAccepted()) != NULL) & ( ($b = $ProblemsB->getAccepted()) != NULL) ){
				$sql .= " accepted >= ? AND accepted <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " accepted = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemsA->getDifficulty()) != NULL) & ( ($b = $ProblemsB->getDifficulty()) != NULL) ){
				$sql .= " difficulty >= ? AND difficulty <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " difficulty = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemsA->getCreationDate()) != NULL) & ( ($b = $ProblemsB->getCreationDate()) != NULL) ){
				$sql .= " creation_date >= ? AND creation_date <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " creation_date = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemsA->getSource()) != NULL) & ( ($b = $ProblemsB->getSource()) != NULL) ){
				$sql .= " source >= ? AND source <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " source = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemsA->getOrder()) != NULL) & ( ($b = $ProblemsB->getOrder()) != NULL) ){
				$sql .= " order >= ? AND order <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " order = ? AND"; 
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
    		array_push( $ar, new Problems($foo));
		}
		return $ar;
	}


	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto Problems suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param Problems [$Problems] El objeto de tipo Problems a eliminar
	  **/
	public static final function delete( &$Problems )
	{
		if(self::getByPK($Problems->getProblemId()) === NULL) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Problems WHERE  problem_id = ?;";
		$params = array( $Problems->getProblemId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
