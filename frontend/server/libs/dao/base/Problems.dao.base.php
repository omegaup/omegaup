<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Problems Data Access Object (DAO) Base.
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Problems }.
  * @access public
  * @abstract
  *
  */
abstract class ProblemsDAOBase extends DAO
{
	/**
	  *	Guardar registros.
	  *
	  *	Este metodo guarda el estado actual del objeto {@link Problems} pasado en la base de datos. La llave
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param Problems [$Problems] El objeto de tipo Problems
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( $Problems )
	{
		if (!is_null(self::getByPK( $Problems->problem_id)))
		{
			return ProblemsDAOBase::update( $Problems);
		} else {
			return ProblemsDAOBase::create( $Problems);
		}
	}

	/**
	  *	Obtener {@link Problems} por llave primaria.
	  *
	  * Este metodo cargara un objeto {@link Problems} de la base de datos
	  * usando sus llaves primarias.
	  *
	  *	@static
	  * @return @link Problems Un objeto del tipo {@link Problems}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $problem_id )
	{
		if(  is_null( $problem_id )  ){ return NULL; }
		$sql = "SELECT * FROM Problems WHERE (problem_id = ? ) LIMIT 1;";
		$params = array(  $problem_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0) return NULL;
		$foo = new Problems( $rs );
		return $foo;
	}

	/**
	  *	Obtener todas las filas.
	  *
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link Problems}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link Problems}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Problems";
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
			$bar = new Problems($foo);
    		array_push( $allData, $bar);
		}
		return $allData;
	}

	/**
	  *	Buscar registros.
	  *
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Problems} de la base de datos.
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
	  * @param Problems [$Problems] El objeto de tipo Problems
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Problems , $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = NULL, $likeColumns = NULL)
	{
		if (!($Problems instanceof Problems)) {
			return self::search(new Problems($Problems));
		}

		$sql = "SELECT * from Problems WHERE (";
		$val = array();
		if (!is_null( $Problems->problem_id)) {
			$sql .= " `problem_id` = ? AND";
			array_push( $val, $Problems->problem_id );
		}
		if (!is_null( $Problems->public)) {
			$sql .= " `public` = ? AND";
			array_push( $val, $Problems->public );
		}
		if (!is_null( $Problems->author_id)) {
			$sql .= " `author_id` = ? AND";
			array_push( $val, $Problems->author_id );
		}
		if (!is_null( $Problems->title)) {
			$sql .= " `title` = ? AND";
			array_push( $val, $Problems->title );
		}
		if (!is_null( $Problems->alias)) {
			$sql .= " `alias` = ? AND";
			array_push( $val, $Problems->alias );
		}
		if (!is_null( $Problems->validator)) {
			$sql .= " `validator` = ? AND";
			array_push( $val, $Problems->validator );
		}
		if (!is_null( $Problems->languages)) {
			$sql .= " `languages` = ? AND";
			array_push( $val, $Problems->languages );
		}
		if (!is_null( $Problems->server)) {
			$sql .= " `server` = ? AND";
			array_push( $val, $Problems->server );
		}
		if (!is_null( $Problems->remote_id)) {
			$sql .= " `remote_id` = ? AND";
			array_push( $val, $Problems->remote_id );
		}
		if (!is_null( $Problems->time_limit)) {
			$sql .= " `time_limit` = ? AND";
			array_push( $val, $Problems->time_limit );
		}
		if (!is_null( $Problems->validator_time_limit)) {
			$sql .= " `validator_time_limit` = ? AND";
			array_push( $val, $Problems->validator_time_limit );
		}
		if (!is_null( $Problems->overall_wall_time_limit)) {
			$sql .= " `overall_wall_time_limit` = ? AND";
			array_push( $val, $Problems->overall_wall_time_limit );
		}
		if (!is_null( $Problems->extra_wall_time)) {
			$sql .= " `extra_wall_time` = ? AND";
			array_push( $val, $Problems->extra_wall_time );
		}
		if (!is_null( $Problems->memory_limit)) {
			$sql .= " `memory_limit` = ? AND";
			array_push( $val, $Problems->memory_limit );
		}
		if (!is_null( $Problems->output_limit)) {
			$sql .= " `output_limit` = ? AND";
			array_push( $val, $Problems->output_limit );
		}
		if (!is_null( $Problems->stack_limit)) {
			$sql .= " `stack_limit` = ? AND";
			array_push( $val, $Problems->stack_limit );
		}
		if (!is_null( $Problems->visits)) {
			$sql .= " `visits` = ? AND";
			array_push( $val, $Problems->visits );
		}
		if (!is_null( $Problems->submissions)) {
			$sql .= " `submissions` = ? AND";
			array_push( $val, $Problems->submissions );
		}
		if (!is_null( $Problems->accepted)) {
			$sql .= " `accepted` = ? AND";
			array_push( $val, $Problems->accepted );
		}
		if (!is_null( $Problems->difficulty)) {
			$sql .= " `difficulty` = ? AND";
			array_push( $val, $Problems->difficulty );
		}
		if (!is_null( $Problems->creation_date)) {
			$sql .= " `creation_date` = ? AND";
			array_push( $val, $Problems->creation_date );
		}
		if (!is_null( $Problems->source)) {
			$sql .= " `source` = ? AND";
			array_push( $val, $Problems->source );
		}
		if (!is_null( $Problems->order)) {
			$sql .= " `order` = ? AND";
			array_push( $val, $Problems->order );
		}
		if (!is_null( $Problems->tolerance)) {
			$sql .= " `tolerance` = ? AND";
			array_push( $val, $Problems->tolerance );
		}
		if (!is_null( $Problems->slow)) {
			$sql .= " `slow` = ? AND";
			array_push( $val, $Problems->slow );
		}
		if (!is_null( $Problems->deprecated)) {
			$sql .= " `deprecated` = ? AND";
			array_push( $val, $Problems->deprecated );
		}
		if (!is_null( $Problems->email_clarifications)) {
			$sql .= " `email_clarifications` = ? AND";
			array_push( $val, $Problems->email_clarifications );
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
			$bar =  new Problems($foo);
			array_push( $ar,$bar);
		}
		return $ar;
	}

	/**
	  *	Actualizar registros.
	  *
	  * @return Filas afectadas
	  * @param Problems [$Problems] El objeto de tipo Problems a actualizar.
	  **/
	private static final function update($Problems)
	{
		$sql = "UPDATE Problems SET  `public` = ?, `author_id` = ?, `title` = ?, `alias` = ?, `validator` = ?, `languages` = ?, `server` = ?, `remote_id` = ?, `time_limit` = ?, `validator_time_limit` = ?, `overall_wall_time_limit` = ?, `extra_wall_time` = ?, `memory_limit` = ?, `output_limit` = ?, `stack_limit` = ?, `visits` = ?, `submissions` = ?, `accepted` = ?, `difficulty` = ?, `creation_date` = ?, `source` = ?, `order` = ?, `tolerance` = ?, `slow` = ?, `deprecated` = ?, `email_clarifications` = ? WHERE  `problem_id` = ?;";
		$params = array(
			$Problems->public,
			$Problems->author_id,
			$Problems->title,
			$Problems->alias,
			$Problems->validator,
			$Problems->languages,
			$Problems->server,
			$Problems->remote_id,
			$Problems->time_limit,
			$Problems->validator_time_limit,
			$Problems->overall_wall_time_limit,
			$Problems->extra_wall_time,
			$Problems->memory_limit,
			$Problems->output_limit,
			$Problems->stack_limit,
			$Problems->visits,
			$Problems->submissions,
			$Problems->accepted,
			$Problems->difficulty,
			$Problems->creation_date,
			$Problems->source,
			$Problems->order,
			$Problems->tolerance,
			$Problems->slow,
			$Problems->deprecated,
			$Problems->email_clarifications,
			$Problems->problem_id, );
		global $conn;
		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}

