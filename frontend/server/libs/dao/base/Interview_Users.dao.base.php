<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** InterviewUsers Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link InterviewUsers }. 
  * @access public
  * @abstract
  * 
  */
abstract class InterviewUsersDAOBase extends DAO
{

	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link InterviewUsers} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param InterviewUsers [$Interview_Users] El objeto de tipo InterviewUsers
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( $Interview_Users )
	{
		if (!is_null(self::getByPK( $Interview_Users->getUserId() )))
		{
			return InterviewUsersDAOBase::update( $Interview_Users);
		} else {
			return InterviewUsersDAOBase::create( $Interview_Users);
		}
	}


	/**
	  *	Obtener {@link InterviewUsers} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link InterviewUsers} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link InterviewUsers Un objeto del tipo {@link InterviewUsers}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $user_id )
	{
		if(  is_null( $user_id )  ){ return NULL; }
		$sql = "SELECT * FROM Interview_Users WHERE (user_id = ? ) LIMIT 1;";
		$params = array(  $user_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0) return NULL;
		$foo = new InterviewUsers( $rs );
		return $foo;
	}

	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link InterviewUsers}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link InterviewUsers}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Interview_Users";
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
			$bar = new InterviewUsers($foo);
    		array_push( $allData, $bar);
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link InterviewUsers} de la base de datos. 
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
	  * @param InterviewUsers [$Interview_Users] El objeto de tipo InterviewUsers
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Interview_Users , $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = NULL, $likeColumns = NULL)
	{
		if (!($Interview_Users instanceof InterviewUsers)) {
			return self::search(new InterviewUsers($Interview_Users));
		}

		$sql = "SELECT * from Interview_Users WHERE ("; 
		$val = array();
		if (!is_null( $Interview_Users->getUserId())) {
			$sql .= " `user_id` = ? AND";
			array_push( $val, $Interview_Users->getUserId() );
		}
		if (!is_null( $Interview_Users->getInterviewId())) {
			$sql .= " `interview_id` = ? AND";
			array_push( $val, $Interview_Users->getInterviewId() );
		}
		if (!is_null( $Interview_Users->getInvitationSent())) {
			$sql .= " `invitation_sent` = ? AND";
			array_push( $val, $Interview_Users->getInvitationSent() );
		}
		if (!is_null( $Interview_Users->getAccessTime())) {
			$sql .= " `access_time` = ? AND";
			array_push( $val, $Interview_Users->getAccessTime() );
		}
		if (!is_null( $Interview_Users->getScore())) {
			$sql .= " `score` = ? AND";
			array_push( $val, $Interview_Users->getScore() );
		}
		if (!is_null( $Interview_Users->getTime())) {
			$sql .= " `time` = ? AND";
			array_push( $val, $Interview_Users->getTime() );
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
			$bar =  new InterviewUsers($foo);
			array_push( $ar,$bar);
		}
		return $ar;
	}

	/**
	  *	Actualizar registros.
	  *
	  * @return Filas afectadas
	  * @param InterviewUsers [$Interview_Users] El objeto de tipo InterviewUsers a actualizar.
	  **/
	private static final function update($Interview_Users)
	{
		$sql = "UPDATE Interview_Users SET  `interview_id` = ?, `invitation_sent` = ?, `access_time` = ?, `score` = ?, `time` = ? WHERE  `user_id` = ?;";
		$params = array( 
			$Interview_Users->getInterviewId(), 
			$Interview_Users->getInvitationSent(), 
			$Interview_Users->getAccessTime(), 
			$Interview_Users->getScore(), 
			$Interview_Users->getTime(), 
			$Interview_Users->getUserId(), );
		global $conn;
		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}

	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto InterviewUsers suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto InterviewUsers dentro de la misma transaccion.
	  *	
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param InterviewUsers [$Interview_Users] El objeto de tipo InterviewUsers a crear.
	  **/
	private static final function create( $Interview_Users )
	{
		if (is_null($Interview_Users->invitation_sent)) $Interview_Users->invitation_sent = FALSE;
		if (is_null($Interview_Users->access_time)) $Interview_Users->access_time = '0000-00-00 00:00:00';
		if (is_null($Interview_Users->score)) $Interview_Users->score = '1';
		if (is_null($Interview_Users->time)) $Interview_Users->time = '1';
		$sql = "INSERT INTO Interview_Users ( `user_id`, `interview_id`, `invitation_sent`, `access_time`, `score`, `time` ) VALUES ( ?, ?, ?, ?, ?, ?);";
		$params = array( 
			$Interview_Users->user_id,
			$Interview_Users->interview_id,
			$Interview_Users->invitation_sent,
			$Interview_Users->access_time,
			$Interview_Users->score,
			$Interview_Users->time,
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
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link InterviewUsers} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link InterviewUsers}.
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
	  * @param InterviewUsers [$Interview_Users] El objeto de tipo InterviewUsers
	  * @param InterviewUsers [$Interview_Users] El objeto de tipo InterviewUsers
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $Interview_UsersA , $Interview_UsersB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Interview_Users WHERE ("; 
		$val = array();
		if( ( !is_null (($a = $Interview_UsersA->getUserId()) ) ) & ( ! is_null ( ($b = $Interview_UsersB->getUserId()) ) ) ){
				$sql .= " `user_id` >= ? AND `user_id` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `user_id` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $Interview_UsersA->getInterviewId()) ) ) & ( ! is_null ( ($b = $Interview_UsersB->getInterviewId()) ) ) ){
				$sql .= " `interview_id` >= ? AND `interview_id` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `interview_id` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $Interview_UsersA->getInvitationSent()) ) ) & ( ! is_null ( ($b = $Interview_UsersB->getInvitationSent()) ) ) ){
				$sql .= " `invitation_sent` >= ? AND `invitation_sent` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `invitation_sent` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $Interview_UsersA->getAccessTime()) ) ) & ( ! is_null ( ($b = $Interview_UsersB->getAccessTime()) ) ) ){
				$sql .= " `access_time` >= ? AND `access_time` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `access_time` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $Interview_UsersA->getScore()) ) ) & ( ! is_null ( ($b = $Interview_UsersB->getScore()) ) ) ){
				$sql .= " `score` >= ? AND `score` <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `score` = ? AND"; 
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
			
		}

		if( ( !is_null (($a = $Interview_UsersA->getTime()) ) ) & ( ! is_null ( ($b = $Interview_UsersB->getTime()) ) ) ){
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
			array_push( $ar, $bar = new InterviewUsers($row));
		}
		return $ar;
	}

	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto InterviewUsers suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param InterviewUsers [$Interview_Users] El objeto de tipo InterviewUsers a eliminar
	  **/
	public static final function delete( $Interview_Users )
	{
		if( is_null( self::getByPK($Interview_Users->getUserId()) ) ) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Interview_Users WHERE  user_id = ?;";
		$params = array( $Interview_Users->getUserId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
