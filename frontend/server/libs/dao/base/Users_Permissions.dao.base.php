<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** UsersPermissions Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link UsersPermissions }. 
  * @access public
  * @abstract
  * 
  */
abstract class UsersPermissionsDAOBase extends DAO
{

	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link UsersPermissions} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param UsersPermissions [$Users_Permissions] El objeto de tipo UsersPermissions
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Users_Permissions )
	{
		if (!is_null(self::getByPK( $Users_Permissions->getUserId() , $Users_Permissions->getPermissionId() )))
		{
			return UsersPermissionsDAOBase::update( $Users_Permissions);
		} else {
			return UsersPermissionsDAOBase::create( $Users_Permissions);
		}
	}


	/**
	  *	Obtener {@link UsersPermissions} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link UsersPermissions} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link UsersPermissions Un objeto del tipo {@link UsersPermissions}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $user_id, $permission_id )
	{
		if(  is_null( $user_id ) || is_null( $permission_id )  ){ return NULL; }
			return new UsersPermissions($obj);
		}
		$sql = "SELECT * FROM Users_Permissions WHERE (user_id = ? AND permission_id = ? ) LIMIT 1;";
		$params = array(  $user_id, $permission_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0) return NULL;
		$foo = new UsersPermissions( $rs );
		return $foo;
	}

	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link UsersPermissions}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link UsersPermissions}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Users_Permissions";
		if( ! is_null ( $orden ) )
		{ $sql .= " ORDER BY " . $orden . " " . $tipo_de_orden;	}
		if( ! is_null ( $pagina ) )
		{
			$sql .= " LIMIT " . (( $pagina - 1 )*$columnas_por_pagina) . "," . $columnas_por_pagina; 
		}
		global $conn;
		$rs = $conn->Execute($sql);
		$allData = array();
		foreach ($rs as $foo) {
			$bar = new UsersPermissions($foo);
    		array_push( $allData, $bar);
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link UsersPermissions} de la base de datos. 
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
	  * @param UsersPermissions [$Users_Permissions] El objeto de tipo UsersPermissions
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Users_Permissions , $orderBy = null, $orden = 'ASC')
	{
		if (!($Users_Permissions instanceof UsersPermissions)) {
			return self::search(new UsersPermissions($Users_Permissions));
		}

		$sql = "SELECT * from Users_Permissions WHERE ("; 
		$val = array();
		if (!is_null( $Users_Permissions->getUserId())) {
			$sql .= " `user_id` = ? AND";
			array_push( $val, $Users_Permissions->getUserId() );
		}
		if (!is_null( $Users_Permissions->getPermissionId())) {
			$sql .= " `permission_id` = ? AND";
			array_push( $val, $Users_Permissions->getPermissionId() );
		}
		if (!is_null( $Users_Permissions->getContestId())) {
			$sql .= " `contest_id` = ? AND";
			array_push( $val, $Users_Permissions->getContestId() );
		}
		if(sizeof($val) == 0) {
			return self::getAll();
		}
		$sql = substr($sql, 0, -3) . " )";
		if( ! is_null ( $orderBy ) ){
			$sql .= " order by " . $orderBy . " " . $orden ;
		}
		global $conn;
		$rs = $conn->Execute($sql, $val);
		$ar = array();
		foreach ($rs as $foo) {
			$bar =  new UsersPermissions($foo);
			array_push( $ar,$bar);
		}
		return $ar;
	}

	/**
	  *	Actualizar registros.
	  *
	  * @return Filas afectadas
	  * @param UsersPermissions [$Users_Permissions] El objeto de tipo UsersPermissions a actualizar.
	  **/
	private static final function update($Users_Permissions)
	{
		$sql = "UPDATE Users_Permissions SET  `contest_id` = ? WHERE  `user_id` = ? AND `permission_id` = ?;";
		$params = array( 
			$Users_Permissions->getContestId(), 
			$Users_Permissions->getUserId(),$Users_Permissions->getPermissionId(), );
		global $conn;
		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}

	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto UsersPermissions suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto UsersPermissions dentro de la misma transaccion.
	  *	
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param UsersPermissions [$Users_Permissions] El objeto de tipo UsersPermissions a crear.
	  **/
	private static final function create( &$Users_Permissions )
	{
		$sql = "INSERT INTO Users_Permissions ( `user_id`, `permission_id`, `contest_id` ) VALUES ( ?, ?, ?);";
		$params = array( 
			$Users_Permissions->getUserId(), 
			$Users_Permissions->getPermissionId(), 
			$Users_Permissions->getContestId(), 
		 );
		global $conn;
		$conn->Execute($sql, $params);
		$ar = $conn->Affected_Rows();
		if($ar == 0) return 0;
 
		return $ar;
	}

	/**
	  *	Buscar por rango.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link UsersPermissions} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link UsersPermissions}.
	  * 
	  * Aquellas variables que tienen valores NULL seran excluidos en la busqueda (los valores 0 y false no son tomados como NULL) .
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
	  * @param UsersPermissions [$Users_Permissions] El objeto de tipo UsersPermissions
	  * @param UsersPermissions [$Users_Permissions] El objeto de tipo UsersPermissions
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $Users_PermissionsA , $Users_PermissionsB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Users_Permissions WHERE ("; 
		$val = array();
		if( ( !is_null (($a = $Users_PermissionsA->getUserId()) ) ) & ( ! is_null ( ($b = $Users_PermissionsB->getUserId()) ) ) ){
				$sql .= " `user_id` >= ? AND `user_id` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `user_id` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $Users_PermissionsA->getPermissionId()) ) ) & ( ! is_null ( ($b = $Users_PermissionsB->getPermissionId()) ) ) ){
				$sql .= " `permission_id` >= ? AND `permission_id` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `permission_id` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $Users_PermissionsA->getContestId()) ) ) & ( ! is_null ( ($b = $Users_PermissionsB->getContestId()) ) ) ){
				$sql .= " `contest_id` >= ? AND `contest_id` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `contest_id` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		$sql = substr($sql, 0, -3) . " )";
		if( !is_null ( $orderBy ) ){
		    $sql .= " order by " . $orderBy . " " . $orden ;

		}
		global $conn;
		$rs = $conn->Execute($sql, $val);
		$ar = array();
		foreach ($rs as $row) {
			array_push( $ar, $bar = new UsersPermissions($row));
		}
		return $ar;
	}

	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto UsersPermissions suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param UsersPermissions [$Users_Permissions] El objeto de tipo UsersPermissions a eliminar
	  **/
	public static final function delete( $Users_Permissions )
	{
		if( is_null( self::getByPK($Users_Permissions->getUserId(), $Users_Permissions->getPermissionId()) ) ) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Users_Permissions WHERE  user_id = ? AND permission_id = ?;";
		$params = array( $Users_Permissions->getUserId(), $Users_Permissions->getPermissionId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
