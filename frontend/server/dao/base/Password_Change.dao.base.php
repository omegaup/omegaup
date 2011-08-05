<?php
/** PasswordChange Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link PasswordChange }. 
  * @author alanboy
  * @access private
  * @abstract
  * @package docs
  * 
  */
abstract class PasswordChangeDAOBase extends DAO
{

		private static $loadedRecords = array();

		private static function recordExists(  $user_id ){
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
	  *	Este metodo guarda el estado actual del objeto {@link PasswordChange} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param PasswordChange [$Password_Change] El objeto de tipo PasswordChange
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Password_Change )
	{
		if(  self::getByPK(  $Password_Change->getUserId() ) !== NULL )
		{
			try{ return PasswordChangeDAOBase::update( $Password_Change) ; } catch(Exception $e){ throw $e; }
		}else{
			try{ return PasswordChangeDAOBase::create( $Password_Change) ; } catch(Exception $e){ throw $e; }
		}
	}


	/**
	  *	Obtener {@link PasswordChange} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link PasswordChange} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link PasswordChange Un objeto del tipo {@link PasswordChange}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $user_id )
	{
		if(self::recordExists(  $user_id)){
			return self::getRecord( $user_id );
		}
		$sql = "SELECT * FROM Password_Change WHERE (user_id = ? ) LIMIT 1;";
		$params = array(  $user_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
			$foo = new PasswordChange( $rs );
			self::pushRecord( $foo,  $user_id );
			return $foo;
	}


	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link PasswordChange}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link PasswordChange}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Password_Change";
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
			$bar = new PasswordChange($foo);
    		array_push( $allData, $bar);
			//user_id
    		self::pushRecord( $bar, $foo["user_id"] );
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link PasswordChange} de la base de datos. 
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
	  * @param PasswordChange [$Password_Change] El objeto de tipo PasswordChange
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Password_Change , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Password_Change WHERE ("; 
		$val = array();
		if( $Password_Change->getUserId() != NULL){
			$sql .= " user_id = ? AND";
			array_push( $val, $Password_Change->getUserId() );
		}

		if( $Password_Change->getToken() != NULL){
			$sql .= " token = ? AND";
			array_push( $val, $Password_Change->getToken() );
		}

		if( $Password_Change->getIp() != NULL){
			$sql .= " ip = ? AND";
			array_push( $val, $Password_Change->getIp() );
		}

		if( $Password_Change->getExpirationDate() != NULL){
			$sql .= " expiration_date = ? AND";
			array_push( $val, $Password_Change->getExpirationDate() );
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
			$bar =  new PasswordChange($foo);
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
	  * aqui, sin embargo. El valor de retorno indica cu‡ntas filas se vieron afectadas.
	  *	
	  * @internal private information for advanced developers only
	  * @return Filas afectadas o un string con la descripcion del error
	  * @param PasswordChange [$Password_Change] El objeto de tipo PasswordChange a actualizar.
	  **/
	private static final function update( $Password_Change )
	{
		$sql = "UPDATE Password_Change SET  token = ?, ip = ?, expiration_date = ? WHERE  user_id = ?;";
		$params = array( 
			$Password_Change->getToken(), 
			$Password_Change->getIp(), 
			$Password_Change->getExpirationDate(), 
			$Password_Change->getUserId(), );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		return $conn->Affected_Rows();
	}


	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto PasswordChange suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto PasswordChange dentro de la misma transaccion.
	  *	
	  * @internal private information for advanced developers only
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param PasswordChange [$Password_Change] El objeto de tipo PasswordChange a crear.
	  **/
	private static final function create( &$Password_Change )
	{
		$sql = "INSERT INTO Password_Change ( user_id, token, ip, expiration_date ) VALUES ( ?, ?, ?, ?);";
		$params = array( 
			$Password_Change->getUserId(), 
			$Password_Change->getToken(), 
			$Password_Change->getIp(), 
			$Password_Change->getExpirationDate(), 
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
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link PasswordChange} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link PasswordChange}.
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
	  * @param PasswordChange [$Password_Change] El objeto de tipo PasswordChange
	  * @param PasswordChange [$Password_Change] El objeto de tipo PasswordChange
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $Password_ChangeA , $Password_ChangeB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Password_Change WHERE ("; 
		$val = array();
		if( (($a = $Password_ChangeA->getUserId()) != NULL) & ( ($b = $Password_ChangeB->getUserId()) != NULL) ){
				$sql .= " user_id >= ? AND user_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " user_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $Password_ChangeA->getToken()) != NULL) & ( ($b = $Password_ChangeB->getToken()) != NULL) ){
				$sql .= " token >= ? AND token <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " token = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $Password_ChangeA->getIp()) != NULL) & ( ($b = $Password_ChangeB->getIp()) != NULL) ){
				$sql .= " ip >= ? AND ip <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " ip = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $Password_ChangeA->getExpirationDate()) != NULL) & ( ($b = $Password_ChangeB->getExpirationDate()) != NULL) ){
				$sql .= " expiration_date >= ? AND expiration_date <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " expiration_date = ? AND"; 
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
    		array_push( $ar, new PasswordChange($foo));
		}
		return $ar;
	}


	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto PasswordChange suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param PasswordChange [$Password_Change] El objeto de tipo PasswordChange a eliminar
	  **/
	public static final function delete( &$Password_Change )
	{
		if(self::getByPK($Password_Change->getUserId()) === NULL) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Password_Change WHERE  user_id = ?;";
		$params = array( $Password_Change->getUserId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
