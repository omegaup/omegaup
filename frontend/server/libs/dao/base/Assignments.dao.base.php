<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Assignments Data Access Object (DAO) Base.
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Assignments }.
  * @access public
  * @abstract
  *
  */
abstract class AssignmentsDAOBase extends DAO
{
	/**
	  *	Guardar registros.
	  *
	  *	Este metodo guarda el estado actual del objeto {@link Assignments} pasado en la base de datos. La llave
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param Assignments [$Assignments] El objeto de tipo Assignments
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( $Assignments )
	{
		if (!is_null(self::getByPK( $Assignments->assignment_id)))
		{
			return AssignmentsDAOBase::update( $Assignments);
		} else {
			return AssignmentsDAOBase::create( $Assignments);
		}
	}

	/**
	  *	Obtener {@link Assignments} por llave primaria.
	  *
	  * Este metodo cargara un objeto {@link Assignments} de la base de datos
	  * usando sus llaves primarias.
	  *
	  *	@static
	  * @return @link Assignments Un objeto del tipo {@link Assignments}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $assignment_id )
	{
		if(  is_null( $assignment_id )  ){ return NULL; }
		$sql = "SELECT * FROM Assignments WHERE (assignment_id = ? ) LIMIT 1;";
		$params = array(  $assignment_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0) return NULL;
		$foo = new Assignments( $rs );
		return $foo;
	}

	/**
	  *	Obtener todas las filas.
	  *
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link Assignments}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link Assignments}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Assignments";
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
			$bar = new Assignments($foo);
    		array_push( $allData, $bar);
		}
		return $allData;
	}

	/**
	  *	Buscar registros.
	  *
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Assignments} de la base de datos.
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
	  * @param Assignments [$Assignments] El objeto de tipo Assignments
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Assignments , $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = NULL, $likeColumns = NULL)
	{
		if (!($Assignments instanceof Assignments)) {
			return self::search(new Assignments($Assignments));
		}

		$sql = "SELECT * from Assignments WHERE (";
		$val = array();
		if (!is_null( $Assignments->assignment_id)) {
			$sql .= " `assignment_id` = ? AND";
			array_push( $val, $Assignments->assignment_id );
		}
		if (!is_null( $Assignments->id_course)) {
			$sql .= " `id_course` = ? AND";
			array_push( $val, $Assignments->id_course );
		}
		if (!is_null( $Assignments->id_problemset)) {
			$sql .= " `id_problemset` = ? AND";
			array_push( $val, $Assignments->id_problemset );
		}
		if (!is_null( $Assignments->name)) {
			$sql .= " `name` = ? AND";
			array_push( $val, $Assignments->name );
		}
		if (!is_null( $Assignments->description)) {
			$sql .= " `description` = ? AND";
			array_push( $val, $Assignments->description );
		}
		if (!is_null( $Assignments->alias)) {
			$sql .= " `alias` = ? AND";
			array_push( $val, $Assignments->alias );
		}
		if (!is_null( $Assignments->publish_time_delay)) {
			$sql .= " `publish_time_delay` = ? AND";
			array_push( $val, $Assignments->publish_time_delay );
		}
		if (!is_null( $Assignments->assignment_type)) {
			$sql .= " `assignment_type` = ? AND";
			array_push( $val, $Assignments->assignment_type );
		}
		if (!is_null( $Assignments->start_time)) {
			$sql .= " `start_time` = ? AND";
			array_push( $val, $Assignments->start_time );
		}
		if (!is_null( $Assignments->finish_time)) {
			$sql .= " `finish_time` = ? AND";
			array_push( $val, $Assignments->finish_time );
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
			$bar =  new Assignments($foo);
			array_push( $ar,$bar);
		}
		return $ar;
	}

	/**
	  *	Actualizar registros.
	  *
	  * @return Filas afectadas
	  * @param Assignments [$Assignments] El objeto de tipo Assignments a actualizar.
	  **/
	private static final function update($Assignments)
	{
		$sql = "UPDATE Assignments SET  `id_course` = ?, `id_problemset` = ?, `name` = ?, `description` = ?, `alias` = ?, `publish_time_delay` = ?, `assignment_type` = ?, `start_time` = ?, `finish_time` = ? WHERE  `assignment_id` = ?;";
		$params = array(
			$Assignments->id_course,
			$Assignments->id_problemset,
			$Assignments->name,
			$Assignments->description,
			$Assignments->alias,
			$Assignments->publish_time_delay,
			$Assignments->assignment_type,
			$Assignments->start_time,
			$Assignments->finish_time,
			$Assignments->assignment_id, );
		global $conn;
		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}

