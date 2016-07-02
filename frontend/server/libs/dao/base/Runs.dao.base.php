<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Runs Data Access Object (DAO) Base.
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Runs }.
  * @access public
  * @abstract
  *
  */
abstract class RunsDAOBase extends DAO
{
	/**
	  *	Guardar registros.
	  *
	  *	Este metodo guarda el estado actual del objeto {@link Runs} pasado en la base de datos. La llave
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param Runs [$Runs] El objeto de tipo Runs
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( $Runs )
	{
		if (!is_null(self::getByPK( $Runs->getRunId() )))
		{
			return RunsDAOBase::update( $Runs);
		} else {
			return RunsDAOBase::create( $Runs);
		}
	}

	/**
	  *	Obtener {@link Runs} por llave primaria.
	  *
	  * Este metodo cargara un objeto {@link Runs} de la base de datos
	  * usando sus llaves primarias.
	  *
	  *	@static
	  * @return @link Runs Un objeto del tipo {@link Runs}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $run_id )
	{
		if(  is_null( $run_id )  ){ return NULL; }
		$sql = "SELECT * FROM Runs WHERE (run_id = ? ) LIMIT 1;";
		$params = array(  $run_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0) return NULL;
		$foo = new Runs( $rs );
		return $foo;
	}

	/**
	  *	Obtener todas las filas.
	  *
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link Runs}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link Runs}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Runs";
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
			$bar = new Runs($foo);
    		array_push( $allData, $bar);
		}
		return $allData;
	}

	/**
	  *	Buscar registros.
	  *
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Runs} de la base de datos.
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
	  * @param Runs [$Runs] El objeto de tipo Runs
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Runs , $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = NULL, $likeColumns = NULL)
	{
		if (!($Runs instanceof Runs)) {
			return self::search(new Runs($Runs));
		}

		$sql = "SELECT * from Runs WHERE (";
		$val = array();
		if (!is_null( $Runs->getRunId())) {
			$sql .= " `run_id` = ? AND";
			array_push( $val, $Runs->getRunId() );
		}
		if (!is_null( $Runs->getUserId())) {
			$sql .= " `user_id` = ? AND";
			array_push( $val, $Runs->getUserId() );
		}
		if (!is_null( $Runs->getProblemId())) {
			$sql .= " `problem_id` = ? AND";
			array_push( $val, $Runs->getProblemId() );
		}
		if (!is_null( $Runs->getContestId())) {
			$sql .= " `contest_id` = ? AND";
			array_push( $val, $Runs->getContestId() );
		}
		if (!is_null( $Runs->getGuid())) {
			$sql .= " `guid` = ? AND";
			array_push( $val, $Runs->getGuid() );
		}
		if (!is_null( $Runs->getLanguage())) {
			$sql .= " `language` = ? AND";
			array_push( $val, $Runs->getLanguage() );
		}
		if (!is_null( $Runs->getStatus())) {
			$sql .= " `status` = ? AND";
			array_push( $val, $Runs->getStatus() );
		}
		if (!is_null( $Runs->getVerdict())) {
			$sql .= " `verdict` = ? AND";
			array_push( $val, $Runs->getVerdict() );
		}
		if (!is_null( $Runs->getRuntime())) {
			$sql .= " `runtime` = ? AND";
			array_push( $val, $Runs->getRuntime() );
		}
		if (!is_null( $Runs->getPenalty())) {
			$sql .= " `penalty` = ? AND";
			array_push( $val, $Runs->getPenalty() );
		}
		if (!is_null( $Runs->getMemory())) {
			$sql .= " `memory` = ? AND";
			array_push( $val, $Runs->getMemory() );
		}
		if (!is_null( $Runs->getScore())) {
			$sql .= " `score` = ? AND";
			array_push( $val, $Runs->getScore() );
		}
		if (!is_null( $Runs->getContestScore())) {
			$sql .= " `contest_score` = ? AND";
			array_push( $val, $Runs->getContestScore() );
		}
		if (!is_null( $Runs->getTime())) {
			$sql .= " `time` = ? AND";
			array_push( $val, $Runs->getTime() );
		}
		if (!is_null( $Runs->getSubmitDelay())) {
			$sql .= " `submit_delay` = ? AND";
			array_push( $val, $Runs->getSubmitDelay() );
		}
		if (!is_null( $Runs->getTest())) {
			$sql .= " `test` = ? AND";
			array_push( $val, $Runs->getTest() );
		}
		if (!is_null( $Runs->getJudgedBy())) {
			$sql .= " `judged_by` = ? AND";
			array_push( $val, $Runs->getJudgedBy() );
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
			$bar =  new Runs($foo);
			array_push( $ar,$bar);
		}
		return $ar;
	}

	/**
	  *	Actualizar registros.
	  *
	  * @return Filas afectadas
	  * @param Runs [$Runs] El objeto de tipo Runs a actualizar.
	  **/
	private static final function update($Runs)
	{
		$sql = "UPDATE Runs SET  `user_id` = ?, `problem_id` = ?, `contest_id` = ?, `guid` = ?, `language` = ?, `status` = ?, `verdict` = ?, `runtime` = ?, `penalty` = ?, `memory` = ?, `score` = ?, `contest_score` = ?, `time` = ?, `submit_delay` = ?, `test` = ?, `judged_by` = ? WHERE  `run_id` = ?;";
		$params = array(
			$Runs->getUserId(),
			$Runs->getProblemId(),
			$Runs->getContestId(),
			$Runs->getGuid(),
			$Runs->getLanguage(),
			$Runs->getStatus(),
			$Runs->getVerdict(),
			$Runs->getRuntime(),
			$Runs->getPenalty(),
			$Runs->getMemory(),
			$Runs->getScore(),
			$Runs->getContestScore(),
			$Runs->getTime(),
			$Runs->getSubmitDelay(),
			$Runs->getTest(),
			$Runs->getJudgedBy(),
			$Runs->getRunId(), );
		global $conn;
		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}

