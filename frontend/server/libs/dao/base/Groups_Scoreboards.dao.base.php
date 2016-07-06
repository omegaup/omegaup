<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** GroupsScoreboards Data Access Object (DAO) Base.
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link GroupsScoreboards }.
  * @access public
  * @abstract
  *
  */
abstract class GroupsScoreboardsDAOBase extends DAO
{
	/**
	  *	Guardar registros.
	  *
	  *	Este metodo guarda el estado actual del objeto {@link GroupsScoreboards} pasado en la base de datos. La llave
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param GroupsScoreboards [$Groups_Scoreboards] El objeto de tipo GroupsScoreboards
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( $Groups_Scoreboards )
	{
		if (!is_null(self::getByPK( $Groups_Scoreboards->getGroupScoreboardId() )))
		{
			return GroupsScoreboardsDAOBase::update( $Groups_Scoreboards);
		} else {
			return GroupsScoreboardsDAOBase::create( $Groups_Scoreboards);
		}
	}

	/**
	  *	Obtener {@link GroupsScoreboards} por llave primaria.
	  *
	  * Este metodo cargara un objeto {@link GroupsScoreboards} de la base de datos
	  * usando sus llaves primarias.
	  *
	  *	@static
	  * @return @link GroupsScoreboards Un objeto del tipo {@link GroupsScoreboards}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $group_scoreboard_id )
	{
		if(  is_null( $group_scoreboard_id )  ){ return NULL; }
		$sql = "SELECT * FROM Groups_Scoreboards WHERE (group_scoreboard_id = ? ) LIMIT 1;";
		$params = array(  $group_scoreboard_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0) return NULL;
		$foo = new GroupsScoreboards( $rs );
		return $foo;
	}

	/**
	  *	Obtener todas las filas.
	  *
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link GroupsScoreboards}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link GroupsScoreboards}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Groups_Scoreboards";
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
			$bar = new GroupsScoreboards($foo);
    		array_push( $allData, $bar);
		}
		return $allData;
	}

	/**
	  *	Buscar registros.
	  *
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link GroupsScoreboards} de la base de datos.
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
	  * @param GroupsScoreboards [$Groups_Scoreboards] El objeto de tipo GroupsScoreboards
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Groups_Scoreboards , $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = NULL, $likeColumns = NULL)
	{
		if (!($Groups_Scoreboards instanceof GroupsScoreboards)) {
			return self::search(new GroupsScoreboards($Groups_Scoreboards));
		}

		$sql = "SELECT * from Groups_Scoreboards WHERE (";
		$val = array();
		if (!is_null( $Groups_Scoreboards->getGroupScoreboardId())) {
			$sql .= " `group_scoreboard_id` = ? AND";
			array_push( $val, $Groups_Scoreboards->getGroupScoreboardId() );
		}
		if (!is_null( $Groups_Scoreboards->getGroupId())) {
			$sql .= " `group_id` = ? AND";
			array_push( $val, $Groups_Scoreboards->getGroupId() );
		}
		if (!is_null( $Groups_Scoreboards->getCreateTime())) {
			$sql .= " `create_time` = ? AND";
			array_push( $val, $Groups_Scoreboards->getCreateTime() );
		}
		if (!is_null( $Groups_Scoreboards->getAlias())) {
			$sql .= " `alias` = ? AND";
			array_push( $val, $Groups_Scoreboards->getAlias() );
		}
		if (!is_null( $Groups_Scoreboards->getName())) {
			$sql .= " `name` = ? AND";
			array_push( $val, $Groups_Scoreboards->getName() );
		}
		if (!is_null( $Groups_Scoreboards->getDescription())) {
			$sql .= " `description` = ? AND";
			array_push( $val, $Groups_Scoreboards->getDescription() );
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
			$bar =  new GroupsScoreboards($foo);
			array_push( $ar,$bar);
		}
		return $ar;
	}

	/**
	  *	Actualizar registros.
	  *
	  * @return Filas afectadas
	  * @param GroupsScoreboards [$Groups_Scoreboards] El objeto de tipo GroupsScoreboards a actualizar.
	  **/
	private static final function update($Groups_Scoreboards)
	{
		$sql = "UPDATE Groups_Scoreboards SET  `group_id` = ?, `create_time` = ?, `alias` = ?, `name` = ?, `description` = ? WHERE  `group_scoreboard_id` = ?;";
		$params = array(
			$Groups_Scoreboards->getGroupId(),
			$Groups_Scoreboards->getCreateTime(),
			$Groups_Scoreboards->getAlias(),
			$Groups_Scoreboards->getName(),
			$Groups_Scoreboards->getDescription(),
			$Groups_Scoreboards->getGroupScoreboardId(), );
		global $conn;
		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}

