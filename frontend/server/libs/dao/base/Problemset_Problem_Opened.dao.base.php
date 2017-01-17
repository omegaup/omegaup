<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ProblemsetProblemOpened Data Access Object (DAO) Base.
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsetProblemOpened }.
  * @access public
  * @abstract
  *
  */
abstract class ProblemsetProblemOpenedDAOBase extends DAO
{
	/**
	  *	Guardar registros.
	  *
	  *	Este metodo guarda el estado actual del objeto {@link ProblemsetProblemOpened} pasado en la base de datos. La llave
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param ProblemsetProblemOpened [$Problemset_Problem_Opened] El objeto de tipo ProblemsetProblemOpened
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( $Problemset_Problem_Opened )
	{
		if (!is_null(self::getByPK( $Problemset_Problem_Opened->problemset_id, $Problemset_Problem_Opened->problem_id, $Problemset_Problem_Opened->user_id)))
		{
			return ProblemsetProblemOpenedDAOBase::update( $Problemset_Problem_Opened);
		} else {
			return ProblemsetProblemOpenedDAOBase::create( $Problemset_Problem_Opened);
		}
	}

	/**
	  *	Obtener {@link ProblemsetProblemOpened} por llave primaria.
	  *
	  * Este metodo cargara un objeto {@link ProblemsetProblemOpened} de la base de datos
	  * usando sus llaves primarias.
	  *
	  *	@static
	  * @return @link ProblemsetProblemOpened Un objeto del tipo {@link ProblemsetProblemOpened}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $problemset_id, $problem_id, $user_id )
	{
		if(  is_null( $problemset_id ) || is_null( $problem_id ) || is_null( $user_id )  ){ return NULL; }
		$sql = "SELECT * FROM Problemset_Problem_Opened WHERE (problemset_id = ? AND problem_id = ? AND user_id = ? ) LIMIT 1;";
		$params = array(  $problemset_id, $problem_id, $user_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0) return NULL;
		$foo = new ProblemsetProblemOpened( $rs );
		return $foo;
	}

	/**
	  *	Obtener todas las filas.
	  *
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link ProblemsetProblemOpened}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link ProblemsetProblemOpened}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Problemset_Problem_Opened";
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
			$bar = new ProblemsetProblemOpened($foo);
    		array_push( $allData, $bar);
		}
		return $allData;
	}

	/**
	  *	Buscar registros.
	  *
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ProblemsetProblemOpened} de la base de datos.
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
	  * @param ProblemsetProblemOpened [$Problemset_Problem_Opened] El objeto de tipo ProblemsetProblemOpened
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Problemset_Problem_Opened , $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = NULL, $likeColumns = NULL)
	{
		if (!($Problemset_Problem_Opened instanceof ProblemsetProblemOpened)) {
			return self::search(new ProblemsetProblemOpened($Problemset_Problem_Opened));
		}

		$sql = "SELECT * from Problemset_Problem_Opened WHERE (";
		$val = array();
		if (!is_null( $Problemset_Problem_Opened->problemset_id)) {
			$sql .= " `problemset_id` = ? AND";
			array_push( $val, $Problemset_Problem_Opened->problemset_id );
		}
		if (!is_null( $Problemset_Problem_Opened->problem_id)) {
			$sql .= " `problem_id` = ? AND";
			array_push( $val, $Problemset_Problem_Opened->problem_id );
		}
		if (!is_null( $Problemset_Problem_Opened->user_id)) {
			$sql .= " `user_id` = ? AND";
			array_push( $val, $Problemset_Problem_Opened->user_id );
		}
		if (!is_null( $Problemset_Problem_Opened->open_time)) {
			$sql .= " `open_time` = ? AND";
			array_push( $val, $Problemset_Problem_Opened->open_time );
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
			$bar =  new ProblemsetProblemOpened($foo);
			array_push( $ar,$bar);
		}
		return $ar;
	}

	/**
	  *	Actualizar registros.
	  *
	  * @return Filas afectadas
	  * @param ProblemsetProblemOpened [$Problemset_Problem_Opened] El objeto de tipo ProblemsetProblemOpened a actualizar.
	  **/
	private static final function update($Problemset_Problem_Opened)
	{
		$sql = "UPDATE Problemset_Problem_Opened SET  `open_time` = ? WHERE  `problemset_id` = ? AND `problem_id` = ? AND `user_id` = ?;";
		$params = array(
			$Problemset_Problem_Opened->open_time,
			$Problemset_Problem_Opened->problemset_id,$Problemset_Problem_Opened->problem_id,$Problemset_Problem_Opened->user_id, );
		global $conn;
		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}