	/**
	  *	Crear registros.
	  *
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los
	  * contenidos del objeto Runs suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave
	  * primaria generada en el objeto Runs dentro de la misma transaccion.
	  *
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param Runs [$Runs] El objeto de tipo Runs a crear.
	  **/
	private static final function create( $Runs )
	{
		if (is_null($Runs->status)) $Runs->status = 'new';
		if (is_null($Runs->runtime)) $Runs->runtime = '0';
		if (is_null($Runs->penalty)) $Runs->penalty = '0';
		if (is_null($Runs->memory)) $Runs->memory = '0';
		if (is_null($Runs->score)) $Runs->score = '0';
		if (is_null($Runs->time)) $Runs->time = gmdate('Y-m-d H:i:s');
		if (is_null($Runs->submit_delay)) $Runs->submit_delay = '0';
		if (is_null($Runs->test)) $Runs->test = '0';
		$sql = "INSERT INTO Runs ( `run_id`, `user_id`, `problem_id`, `contest_id`, `guid`, `language`, `status`, `verdict`, `runtime`, `penalty`, `memory`, `score`, `contest_score`, `time`, `submit_delay`, `test`, `judged_by` ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
		$params = array(
			$Runs->run_id,
			$Runs->user_id,
			$Runs->problem_id,
			$Runs->contest_id,
			$Runs->guid,
			$Runs->language,
			$Runs->status,
			$Runs->verdict,
			$Runs->runtime,
			$Runs->penalty,
			$Runs->memory,
			$Runs->score,
			$Runs->contest_score,
			$Runs->time,
			$Runs->submit_delay,
			$Runs->test,
			$Runs->judged_by,
		 );
		global $conn;
		$conn->Execute($sql, $params);
		$ar = $conn->Affected_Rows();
		if($ar == 0) return 0;
 		$Runs->run_id = $conn->Insert_ID();

		return $ar;
	}

