<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Contests Data Access Object (DAO) Base.
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Contests }.
  * @access public
  * @abstract
  *
  */
abstract class ContestsDAOBase extends DAO
{
	/**
	  *	Guardar registros.
	  *
	  *	Este metodo guarda el estado actual del objeto {@link Contests} pasado en la base de datos. La llave
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param Contests [$Contests] El objeto de tipo Contests
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( $Contests )
	{
		if (!is_null(self::getByPK( $Contests->contest_id)))
		{
			return ContestsDAOBase::update( $Contests);
		} else {
			return ContestsDAOBase::create( $Contests);
		}
	}

	/**
	  *	Obtener {@link Contests} por llave primaria.
	  *
	  * Este metodo cargara un objeto {@link Contests} de la base de datos
	  * usando sus llaves primarias.
	  *
	  *	@static
	  * @return @link Contests Un objeto del tipo {@link Contests}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $contest_id )
	{
		if(  is_null( $contest_id )  ){ return NULL; }
		$sql = "SELECT * FROM Contests WHERE (contest_id = ? ) LIMIT 1;";
		$params = array(  $contest_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0) return NULL;
		$foo = new Contests( $rs );
		return $foo;
	}

	/**
	  *	Obtener todas las filas.
	  *
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link Contests}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link Contests}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Contests";
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
			$bar = new Contests($foo);
    		array_push( $allData, $bar);
		}
		return $allData;
	}

	/**
	  *	Buscar registros.
	  *
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Contests} de la base de datos.
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
	  * @param Contests [$Contests] El objeto de tipo Contests
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Contests , $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = NULL, $likeColumns = NULL)
	{
		if (!($Contests instanceof Contests)) {
			return self::search(new Contests($Contests));
		}

		$sql = "SELECT * from Contests WHERE (";
		$val = array();
		if (!is_null( $Contests->contest_id)) {
			$sql .= " `contest_id` = ? AND";
			array_push( $val, $Contests->contest_id );
		}
		if (!is_null( $Contests->acl_id)) {
			$sql .= " `acl_id` = ? AND";
			array_push( $val, $Contests->acl_id );
		}
		if (!is_null( $Contests->problemset_id)) {
			$sql .= " `problemset_id` = ? AND";
			array_push( $val, $Contests->problemset_id );
		}
		if (!is_null( $Contests->title)) {
			$sql .= " `title` = ? AND";
			array_push( $val, $Contests->title );
		}
		if (!is_null( $Contests->description)) {
			$sql .= " `description` = ? AND";
			array_push( $val, $Contests->description );
		}
		if (!is_null( $Contests->start_time)) {
			$sql .= " `start_time` = ? AND";
			array_push( $val, $Contests->start_time );
		}
		if (!is_null( $Contests->finish_time)) {
			$sql .= " `finish_time` = ? AND";
			array_push( $val, $Contests->finish_time );
		}
		if (!is_null( $Contests->window_length)) {
			$sql .= " `window_length` = ? AND";
			array_push( $val, $Contests->window_length );
		}
		if (!is_null( $Contests->rerun_id)) {
			$sql .= " `rerun_id` = ? AND";
			array_push( $val, $Contests->rerun_id );
		}
		if (!is_null( $Contests->public)) {
			$sql .= " `public` = ? AND";
			array_push( $val, $Contests->public );
		}
		if (!is_null( $Contests->alias)) {
			$sql .= " `alias` = ? AND";
			array_push( $val, $Contests->alias );
		}
		if (!is_null( $Contests->scoreboard)) {
			$sql .= " `scoreboard` = ? AND";
			array_push( $val, $Contests->scoreboard );
		}
		if (!is_null( $Contests->points_decay_factor)) {
			$sql .= " `points_decay_factor` = ? AND";
			array_push( $val, $Contests->points_decay_factor );
		}
		if (!is_null( $Contests->partial_score)) {
			$sql .= " `partial_score` = ? AND";
			array_push( $val, $Contests->partial_score );
		}
		if (!is_null( $Contests->submissions_gap)) {
			$sql .= " `submissions_gap` = ? AND";
			array_push( $val, $Contests->submissions_gap );
		}
		if (!is_null( $Contests->feedback)) {
			$sql .= " `feedback` = ? AND";
			array_push( $val, $Contests->feedback );
		}
		if (!is_null( $Contests->penalty)) {
			$sql .= " `penalty` = ? AND";
			array_push( $val, $Contests->penalty );
		}
		if (!is_null( $Contests->penalty_type)) {
			$sql .= " `penalty_type` = ? AND";
			array_push( $val, $Contests->penalty_type );
		}
		if (!is_null( $Contests->penalty_calc_policy)) {
			$sql .= " `penalty_calc_policy` = ? AND";
			array_push( $val, $Contests->penalty_calc_policy );
		}
		if (!is_null( $Contests->show_scoreboard_after)) {
			$sql .= " `show_scoreboard_after` = ? AND";
			array_push( $val, $Contests->show_scoreboard_after );
		}
		if (!is_null( $Contests->scoreboard_url)) {
			$sql .= " `scoreboard_url` = ? AND";
			array_push( $val, $Contests->scoreboard_url );
		}
		if (!is_null( $Contests->scoreboard_url_admin)) {
			$sql .= " `scoreboard_url_admin` = ? AND";
			array_push( $val, $Contests->scoreboard_url_admin );
		}
		if (!is_null( $Contests->urgent)) {
			$sql .= " `urgent` = ? AND";
			array_push( $val, $Contests->urgent );
		}
		if (!is_null( $Contests->contestant_must_register)) {
			$sql .= " `contestant_must_register` = ? AND";
			array_push( $val, $Contests->contestant_must_register );
		}
		if (!is_null( $Contests->languages)) {
			$sql .= " `languages` = ? AND";
			array_push( $val, $Contests->languages );
		}
		if (!is_null( $Contests->recommended)) {
			$sql .= " `recommended` = ? AND";
			array_push( $val, $Contests->recommended );
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
			$bar =  new Contests($foo);
			array_push( $ar,$bar);
		}
		return $ar;
	}

	/**
	  *	Actualizar registros.
	  *
	  * @return Filas afectadas
	  * @param Contests [$Contests] El objeto de tipo Contests a actualizar.
	  **/
	private static final function update($Contests)
	{
		$sql = "UPDATE Contests SET  `acl_id` = ?, `problemset_id` = ?, `title` = ?, `description` = ?, `start_time` = ?, `finish_time` = ?, `window_length` = ?, `rerun_id` = ?, `public` = ?, `alias` = ?, `scoreboard` = ?, `points_decay_factor` = ?, `partial_score` = ?, `submissions_gap` = ?, `feedback` = ?, `penalty` = ?, `penalty_type` = ?, `penalty_calc_policy` = ?, `show_scoreboard_after` = ?, `scoreboard_url` = ?, `scoreboard_url_admin` = ?, `urgent` = ?, `contestant_must_register` = ?, `languages` = ?, `recommended` = ? WHERE  `contest_id` = ?;";
		$params = array(
			$Contests->acl_id,
			$Contests->problemset_id,
			$Contests->title,
			$Contests->description,
			$Contests->start_time,
			$Contests->finish_time,
			$Contests->window_length,
			$Contests->rerun_id,
			$Contests->public,
			$Contests->alias,
			$Contests->scoreboard,
			$Contests->points_decay_factor,
			$Contests->partial_score,
			$Contests->submissions_gap,
			$Contests->feedback,
			$Contests->penalty,
			$Contests->penalty_type,
			$Contests->penalty_calc_policy,
			$Contests->show_scoreboard_after,
			$Contests->scoreboard_url,
			$Contests->scoreboard_url_admin,
			$Contests->urgent,
			$Contests->contestant_must_register,
			$Contests->languages,
			$Contests->recommended,
			$Contests->contest_id, );
		global $conn;
		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}

