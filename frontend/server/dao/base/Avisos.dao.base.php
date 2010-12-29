<?php
/** Avisos Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link Avisos }. 
  * @author Alan Gonzalez <alan@caffeina.mx> 
  * @abstract
  * @package openjudge
  * 
  */
abstract class AvisosDAOBase extends DAO
{

	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link Avisos} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param Avisos [$Avisos] El objeto de tipo Avisos
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Avisos )
	{
		if( self::getByPK(  $Avisos->getAvisoID() ) === NULL )
		{
			try{ return AvisosDAOBase::create( $Avisos) ; } catch(Exception $e){ throw $e; }
		}else{
			try{ return AvisosDAOBase::update( $Avisos) ; } catch(Exception $e){ throw $e; }
		}
	}


	/**
	  *	Obtener {@link Avisos} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link Avisos} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link Avisos Un objeto del tipo {@link Avisos}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $avisoID )
	{
		$sql = "SELECT * FROM Avisos WHERE (avisoID = ? ) LIMIT 1;";
		$params = array(  $avisoID );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
		return new Avisos( $rs );
	}


	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link Avisos}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link Avisos}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Avisos";
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
    		array_push( $allData, new Avisos($foo));
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Avisos} de la base de datos. 
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
	  * @param Avisos [$Avisos] El objeto de tipo Avisos
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Avisos , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Avisos WHERE ("; 
		$val = array();
		if( $Avisos->getAvisoID() != NULL){
			$sql .= " avisoID = ? AND";
			array_push( $val, $Avisos->getAvisoID() );
		}

		if( $Avisos->getUserID() != NULL){
			$sql .= " userID = ? AND";
			array_push( $val, $Avisos->getUserID() );
		}

		if( $Avisos->getFecha() != NULL){
			$sql .= " fecha = ? AND";
			array_push( $val, $Avisos->getFecha() );
		}

		if( $Avisos->getAviso() != NULL){
			$sql .= " aviso = ? AND";
			array_push( $val, $Avisos->getAviso() );
		}

		$sql = substr($sql, 0, -3) . " )";
		if( $orderBy !== null ){
		    $sql .= " order by " . $orderBy . " " . $orden ;
		
		}
		global $conn;
		$rs = $conn->Execute($sql, $val);
		$ar = array();
		foreach ($rs as $foo) {
    		array_push( $ar, new Avisos($foo));
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
	  * @param Avisos [$Avisos] El objeto de tipo Avisos a actualizar.
	  **/
	private static final function update( $Avisos )
	{
		$sql = "UPDATE Avisos SET  userID = ?, fecha = ?, aviso = ? WHERE  avisoID = ?;";
		$params = array( 
			$Avisos->getUserID(), 
			$Avisos->getFecha(), 
			$Avisos->getAviso(), 
			$Avisos->getAvisoID(), );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		return $conn->Affected_Rows();
	}


	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto Avisos suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto Avisos dentro de la misma transaccion.
	  *	
	  * @internal private information for advanced developers only
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param Avisos [$Avisos] El objeto de tipo Avisos a crear.
	  **/
	private static final function create( &$Avisos )
	{
		$sql = "INSERT INTO Avisos ( avisoID, userID, fecha, aviso ) VALUES ( ?, ?, ?, ?);";
		$params = array( 
			$Avisos->getAvisoID(), 
			$Avisos->getUserID(), 
			$Avisos->getFecha(), 
			$Avisos->getAviso(), 
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
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Avisos} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Avisos}.
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
	  * @param Avisos [$Avisos] El objeto de tipo Avisos
	  * @param Avisos [$Avisos] El objeto de tipo Avisos
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $AvisosA , $AvisosB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Avisos WHERE ("; 
		$val = array();
		if( (($a = $AvisosA->getAvisoID()) != NULL) & ( ($b = $AvisosB->getAvisoID()) != NULL) ){
				$sql .= " avisoID >= ? AND avisoID <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " avisoID = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $AvisosA->getUserID()) != NULL) & ( ($b = $AvisosB->getUserID()) != NULL) ){
				$sql .= " userID >= ? AND userID <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " userID = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $AvisosA->getFecha()) != NULL) & ( ($b = $AvisosB->getFecha()) != NULL) ){
				$sql .= " fecha >= ? AND fecha <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " fecha = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $AvisosA->getAviso()) != NULL) & ( ($b = $AvisosB->getAviso()) != NULL) ){
				$sql .= " aviso >= ? AND aviso <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " aviso = ? AND"; 
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
    		array_push( $ar, new Avisos($foo));
		}
		return $ar;
	}


	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto Avisos suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param Avisos [$Avisos] El objeto de tipo Avisos a eliminar
	  **/
	public static final function delete( &$Avisos )
	{
		if(self::getByPK($Avisos->getAvisoID()) === NULL) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Avisos WHERE  avisoID = ?;";
		$params = array( $Avisos->getAvisoID() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
