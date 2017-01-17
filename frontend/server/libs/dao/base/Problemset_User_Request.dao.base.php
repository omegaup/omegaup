<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ProblemsetUserRequest Data Access Object (DAO) Base.
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsetUserRequest }.
  * @access public
  * @abstract
  *
  */
abstract class ProblemsetUserRequestDAOBase extends DAO
{
	/**
	  *	Guardar registros.
	  *
	  *	Este metodo guarda el estado actual del objeto {@link ProblemsetUserRequest} pasado en la base de datos. La llave
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param ProblemsetUserRequest [$Problemset_User_Request] El objeto de tipo ProblemsetUserRequest
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( $Problemset_User_Request )
	{
		if (!is_null(self::getByPK( $Problemset_User_Request->user_id, $Problemset_User_Request->problemset_id)))
		{
			return ProblemsetUserRequestDAOBase::update( $Problemset_User_Request);
		} else {
			return ProblemsetUserRequestDAOBase::create( $Problemset_User_Request);
		}
	}

	/**
	  *	Obtener {@link ProblemsetUserRequest} por llave primaria.
	  *
	  * Este metodo cargara un objeto {@link ProblemsetUserRequest} de la base de datos
	  * usando sus llaves primarias.
	  *
	  *	@static
	  * @return @link ProblemsetUserRequest Un objeto del tipo {@link ProblemsetUserRequest}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $user_id, $problemset_id )
	{
		if(  is_null( $user_id ) || is_null( $problemset_id )  ){ return NULL; }
		$sql = "SELECT * FROM Problemset_User_Request WHERE (user_id = ? AND problemset_id = ? ) LIMIT 1;";
		$params = array(  $user_id, $problemset_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0) return NULL;
		$foo = new ProblemsetUserRequest( $rs );
		return $foo;
	}

	/**
	  *	Obtener todas las filas.
	  *
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link ProblemsetUserRequest}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link ProblemsetUserRequest}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Problemset_User_Request";
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
			$bar = new ProblemsetUserRequest($foo);
    		array_push( $allData, $bar);
		}
		return $allData;
	}

	/**
	  *	Buscar registros.
	  *
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ProblemsetUserRequest} de la base de datos.
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
	  *	  	echo $c->nombre . "<br>";
	  *	  }
	  * </code>
	  *	@static
	  * @param ProblemsetUserRequest [$Problemset_User_Request] El objeto de tipo ProblemsetUserRequest
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Problemset_User_Request , $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = NULL, $likeColumns = NULL)
	{
		if (!($Problemset_User_Request instanceof ProblemsetUserRequest)) {
			return self::search(new ProblemsetUserRequest($Problemset_User_Request));
		}

		$sql = "SELECT * from Problemset_User_Request WHERE (";
		$val = array();
		if (!is_null( $Problemset_User_Request->user_id)) {
			$sql .= " `user_id` = ? AND";
			array_push( $val, $Problemset_User_Request->user_id );
		}
		if (!is_null( $Problemset_User_Request->problemset_id)) {
			$sql .= " `problemset_id` = ? AND";
			array_push( $val, $Problemset_User_Request->problemset_id );
		}
		if (!is_null( $Problemset_User_Request->request_time)) {
			$sql .= " `request_time` = ? AND";
			array_push( $val, $Problemset_User_Request->request_time );
		}
		if (!is_null( $Problemset_User_Request->last_update)) {
			$sql .= " `last_update` = ? AND";
			array_push( $val, $Problemset_User_Request->last_update );
		}
		if (!is_null( $Problemset_User_Request->accepted)) {
			$sql .= " `accepted` = ? AND";
			array_push( $val, $Problemset_User_Request->accepted );
		}
		if (!is_null( $Problemset_User_Request->extra_note)) {
			$sql .= " `extra_note` = ? AND";
			array_push( $val, $Problemset_User_Request->extra_note );
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
			$bar =  new ProblemsetUserRequest($foo);
			array_push( $ar,$bar);
		}
		return $ar;
	}

	/**
	  *	Actualizar registros.
	  *
	  * @return Filas afectadas
	  * @param ProblemsetUserRequest [$Problemset_User_Request] El objeto de tipo ProblemsetUserRequest a actualizar.
	  **/
	private static final function update($Problemset_User_Request)
	{
		$sql = "UPDATE Problemset_User_Request SET  `request_time` = ?, `last_update` = ?, `accepted` = ?, `extra_note` = ? WHERE  `user_id` = ? AND `problemset_id` = ?;";
		$params = array(
			$Problemset_User_Request->request_time,
			$Problemset_User_Request->last_update,
			$Problemset_User_Request->accepted,
			$Problemset_User_Request->extra_note,
			$Problemset_User_Request->user_id,$Problemset_User_Request->problemset_id, );
		global $conn;
		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}