	/**
	  *	Crear registros.
	  *
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los
	  * contenidos del objeto Contests suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave
	  * primaria generada en el objeto Contests dentro de la misma transaccion.
	  *
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param Contests [$Contests] El objeto de tipo Contests a crear.
	  **/
	private static final function create( $Contests )
	{
		if (is_null($Contests->start_time)) $Contests->start_time = '2000-01-01 06:00:00';
		if (is_null($Contests->finish_time)) $Contests->finish_time = '2000-01-01 06:00:00';
		if (is_null($Contests->public)) $Contests->public = '1';
		if (is_null($Contests->scoreboard)) $Contests->scoreboard = '1';
		if (is_null($Contests->points_decay_factor)) $Contests->points_decay_factor = '0';
		if (is_null($Contests->partial_score)) $Contests->partial_score = '1';
		if (is_null($Contests->submissions_gap)) $Contests->submissions_gap = '1';
		if (is_null($Contests->penalty)) $Contests->penalty = '1';
		if (is_null($Contests->show_scoreboard_after)) $Contests->show_scoreboard_after =  '1';
		if (is_null($Contests->urgent)) $Contests->urgent = 0;
		if (is_null($Contests->contestant_must_register)) $Contests->contestant_must_register = '0';
		if (is_null($Contests->recommended)) $Contests->recommended =  '0';
		$sql = "INSERT INTO Contests ( `contest_id`, `acl_id`, `problemset_id`, `title`, `description`, `start_time`, `finish_time`, `window_length`, `rerun_id`, `public`, `alias`, `scoreboard`, `points_decay_factor`, `partial_score`, `submissions_gap`, `feedback`, `penalty`, `penalty_type`, `penalty_calc_policy`, `show_scoreboard_after`, `scoreboard_url`, `scoreboard_url_admin`, `urgent`, `contestant_must_register`, `languages`, `recommended` ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
		$params = array(
			$Contests->contest_id,
			$Contests->acl_id,
			$Contests->problemset_id,
			$Contests->title,
			$Contests->description,
			$Contests->start_time,
			$Contests->finish_time,
			$Contests->window_length,
			$Contests->rerun_id,
			$Contests->public,
			$Contests->alias,
			$Contests->scoreboard,
			$Contests->points_decay_factor,
			$Contests->partial_score,
			$Contests->submissions_gap,
			$Contests->feedback,
			$Contests->penalty,
			$Contests->penalty_type,
			$Contests->penalty_calc_policy,
			$Contests->show_scoreboard_after,
			$Contests->scoreboard_url,
			$Contests->scoreboard_url_admin,
			$Contests->urgent,
			$Contests->contestant_must_register,
			$Contests->languages,
			$Contests->recommended,
		 );
		global $conn;
		$conn->Execute($sql, $params);
		$ar = $conn->Affected_Rows();
		if($ar == 0) return 0;
		$Contests->contest_id = $conn->Insert_ID();

		return $ar;
	}