	/**
	  *	Crear registros.
	  *
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los
	  * contenidos del objeto GroupsScoreboards suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave
	  * primaria generada en el objeto GroupsScoreboards dentro de la misma transaccion.
	  *
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param GroupsScoreboards [$Groups_Scoreboards] El objeto de tipo GroupsScoreboards a crear.
	  **/
	private static final function create( $Groups_Scoreboards )
	{
		if (is_null($Groups_Scoreboards->create_time)) $Groups_Scoreboards->create_time = gmdate('Y-m-d H:i:s');
		$sql = "INSERT INTO Groups_Scoreboards ( `group_scoreboard_id`, `group_id`, `create_time`, `alias`, `name`, `description` ) VALUES ( ?, ?, ?, ?, ?, ?);";
		$params = array(
			$Groups_Scoreboards->group_scoreboard_id,
			$Groups_Scoreboards->group_id,
			$Groups_Scoreboards->create_time,
			$Groups_Scoreboards->alias,
			$Groups_Scoreboards->name,
			$Groups_Scoreboards->description,
		 );
		global $conn;
		$conn->Execute($sql, $params);
		$ar = $conn->Affected_Rows();
		if($ar == 0) return 0;
 		$Groups_Scoreboards->group_scoreboard_id = $conn->Insert_ID();

		return $ar;
	}

	/**
	  *	Buscar por rango.
	  *
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link GroupsScoreboards} de la base de datos siempre y cuando
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link GroupsScoreboards}.
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
	  * @param GroupsScoreboards [$Groups_Scoreboards] El objeto de tipo GroupsScoreboards
	  * @param GroupsScoreboards [$Groups_Scoreboards] El objeto de tipo GroupsScoreboards
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $Groups_ScoreboardsA , $Groups_ScoreboardsB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Groups_Scoreboards WHERE (";
		$val = array();
		if( ( !is_null (($a = $Groups_ScoreboardsA->getGroupScoreboardId()) ) ) & ( ! is_null ( ($b = $Groups_ScoreboardsB->getGroupScoreboardId()) ) ) ){
				$sql .= " `group_scoreboard_id` >= ? AND `group_scoreboard_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `group_scoreboard_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $Groups_ScoreboardsA->getGroupId()) ) ) & ( ! is_null ( ($b = $Groups_ScoreboardsB->getGroupId()) ) ) ){
				$sql .= " `group_id` >= ? AND `group_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `group_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $Groups_ScoreboardsA->getCreateTime()) ) ) & ( ! is_null ( ($b = $Groups_ScoreboardsB->getCreateTime()) ) ) ){
				$sql .= " `create_time` >= ? AND `create_time` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `create_time` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $Groups_ScoreboardsA->getAlias()) ) ) & ( ! is_null ( ($b = $Groups_ScoreboardsB->getAlias()) ) ) ){
				$sql .= " `alias` >= ? AND `alias` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `alias` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $Groups_ScoreboardsA->getName()) ) ) & ( ! is_null ( ($b = $Groups_ScoreboardsB->getName()) ) ) ){
				$sql .= " `name` >= ? AND `name` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `name` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $Groups_ScoreboardsA->getDescription()) ) ) & ( ! is_null ( ($b = $Groups_ScoreboardsB->getDescription()) ) ) ){
				$sql .= " `description` >= ? AND `description` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `description` = ? AND";
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
			array_push( $ar, $bar = new GroupsScoreboards($row));
		}
		return $ar;
	}

	/**
	  *	Eliminar registros.
	  *
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto GroupsScoreboards suministrado. Una vez que se ha suprimido un objeto, este no
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param GroupsScoreboards [$Groups_Scoreboards] El objeto de tipo GroupsScoreboards a eliminar
	  **/
	public static final function delete( $Groups_Scoreboards )
	{
		if( is_null( self::getByPK($Groups_Scoreboards->getGroupScoreboardId()) ) ) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Groups_Scoreboards WHERE  group_scoreboard_id = ?;";
		$params = array( $Groups_Scoreboards->getGroupScoreboardId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}
}
