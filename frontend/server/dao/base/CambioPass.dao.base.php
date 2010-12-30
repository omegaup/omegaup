<?php
/** CambioPass Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link CambioPass }. 
  * @author Alan Gonzalez <alan@caffeina.mx> 
  * @abstract
  * @package openjudge
  * 
  */
abstract class CambioPassDAOBase extends DAO
{

	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link CambioPass} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param CambioPass [$CambioPass] El objeto de tipo CambioPass
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$CambioPass )
	{
		if( self::getByPK(  $CambioPass->getUserID() ) === NULL )
		{
			try{ return CambioPassDAOBase::create( $CambioPass) ; } catch(Exception $e){ throw $e; }
		}else{
			try{ return CambioPassDAOBase::update( $CambioPass) ; } catch(Exception $e){ throw $e; }
		}
	}


	/**
	  *	Obtener {@link CambioPass} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link CambioPass} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link CambioPass Un objeto del tipo {@link CambioPass}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $userID )
	{
		$sql = "SELECT * FROM CambioPass WHERE (userID = ? ) LIMIT 1;";
		$params = array(  $userID );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
		return new CambioPass( $rs );
	}


	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link CambioPass}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link CambioPass}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from CambioPass";
		if($orden != NULL)
		{ $sql .= " ORDER BY " . $orden . " " . $tipo_de_orden;	}
		if($pagina != NULL)
		{
			$sql .= " LIMIT " . (( $pagina - 1 )*$columnas_por_pagina) . "," . $columnas_por_pagina; 
		}
		global $conn;
		$rs = $conn->Execute($sql);
		$allData = array();
		foreach ($rs as $foo) {
    		array_push( $allData, new CambioPass($foo));
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link CambioPass} de la base de datos. 
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
	  * @param CambioPass [$CambioPass] El objeto de tipo CambioPass
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $CambioPass , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from CambioPass WHERE ("; 
		$val = array();
		if( $CambioPass->getUserID() != NULL){
			$sql .= " userID = ? AND";
			array_push( $val, $CambioPass->getUserID() );
		}

		if( $CambioPass->getToken() != NULL){
			$sql .= " token = ? AND";
			array_push( $val, $CambioPass->getToken() );
		}

		if( $CambioPass->getIp() != NULL){
			$sql .= " ip = ? AND";
			array_push( $val, $CambioPass->getIp() );
		}

		if( $CambioPass->getExpiracion() != NULL){
			$sql .= " expiracion = ? AND";
			array_push( $val, $CambioPass->getExpiracion() );
		}

		$sql = substr($sql, 0, -3) . " )";
		if( $orderBy !== null ){
		    $sql .= " order by " . $orderBy . " " . $orden ;
		
		}
		global $conn;
		$rs = $conn->Execute($sql, $val);
		$ar = array();
		foreach ($rs as $foo) {
    		array_push( $ar, new CambioPass($foo));
		}
		return $ar;
	}


	/**
	  *	Actualizar registros.
	  *	
	  * Este metodo es un metodo de ayuda para uso interno. Se ejecutara todas las manipulaciones
	  * en la base de datos que estan dadas en el objeto pasado.No se haran consultas SELECT 
	  * aqui, sin embargo. El valor de retorno indica cuántas filas se vieron afectadas.
	  *	
	  * @internal private information for advanced developers only
	  * @return Filas afectadas o un string con la descripcion del error
	  * @param CambioPass [$CambioPass] El objeto de tipo CambioPass a actualizar.
	  **/
	private static final function update( $CambioPass )
	{
		$sql = "UPDATE CambioPass SET  token = ?, ip = ?, expiracion = ? WHERE  userID = ?;";
		$params = array( 
			$CambioPass->getToken(), 
			$CambioPass->getIp(), 
			$CambioPass->getExpiracion(), 
			$CambioPass->getUserID(), );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		return $conn->Affected_Rows();
	}


	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto CambioPass suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto CambioPass dentro de la misma transaccion.
	  *	
	  * @internal private information for advanced developers only
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param CambioPass [$CambioPass] El objeto de tipo CambioPass a crear.
	  **/
	private static final function create( &$CambioPass )
	{
		$sql = "INSERT INTO CambioPass ( userID, token, ip, expiracion ) VALUES ( ?, ?, ?, ?);";
		$params = array( 
			$CambioPass->getUserID(), 
			$CambioPass->getToken(), 
			$CambioPass->getIp(), 
			$CambioPass->getExpiracion(), 
		 );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		$ar = $conn->Affected_Rows();
		if($ar == 0) return 0;
		
		return $ar;
	}


	/**
	  *	Buscar por rango.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link CambioPass} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link CambioPass}.
	  * 
	  * Aquellas variables que tienen valores NULL seran excluidos en la busqueda. 
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
	  * @param CambioPass [$CambioPass] El objeto de tipo CambioPass
	  * @param CambioPass [$CambioPass] El objeto de tipo CambioPass
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $CambioPassA , $CambioPassB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from CambioPass WHERE ("; 
		$val = array();
		if( (($a = $CambioPassA->getUserID()) != NULL) & ( ($b = $CambioPassB->getUserID()) != NULL) ){
				$sql .= " userID >= ? AND userID <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " userID = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $CambioPassA->getToken()) != NULL) & ( ($b = $CambioPassB->getToken()) != NULL) ){
				$sql .= " token >= ? AND token <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " token = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $CambioPassA->getIp()) != NULL) & ( ($b = $CambioPassB->getIp()) != NULL) ){
				$sql .= " ip >= ? AND ip <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " ip = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $CambioPassA->getExpiracion()) != NULL) & ( ($b = $CambioPassB->getExpiracion()) != NULL) ){
				$sql .= " expiracion >= ? AND expiracion <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " expiracion = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		$sql = substr($sql, 0, -3) . " )";
		if( $orderBy !== null ){
		    $sql .= " order by " . $orderBy . " " . $orden ;
		
		}
		global $conn;
		$rs = $conn->Execute($sql, $val);
		$ar = array();
		foreach ($rs as $foo) {
    		array_push( $ar, new CambioPass($foo));
		}
		return $ar;
	}


	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto CambioPass suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param CambioPass [$CambioPass] El objeto de tipo CambioPass a eliminar
	  **/
	public static final function delete( &$CambioPass )
	{
		if(self::getByPK($CambioPass->getUserID()) === NULL) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM CambioPass WHERE  userID = ?;";
		$params = array( $CambioPass->getUserID() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