	/**
	  *	Crear registros.
	  *
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los
	  * contenidos del objeto ProblemsetUserRequest suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave
	  * primaria generada en el objeto ProblemsetUserRequest dentro de la misma transaccion.
	  *
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param ProblemsetUserRequest [$Problemset_User_Request] El objeto de tipo ProblemsetUserRequest a crear.
	  **/
	private static final function create( $Problemset_User_Request )
	{
		if (is_null($Problemset_User_Request->request_time)) $Problemset_User_Request->request_time = gmdate('Y-m-d H:i:s');
		$sql = "INSERT INTO Problemset_User_Request ( `user_id`, `problemset_id`, `request_time`, `last_update`, `accepted`, `extra_note` ) VALUES ( ?, ?, ?, ?, ?, ?);";
		$params = array(
			$Problemset_User_Request->user_id,
			$Problemset_User_Request->problemset_id,
			$Problemset_User_Request->request_time,
			$Problemset_User_Request->last_update,
			$Problemset_User_Request->accepted,
			$Problemset_User_Request->extra_note,
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
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ProblemsetUserRequest} de la base de datos siempre y cuando
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link ProblemsetUserRequest}.
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
	  *	  $cr1->limite_credito = "2000";
	  *	  $cr1->descuento = "50";
	  *
	  *	  $cr2 = new Cliente();
	  *	  $cr2->limite_credito = "5000";
	  *	  $resultados = ClienteDAO::byRange($cr1, $cr2);
	  *
	  *	  foreach($resultados as $c ){
	  *	  	echo $c->nombre . "<br>";
	  *	  }
	  * </code>
	  *	@static
	  * @param ProblemsetUserRequest [$Problemset_User_Request] El objeto de tipo ProblemsetUserRequest
	  * @param ProblemsetUserRequest [$Problemset_User_Request] El objeto de tipo ProblemsetUserRequest
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $Problemset_User_RequestA , $Problemset_User_RequestB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Problemset_User_Request WHERE (";
		$val = array();
		if( ( !is_null (($a = $Problemset_User_RequestA->user_id) ) ) & ( ! is_null ( ($b = $Problemset_User_RequestB->user_id) ) ) ){
				$sql .= " `user_id` >= ? AND `user_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `user_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $Problemset_User_RequestA->problemset_id) ) ) & ( ! is_null ( ($b = $Problemset_User_RequestB->problemset_id) ) ) ){
				$sql .= " `problemset_id` >= ? AND `problemset_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `problemset_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $Problemset_User_RequestA->request_time) ) ) & ( ! is_null ( ($b = $Problemset_User_RequestB->request_time) ) ) ){
				$sql .= " `request_time` >= ? AND `request_time` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `request_time` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $Problemset_User_RequestA->last_update) ) ) & ( ! is_null ( ($b = $Problemset_User_RequestB->last_update) ) ) ){
				$sql .= " `last_update` >= ? AND `last_update` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `last_update` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $Problemset_User_RequestA->accepted) ) ) & ( ! is_null ( ($b = $Problemset_User_RequestB->accepted) ) ) ){
				$sql .= " `accepted` >= ? AND `accepted` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `accepted` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $Problemset_User_RequestA->extra_note) ) ) & ( ! is_null ( ($b = $Problemset_User_RequestB->extra_note) ) ) ){
				$sql .= " `extra_note` >= ? AND `extra_note` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `extra_note` = ? AND";
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
			array_push( $ar, $bar = new ProblemsetUserRequest($row));
		}
		return $ar;
	}

	/**
	  *	Eliminar registros.
	  *
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto ProblemsetUserRequest suministrado. Una vez que se ha suprimido un objeto, este no
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param ProblemsetUserRequest [$Problemset_User_Request] El objeto de tipo ProblemsetUserRequest a eliminar
	  **/
	public static final function delete( $Problemset_User_Request )
	{
		if( is_null( self::getByPK($Problemset_User_Request->user_id, $Problemset_User_Request->problemset_id) ) ) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Problemset_User_Request WHERE  user_id = ? AND problemset_id = ?;";
		$params = array( $Problemset_User_Request->user_id, $Problemset_User_Request->problemset_id );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}
}