	/**
	  *	Crear registros.
	  *
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los
	  * contenidos del objeto ProblemsetProblemOpened suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave
	  * primaria generada en el objeto ProblemsetProblemOpened dentro de la misma transaccion.
	  *
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param ProblemsetProblemOpened [$Problemset_Problem_Opened] El objeto de tipo ProblemsetProblemOpened a crear.
	  **/
	private static final function create( $Problemset_Problem_Opened )
	{
		if (is_null($Problemset_Problem_Opened->open_time)) $Problemset_Problem_Opened->open_time = gmdate('Y-m-d H:i:s');
		$sql = "INSERT INTO Problemset_Problem_Opened ( `problemset_id`, `problem_id`, `user_id`, `open_time` ) VALUES ( ?, ?, ?, ?);";
		$params = array(
			$Problemset_Problem_Opened->problemset_id,
			$Problemset_Problem_Opened->problem_id,
			$Problemset_Problem_Opened->user_id,
			$Problemset_Problem_Opened->open_time,
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
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ProblemsetProblemOpened} de la base de datos siempre y cuando
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link ProblemsetProblemOpened}.
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
	  * @param ProblemsetProblemOpened [$Problemset_Problem_Opened] El objeto de tipo ProblemsetProblemOpened
	  * @param ProblemsetProblemOpened [$Problemset_Problem_Opened] El objeto de tipo ProblemsetProblemOpened
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $Problemset_Problem_OpenedA , $Problemset_Problem_OpenedB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Problemset_Problem_Opened WHERE (";
		$val = array();
		if( ( !is_null (($a = $Problemset_Problem_OpenedA->problemset_id) ) ) & ( ! is_null ( ($b = $Problemset_Problem_OpenedB->problemset_id) ) ) ){
				$sql .= " `problemset_id` >= ? AND `problemset_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `problemset_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $Problemset_Problem_OpenedA->problem_id) ) ) & ( ! is_null ( ($b = $Problemset_Problem_OpenedB->problem_id) ) ) ){
				$sql .= " `problem_id` >= ? AND `problem_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `problem_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $Problemset_Problem_OpenedA->user_id) ) ) & ( ! is_null ( ($b = $Problemset_Problem_OpenedB->user_id) ) ) ){
				$sql .= " `user_id` >= ? AND `user_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `user_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $Problemset_Problem_OpenedA->open_time) ) ) & ( ! is_null ( ($b = $Problemset_Problem_OpenedB->open_time) ) ) ){
				$sql .= " `open_time` >= ? AND `open_time` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `open_time` = ? AND";
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
			array_push( $ar, $bar = new ProblemsetProblemOpened($row));
		}
		return $ar;
	}

	/**
	  *	Eliminar registros.
	  *
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto ProblemsetProblemOpened suministrado. Una vez que se ha suprimido un objeto, este no
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param ProblemsetProblemOpened [$Problemset_Problem_Opened] El objeto de tipo ProblemsetProblemOpened a eliminar
	  **/
	public static final function delete( $Problemset_Problem_Opened )
	{
		if( is_null( self::getByPK($Problemset_Problem_Opened->problemset_id, $Problemset_Problem_Opened->problem_id, $Problemset_Problem_Opened->user_id) ) ) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Problemset_Problem_Opened WHERE  problemset_id = ? AND problem_id = ? AND user_id = ?;";
		$params = array( $Problemset_Problem_Opened->problemset_id, $Problemset_Problem_Opened->problem_id, $Problemset_Problem_Opened->user_id );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}
}
