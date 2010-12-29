<?php
/** Problemas Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link Problemas }. 
  * @author Alan Gonzalez <alan@caffeina.mx> 
  * @abstract
  * @package openjudge
  * 
  */
abstract class ProblemasDAOBase extends DAO
{

	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link Problemas} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param Problemas [$Problemas] El objeto de tipo Problemas
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Problemas )
	{
		if( self::getByPK(  $Problemas->getProblemaID() ) === NULL )
		{
			try{ return ProblemasDAOBase::create( $Problemas) ; } catch(Exception $e){ throw $e; }
		}else{
			try{ return ProblemasDAOBase::update( $Problemas) ; } catch(Exception $e){ throw $e; }
		}
	}


	/**
	  *	Obtener {@link Problemas} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link Problemas} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link Problemas Un objeto del tipo {@link Problemas}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $problemaID )
	{
		$sql = "SELECT * FROM Problemas WHERE (problemaID = ? ) LIMIT 1;";
		$params = array(  $problemaID );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
		return new Problemas( $rs );
	}


	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link Problemas}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link Problemas}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Problemas";
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
    		array_push( $allData, new Problemas($foo));
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Problemas} de la base de datos. 
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
	  * @param Problemas [$Problemas] El objeto de tipo Problemas
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Problemas , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Problemas WHERE ("; 
		$val = array();
		if( $Problemas->getProblemaID() != NULL){
			$sql .= " problemaID = ? AND";
			array_push( $val, $Problemas->getProblemaID() );
		}

		if( $Problemas->getPublico() != NULL){
			$sql .= " publico = ? AND";
			array_push( $val, $Problemas->getPublico() );
		}

		if( $Problemas->getAutor() != NULL){
			$sql .= " autor = ? AND";
			array_push( $val, $Problemas->getAutor() );
		}

		if( $Problemas->getTitulo() != NULL){
			$sql .= " titulo = ? AND";
			array_push( $val, $Problemas->getTitulo() );
		}

		if( $Problemas->getAlias() != NULL){
			$sql .= " alias = ? AND";
			array_push( $val, $Problemas->getAlias() );
		}

		if( $Problemas->getValidador() != NULL){
			$sql .= " validador = ? AND";
			array_push( $val, $Problemas->getValidador() );
		}

		if( $Problemas->getServidor() != NULL){
			$sql .= " servidor = ? AND";
			array_push( $val, $Problemas->getServidor() );
		}

		if( $Problemas->getIdRemoto() != NULL){
			$sql .= " id_remoto = ? AND";
			array_push( $val, $Problemas->getIdRemoto() );
		}

		if( $Problemas->getTiempoLimite() != NULL){
			$sql .= " tiempoLimite = ? AND";
			array_push( $val, $Problemas->getTiempoLimite() );
		}

		if( $Problemas->getMemoriaLimite() != NULL){
			$sql .= " memoriaLimite = ? AND";
			array_push( $val, $Problemas->getMemoriaLimite() );
		}

		if( $Problemas->getVistas() != NULL){
			$sql .= " vistas = ? AND";
			array_push( $val, $Problemas->getVistas() );
		}

		if( $Problemas->getEnvios() != NULL){
			$sql .= " envios = ? AND";
			array_push( $val, $Problemas->getEnvios() );
		}

		if( $Problemas->getAceptados() != NULL){
			$sql .= " aceptados = ? AND";
			array_push( $val, $Problemas->getAceptados() );
		}

		if( $Problemas->getDificultad() != NULL){
			$sql .= " dificultad = ? AND";
			array_push( $val, $Problemas->getDificultad() );
		}

		$sql = substr($sql, 0, -3) . " )";
		if( $orderBy !== null ){
		    $sql .= " order by " . $orderBy . " " . $orden ;
		
		}
		global $conn;
		$rs = $conn->Execute($sql, $val);
		$ar = array();
		foreach ($rs as $foo) {
    		array_push( $ar, new Problemas($foo));
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
	  * @param Problemas [$Problemas] El objeto de tipo Problemas a actualizar.
	  **/
	private static final function update( $Problemas )
	{
		$sql = "UPDATE Problemas SET  publico = ?, autor = ?, titulo = ?, alias = ?, validador = ?, servidor = ?, id_remoto = ?, tiempoLimite = ?, memoriaLimite = ?, vistas = ?, envios = ?, aceptados = ?, dificultad = ? WHERE  problemaID = ?;";
		$params = array( 
			$Problemas->getPublico(), 
			$Problemas->getAutor(), 
			$Problemas->getTitulo(), 
			$Problemas->getAlias(), 
			$Problemas->getValidador(), 
			$Problemas->getServidor(), 
			$Problemas->getIdRemoto(), 
			$Problemas->getTiempoLimite(), 
			$Problemas->getMemoriaLimite(), 
			$Problemas->getVistas(), 
			$Problemas->getEnvios(), 
			$Problemas->getAceptados(), 
			$Problemas->getDificultad(), 
			$Problemas->getProblemaID(), );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		return $conn->Affected_Rows();
	}


	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto Problemas suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto Problemas dentro de la misma transaccion.
	  *	
	  * @internal private information for advanced developers only
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param Problemas [$Problemas] El objeto de tipo Problemas a crear.
	  **/
	private static final function create( &$Problemas )
	{
		$sql = "INSERT INTO Problemas ( problemaID, publico, autor, titulo, alias, validador, servidor, id_remoto, tiempoLimite, memoriaLimite, vistas, envios, aceptados, dificultad ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
		$params = array( 
			$Problemas->getProblemaID(), 
			$Problemas->getPublico(), 
			$Problemas->getAutor(), 
			$Problemas->getTitulo(), 
			$Problemas->getAlias(), 
			$Problemas->getValidador(), 
			$Problemas->getServidor(), 
			$Problemas->getIdRemoto(), 
			$Problemas->getTiempoLimite(), 
			$Problemas->getMemoriaLimite(), 
			$Problemas->getVistas(), 
			$Problemas->getEnvios(), 
			$Problemas->getAceptados(), 
			$Problemas->getDificultad(), 
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
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Problemas} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Problemas}.
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
	  * @param Problemas [$Problemas] El objeto de tipo Problemas
	  * @param Problemas [$Problemas] El objeto de tipo Problemas
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $ProblemasA , $ProblemasB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Problemas WHERE ("; 
		$val = array();
		if( (($a = $ProblemasA->getProblemaID()) != NULL) & ( ($b = $ProblemasB->getProblemaID()) != NULL) ){
				$sql .= " problemaID >= ? AND problemaID <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " problemaID = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemasA->getPublico()) != NULL) & ( ($b = $ProblemasB->getPublico()) != NULL) ){
				$sql .= " publico >= ? AND publico <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " publico = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemasA->getAutor()) != NULL) & ( ($b = $ProblemasB->getAutor()) != NULL) ){
				$sql .= " autor >= ? AND autor <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " autor = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemasA->getTitulo()) != NULL) & ( ($b = $ProblemasB->getTitulo()) != NULL) ){
				$sql .= " titulo >= ? AND titulo <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " titulo = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemasA->getAlias()) != NULL) & ( ($b = $ProblemasB->getAlias()) != NULL) ){
				$sql .= " alias >= ? AND alias <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " alias = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemasA->getValidador()) != NULL) & ( ($b = $ProblemasB->getValidador()) != NULL) ){
				$sql .= " validador >= ? AND validador <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " validador = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemasA->getServidor()) != NULL) & ( ($b = $ProblemasB->getServidor()) != NULL) ){
				$sql .= " servidor >= ? AND servidor <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " servidor = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemasA->getIdRemoto()) != NULL) & ( ($b = $ProblemasB->getIdRemoto()) != NULL) ){
				$sql .= " id_remoto >= ? AND id_remoto <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " id_remoto = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemasA->getTiempoLimite()) != NULL) & ( ($b = $ProblemasB->getTiempoLimite()) != NULL) ){
				$sql .= " tiempoLimite >= ? AND tiempoLimite <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " tiempoLimite = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemasA->getMemoriaLimite()) != NULL) & ( ($b = $ProblemasB->getMemoriaLimite()) != NULL) ){
				$sql .= " memoriaLimite >= ? AND memoriaLimite <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " memoriaLimite = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemasA->getVistas()) != NULL) & ( ($b = $ProblemasB->getVistas()) != NULL) ){
				$sql .= " vistas >= ? AND vistas <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " vistas = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemasA->getEnvios()) != NULL) & ( ($b = $ProblemasB->getEnvios()) != NULL) ){
				$sql .= " envios >= ? AND envios <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " envios = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemasA->getAceptados()) != NULL) & ( ($b = $ProblemasB->getAceptados()) != NULL) ){
				$sql .= " aceptados >= ? AND aceptados <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " aceptados = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ProblemasA->getDificultad()) != NULL) & ( ($b = $ProblemasB->getDificultad()) != NULL) ){
				$sql .= " dificultad >= ? AND dificultad <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " dificultad = ? AND"; 
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
    		array_push( $ar, new Problemas($foo));
		}
		return $ar;
	}


	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto Problemas suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param Problemas [$Problemas] El objeto de tipo Problemas a eliminar
	  **/
	public static final function delete( &$Problemas )
	{
		if(self::getByPK($Problemas->getProblemaID()) === NULL) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Problemas WHERE  problemaID = ?;";
		$params = array( $Problemas->getProblemaID() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