	/**
	  *	Crear registros.
	  *
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los
	  * contenidos del objeto Problems suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave
	  * primaria generada en el objeto Problems dentro de la misma transaccion.
	  *
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param Problems [$Problems] El objeto de tipo Problems a crear.
	  **/
	private static final function create( $Problems )
	{
		if (is_null($Problems->public)) $Problems->public = '1';
		if (is_null($Problems->validator)) $Problems->validator = 'token-numeric';
		if (is_null($Problems->languages)) $Problems->languages = 'c,cpp,java,py,rb,pl,cs,pas,hs,cpp11';
		if (is_null($Problems->time_limit)) $Problems->time_limit = '3000';
		if (is_null($Problems->validator_time_limit)) $Problems->validator_time_limit = '3000';
		if (is_null($Problems->overall_wall_time_limit)) $Problems->overall_wall_time_limit = '60000';
		if (is_null($Problems->extra_wall_time)) $Problems->extra_wall_time = '0';
		if (is_null($Problems->memory_limit)) $Problems->memory_limit = '64';
		if (is_null($Problems->output_limit)) $Problems->output_limit = '10240';
		if (is_null($Problems->stack_limit)) $Problems->stack_limit = '10485760';
		if (is_null($Problems->visits)) $Problems->visits = '0';
		if (is_null($Problems->submissions)) $Problems->submissions = '0';
		if (is_null($Problems->accepted)) $Problems->accepted = '0';
		if (is_null($Problems->difficulty)) $Problems->difficulty = '0';
		if (is_null($Problems->creation_date)) $Problems->creation_date = gmdate('Y-m-d H:i:s');
		if (is_null($Problems->order)) $Problems->order = 'normal';
		if (is_null($Problems->tolerance)) $Problems->tolerance = 1e-9;
		if (is_null($Problems->slow)) $Problems->slow = 0;
		if (is_null($Problems->deprecated)) $Problems->deprecated = 0;
		if (is_null($Problems->email_clarifications)) $Problems->email_clarifications = 0;
		$sql = "INSERT INTO Problems ( `problem_id`, `public`, `author_id`, `title`, `alias`, `validator`, `languages`, `server`, `remote_id`, `time_limit`, `validator_time_limit`, `overall_wall_time_limit`, `extra_wall_time`, `memory_limit`, `output_limit`, `stack_limit`, `visits`, `submissions`, `accepted`, `difficulty`, `creation_date`, `source`, `order`, `tolerance`, `slow`, `deprecated`, `email_clarifications` ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
		$params = array(
			$Problems->problem_id,
			$Problems->public,
			$Problems->author_id,
			$Problems->title,
			$Problems->alias,
			$Problems->validator,
			$Problems->languages,
			$Problems->server,
			$Problems->remote_id,
			$Problems->time_limit,
			$Problems->validator_time_limit,
			$Problems->overall_wall_time_limit,
			$Problems->extra_wall_time,
			$Problems->memory_limit,
			$Problems->output_limit,
			$Problems->stack_limit,
			$Problems->visits,
			$Problems->submissions,
			$Problems->accepted,
			$Problems->difficulty,
			$Problems->creation_date,
			$Problems->source,
			$Problems->order,
			$Problems->tolerance,
			$Problems->slow,
			$Problems->deprecated,
			$Problems->email_clarifications,
		 );
		global $conn;
		$conn->Execute($sql, $params);
		$ar = $conn->Affected_Rows();
		if($ar == 0) return 0;
		$Problems->problem_id = $conn->Insert_ID();

		return $ar;
	}

