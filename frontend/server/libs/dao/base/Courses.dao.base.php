<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Courses Data Access Object (DAO) Base.
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Courses }.
  * @access public
  * @abstract
  *
  */
abstract class CoursesDAOBase extends DAO
{
	/**
	  *	Guardar registros.
	  *
	  *	Este metodo guarda el estado actual del objeto {@link Courses} pasado en la base de datos. La llave
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param Courses [$Courses] El objeto de tipo Courses
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( $Courses )
	{
		if (!is_null(self::getByPK( $Courses->course_id)))
		{
			return CoursesDAOBase::update( $Courses);
		} else {
			return CoursesDAOBase::create( $Courses);
		}
	}

	/**
	  *	Obtener {@link Courses} por llave primaria.
	  *
	  * Este metodo cargara un objeto {@link Courses} de la base de datos
	  * usando sus llaves primarias.
	  *
	  *	@static
	  * @return @link Courses Un objeto del tipo {@link Courses}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $course_id )
	{
		if(  is_null( $course_id )  ){ return NULL; }
		$sql = "SELECT * FROM Courses WHERE (course_id = ? ) LIMIT 1;";
		$params = array(  $course_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0) return NULL;
		$foo = new Courses( $rs );
		return $foo;
	}

	/**
	  *	Obtener todas las filas.
	  *
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link Courses}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link Courses}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Courses";
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
			$bar = new Courses($foo);
    		array_push( $allData, $bar);
		}
		return $allData;
	}

	/**
	  *	Buscar registros.
	  *
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Courses} de la base de datos.
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
	  * @param Courses [$Courses] El objeto de tipo Courses
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Courses , $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = NULL, $likeColumns = NULL)
	{
		if (!($Courses instanceof Courses)) {
			return self::search(new Courses($Courses));
		}

		$sql = "SELECT * from Courses WHERE (";
		$val = array();
		if (!is_null( $Courses->course_id)) {
			$sql .= " `course_id` = ? AND";
			array_push( $val, $Courses->course_id );
		}
		if (!is_null( $Courses->name)) {
			$sql .= " `name` = ? AND";
			array_push( $val, $Courses->name );
		}
		if (!is_null( $Courses->description)) {
			$sql .= " `description` = ? AND";
			array_push( $val, $Courses->description );
		}
		if (!is_null( $Courses->alias)) {
			$sql .= " `alias` = ? AND";
			array_push( $val, $Courses->alias );
		}
		if (!is_null( $Courses->group_id)) {
			$sql .= " `group_id` = ? AND";
			array_push( $val, $Courses->group_id );
		}
		if (!is_null( $Courses->acl_id)) {
			$sql .= " `acl_id` = ? AND";
			array_push( $val, $Courses->acl_id );
		}
		if (!is_null( $Courses->start_time)) {
			$sql .= " `start_time` = ? AND";
			array_push( $val, $Courses->start_time );
		}
		if (!is_null( $Courses->finish_time)) {
			$sql .= " `finish_time` = ? AND";
			array_push( $val, $Courses->finish_time );
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
			$bar =  new Courses($foo);
			array_push( $ar,$bar);
		}
		return $ar;
	}

	/**
	  *	Actualizar registros.
	  *
	  * @return Filas afectadas
	  * @param Courses [$Courses] El objeto de tipo Courses a actualizar.
	  **/
	private static final function update($Courses)
	{
		$sql = "UPDATE Courses SET  `name` = ?, `description` = ?, `alias` = ?, `group_id` = ?, `acl_id` = ?, `start_time` = ?, `finish_time` = ? WHERE  `course_id` = ?;";
		$params = array(
			$Courses->name,
			$Courses->description,
			$Courses->alias,
			$Courses->group_id,
			$Courses->acl_id,
			$Courses->start_time,
			$Courses->finish_time,
			$Courses->course_id, );
		global $conn;
		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}

	/**
	  *	Crear registros.
	  *
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los
	  * contenidos del objeto Courses suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave
	  * primaria generada en el objeto Courses dentro de la misma transaccion.
	  *
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param Courses [$Courses] El objeto de tipo Courses a crear.
	  **/
	private static final function create( $Courses )
	{
		if (is_null($Courses->start_time)) $Courses->start_time = '2000-01-01 06:00:00';
		if (is_null($Courses->finish_time)) $Courses->finish_time = '2000-01-01 06:00:00';
		$sql = "INSERT INTO Courses ( `course_id`, `name`, `description`, `alias`, `group_id`, `acl_id`, `start_time`, `finish_time` ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?);";
		$params = array(
			$Courses->course_id,
			$Courses->name,
			$Courses->description,
			$Courses->alias,
			$Courses->group_id,
			$Courses->acl_id,
			$Courses->start_time,
			$Courses->finish_time,
		 );
		global $conn;
		$conn->Execute($sql, $params);
		$ar = $conn->Affected_Rows();
		if($ar == 0) return 0;
		$Courses->course_id = $conn->Insert_ID();

		return $ar;
	}

	/**
	  *	Buscar por rango.
	  *
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Courses} de la base de datos siempre y cuando
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Courses}.
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
	  * @param Courses [$Courses] El objeto de tipo Courses
	  * @param Courses [$Courses] El objeto de tipo Courses
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $CoursesA , $CoursesB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Courses WHERE (";
		$val = array();
		if( ( !is_null (($a = $CoursesA->course_id) ) ) & ( ! is_null ( ($b = $CoursesB->course_id) ) ) ){
				$sql .= " `course_id` >= ? AND `course_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `course_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $CoursesA->name) ) ) & ( ! is_null ( ($b = $CoursesB->name) ) ) ){
				$sql .= " `name` >= ? AND `name` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `name` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $CoursesA->description) ) ) & ( ! is_null ( ($b = $CoursesB->description) ) ) ){
				$sql .= " `description` >= ? AND `description` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `description` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $CoursesA->alias) ) ) & ( ! is_null ( ($b = $CoursesB->alias) ) ) ){
				$sql .= " `alias` >= ? AND `alias` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `alias` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $CoursesA->group_id) ) ) & ( ! is_null ( ($b = $CoursesB->group_id) ) ) ){
				$sql .= " `group_id` >= ? AND `group_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `group_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $CoursesA->acl_id) ) ) & ( ! is_null ( ($b = $CoursesB->acl_id) ) ) ){
				$sql .= " `acl_id` >= ? AND `acl_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `acl_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $CoursesA->start_time) ) ) & ( ! is_null ( ($b = $CoursesB->start_time) ) ) ){
				$sql .= " `start_time` >= ? AND `start_time` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `start_time` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $CoursesA->finish_time) ) ) & ( ! is_null ( ($b = $CoursesB->finish_time) ) ) ){
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
			array_push( $ar, $bar = new Courses($row));
		}
		return $ar;
	}

	/**
	  *	Eliminar registros.
	  *
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto Courses suministrado. Una vez que se ha suprimido un objeto, este no
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param Courses [$Courses] El objeto de tipo Courses a eliminar
	  **/
	public static final function delete( $Courses )
	{
		if( is_null( self::getByPK($Courses->course_id) ) ) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Courses WHERE  course_id = ?;";
		$params = array( $Courses->course_id );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}
}
