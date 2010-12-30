<?php
/** Ejecuciones Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link Ejecuciones }. 
  * @author Alan Gonzalez <alan@caffeina.mx> 
  * @abstract
  * @package openjudge
  * 
  */
abstract class EjecucionesDAOBase extends DAO
{

	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link Ejecuciones} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param Ejecuciones [$Ejecuciones] El objeto de tipo Ejecuciones
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Ejecuciones )
	{
		if( self::getByPK(  $Ejecuciones->getEjecucionID() ) === NULL )
		{
			try{ return EjecucionesDAOBase::create( $Ejecuciones) ; } catch(Exception $e){ throw $e; }
		}else{
			try{ return EjecucionesDAOBase::update( $Ejecuciones) ; } catch(Exception $e){ throw $e; }
		}
	}


	/**
	  *	Obtener {@link Ejecuciones} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link Ejecuciones} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link Ejecuciones Un objeto del tipo {@link Ejecuciones}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $ejecucionID )
	{
		$sql = "SELECT * FROM Ejecuciones WHERE (ejecucionID = ? ) LIMIT 1;";
		$params = array(  $ejecucionID );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
		return new Ejecuciones( $rs );
	}


	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link Ejecuciones}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link Ejecuciones}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Ejecuciones";
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
    		array_push( $allData, new Ejecuciones($foo));
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Ejecuciones} de la base de datos. 
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
	  * @param Ejecuciones [$Ejecuciones] El objeto de tipo Ejecuciones
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Ejecuciones , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Ejecuciones WHERE ("; 
		$val = array();
		if( $Ejecuciones->getEjecucionID() != NULL){
			$sql .= " ejecucionID = ? AND";
			array_push( $val, $Ejecuciones->getEjecucionID() );
		}

		if( $Ejecuciones->getUsuarioID() != NULL){
			$sql .= " usuarioID = ? AND";
			array_push( $val, $Ejecuciones->getUsuarioID() );
		}

		if( $Ejecuciones->getProblemaID() != NULL){
			$sql .= " problemaID = ? AND";
			array_push( $val, $Ejecuciones->getProblemaID() );
		}

		if( $Ejecuciones->getConcursoID() != NULL){
			$sql .= " concursoID = ? AND";
			array_push( $val, $Ejecuciones->getConcursoID() );
		}

		if( $Ejecuciones->getGuid() != NULL){
			$sql .= " guid = ? AND";
			array_push( $val, $Ejecuciones->getGuid() );
		}

		if( $Ejecuciones->getLenguaje() != NULL){
			$sql .= " lenguaje = ? AND";
			array_push( $val, $Ejecuciones->getLenguaje() );
		}

		if( $Ejecuciones->getEstado() != NULL){
			$sql .= " estado = ? AND";
			array_push( $val, $Ejecuciones->getEstado() );
		}

		if( $Ejecuciones->getVeredicto() != NULL){
			$sql .= " veredicto = ? AND";
			array_push( $val, $Ejecuciones->getVeredicto() );
		}

		if( $Ejecuciones->getTiempo() != NULL){
			$sql .= " tiempo = ? AND";
			array_push( $val, $Ejecuciones->getTiempo() );
		}

		if( $Ejecuciones->getMemoria() != NULL){
			$sql .= " memoria = ? AND";
			array_push( $val, $Ejecuciones->getMemoria() );
		}

		if( $Ejecuciones->getPuntuacion() != NULL){
			$sql .= " puntuacion = ? AND";
			array_push( $val, $Ejecuciones->getPuntuacion() );
		}

		if( $Ejecuciones->getIp() != NULL){
			$sql .= " ip = ? AND";
			array_push( $val, $Ejecuciones->getIp() );
		}

		if( $Ejecuciones->getFecha() != NULL){
			$sql .= " fecha = ? AND";
			array_push( $val, $Ejecuciones->getFecha() );
		}

		$sql = substr($sql, 0, -3) . " )";
		if( $orderBy !== null ){
		    $sql .= " order by " . $orderBy . " " . $orden ;
		
		}
		global $conn;
		$rs = $conn->Execute($sql, $val);
		$ar = array();
		foreach ($rs as $foo) {
    		array_push( $ar, new Ejecuciones($foo));
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
	  * @param Ejecuciones [$Ejecuciones] El objeto de tipo Ejecuciones a actualizar.
	  **/
	private static final function update( $Ejecuciones )
	{
		$sql = "UPDATE Ejecuciones SET  usuarioID = ?, problemaID = ?, concursoID = ?, guid = ?, lenguaje = ?, estado = ?, veredicto = ?, tiempo = ?, memoria = ?, puntuacion = ?, ip = ?, fecha = ? WHERE  ejecucionID = ?;";
		$params = array( 
			$Ejecuciones->getUsuarioID(), 
			$Ejecuciones->getProblemaID(), 
			$Ejecuciones->getConcursoID(), 
			$Ejecuciones->getGuid(), 
			$Ejecuciones->getLenguaje(), 
			$Ejecuciones->getEstado(), 
			$Ejecuciones->getVeredicto(), 
			$Ejecuciones->getTiempo(), 
			$Ejecuciones->getMemoria(), 
			$Ejecuciones->getPuntuacion(), 
			$Ejecuciones->getIp(), 
			$Ejecuciones->getFecha(), 
			$Ejecuciones->getEjecucionID(), );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		return $conn->Affected_Rows();
	}


	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto Ejecuciones suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto Ejecuciones dentro de la misma transaccion.
	  *	
	  * @internal private information for advanced developers only
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param Ejecuciones [$Ejecuciones] El objeto de tipo Ejecuciones a crear.
	  **/
	private static final function create( &$Ejecuciones )
	{
		$sql = "INSERT INTO Ejecuciones ( ejecucionID, usuarioID, problemaID, concursoID, guid, lenguaje, estado, veredicto, tiempo, memoria, puntuacion, ip, fecha ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
		$params = array( 
			$Ejecuciones->getEjecucionID(), 
			$Ejecuciones->getUsuarioID(), 
			$Ejecuciones->getProblemaID(), 
			$Ejecuciones->getConcursoID(), 
			$Ejecuciones->getGuid(), 
			$Ejecuciones->getLenguaje(), 
			$Ejecuciones->getEstado(), 
			$Ejecuciones->getVeredicto(), 
			$Ejecuciones->getTiempo(), 
			$Ejecuciones->getMemoria(), 
			$Ejecuciones->getPuntuacion(), 
			$Ejecuciones->getIp(), 
			$Ejecuciones->getFecha(), 
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
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Ejecuciones} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Ejecuciones}.
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
	  * @param Ejecuciones [$Ejecuciones] El objeto de tipo Ejecuciones
	  * @param Ejecuciones [$Ejecuciones] El objeto de tipo Ejecuciones
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $EjecucionesA , $EjecucionesB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Ejecuciones WHERE ("; 
		$val = array();
		if( (($a = $EjecucionesA->getEjecucionID()) != NULL) & ( ($b = $EjecucionesB->getEjecucionID()) != NULL) ){
				$sql .= " ejecucionID >= ? AND ejecucionID <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " ejecucionID = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $EjecucionesA->getUsuarioID()) != NULL) & ( ($b = $EjecucionesB->getUsuarioID()) != NULL) ){
				$sql .= " usuarioID >= ? AND usuarioID <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " usuarioID = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $EjecucionesA->getProblemaID()) != NULL) & ( ($b = $EjecucionesB->getProblemaID()) != NULL) ){
				$sql .= " problemaID >= ? AND problemaID <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " problemaID = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $EjecucionesA->getConcursoID()) != NULL) & ( ($b = $EjecucionesB->getConcursoID()) != NULL) ){
				$sql .= " concursoID >= ? AND concursoID <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " concursoID = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $EjecucionesA->getGuid()) != NULL) & ( ($b = $EjecucionesB->getGuid()) != NULL) ){
				$sql .= " guid >= ? AND guid <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " guid = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $EjecucionesA->getLenguaje()) != NULL) & ( ($b = $EjecucionesB->getLenguaje()) != NULL) ){
				$sql .= " lenguaje >= ? AND lenguaje <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " lenguaje = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $EjecucionesA->getEstado()) != NULL) & ( ($b = $EjecucionesB->getEstado()) != NULL) ){
				$sql .= " estado >= ? AND estado <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " estado = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $EjecucionesA->getVeredicto()) != NULL) & ( ($b = $EjecucionesB->getVeredicto()) != NULL) ){
				$sql .= " veredicto >= ? AND veredicto <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " veredicto = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $EjecucionesA->getTiempo()) != NULL) & ( ($b = $EjecucionesB->getTiempo()) != NULL) ){
				$sql .= " tiempo >= ? AND tiempo <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " tiempo = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $EjecucionesA->getMemoria()) != NULL) & ( ($b = $EjecucionesB->getMemoria()) != NULL) ){
				$sql .= " memoria >= ? AND memoria <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " memoria = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $EjecucionesA->getPuntuacion()) != NULL) & ( ($b = $EjecucionesB->getPuntuacion()) != NULL) ){
				$sql .= " puntuacion >= ? AND puntuacion <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " puntuacion = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $EjecucionesA->getIp()) != NULL) & ( ($b = $EjecucionesB->getIp()) != NULL) ){
				$sql .= " ip >= ? AND ip <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " ip = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $EjecucionesA->getFecha()) != NULL) & ( ($b = $EjecucionesB->getFecha()) != NULL) ){
				$sql .= " fecha >= ? AND fecha <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " fecha = ? AND"; 
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
    		array_push( $ar, new Ejecuciones($foo));
		}
		return $ar;
	}


	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto Ejecuciones suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param Ejecuciones [$Ejecuciones] El objeto de tipo Ejecuciones a eliminar
	  **/
	public static final function delete( &$Ejecuciones )
	{
		if(self::getByPK($Ejecuciones->getEjecucionID()) === NULL) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Ejecuciones WHERE  ejecucionID = ?;";
		$params = array( $Ejecuciones->getEjecucionID() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