	/**
	  *	Buscar por rango.
	  *
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Problems} de la base de datos siempre y cuando
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Problems}.
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
	  * @param Problems [$Problems] El objeto de tipo Problems
	  * @param Problems [$Problems] El objeto de tipo Problems
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $ProblemsA , $ProblemsB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Problems WHERE (";
		$val = array();
		if( ( !is_null (($a = $ProblemsA->problem_id) ) ) & ( ! is_null ( ($b = $ProblemsB->problem_id) ) ) ){
				$sql .= " `problem_id` >= ? AND `problem_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `problem_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->public) ) ) & ( ! is_null ( ($b = $ProblemsB->public) ) ) ){
				$sql .= " `public` >= ? AND `public` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `public` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->author_id) ) ) & ( ! is_null ( ($b = $ProblemsB->author_id) ) ) ){
				$sql .= " `author_id` >= ? AND `author_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `author_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->title) ) ) & ( ! is_null ( ($b = $ProblemsB->title) ) ) ){
				$sql .= " `title` >= ? AND `title` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `title` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->alias) ) ) & ( ! is_null ( ($b = $ProblemsB->alias) ) ) ){
				$sql .= " `alias` >= ? AND `alias` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `alias` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->validator) ) ) & ( ! is_null ( ($b = $ProblemsB->validator) ) ) ){
				$sql .= " `validator` >= ? AND `validator` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `validator` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->languages) ) ) & ( ! is_null ( ($b = $ProblemsB->languages) ) ) ){
				$sql .= " `languages` >= ? AND `languages` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `languages` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->server) ) ) & ( ! is_null ( ($b = $ProblemsB->server) ) ) ){
				$sql .= " `server` >= ? AND `server` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `server` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->remote_id) ) ) & ( ! is_null ( ($b = $ProblemsB->remote_id) ) ) ){
				$sql .= " `remote_id` >= ? AND `remote_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `remote_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->time_limit) ) ) & ( ! is_null ( ($b = $ProblemsB->time_limit) ) ) ){
				$sql .= " `time_limit` >= ? AND `time_limit` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `time_limit` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->validator_time_limit) ) ) & ( ! is_null ( ($b = $ProblemsB->validator_time_limit) ) ) ){
				$sql .= " `validator_time_limit` >= ? AND `validator_time_limit` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `validator_time_limit` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->overall_wall_time_limit) ) ) & ( ! is_null ( ($b = $ProblemsB->overall_wall_time_limit) ) ) ){
				$sql .= " `overall_wall_time_limit` >= ? AND `overall_wall_time_limit` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `overall_wall_time_limit` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->extra_wall_time) ) ) & ( ! is_null ( ($b = $ProblemsB->extra_wall_time) ) ) ){
				$sql .= " `extra_wall_time` >= ? AND `extra_wall_time` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `extra_wall_time` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->memory_limit) ) ) & ( ! is_null ( ($b = $ProblemsB->memory_limit) ) ) ){
				$sql .= " `memory_limit` >= ? AND `memory_limit` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `memory_limit` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->output_limit) ) ) & ( ! is_null ( ($b = $ProblemsB->output_limit) ) ) ){
				$sql .= " `output_limit` >= ? AND `output_limit` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `output_limit` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->stack_limit) ) ) & ( ! is_null ( ($b = $ProblemsB->stack_limit) ) ) ){
				$sql .= " `stack_limit` >= ? AND `stack_limit` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `stack_limit` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->visits) ) ) & ( ! is_null ( ($b = $ProblemsB->visits) ) ) ){
				$sql .= " `visits` >= ? AND `visits` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `visits` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->submissions) ) ) & ( ! is_null ( ($b = $ProblemsB->submissions) ) ) ){
				$sql .= " `submissions` >= ? AND `submissions` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `submissions` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->accepted) ) ) & ( ! is_null ( ($b = $ProblemsB->accepted) ) ) ){
				$sql .= " `accepted` >= ? AND `accepted` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `accepted` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->difficulty) ) ) & ( ! is_null ( ($b = $ProblemsB->difficulty) ) ) ){
				$sql .= " `difficulty` >= ? AND `difficulty` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `difficulty` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->creation_date) ) ) & ( ! is_null ( ($b = $ProblemsB->creation_date) ) ) ){
				$sql .= " `creation_date` >= ? AND `creation_date` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `creation_date` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->source) ) ) & ( ! is_null ( ($b = $ProblemsB->source) ) ) ){
				$sql .= " `source` >= ? AND `source` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `source` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->order) ) ) & ( ! is_null ( ($b = $ProblemsB->order) ) ) ){
				$sql .= " `order` >= ? AND `order` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `order` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->tolerance) ) ) & ( ! is_null ( ($b = $ProblemsB->tolerance) ) ) ){
				$sql .= " `tolerance` >= ? AND `tolerance` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `tolerance` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->slow) ) ) & ( ! is_null ( ($b = $ProblemsB->slow) ) ) ){
				$sql .= " `slow` >= ? AND `slow` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `slow` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->deprecated) ) ) & ( ! is_null ( ($b = $ProblemsB->deprecated) ) ) ){
				$sql .= " `deprecated` >= ? AND `deprecated` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `deprecated` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $ProblemsA->email_clarifications) ) ) & ( ! is_null ( ($b = $ProblemsB->email_clarifications) ) ) ){
				$sql .= " `email_clarifications` >= ? AND `email_clarifications` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `email_clarifications` = ? AND";
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
			array_push( $ar, $bar = new Problems($row));
		}
		return $ar;
	}

	/**
	  *	Eliminar registros.
	  *
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto Problems suministrado. Una vez que se ha suprimido un objeto, este no
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param Problems [$Problems] El objeto de tipo Problems a eliminar
	  **/
	public static final function delete( $Problems )
	{
		if( is_null( self::getByPK($Problems->problem_id) ) ) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Problems WHERE  problem_id = ?;";
		$params = array( $Problems->problem_id );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}
}
