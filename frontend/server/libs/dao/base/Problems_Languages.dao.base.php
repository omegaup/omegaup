<?php
/** ProblemsLanguages Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsLanguages }. 
  * @author alanboy
  * @access private
  * @abstract
  * @package docs
  * 
  */
abstract class ProblemsLanguagesDAOBase extends DAO
{

		private static $loadedRecords = array();

		private static function recordExists(  $problem_id, $language_id ){
			$pk = "";
			$pk .= $problem_id . "-";
			$pk .= $language_id . "-";
			return array_key_exists ( $pk , self::$loadedRecords );
		}
		private static function pushRecord( $inventario,  $problem_id, $language_id){
			$pk = "";
			$pk .= $problem_id . "-";
			$pk .= $language_id . "-";
			self::$loadedRecords [$pk] = $inventario;
		}
		private static function getRecord(  $problem_id, $language_id ){
			$pk = "";
			$pk .= $problem_id . "-";
			$pk .= $language_id . "-";
			return self::$loadedRecords[$pk];
		}
	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link ProblemsLanguages} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param ProblemsLanguages [$Problems_Languages] El objeto de tipo ProblemsLanguages
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Problems_Languages )
	{
		if(  self::getByPK(  $Problems_Languages->getProblemId() , $Problems_Languages->getLanguageId() ) !== NULL )
		{
			try{ return ProblemsLanguagesDAOBase::update( $Problems_Languages) ; } catch(Exception $e){ throw $e; }
		}else{
			try{ return ProblemsLanguagesDAOBase::create( $Problems_Languages) ; } catch(Exception $e){ throw $e; }
		}
	}


	/**
	  *	Obtener {@link ProblemsLanguages} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link ProblemsLanguages} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link ProblemsLanguages Un objeto del tipo {@link ProblemsLanguages}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $problem_id, $language_id )
	{
		if(self::recordExists(  $problem_id, $language_id)){
			return self::getRecord( $problem_id, $language_id );
		}
		$sql = "SELECT * FROM Problems_Languages WHERE (problem_id = ? AND language_id = ? ) LIMIT 1;";
		$params = array(  $problem_id, $language_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
			$foo = new ProblemsLanguages( $rs );
			self::pushRecord( $foo,  $problem_id, $language_id );
			return $foo;
	}


	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link ProblemsLanguages}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link ProblemsLanguages}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Problems_Languages";
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
			$bar = new ProblemsLanguages($foo);
    		array_push( $allData, $bar);
			//problem_id
			//language_id
    		self::pushRecord( $bar, $foo["problem_id"],$foo["language_id"] );
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ProblemsLanguages} de la base de datos. 
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
	  * @param ProblemsLanguages [$Problems_Languages] El objeto de tipo ProblemsLanguages
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Problems_Languages , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Problems_Languages WHERE ("; 
		$val = array();
		if( $Problems_Languages->getProblemId() != NULL){
			$sql .= " problem_id = ? AND";
			array_push( $val, $Problems_Languages->getProblemId() );
		}

		if( $Problems_Languages->getLanguageId() != NULL){
			$sql .= " language_id = ? AND";
			array_push( $val, $Problems_Languages->getLanguageId() );
		}

		if( $Problems_Languages->getTranslatorId() != NULL){
			$sql .= " translator_id = ? AND";
			array_push( $val, $Problems_Languages->getTranslatorId() );
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
			$bar =  new ProblemsLanguages($foo);
    		array_push( $ar,$bar);
    		self::pushRecord( $bar, $foo["problem_id"],$foo["language_id"] );
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
	  * @param ProblemsLanguages [$Problems_Languages] El objeto de tipo ProblemsLanguages a actualizar.
	  **/
	private static final function update( $Problems_Languages )
	{
		$sql = "UPDATE Problems_Languages SET  translator_id = ? WHERE  problem_id = ? AND language_id = ?;";
		$params = array( 
			$Problems_Languages->getTranslatorId(), 
			$Problems_Languages->getProblemId(),$Problems_Languages->getLanguageId(), );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		return $conn->Affected_Rows();
	}


	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto ProblemsLanguages suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto ProblemsLanguages dentro de la misma transaccion.
	  *	
	  * @internal private information for advanced developers only
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param ProblemsLanguages [$Problems_Languages] El objeto de tipo ProblemsLanguages a crear.
	  **/
	private static final function create( &$Problems_Languages )
	{
		$sql = "INSERT INTO Problems_Languages ( problem_id, language_id, translator_id ) VALUES ( ?, ?, ?);";
		$params = array( 
			$Problems_Languages->getProblemId(), 
			$Problems_Languages->getLanguageId(), 
			$Problems_Languages->getTranslatorId(), 
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
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ProblemsLanguages} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link ProblemsLanguages}.
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
	  * @param ProblemsLanguages [$Problems_Languages] El objeto de tipo ProblemsLanguages
	  * @param ProblemsLanguages [$Problems_Languages] El objeto de tipo ProblemsLanguages
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $Problems_LanguagesA , $Problems_LanguagesB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Problems_Languages WHERE ("; 
		$val = array();
		if( (($a = $Problems_LanguagesA->getProblemId()) != NULL) & ( ($b = $Problems_LanguagesB->getProblemId()) != NULL) ){
				$sql .= " problem_id >= ? AND problem_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " problem_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $Problems_LanguagesA->getLanguageId()) != NULL) & ( ($b = $Problems_LanguagesB->getLanguageId()) != NULL) ){
				$sql .= " language_id >= ? AND language_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " language_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $Problems_LanguagesA->getTranslatorId()) != NULL) & ( ($b = $Problems_LanguagesB->getTranslatorId()) != NULL) ){
				$sql .= " translator_id >= ? AND translator_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " translator_id = ? AND"; 
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
    		array_push( $ar, new ProblemsLanguages($foo));
		}
		return $ar;
	}


	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto ProblemsLanguages suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param ProblemsLanguages [$Problems_Languages] El objeto de tipo ProblemsLanguages a eliminar
	  **/
	public static final function delete( &$Problems_Languages )
	{
		if(self::getByPK($Problems_Languages->getProblemId(), $Problems_Languages->getLanguageId()) === NULL) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Problems_Languages WHERE  problem_id = ? AND language_id = ?;";
		$params = array( $Problems_Languages->getProblemId(), $Problems_Languages->getLanguageId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
