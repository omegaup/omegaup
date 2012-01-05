<?php
/** Emails Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link Emails }. 
  * @author alanboy
  * @access private
  * @abstract
  * @package docs
  * 
  */
abstract class EmailsDAOBase extends DAO
{

		private static $loadedRecords = array();

		private static function recordExists(  $email_id ){
			$pk = "";
			$pk .= $email_id . "-";
			return array_key_exists ( $pk , self::$loadedRecords );
		}
		private static function pushRecord( $inventario,  $email_id){
			$pk = "";
			$pk .= $email_id . "-";
			self::$loadedRecords [$pk] = $inventario;
		}
		private static function getRecord(  $email_id ){
			$pk = "";
			$pk .= $email_id . "-";
			return self::$loadedRecords[$pk];
		}
	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link Emails} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param Emails [$Emails] El objeto de tipo Emails
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Emails )
	{
		if(  self::getByPK(  $Emails->getEmailId() ) !== NULL )
		{
			try{ return EmailsDAOBase::update( $Emails) ; } catch(Exception $e){ throw $e; }
		}else{
			try{ return EmailsDAOBase::create( $Emails) ; } catch(Exception $e){ throw $e; }
		}
	}


	/**
	  *	Obtener {@link Emails} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link Emails} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link Emails Un objeto del tipo {@link Emails}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $email_id )
	{
		if(self::recordExists(  $email_id)){
			return self::getRecord( $email_id );
		}
		$sql = "SELECT * FROM Emails WHERE (email_id = ? ) LIMIT 1;";
		$params = array(  $email_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
			$foo = new Emails( $rs );
			self::pushRecord( $foo,  $email_id );
			return $foo;
	}


	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link Emails}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link Emails}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Emails";
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
			$bar = new Emails($foo);
    		array_push( $allData, $bar);
			//email_id
    		self::pushRecord( $bar, $foo["email_id"] );
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Emails} de la base de datos. 
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
	  * @param Emails [$Emails] El objeto de tipo Emails
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Emails , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Emails WHERE ("; 
		$val = array();
		if( $Emails->getEmailId() != NULL){
			$sql .= " email_id = ? AND";
			array_push( $val, $Emails->getEmailId() );
		}

		if( $Emails->getEmail() != NULL){
			$sql .= " email = ? AND";
			array_push( $val, $Emails->getEmail() );
		}

		if( $Emails->getUserId() != NULL){
			$sql .= " user_id = ? AND";
			array_push( $val, $Emails->getUserId() );
		}

		if(sizeof($val) == 0){return array();}
		$sql = substr($sql, 0, -3) . " )";
		if( $orderBy !== null ){
		    $sql .= " order by " . $orderBy . " " . $orden ;
		
		}
		global $conn;
		$rs = $conn->Execute($sql, $val);
		$ar = array();
		foreach ($rs as $foo) {
			$bar =  new Emails($foo);
    		array_push( $ar,$bar);
    		self::pushRecord( $bar, $foo["email_id"] );
		}
		return $ar;
	}


	/**
	  *	Actualizar registros.
	  *	
	  * Este metodo es un metodo de ayuda para uso interno. Se ejecutara todas las manipulaciones
	  * en la base de datos que estan dadas en el objeto pasado.No se haran consultas SELECT 
	  * aqui, sin embargo. El valor de retorno indica cu‡ntas filas se vieron afectadas.
	  *	
	  * @internal private information for advanced developers only
	  * @return Filas afectadas o un string con la descripcion del error
	  * @param Emails [$Emails] El objeto de tipo Emails a actualizar.
	  **/
	private static final function update( $Emails )
	{
		$sql = "UPDATE Emails SET  email = ?, user_id = ? WHERE  email_id = ?;";
		$params = array( 
			$Emails->getEmail(), 
			$Emails->getUserId(), 
			$Emails->getEmailId(), );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		return $conn->Affected_Rows();
	}


	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto Emails suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto Emails dentro de la misma transaccion.
	  *	
	  * @internal private information for advanced developers only
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param Emails [$Emails] El objeto de tipo Emails a crear.
	  **/
	private static final function create( &$Emails )
	{
		$sql = "INSERT INTO Emails ( email_id, email, user_id ) VALUES ( ?, ?, ?);";
		$params = array( 
			$Emails->getEmailId(), 
			$Emails->getEmail(), 
			$Emails->getUserId(), 
		 );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		$ar = $conn->Affected_Rows();
		if($ar == 0) return 0;
		/* save autoincremented value on obj */ $Emails->setEmailId( $conn->Insert_ID() );   /*  */ 
		return $ar;
	}


	/**
	  *	Buscar por rango.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Emails} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Emails}.
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
	  * @param Emails [$Emails] El objeto de tipo Emails
	  * @param Emails [$Emails] El objeto de tipo Emails
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $EmailsA , $EmailsB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Emails WHERE ("; 
		$val = array();
		if( (($a = $EmailsA->getEmailId()) != NULL) & ( ($b = $EmailsB->getEmailId()) != NULL) ){
				$sql .= " email_id >= ? AND email_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " email_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $EmailsA->getEmail()) != NULL) & ( ($b = $EmailsB->getEmail()) != NULL) ){
				$sql .= " email >= ? AND email <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " email = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $EmailsA->getUserId()) != NULL) & ( ($b = $EmailsB->getUserId()) != NULL) ){
				$sql .= " user_id >= ? AND user_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " user_id = ? AND"; 
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
    		array_push( $ar, new Emails($foo));
		}
		return $ar;
	}


	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto Emails suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param Emails [$Emails] El objeto de tipo Emails a eliminar
	  **/
	public static final function delete( &$Emails )
	{
		if(self::getByPK($Emails->getEmailId()) === NULL) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Emails WHERE  email_id = ?;";
		$params = array( $Emails->getEmailId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