	/**
	  *	Crear registros.
	  *
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los
	  * contenidos del objeto Assignments suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave
	  * primaria generada en el objeto Assignments dentro de la misma transaccion.
	  *
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param Assignments [$Assignments] El objeto de tipo Assignments a crear.
	  **/
	private static final function create( $Assignments )
	{
		if (is_null($Assignments->start_time)) $Assignments->start_time = '2000-01-01 06:00:00';
		if (is_null($Assignments->finish_time)) $Assignments->finish_time = '2000-01-01 06:00:00';
		$sql = "INSERT INTO Assignments ( `assignment_id`, `id_course`, `id_problemset`, `name`, `description`, `alias`, `publish_time_delay`, `assignment_type`, `start_time`, `finish_time` ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
		$params = array(
			$Assignments->assignment_id,
			$Assignments->id_course,
			$Assignments->id_problemset,
			$Assignments->name,
			$Assignments->description,
			$Assignments->alias,
			$Assignments->publish_time_delay,
			$Assignments->assignment_type,
			$Assignments->start_time,
			$Assignments->finish_time,
		 );
		global $conn;
		$conn->Execute($sql, $params);
		$ar = $conn->Affected_Rows();
		if($ar == 0) return 0;
		$Assignments->assignment_id = $conn->Insert_ID();

		return $ar;
	}

	/**
	  *	Buscar por rango.
	  *
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Assignments} de la base de datos siempre y cuando
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Assignments}.
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
	  * @param Assignments [$Assignments] El objeto de tipo Assignments
	  * @param Assignments [$Assignments] El objeto de tipo Assignments
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $AssignmentsA , $AssignmentsB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Assignments WHERE (";
		$val = array();
		if( ( !is_null (($a = $AssignmentsA->assignment_id) ) ) & ( ! is_null ( ($b = $AssignmentsB->assignment_id) ) ) ){
				$sql .= " `assignment_id` >= ? AND `assignment_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `assignment_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $AssignmentsA->id_course) ) ) & ( ! is_null ( ($b = $AssignmentsB->id_course) ) ) ){
				$sql .= " `id_course` >= ? AND `id_course` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `id_course` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $AssignmentsA->id_problemset) ) ) & ( ! is_null ( ($b = $AssignmentsB->id_problemset) ) ) ){
				$sql .= " `id_problemset` >= ? AND `id_problemset` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `id_problemset` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $AssignmentsA->name) ) ) & ( ! is_null ( ($b = $AssignmentsB->name) ) ) ){
				$sql .= " `name` >= ? AND `name` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `name` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $AssignmentsA->description) ) ) & ( ! is_null ( ($b = $AssignmentsB->description) ) ) ){
				$sql .= " `description` >= ? AND `description` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `description` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $AssignmentsA->alias) ) ) & ( ! is_null ( ($b = $AssignmentsB->alias) ) ) ){
				$sql .= " `alias` >= ? AND `alias` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `alias` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $AssignmentsA->publish_time_delay) ) ) & ( ! is_null ( ($b = $AssignmentsB->publish_time_delay) ) ) ){
				$sql .= " `publish_time_delay` >= ? AND `publish_time_delay` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `publish_time_delay` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $AssignmentsA->assignment_type) ) ) & ( ! is_null ( ($b = $AssignmentsB->assignment_type) ) ) ){
				$sql .= " `assignment_type` >= ? AND `assignment_type` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `assignment_type` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $AssignmentsA->start_time) ) ) & ( ! is_null ( ($b = $AssignmentsB->start_time) ) ) ){
				$sql .= " `start_time` >= ? AND `start_time` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `start_time` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $AssignmentsA->finish_time) ) ) & ( ! is_null ( ($b = $AssignmentsB->finish_time) ) ) ){
				$sql .= " `finish_time` >= ? AND `finish_time` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `finish_time` = ? AND";
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
			array_push( $ar, $bar = new Assignments($row));
		}
		return $ar;
	}

	/**
	  *	Eliminar registros.
	  *
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto Assignments suministrado. Una vez que se ha suprimido un objeto, este no
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param Assignments [$Assignments] El objeto de tipo Assignments a eliminar
	  **/
	public static final function delete( $Assignments )
	{
		if( is_null( self::getByPK($Assignments->assignment_id) ) ) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Assignments WHERE  assignment_id = ?;";
		$params = array( $Assignments->assignment_id );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}
}
