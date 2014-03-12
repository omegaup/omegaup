<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** CoderOfTheMonth Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link CoderOfTheMonth }. 
  * @access public
  * @abstract
  * 
  */
abstract class CoderOfTheMonthDAOBase extends DAO
{

	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link CoderOfTheMonth} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param CoderOfTheMonth [$Coder_of_the_Month] El objeto de tipo CoderOfTheMonth
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Coder_of_the_Month )
	{
		if (!is_null(self::getByPK( $Coder_of_the_Month->getCoderOfTheMonthId() )))
		{
			return CoderOfTheMonthDAOBase::update( $Coder_of_the_Month);
		} else {
			return CoderOfTheMonthDAOBase::create( $Coder_of_the_Month);
		}
	}


	/**
	  *	Obtener {@link CoderOfTheMonth} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link CoderOfTheMonth} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link CoderOfTheMonth Un objeto del tipo {@link CoderOfTheMonth}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $coder_of_the_month_id )
	{
		if(  is_null( $coder_of_the_month_id )  ){ return NULL; }
			return new CoderOfTheMonth($obj);
		}
		$sql = "SELECT * FROM Coder_of_the_Month WHERE (coder_of_the_month_id = ? ) LIMIT 1;";
		$params = array(  $coder_of_the_month_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0) return NULL;
		$foo = new CoderOfTheMonth( $rs );
		return $foo;
	}

	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link CoderOfTheMonth}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link CoderOfTheMonth}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Coder_of_the_Month";
		if( ! is_null ( $orden ) )
		{ $sql .= " ORDER BY " . $orden . " " . $tipo_de_orden;	}
		if( ! is_null ( $pagina ) )
		{
			$sql .= " LIMIT " . (( $pagina - 1 )*$columnas_por_pagina) . "," . $columnas_por_pagina; 
		}
		global $conn;
		$rs = $conn->Execute($sql);
		$allData = array();
		foreach ($rs as $foo) {
			$bar = new CoderOfTheMonth($foo);
    		array_push( $allData, $bar);
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link CoderOfTheMonth} de la base de datos. 
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
	  * @param CoderOfTheMonth [$Coder_of_the_Month] El objeto de tipo CoderOfTheMonth
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Coder_of_the_Month , $orderBy = null, $orden = 'ASC')
	{
		if (!($Coder_of_the_Month instanceof CoderOfTheMonth)) {
			return self::search(new CoderOfTheMonth($Coder_of_the_Month));
		}

		$sql = "SELECT * from Coder_of_the_Month WHERE ("; 
		$val = array();
		if (!is_null( $Coder_of_the_Month->getCoderOfTheMonthId())) {
			$sql .= " `coder_of_the_month_id` = ? AND";
			array_push( $val, $Coder_of_the_Month->getCoderOfTheMonthId() );
		}
		if (!is_null( $Coder_of_the_Month->getDescription())) {
			$sql .= " `description` = ? AND";
			array_push( $val, $Coder_of_the_Month->getDescription() );
		}
		if (!is_null( $Coder_of_the_Month->getTime())) {
			$sql .= " `time` = ? AND";
			array_push( $val, $Coder_of_the_Month->getTime() );
		}
		if (!is_null( $Coder_of_the_Month->getInterviewUrl())) {
			$sql .= " `interview_url` = ? AND";
			array_push( $val, $Coder_of_the_Month->getInterviewUrl() );
		}
		if(sizeof($val) == 0) {
			return self::getAll();
		}
		$sql = substr($sql, 0, -3) . " )";
		if( ! is_null ( $orderBy ) ){
			$sql .= " order by " . $orderBy . " " . $orden ;
		}
		global $conn;
		$rs = $conn->Execute($sql, $val);
		$ar = array();
		foreach ($rs as $foo) {
			$bar =  new CoderOfTheMonth($foo);
			array_push( $ar,$bar);
		}
		return $ar;
	}

	/**
	  *	Actualizar registros.
	  *
	  * @return Filas afectadas
	  * @param CoderOfTheMonth [$Coder_of_the_Month] El objeto de tipo CoderOfTheMonth a actualizar.
	  **/
	private static final function update($Coder_of_the_Month)
	{
		$sql = "UPDATE Coder_of_the_Month SET  `description` = ?, `time` = ?, `interview_url` = ? WHERE  `coder_of_the_month_id` = ?;";
		$params = array( 
			$Coder_of_the_Month->getDescription(), 
			$Coder_of_the_Month->getTime(), 
			$Coder_of_the_Month->getInterviewUrl(), 
			$Coder_of_the_Month->getCoderOfTheMonthId(), );
		global $conn;
		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}

	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto CoderOfTheMonth suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto CoderOfTheMonth dentro de la misma transaccion.
	  *	
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param CoderOfTheMonth [$Coder_of_the_Month] El objeto de tipo CoderOfTheMonth a crear.
	  **/
	private static final function create( &$Coder_of_the_Month )
	{
		$sql = "INSERT INTO Coder_of_the_Month ( `coder_of_the_month_id`, `description`, `time`, `interview_url` ) VALUES ( ?, ?, ?, ?);";
		$params = array( 
			$Coder_of_the_Month->getCoderOfTheMonthId(), 
			$Coder_of_the_Month->getDescription(), 
			$Coder_of_the_Month->getTime(), 
			$Coder_of_the_Month->getInterviewUrl(), 
		 );
		global $conn;
		$conn->Execute($sql, $params);
		$ar = $conn->Affected_Rows();
		if($ar == 0) return 0;
 		$Coder_of_the_Month->setCoderOfTheMonthId( $conn->Insert_ID() );

		return $ar;
	}

	/**
	  *	Buscar por rango.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link CoderOfTheMonth} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link CoderOfTheMonth}.
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
	  * @param CoderOfTheMonth [$Coder_of_the_Month] El objeto de tipo CoderOfTheMonth
	  * @param CoderOfTheMonth [$Coder_of_the_Month] El objeto de tipo CoderOfTheMonth
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $Coder_of_the_MonthA , $Coder_of_the_MonthB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Coder_of_the_Month WHERE ("; 
		$val = array();
		if( ( !is_null (($a = $Coder_of_the_MonthA->getCoderOfTheMonthId()) ) ) & ( ! is_null ( ($b = $Coder_of_the_MonthB->getCoderOfTheMonthId()) ) ) ){
				$sql .= " `coder_of_the_month_id` >= ? AND `coder_of_the_month_id` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `coder_of_the_month_id` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $Coder_of_the_MonthA->getDescription()) ) ) & ( ! is_null ( ($b = $Coder_of_the_MonthB->getDescription()) ) ) ){
				$sql .= " `description` >= ? AND `description` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `description` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $Coder_of_the_MonthA->getTime()) ) ) & ( ! is_null ( ($b = $Coder_of_the_MonthB->getTime()) ) ) ){
				$sql .= " `time` >= ? AND `time` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `time` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $Coder_of_the_MonthA->getInterviewUrl()) ) ) & ( ! is_null ( ($b = $Coder_of_the_MonthB->getInterviewUrl()) ) ) ){
				$sql .= " `interview_url` >= ? AND `interview_url` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `interview_url` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		$sql = substr($sql, 0, -3) . " )";
		if( !is_null ( $orderBy ) ){
		    $sql .= " order by " . $orderBy . " " . $orden ;

		}
		global $conn;
		$rs = $conn->Execute($sql, $val);
		$ar = array();
		foreach ($rs as $row) {
			array_push( $ar, $bar = new CoderOfTheMonth($row));
		}
		return $ar;
	}

	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto CoderOfTheMonth suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param CoderOfTheMonth [$Coder_of_the_Month] El objeto de tipo CoderOfTheMonth a eliminar
	  **/
	public static final function delete( $Coder_of_the_Month )
	{
		if( is_null( self::getByPK($Coder_of_the_Month->getCoderOfTheMonthId()) ) ) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Coder_of_the_Month WHERE  coder_of_the_month_id = ?;";
		$params = array( $Coder_of_the_Month->getCoderOfTheMonthId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
