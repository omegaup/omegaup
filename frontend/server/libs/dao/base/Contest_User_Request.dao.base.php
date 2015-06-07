<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ContestUserRequest Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link ContestUserRequest }. 
  * @access public
  * @abstract
  * 
  */
abstract class ContestUserRequestDAOBase extends DAO
{

	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link ContestUserRequest} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param ContestUserRequest [$Contest_User_Request] El objeto de tipo ContestUserRequest
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( $Contest_User_Request )
	{
		if (!is_null(self::getByPK( $Contest_User_Request->getUserId() , $Contest_User_Request->getContestId() )))
		{
			return ContestUserRequestDAOBase::update( $Contest_User_Request);
		} else {
			return ContestUserRequestDAOBase::create( $Contest_User_Request);
		}
	}


	/**
	  *	Obtener {@link ContestUserRequest} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link ContestUserRequest} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link ContestUserRequest Un objeto del tipo {@link ContestUserRequest}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $user_id, $contest_id )
	{
		if(  is_null( $user_id ) || is_null( $contest_id )  ){ return NULL; }
		$sql = "SELECT * FROM Contest_User_Request WHERE (user_id = ? AND contest_id = ? ) LIMIT 1;";
		$params = array(  $user_id, $contest_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0) return NULL;
		$foo = new ContestUserRequest( $rs );
		return $foo;
	}

	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link ContestUserRequest}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link ContestUserRequest}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Contest_User_Request";
		if( ! is_null ( $orden ) )
		{ $sql .= " ORDER BY `" . $orden . "` " . $tipo_de_orden;	}
		if( ! is_null ( $pagina ) )
		{
			$sql .= " LIMIT " . (( $pagina - 1 )*$columnas_por_pagina) . "," . $columnas_por_pagina; 
		}
		global $conn;
		$rs = $conn->Execute($sql);
		$allData = array();
		foreach ($rs as $foo) {
			$bar = new ContestUserRequest($foo);
    		array_push( $allData, $bar);
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ContestUserRequest} de la base de datos. 
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
	  * @param ContestUserRequest [$Contest_User_Request] El objeto de tipo ContestUserRequest
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Contest_User_Request , $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = NULL, $likeColumns = NULL)
	{
		if (!($Contest_User_Request instanceof ContestUserRequest)) {
			return self::search(new ContestUserRequest($Contest_User_Request));
		}

		$sql = "SELECT * from Contest_User_Request WHERE ("; 
		$val = array();
		if (!is_null( $Contest_User_Request->getUserId())) {
			$sql .= " `user_id` = ? AND";
			array_push( $val, $Contest_User_Request->getUserId() );
		}
		if (!is_null( $Contest_User_Request->getContestId())) {
			$sql .= " `contest_id` = ? AND";
			array_push( $val, $Contest_User_Request->getContestId() );
		}
		if (!is_null( $Contest_User_Request->getRequestTime())) {
			$sql .= " `request_time` = ? AND";
			array_push( $val, $Contest_User_Request->getRequestTime() );
		}
		if (!is_null( $Contest_User_Request->getLastUpdate())) {
			$sql .= " `last_update` = ? AND";
			array_push( $val, $Contest_User_Request->getLastUpdate() );
		}
		if (!is_null( $Contest_User_Request->getAccepted())) {
			$sql .= " `accepted` = ? AND";
			array_push( $val, $Contest_User_Request->getAccepted() );
		}
		if (!is_null( $Contest_User_Request->getExtraNote())) {
			$sql .= " `extra_note` = ? AND";
			array_push( $val, $Contest_User_Request->getExtraNote() );
		}
		if (!is_null( $Contest_User_Request->getReason())) {
			$sql .= " `reason` = ? AND";
			array_push( $val, $Contest_User_Request->getReason() );
		}
		if (!is_null($likeColumns)) {
			foreach ($likeColumns as $column => $value) {
				$escapedValue = mysql_real_escape_string($value);
				$sql .= "`{$column}` LIKE '%{$value}%' AND";
			}
		}
		if(sizeof($val) == 0) {
			return self::getAll();
		}
		$sql = substr($sql, 0, -3) . " )";
		if( ! is_null ( $orderBy ) ){
			$sql .= " ORDER BY `" . $orderBy . "` " . $orden;
		}
		// Add LIMIT offset, rowcount if rowcount is set
		if (!is_null($rowcount)) {
			$sql .= " LIMIT ". $offset . "," . $rowcount;
		}
		global $conn;
		$rs = $conn->Execute($sql, $val);
		$ar = array();
		foreach ($rs as $foo) {
			$bar =  new ContestUserRequest($foo);
			array_push( $ar,$bar);
		}
		return $ar;
	}

	/**
	  *	Actualizar registros.
	  *
	  * @return Filas afectadas
	  * @param ContestUserRequest [$Contest_User_Request] El objeto de tipo ContestUserRequest a actualizar.
	  **/
	private static final function update($Contest_User_Request)
	{
		$sql = "UPDATE Contest_User_Request SET  `request_time` = ?, `last_update` = ?, `accepted` = ?, `extra_note` = ?, `reason` = ? WHERE  `user_id` = ? AND `contest_id` = ?;";
		$params = array( 
			$Contest_User_Request->getRequestTime(), 
			$Contest_User_Request->getLastUpdate(), 
			$Contest_User_Request->getAccepted(), 
			$Contest_User_Request->getExtraNote(), 
			$Contest_User_Request->getReason(), 
			$Contest_User_Request->getUserId(),$Contest_User_Request->getContestId(), );
		global $conn;
		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}

	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto ContestUserRequest suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto ContestUserRequest dentro de la misma transaccion.
	  *	
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param ContestUserRequest [$Contest_User_Request] El objeto de tipo ContestUserRequest a crear.
	  **/
	private static final function create( $Contest_User_Request )
	{
		if (is_null($Contest_User_Request->request_time)) $Contest_User_Request->request_time = gmdate('Y-m-d H:i:s');
		$sql = "INSERT INTO Contest_User_Request ( `user_id`, `contest_id`, `request_time`, `last_update`, `accepted`, `extra_note`, `reason` ) VALUES ( ?, ?, ?, ?, ?, ?, ?);";
		$params = array( 
			$Contest_User_Request->user_id,
			$Contest_User_Request->contest_id,
			$Contest_User_Request->request_time,
			$Contest_User_Request->last_update,
			$Contest_User_Request->accepted,
			$Contest_User_Request->extra_note,
			$Contest_User_Request->reason,
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
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ContestUserRequest} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link ContestUserRequest}.
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
	  * @param ContestUserRequest [$Contest_User_Request] El objeto de tipo ContestUserRequest
	  * @param ContestUserRequest [$Contest_User_Request] El objeto de tipo ContestUserRequest
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $Contest_User_RequestA , $Contest_User_RequestB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Contest_User_Request WHERE ("; 
		$val = array();
		if( ( !is_null (($a = $Contest_User_RequestA->getUserId()) ) ) & ( ! is_null ( ($b = $Contest_User_RequestB->getUserId()) ) ) ){
				$sql .= " `user_id` >= ? AND `user_id` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `user_id` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $Contest_User_RequestA->getContestId()) ) ) & ( ! is_null ( ($b = $Contest_User_RequestB->getContestId()) ) ) ){
				$sql .= " `contest_id` >= ? AND `contest_id` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `contest_id` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $Contest_User_RequestA->getRequestTime()) ) ) & ( ! is_null ( ($b = $Contest_User_RequestB->getRequestTime()) ) ) ){
				$sql .= " `request_time` >= ? AND `request_time` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `request_time` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $Contest_User_RequestA->getLastUpdate()) ) ) & ( ! is_null ( ($b = $Contest_User_RequestB->getLastUpdate()) ) ) ){
				$sql .= " `last_update` >= ? AND `last_update` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `last_update` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $Contest_User_RequestA->getAccepted()) ) ) & ( ! is_null ( ($b = $Contest_User_RequestB->getAccepted()) ) ) ){
				$sql .= " `accepted` >= ? AND `accepted` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `accepted` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $Contest_User_RequestA->getExtraNote()) ) ) & ( ! is_null ( ($b = $Contest_User_RequestB->getExtraNote()) ) ) ){
				$sql .= " `extra_note` >= ? AND `extra_note` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `extra_note` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $Contest_User_RequestA->getReason()) ) ) & ( ! is_null ( ($b = $Contest_User_RequestB->getReason()) ) ) ){
				$sql .= " `reason` >= ? AND `reason` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `reason` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		$sql = substr($sql, 0, -3) . " )";
		if( !is_null ( $orderBy ) ){
		    $sql .= " order by `" . $orderBy . "` " . $orden ;

		}
		global $conn;
		$rs = $conn->Execute($sql, $val);
		$ar = array();
		foreach ($rs as $row) {
			array_push( $ar, $bar = new ContestUserRequest($row));
		}
		return $ar;
	}

	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto ContestUserRequest suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param ContestUserRequest [$Contest_User_Request] El objeto de tipo ContestUserRequest a eliminar
	  **/
	public static final function delete( $Contest_User_Request )
	{
		if( is_null( self::getByPK($Contest_User_Request->getUserId(), $Contest_User_Request->getContestId()) ) ) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Contest_User_Request WHERE  user_id = ? AND contest_id = ?;";
		$params = array( $Contest_User_Request->getUserId(), $Contest_User_Request->getContestId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
