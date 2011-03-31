<?php
/** RolesPermissions Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link RolesPermissions }. 
  * @author alan@caffeina.mx
  * @access private
  * @abstract
  * @package docs
  * 
  */
abstract class RolesPermissionsDAOBase extends DAO
{

		private static $loadedRecords = array();

		private static function recordExists(  $role_id, $permission_id ){
			$pk = "";
			$pk .= $role_id . "-";
			$pk .= $permission_id . "-";
			return array_key_exists ( $pk , self::$loadedRecords );
		}
		private static function pushRecord( $inventario,  $role_id, $permission_id){
			$pk = "";
			$pk .= $role_id . "-";
			$pk .= $permission_id . "-";
			self::$loadedRecords [$pk] = $inventario;
		}
		private static function getRecord(  $role_id, $permission_id ){
			$pk = "";
			$pk .= $role_id . "-";
			$pk .= $permission_id . "-";
			return self::$loadedRecords[$pk];
		}
	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link RolesPermissions} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param RolesPermissions [$Roles_Permissions] El objeto de tipo RolesPermissions
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Roles_Permissions )
	{
		if(  self::getByPK(  $Roles_Permissions->getRoleId() , $Roles_Permissions->getPermissionId() ) !== NULL )
		{
			try{ return RolesPermissionsDAOBase::update( $Roles_Permissions) ; } catch(Exception $e){ throw $e; }
		}else{
			try{ return RolesPermissionsDAOBase::create( $Roles_Permissions) ; } catch(Exception $e){ throw $e; }
		}
	}


	/**
	  *	Obtener {@link RolesPermissions} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link RolesPermissions} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link RolesPermissions Un objeto del tipo {@link RolesPermissions}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $role_id, $permission_id )
	{
		if(self::recordExists(  $role_id, $permission_id)){
			return self::getRecord( $role_id, $permission_id );
		}
		$sql = "SELECT * FROM Roles_Permissions WHERE (role_id = ? AND permission_id = ? ) LIMIT 1;";
		$params = array(  $role_id, $permission_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
			$foo = new RolesPermissions( $rs );
			self::pushRecord( $foo,  $role_id, $permission_id );
			return $foo;
	}


	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link RolesPermissions}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link RolesPermissions}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Roles_Permissions";
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
			$bar = new RolesPermissions($foo);
    		array_push( $allData, $bar);
			//role_id
			//permission_id
    		self::pushRecord( $bar, $foo["role_id"],$foo["permission_id"] );
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link RolesPermissions} de la base de datos. 
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
	  * @param RolesPermissions [$Roles_Permissions] El objeto de tipo RolesPermissions
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Roles_Permissions , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Roles_Permissions WHERE ("; 
		$val = array();
		if( $Roles_Permissions->getRoleId() != NULL){
			$sql .= " role_id = ? AND";
			array_push( $val, $Roles_Permissions->getRoleId() );
		}

		if( $Roles_Permissions->getPermissionId() != NULL){
			$sql .= " permission_id = ? AND";
			array_push( $val, $Roles_Permissions->getPermissionId() );
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
			$bar =  new RolesPermissions($foo);
    		array_push( $ar,$bar);
    		self::pushRecord( $bar, $foo["role_id"],$foo["permission_id"] );
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
	  * @param RolesPermissions [$Roles_Permissions] El objeto de tipo RolesPermissions a actualizar.
	  **/
	private static final function update( $Roles_Permissions )
	{
	}


	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto RolesPermissions suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto RolesPermissions dentro de la misma transaccion.
	  *	
	  * @internal private information for advanced developers only
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param RolesPermissions [$Roles_Permissions] El objeto de tipo RolesPermissions a crear.
	  **/
	private static final function create( &$Roles_Permissions )
	{
		$sql = "INSERT INTO Roles_Permissions ( role_id, permission_id ) VALUES ( ?, ?);";
		$params = array( 
			$Roles_Permissions->getRoleId(), 
			$Roles_Permissions->getPermissionId(), 
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
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link RolesPermissions} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link RolesPermissions}.
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
	  * @param RolesPermissions [$Roles_Permissions] El objeto de tipo RolesPermissions
	  * @param RolesPermissions [$Roles_Permissions] El objeto de tipo RolesPermissions
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $Roles_PermissionsA , $Roles_PermissionsB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Roles_Permissions WHERE ("; 
		$val = array();
		if( (($a = $Roles_PermissionsA->getRoleId()) != NULL) & ( ($b = $Roles_PermissionsB->getRoleId()) != NULL) ){
				$sql .= " role_id >= ? AND role_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " role_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $Roles_PermissionsA->getPermissionId()) != NULL) & ( ($b = $Roles_PermissionsB->getPermissionId()) != NULL) ){
				$sql .= " permission_id >= ? AND permission_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " permission_id = ? AND"; 
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
    		array_push( $ar, new RolesPermissions($foo));
		}
		return $ar;
	}


	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto RolesPermissions suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param RolesPermissions [$Roles_Permissions] El objeto de tipo RolesPermissions a eliminar
	  **/
	public static final function delete( &$Roles_Permissions )
	{
		if(self::getByPK($Roles_Permissions->getRoleId(), $Roles_Permissions->getPermissionId()) === NULL) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Roles_Permissions WHERE  role_id = ? AND permission_id = ?;";
		$params = array( $Roles_Permissions->getRoleId(), $Roles_Permissions->getPermissionId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
