<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ContestAccessLog Data Access Object (DAO) Base.
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link ContestAccessLog }.
  * @access public
  * @abstract
  *
  */
abstract class ContestAccessLogDAOBase extends DAO
{
	/**
	  *	Guardar registros.
	  *
	  *	Este metodo guarda el estado actual del objeto {@link ContestAccessLog} pasado en la base de datos.
	  *	save() siempre creara una nueva fila.
	  *
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param ContestAccessLog [$Contest_Access_Log] El objeto de tipo ContestAccessLog
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( $Contest_Access_Log )
	{
		return ContestAccessLogDAOBase::create( $Contest_Access_Log);
	}

	/**
	  *	Obtener todas las filas.
	  *
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link ContestAccessLog}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link ContestAccessLog}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Contest_Access_Log";
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
			$bar = new ContestAccessLog($foo);
    		array_push( $allData, $bar);
		}
		return $allData;
	}

	/**
	  *	Buscar registros.
	  *
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ContestAccessLog} de la base de datos.
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
	  * @param ContestAccessLog [$Contest_Access_Log] El objeto de tipo ContestAccessLog
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Contest_Access_Log , $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = NULL, $likeColumns = NULL)
	{
		if (!($Contest_Access_Log instanceof ContestAccessLog)) {
			return self::search(new ContestAccessLog($Contest_Access_Log));
		}

		$sql = "SELECT * from Contest_Access_Log WHERE (";
		$val = array();
		if (!is_null( $Contest_Access_Log->getContestId())) {
			$sql .= " `contest_id` = ? AND";
			array_push( $val, $Contest_Access_Log->getContestId() );
		}
		if (!is_null( $Contest_Access_Log->getUserId())) {
			$sql .= " `user_id` = ? AND";
			array_push( $val, $Contest_Access_Log->getUserId() );
		}
		if (!is_null( $Contest_Access_Log->getIp())) {
			$sql .= " `ip` = ? AND";
			array_push( $val, $Contest_Access_Log->getIp() );
		}
		if (!is_null( $Contest_Access_Log->getTime())) {
			$sql .= " `time` = ? AND";
			array_push( $val, $Contest_Access_Log->getTime() );
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
			$bar =  new ContestAccessLog($foo);
			array_push( $ar,$bar);
		}
		return $ar;
	}

	/**
	  *	Crear registros.
	  *
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los
	  * contenidos del objeto ContestAccessLog suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave
	  * primaria generada en el objeto ContestAccessLog dentro de la misma transaccion.
	  *
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param ContestAccessLog [$Contest_Access_Log] El objeto de tipo ContestAccessLog a crear.
	  **/
	private static final function create( $Contest_Access_Log )
	{
		if (is_null($Contest_Access_Log->time)) $Contest_Access_Log->time = gmdate('Y-m-d H:i:s');
		$sql = "INSERT INTO Contest_Access_Log ( `contest_id`, `user_id`, `ip`, `time` ) VALUES ( ?, ?, ?, ?);";
		$params = array(
			$Contest_Access_Log->contest_id,
			$Contest_Access_Log->user_id,
			$Contest_Access_Log->ip,
			$Contest_Access_Log->time,
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
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ContestAccessLog} de la base de datos siempre y cuando
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link ContestAccessLog}.
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
	  * @param ContestAccessLog [$Contest_Access_Log] El objeto de tipo ContestAccessLog
	  * @param ContestAccessLog [$Contest_Access_Log] El objeto de tipo ContestAccessLog
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $Contest_Access_LogA , $Contest_Access_LogB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Contest_Access_Log WHERE (";
		$val = array();
		if( ( !is_null (($a = $Contest_Access_LogA->getContestId()) ) ) & ( ! is_null ( ($b = $Contest_Access_LogB->getContestId()) ) ) ){
				$sql .= " `contest_id` >= ? AND `contest_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `contest_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $Contest_Access_LogA->getUserId()) ) ) & ( ! is_null ( ($b = $Contest_Access_LogB->getUserId()) ) ) ){
				$sql .= " `user_id` >= ? AND `user_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `user_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $Contest_Access_LogA->getIp()) ) ) & ( ! is_null ( ($b = $Contest_Access_LogB->getIp()) ) ) ){
				$sql .= " `ip` >= ? AND `ip` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `ip` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $Contest_Access_LogA->getTime()) ) ) & ( ! is_null ( ($b = $Contest_Access_LogB->getTime()) ) ) ){
				$sql .= " `time` >= ? AND `time` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `time` = ? AND";
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
			array_push( $ar, $bar = new ContestAccessLog($row));
		}
		return $ar;
	}
}

