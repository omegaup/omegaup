<?php
/** UsersBadges Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link UsersBadges }. 
  * @author alanboy
  * @access private
  * @abstract
  * @package docs
  * 
  */
abstract class UsersBadgesDAOBase extends DAO
{

		private static $loadedRecords = array();

		private static function recordExists(  $badge_id, $user_id ){
			$pk = "";
			$pk .= $badge_id . "-";
			$pk .= $user_id . "-";
			return array_key_exists ( $pk , self::$loadedRecords );
		}
		private static function pushRecord( $inventario,  $badge_id, $user_id){
			$pk = "";
			$pk .= $badge_id . "-";
			$pk .= $user_id . "-";
			self::$loadedRecords [$pk] = $inventario;
		}
		private static function getRecord(  $badge_id, $user_id ){
			$pk = "";
			$pk .= $badge_id . "-";
			$pk .= $user_id . "-";
			return self::$loadedRecords[$pk];
		}
	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link UsersBadges} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param UsersBadges [$Users_Badges] El objeto de tipo UsersBadges
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Users_Badges )
	{
		if(  self::getByPK(  $Users_Badges->getBadgeId() , $Users_Badges->getUserId() ) !== NULL )
		{
			try{ return UsersBadgesDAOBase::update( $Users_Badges) ; } catch(Exception $e){ throw $e; }
		}else{
			try{ return UsersBadgesDAOBase::create( $Users_Badges) ; } catch(Exception $e){ throw $e; }
		}
	}


	/**
	  *	Obtener {@link UsersBadges} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link UsersBadges} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link UsersBadges Un objeto del tipo {@link UsersBadges}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $badge_id, $user_id )
	{
		if(self::recordExists(  $badge_id, $user_id)){
			return self::getRecord( $badge_id, $user_id );
		}
		$sql = "SELECT * FROM Users_Badges WHERE (badge_id = ? AND user_id = ? ) LIMIT 1;";
		$params = array(  $badge_id, $user_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
			$foo = new UsersBadges( $rs );
			self::pushRecord( $foo,  $badge_id, $user_id );
			return $foo;
	}


	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link UsersBadges}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link UsersBadges}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Users_Badges";
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
			$bar = new UsersBadges($foo);
    		array_push( $allData, $bar);
			//badge_id
			//user_id
    		self::pushRecord( $bar, $foo["badge_id"],$foo["user_id"] );
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link UsersBadges} de la base de datos. 
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
	  * @param UsersBadges [$Users_Badges] El objeto de tipo UsersBadges
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Users_Badges , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Users_Badges WHERE ("; 
		$val = array();
		if( $Users_Badges->getBadgeId() != NULL){
			$sql .= " badge_id = ? AND";
			array_push( $val, $Users_Badges->getBadgeId() );
		}

		if( $Users_Badges->getUserId() != NULL){
			$sql .= " user_id = ? AND";
			array_push( $val, $Users_Badges->getUserId() );
		}

		if( $Users_Badges->getTime() != NULL){
			$sql .= " time = ? AND";
			array_push( $val, $Users_Badges->getTime() );
		}

		if( $Users_Badges->getLastProblemId() != NULL){
			$sql .= " last_problem_id = ? AND";
			array_push( $val, $Users_Badges->getLastProblemId() );
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
			$bar =  new UsersBadges($foo);
    		array_push( $ar,$bar);
    		self::pushRecord( $bar, $foo["badge_id"],$foo["user_id"] );
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
	  * @param UsersBadges [$Users_Badges] El objeto de tipo UsersBadges a actualizar.
	  **/
	private static final function update( $Users_Badges )
	{
		$sql = "UPDATE Users_Badges SET  time = ?, last_problem_id = ? WHERE  badge_id = ? AND user_id = ?;";
		$params = array( 
			$Users_Badges->getTime(), 
			$Users_Badges->getLastProblemId(), 
			$Users_Badges->getBadgeId(),$Users_Badges->getUserId(), );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		return $conn->Affected_Rows();
	}


	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto UsersBadges suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto UsersBadges dentro de la misma transaccion.
	  *	
	  * @internal private information for advanced developers only
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param UsersBadges [$Users_Badges] El objeto de tipo UsersBadges a crear.
	  **/
	private static final function create( &$Users_Badges )
	{
		$sql = "INSERT INTO Users_Badges ( badge_id, user_id, time, last_problem_id ) VALUES ( ?, ?, ?, ?);";
		$params = array( 
			$Users_Badges->getBadgeId(), 
			$Users_Badges->getUserId(), 
			$Users_Badges->getTime(), 
			$Users_Badges->getLastProblemId(), 
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
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link UsersBadges} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link UsersBadges}.
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
	  * @param UsersBadges [$Users_Badges] El objeto de tipo UsersBadges
	  * @param UsersBadges [$Users_Badges] El objeto de tipo UsersBadges
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $Users_BadgesA , $Users_BadgesB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Users_Badges WHERE ("; 
		$val = array();
		if( (($a = $Users_BadgesA->getBadgeId()) != NULL) & ( ($b = $Users_BadgesB->getBadgeId()) != NULL) ){
				$sql .= " badge_id >= ? AND badge_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " badge_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $Users_BadgesA->getUserId()) != NULL) & ( ($b = $Users_BadgesB->getUserId()) != NULL) ){
				$sql .= " user_id >= ? AND user_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " user_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $Users_BadgesA->getTime()) != NULL) & ( ($b = $Users_BadgesB->getTime()) != NULL) ){
				$sql .= " time >= ? AND time <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " time = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $Users_BadgesA->getLastProblemId()) != NULL) & ( ($b = $Users_BadgesB->getLastProblemId()) != NULL) ){
				$sql .= " last_problem_id >= ? AND last_problem_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " last_problem_id = ? AND"; 
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
    		array_push( $ar, new UsersBadges($foo));
		}
		return $ar;
	}


	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto UsersBadges suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param UsersBadges [$Users_Badges] El objeto de tipo UsersBadges a eliminar
	  **/
	public static final function delete( &$Users_Badges )
	{
		if(self::getByPK($Users_Badges->getBadgeId(), $Users_Badges->getUserId()) === NULL) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Users_Badges WHERE  badge_id = ? AND user_id = ?;";
		$params = array( $Users_Badges->getBadgeId(), $Users_Badges->getUserId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
