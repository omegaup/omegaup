<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Clarifications Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link Clarifications }. 
  * @access public
  * @abstract
  * 
  */
abstract class ClarificationsDAOBase extends DAO
{

	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link Clarifications} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param Clarifications [$Clarifications] El objeto de tipo Clarifications
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Clarifications )
	{
		if (!is_null(self::getByPK( $Clarifications->getClarificationId() )))
		{
			return ClarificationsDAOBase::update( $Clarifications);
		} else {
			return ClarificationsDAOBase::create( $Clarifications);
		}
	}


	/**
	  *	Obtener {@link Clarifications} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link Clarifications} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link Clarifications Un objeto del tipo {@link Clarifications}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $clarification_id )
	{
		if(  is_null( $clarification_id )  ){ return NULL; }
			return new Clarifications($obj);
		}
		$sql = "SELECT * FROM Clarifications WHERE (clarification_id = ? ) LIMIT 1;";
		$params = array(  $clarification_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0) return NULL;
		$foo = new Clarifications( $rs );
		return $foo;
	}

	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link Clarifications}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link Clarifications}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Clarifications";
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
			$bar = new Clarifications($foo);
    		array_push( $allData, $bar);
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Clarifications} de la base de datos. 
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
	  * @param Clarifications [$Clarifications] El objeto de tipo Clarifications
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Clarifications , $orderBy = null, $orden = 'ASC')
	{
		if (!($Clarifications instanceof Clarifications)) {
			return self::search(new Clarifications($Clarifications));
		}

		$sql = "SELECT * from Clarifications WHERE ("; 
		$val = array();
		if (!is_null( $Clarifications->getClarificationId())) {
			$sql .= " `clarification_id` = ? AND";
			array_push( $val, $Clarifications->getClarificationId() );
		}
		if (!is_null( $Clarifications->getAuthorId())) {
			$sql .= " `author_id` = ? AND";
			array_push( $val, $Clarifications->getAuthorId() );
		}
		if (!is_null( $Clarifications->getMessage())) {
			$sql .= " `message` = ? AND";
			array_push( $val, $Clarifications->getMessage() );
		}
		if (!is_null( $Clarifications->getAnswer())) {
			$sql .= " `answer` = ? AND";
			array_push( $val, $Clarifications->getAnswer() );
		}
		if (!is_null( $Clarifications->getTime())) {
			$sql .= " `time` = ? AND";
			array_push( $val, $Clarifications->getTime() );
		}
		if (!is_null( $Clarifications->getProblemId())) {
			$sql .= " `problem_id` = ? AND";
			array_push( $val, $Clarifications->getProblemId() );
		}
		if (!is_null( $Clarifications->getContestId())) {
			$sql .= " `contest_id` = ? AND";
			array_push( $val, $Clarifications->getContestId() );
		}
		if (!is_null( $Clarifications->getPublic())) {
			$sql .= " `public` = ? AND";
			array_push( $val, $Clarifications->getPublic() );
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
			$bar =  new Clarifications($foo);
			array_push( $ar,$bar);
		}
		return $ar;
	}

	/**
	  *	Actualizar registros.
	  *
	  * @return Filas afectadas
	  * @param Clarifications [$Clarifications] El objeto de tipo Clarifications a actualizar.
	  **/
	private static final function update($Clarifications)
	{
		$sql = "UPDATE Clarifications SET  `author_id` = ?, `message` = ?, `answer` = ?, `time` = ?, `problem_id` = ?, `contest_id` = ?, `public` = ? WHERE  `clarification_id` = ?;";
		$params = array( 
			$Clarifications->getAuthorId(), 
			$Clarifications->getMessage(), 
			$Clarifications->getAnswer(), 
			$Clarifications->getTime(), 
			$Clarifications->getProblemId(), 
			$Clarifications->getContestId(), 
			$Clarifications->getPublic(), 
			$Clarifications->getClarificationId(), );
		global $conn;
		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}

	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto Clarifications suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto Clarifications dentro de la misma transaccion.
	  *	
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param Clarifications [$Clarifications] El objeto de tipo Clarifications a crear.
	  **/
	private static final function create( &$Clarifications )
	{
		$sql = "INSERT INTO Clarifications ( `clarification_id`, `author_id`, `message`, `answer`, `time`, `problem_id`, `contest_id`, `public` ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?);";
		$params = array( 
			$Clarifications->getClarificationId(), 
			$Clarifications->getAuthorId(), 
			$Clarifications->getMessage(), 
			$Clarifications->getAnswer(), 
			$Clarifications->getTime(), 
			$Clarifications->getProblemId(), 
			$Clarifications->getContestId(), 
			$Clarifications->getPublic(), 
		 );
		global $conn;
		$conn->Execute($sql, $params);
		$ar = $conn->Affected_Rows();
		if($ar == 0) return 0;
 		$Clarifications->setClarificationId( $conn->Insert_ID() );

		return $ar;
	}

	/**
	  *	Buscar por rango.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Clarifications} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Clarifications}.
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
	  * @param Clarifications [$Clarifications] El objeto de tipo Clarifications
	  * @param Clarifications [$Clarifications] El objeto de tipo Clarifications
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $ClarificationsA , $ClarificationsB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Clarifications WHERE ("; 
		$val = array();
		if( ( !is_null (($a = $ClarificationsA->getClarificationId()) ) ) & ( ! is_null ( ($b = $ClarificationsB->getClarificationId()) ) ) ){
				$sql .= " `clarification_id` >= ? AND `clarification_id` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `clarification_id` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $ClarificationsA->getAuthorId()) ) ) & ( ! is_null ( ($b = $ClarificationsB->getAuthorId()) ) ) ){
				$sql .= " `author_id` >= ? AND `author_id` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `author_id` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $ClarificationsA->getMessage()) ) ) & ( ! is_null ( ($b = $ClarificationsB->getMessage()) ) ) ){
				$sql .= " `message` >= ? AND `message` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `message` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $ClarificationsA->getAnswer()) ) ) & ( ! is_null ( ($b = $ClarificationsB->getAnswer()) ) ) ){
				$sql .= " `answer` >= ? AND `answer` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `answer` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $ClarificationsA->getTime()) ) ) & ( ! is_null ( ($b = $ClarificationsB->getTime()) ) ) ){
				$sql .= " `time` >= ? AND `time` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `time` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $ClarificationsA->getProblemId()) ) ) & ( ! is_null ( ($b = $ClarificationsB->getProblemId()) ) ) ){
				$sql .= " `problem_id` >= ? AND `problem_id` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `problem_id` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $ClarificationsA->getContestId()) ) ) & ( ! is_null ( ($b = $ClarificationsB->getContestId()) ) ) ){
				$sql .= " `contest_id` >= ? AND `contest_id` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `contest_id` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $ClarificationsA->getPublic()) ) ) & ( ! is_null ( ($b = $ClarificationsB->getPublic()) ) ) ){
				$sql .= " `public` >= ? AND `public` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `public` = ? AND"; 
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
			array_push( $ar, $bar = new Clarifications($row));
		}
		return $ar;
	}

	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto Clarifications suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param Clarifications [$Clarifications] El objeto de tipo Clarifications a eliminar
	  **/
	public static final function delete( $Clarifications )
	{
		if( is_null( self::getByPK($Clarifications->getClarificationId()) ) ) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Clarifications WHERE  clarification_id = ?;";
		$params = array( $Clarifications->getClarificationId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