	/**
	  *	Buscar por rango.
	  *
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Runs} de la base de datos siempre y cuando
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Runs}.
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
	  * @param Runs [$Runs] El objeto de tipo Runs
	  * @param Runs [$Runs] El objeto de tipo Runs
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $RunsA , $RunsB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Runs WHERE (";
		$val = array();
		if( ( !is_null (($a = $RunsA->getRunId()) ) ) & ( ! is_null ( ($b = $RunsB->getRunId()) ) ) ){
				$sql .= " `run_id` >= ? AND `run_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `run_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $RunsA->getUserId()) ) ) & ( ! is_null ( ($b = $RunsB->getUserId()) ) ) ){
				$sql .= " `user_id` >= ? AND `user_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `user_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $RunsA->getProblemId()) ) ) & ( ! is_null ( ($b = $RunsB->getProblemId()) ) ) ){
				$sql .= " `problem_id` >= ? AND `problem_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `problem_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $RunsA->getContestId()) ) ) & ( ! is_null ( ($b = $RunsB->getContestId()) ) ) ){
				$sql .= " `contest_id` >= ? AND `contest_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `contest_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $RunsA->getGuid()) ) ) & ( ! is_null ( ($b = $RunsB->getGuid()) ) ) ){
				$sql .= " `guid` >= ? AND `guid` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `guid` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $RunsA->getLanguage()) ) ) & ( ! is_null ( ($b = $RunsB->getLanguage()) ) ) ){
				$sql .= " `language` >= ? AND `language` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `language` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $RunsA->getStatus()) ) ) & ( ! is_null ( ($b = $RunsB->getStatus()) ) ) ){
				$sql .= " `status` >= ? AND `status` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `status` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $RunsA->getVerdict()) ) ) & ( ! is_null ( ($b = $RunsB->getVerdict()) ) ) ){
				$sql .= " `verdict` >= ? AND `verdict` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `verdict` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $RunsA->getRuntime()) ) ) & ( ! is_null ( ($b = $RunsB->getRuntime()) ) ) ){
				$sql .= " `runtime` >= ? AND `runtime` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `runtime` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $RunsA->getPenalty()) ) ) & ( ! is_null ( ($b = $RunsB->getPenalty()) ) ) ){
				$sql .= " `penalty` >= ? AND `penalty` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `penalty` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $RunsA->getMemory()) ) ) & ( ! is_null ( ($b = $RunsB->getMemory()) ) ) ){
				$sql .= " `memory` >= ? AND `memory` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `memory` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $RunsA->getScore()) ) ) & ( ! is_null ( ($b = $RunsB->getScore()) ) ) ){
				$sql .= " `score` >= ? AND `score` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `score` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $RunsA->getContestScore()) ) ) & ( ! is_null ( ($b = $RunsB->getContestScore()) ) ) ){
				$sql .= " `contest_score` >= ? AND `contest_score` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `contest_score` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $RunsA->getTime()) ) ) & ( ! is_null ( ($b = $RunsB->getTime()) ) ) ){
				$sql .= " `time` >= ? AND `time` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `time` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $RunsA->getSubmitDelay()) ) ) & ( ! is_null ( ($b = $RunsB->getSubmitDelay()) ) ) ){
				$sql .= " `submit_delay` >= ? AND `submit_delay` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `submit_delay` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $RunsA->getTest()) ) ) & ( ! is_null ( ($b = $RunsB->getTest()) ) ) ){
				$sql .= " `test` >= ? AND `test` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `test` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $RunsA->getJudgedBy()) ) ) & ( ! is_null ( ($b = $RunsB->getJudgedBy()) ) ) ){
				$sql .= " `judged_by` >= ? AND `judged_by` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `judged_by` = ? AND";
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
			array_push( $ar, $bar = new Runs($row));
		}
		return $ar;
	}

	/**
	  *	Eliminar registros.
	  *
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto Runs suministrado. Una vez que se ha suprimido un objeto, este no
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param Runs [$Runs] El objeto de tipo Runs a eliminar
	  **/
	public static final function delete( $Runs )
	{
		if( is_null( self::getByPK($Runs->getRunId()) ) ) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Runs WHERE  run_id = ?;";
		$params = array( $Runs->getRunId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}
}