	/**
	  *	Buscar por rango.
	  *
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Contests} de la base de datos siempre y cuando
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Contests}.
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
	  * @param Contests [$Contests] El objeto de tipo Contests
	  * @param Contests [$Contests] El objeto de tipo Contests
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $ContestsA , $ContestsB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Contests WHERE (";
		$val = array();
		if( ( !is_null (($a = $ContestsA->contest_id) ) ) & ( ! is_null ( ($b = $ContestsB->contest_id) ) ) ){
				$sql .= " `contest_id` >= ? AND `contest_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `contest_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->acl_id) ) ) & ( ! is_null ( ($b = $ContestsB->acl_id) ) ) ){
				$sql .= " `acl_id` >= ? AND `acl_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `acl_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->problemset_id) ) ) & ( ! is_null ( ($b = $ContestsB->problemset_id) ) ) ){
				$sql .= " `problemset_id` >= ? AND `problemset_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `problemset_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->title) ) ) & ( ! is_null ( ($b = $ContestsB->title) ) ) ){
				$sql .= " `title` >= ? AND `title` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `title` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->description) ) ) & ( ! is_null ( ($b = $ContestsB->description) ) ) ){
				$sql .= " `description` >= ? AND `description` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `description` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->start_time) ) ) & ( ! is_null ( ($b = $ContestsB->start_time) ) ) ){
				$sql .= " `start_time` >= ? AND `start_time` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `start_time` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->finish_time) ) ) & ( ! is_null ( ($b = $ContestsB->finish_time) ) ) ){
				$sql .= " `finish_time` >= ? AND `finish_time` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `finish_time` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->window_length) ) ) & ( ! is_null ( ($b = $ContestsB->window_length) ) ) ){
				$sql .= " `window_length` >= ? AND `window_length` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `window_length` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->rerun_id) ) ) & ( ! is_null ( ($b = $ContestsB->rerun_id) ) ) ){
				$sql .= " `rerun_id` >= ? AND `rerun_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `rerun_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->public) ) ) & ( ! is_null ( ($b = $ContestsB->public) ) ) ){
				$sql .= " `public` >= ? AND `public` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `public` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->alias) ) ) & ( ! is_null ( ($b = $ContestsB->alias) ) ) ){
				$sql .= " `alias` >= ? AND `alias` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `alias` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->scoreboard) ) ) & ( ! is_null ( ($b = $ContestsB->scoreboard) ) ) ){
				$sql .= " `scoreboard` >= ? AND `scoreboard` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `scoreboard` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->points_decay_factor) ) ) & ( ! is_null ( ($b = $ContestsB->points_decay_factor) ) ) ){
				$sql .= " `points_decay_factor` >= ? AND `points_decay_factor` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `points_decay_factor` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->partial_score) ) ) & ( ! is_null ( ($b = $ContestsB->partial_score) ) ) ){
				$sql .= " `partial_score` >= ? AND `partial_score` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `partial_score` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->submissions_gap) ) ) & ( ! is_null ( ($b = $ContestsB->submissions_gap) ) ) ){
				$sql .= " `submissions_gap` >= ? AND `submissions_gap` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `submissions_gap` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->feedback) ) ) & ( ! is_null ( ($b = $ContestsB->feedback) ) ) ){
				$sql .= " `feedback` >= ? AND `feedback` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `feedback` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->penalty) ) ) & ( ! is_null ( ($b = $ContestsB->penalty) ) ) ){
				$sql .= " `penalty` >= ? AND `penalty` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `penalty` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->penalty_type) ) ) & ( ! is_null ( ($b = $ContestsB->penalty_type) ) ) ){
				$sql .= " `penalty_type` >= ? AND `penalty_type` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `penalty_type` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->penalty_calc_policy) ) ) & ( ! is_null ( ($b = $ContestsB->penalty_calc_policy) ) ) ){
				$sql .= " `penalty_calc_policy` >= ? AND `penalty_calc_policy` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `penalty_calc_policy` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->show_scoreboard_after) ) ) & ( ! is_null ( ($b = $ContestsB->show_scoreboard_after) ) ) ){
				$sql .= " `show_scoreboard_after` >= ? AND `show_scoreboard_after` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `show_scoreboard_after` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->scoreboard_url) ) ) & ( ! is_null ( ($b = $ContestsB->scoreboard_url) ) ) ){
				$sql .= " `scoreboard_url` >= ? AND `scoreboard_url` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `scoreboard_url` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->scoreboard_url_admin) ) ) & ( ! is_null ( ($b = $ContestsB->scoreboard_url_admin) ) ) ){
				$sql .= " `scoreboard_url_admin` >= ? AND `scoreboard_url_admin` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `scoreboard_url_admin` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->urgent) ) ) & ( ! is_null ( ($b = $ContestsB->urgent) ) ) ){
				$sql .= " `urgent` >= ? AND `urgent` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `urgent` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->contestant_must_register) ) ) & ( ! is_null ( ($b = $ContestsB->contestant_must_register) ) ) ){
				$sql .= " `contestant_must_register` >= ? AND `contestant_must_register` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `contestant_must_register` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->languages) ) ) & ( ! is_null ( ($b = $ContestsB->languages) ) ) ){
				$sql .= " `languages` >= ? AND `languages` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `languages` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ContestsA->recommended) ) ) & ( ! is_null ( ($b = $ContestsB->recommended) ) ) ){
				$sql .= " `recommended` >= ? AND `recommended` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `recommended` = ? AND";
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
			array_push( $ar, $bar = new Contests($row));
		}
		return $ar;
	}

	/**
	  *	Eliminar registros.
	  *
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto Contests suministrado. Una vez que se ha suprimido un objeto, este no
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param Contests [$Contests] El objeto de tipo Contests a eliminar
	  **/
	public static final function delete( $Contests )
	{
		if( is_null( self::getByPK($Contests->contest_id) ) ) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Contests WHERE  contest_id = ?;";
		$params = array( $Contests->contest_id );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}
}
