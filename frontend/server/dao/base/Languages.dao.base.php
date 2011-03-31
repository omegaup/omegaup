<?php
/** Languages Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link Languages }. 
  * @author alan@caffeina.mx
  * @access private
  * @abstract
  * @package docs
  * 
  */
abstract class LanguagesDAOBase extends DAO
{

		private static $loadedRecords = array();

		private static function recordExists(  $language_id ){
			$pk = "";
			$pk .= $language_id . "-";
			return array_key_exists ( $pk , self::$loadedRecords );
		}
		private static function pushRecord( $inventario,  $language_id){
			$pk = "";
			$pk .= $language_id . "-";
			self::$loadedRecords [$pk] = $inventario;
		}
		private static function getRecord(  $language_id ){
			$pk = "";
			$pk .= $language_id . "-";
			return self::$loadedRecords[$pk];
		}
	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link Languages} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param Languages [$Languages] El objeto de tipo Languages
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Languages )
	{
		if(  self::getByPK(  $Languages->getLanguageId() ) !== NULL )
		{
			try{ return LanguagesDAOBase::update( $Languages) ; } catch(Exception $e){ throw $e; }
		}else{
			try{ return LanguagesDAOBase::create( $Languages) ; } catch(Exception $e){ throw $e; }
		}
	}


	/**
	  *	Obtener {@link Languages} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link Languages} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link Languages Un objeto del tipo {@link Languages}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $language_id )
	{
		if(self::recordExists(  $language_id)){
			return self::getRecord( $language_id );
		}
		$sql = "SELECT * FROM Languages WHERE (language_id = ? ) LIMIT 1;";
		$params = array(  $language_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
			$foo = new Languages( $rs );
			self::pushRecord( $foo,  $language_id );
			return $foo;
	}


	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link Languages}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link Languages}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Languages";
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
			$bar = new Languages($foo);
    		array_push( $allData, $bar);
			//language_id
    		self::pushRecord( $bar, $foo["language_id"] );
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Languages} de la base de datos. 
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
	  * @param Languages [$Languages] El objeto de tipo Languages
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Languages , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Languages WHERE ("; 
		$val = array();
		if( $Languages->getLanguageId() != NULL){
			$sql .= " language_id = ? AND";
			array_push( $val, $Languages->getLanguageId() );
		}

		if( $Languages->getName() != NULL){
			$sql .= " name = ? AND";
			array_push( $val, $Languages->getName() );
		}

		if( $Languages->getCountryId() != NULL){
			$sql .= " country_id = ? AND";
			array_push( $val, $Languages->getCountryId() );
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
			$bar =  new Languages($foo);
    		array_push( $ar,$bar);
    		self::pushRecord( $bar, $foo["language_id"] );
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
	  * @param Languages [$Languages] El objeto de tipo Languages a actualizar.
	  **/
	private static final function update( $Languages )
	{
		$sql = "UPDATE Languages SET  name = ?, country_id = ? WHERE  language_id = ?;";
		$params = array( 
			$Languages->getName(), 
			$Languages->getCountryId(), 
			$Languages->getLanguageId(), );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		return $conn->Affected_Rows();
	}


	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto Languages suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto Languages dentro de la misma transaccion.
	  *	
	  * @internal private information for advanced developers only
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param Languages [$Languages] El objeto de tipo Languages a crear.
	  **/
	private static final function create( &$Languages )
	{
		$sql = "INSERT INTO Languages ( language_id, name, country_id ) VALUES ( ?, ?, ?);";
		$params = array( 
			$Languages->getLanguageId(), 
			$Languages->getName(), 
			$Languages->getCountryId(), 
		 );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		$ar = $conn->Affected_Rows();
		if($ar == 0) return 0;
		 
		return $ar;
	}


	/**
	  *	Buscar por rango.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Languages} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Languages}.
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
	  * @param Languages [$Languages] El objeto de tipo Languages
	  * @param Languages [$Languages] El objeto de tipo Languages
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $LanguagesA , $LanguagesB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Languages WHERE ("; 
		$val = array();
		if( (($a = $LanguagesA->getLanguageId()) != NULL) & ( ($b = $LanguagesB->getLanguageId()) != NULL) ){
				$sql .= " language_id >= ? AND language_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " language_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $LanguagesA->getName()) != NULL) & ( ($b = $LanguagesB->getName()) != NULL) ){
				$sql .= " name >= ? AND name <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " name = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $LanguagesA->getCountryId()) != NULL) & ( ($b = $LanguagesB->getCountryId()) != NULL) ){
				$sql .= " country_id >= ? AND country_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " country_id = ? AND"; 
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
    		array_push( $ar, new Languages($foo));
		}
		return $ar;
	}


	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto Languages suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param Languages [$Languages] El objeto de tipo Languages a eliminar
	  **/
	public static final function delete( &$Languages )
	{
		if(self::getByPK($Languages->getLanguageId()) === NULL) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Languages WHERE  language_id = ?;";
		$params = array( $Languages->getLanguageId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
