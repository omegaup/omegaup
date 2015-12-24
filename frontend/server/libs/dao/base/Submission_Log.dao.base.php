<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** SubmissionLog Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link SubmissionLog }. 
  * @access public
  * @abstract
  * 
  */
abstract class SubmissionLogDAOBase extends DAO
{

	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link SubmissionLog} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param SubmissionLog [$Submission_Log] El objeto de tipo SubmissionLog
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( $Submission_Log )
	{
		if (!is_null(self::getByPK( $Submission_Log->getRunId() )))
		{
			return SubmissionLogDAOBase::update( $Submission_Log);
		} else {
			return SubmissionLogDAOBase::create( $Submission_Log);
		}
	}


	/**
	  *	Obtener {@link SubmissionLog} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link SubmissionLog} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link SubmissionLog Un objeto del tipo {@link SubmissionLog}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $run_id )
	{
		if(  is_null( $run_id )  ){ return NULL; }
		$sql = "SELECT * FROM Submission_Log WHERE (run_id = ? ) LIMIT 1;";
		$params = array(  $run_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0) return NULL;
		$foo = new SubmissionLog( $rs );
		return $foo;
	}

	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link SubmissionLog}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link SubmissionLog}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Submission_Log";
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
			$bar = new SubmissionLog($foo);
    		array_push( $allData, $bar);
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link SubmissionLog} de la base de datos. 
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
	  * @param SubmissionLog [$Submission_Log] El objeto de tipo SubmissionLog
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Submission_Log , $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = NULL, $likeColumns = NULL)
	{
		if (!($Submission_Log instanceof SubmissionLog)) {
			return self::search(new SubmissionLog($Submission_Log));
		}

		$sql = "SELECT * from Submission_Log WHERE ("; 
		$val = array();
		if (!is_null( $Submission_Log->getContestId())) {
			$sql .= " `contest_id` = ? AND";
			array_push( $val, $Submission_Log->getContestId() );
		}
		if (!is_null( $Submission_Log->getRunId())) {
			$sql .= " `run_id` = ? AND";
			array_push( $val, $Submission_Log->getRunId() );
		}
		if (!is_null( $Submission_Log->getUserId())) {
			$sql .= " `user_id` = ? AND";
			array_push( $val, $Submission_Log->getUserId() );
		}
		if (!is_null( $Submission_Log->getIp())) {
			$sql .= " `ip` = ? AND";
			array_push( $val, $Submission_Log->getIp() );
		}
		if (!is_null( $Submission_Log->getTime())) {
			$sql .= " `time` = ? AND";
			array_push( $val, $Submission_Log->getTime() );
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
			$bar =  new SubmissionLog($foo);
			array_push( $ar,$bar);
		}
		return $ar;
	}

	/**
	  *	Actualizar registros.
	  *
	  * @return Filas afectadas
	  * @param SubmissionLog [$Submission_Log] El objeto de tipo SubmissionLog a actualizar.
	  **/
	private static final function update($Submission_Log)
	{
		$sql = "UPDATE Submission_Log SET  `contest_id` = ?, `user_id` = ?, `ip` = ?, `time` = ? WHERE  `run_id` = ?;";
		$params = array( 
			$Submission_Log->getContestId(), 
			$Submission_Log->getUserId(), 
			$Submission_Log->getIp(), 
			$Submission_Log->getTime(), 
			$Submission_Log->getRunId(), );
		global $conn;
		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}

	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto SubmissionLog suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto SubmissionLog dentro de la misma transaccion.
	  *	
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param SubmissionLog [$Submission_Log] El objeto de tipo SubmissionLog a crear.
	  **/
	private static final function create( $Submission_Log )
	{
		if (is_null($Submission_Log->time)) $Submission_Log->time = gmdate('Y-m-d H:i:s');
		$sql = "INSERT INTO Submission_Log ( `contest_id`, `run_id`, `user_id`, `ip`, `time` ) VALUES ( ?, ?, ?, ?, ?);";
		$params = array( 
			$Submission_Log->contest_id,
			$Submission_Log->run_id,
			$Submission_Log->user_id,
			$Submission_Log->ip,
			$Submission_Log->time,
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
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link SubmissionLog} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link SubmissionLog}.
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
	  * @param SubmissionLog [$Submission_Log] El objeto de tipo SubmissionLog
	  * @param SubmissionLog [$Submission_Log] El objeto de tipo SubmissionLog
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $Submission_LogA , $Submission_LogB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Submission_Log WHERE ("; 
		$val = array();
		if( ( !is_null (($a = $Submission_LogA->getContestId()) ) ) & ( ! is_null ( ($b = $Submission_LogB->getContestId()) ) ) ){
				$sql .= " `contest_id` >= ? AND `contest_id` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `contest_id` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $Submission_LogA->getRunId()) ) ) & ( ! is_null ( ($b = $Submission_LogB->getRunId()) ) ) ){
				$sql .= " `run_id` >= ? AND `run_id` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `run_id` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $Submission_LogA->getUserId()) ) ) & ( ! is_null ( ($b = $Submission_LogB->getUserId()) ) ) ){
				$sql .= " `user_id` >= ? AND `user_id` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `user_id` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $Submission_LogA->getIp()) ) ) & ( ! is_null ( ($b = $Submission_LogB->getIp()) ) ) ){
				$sql .= " `ip` >= ? AND `ip` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `ip` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $Submission_LogA->getTime()) ) ) & ( ! is_null ( ($b = $Submission_LogB->getTime()) ) ) ){
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
			array_push( $ar, $bar = new SubmissionLog($row));
		}
		return $ar;
	}

	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto SubmissionLog suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param SubmissionLog [$Submission_Log] El objeto de tipo SubmissionLog a eliminar
	  **/
	public static final function delete( $Submission_Log )
	{
		if( is_null( self::getByPK($Submission_Log->getRunId()) ) ) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Submission_Log WHERE  run_id = ?;";
		$params = array( $Submission_Log->getRunId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
