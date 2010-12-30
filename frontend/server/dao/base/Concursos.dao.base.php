<?php
/** Concursos Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link Concursos }. 
  * @author Alan Gonzalez <alan@caffeina.mx> 
  * @abstract
  * @package openjudge
  * 
  */
abstract class ConcursosDAOBase extends DAO
{

	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link Concursos} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param Concursos [$Concursos] El objeto de tipo Concursos
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Concursos )
	{
		if( self::getByPK(  $Concursos->getConcursoID() ) === NULL )
		{
			try{ return ConcursosDAOBase::create( $Concursos) ; } catch(Exception $e){ throw $e; }
		}else{
			try{ return ConcursosDAOBase::update( $Concursos) ; } catch(Exception $e){ throw $e; }
		}
	}


	/**
	  *	Obtener {@link Concursos} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link Concursos} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link Concursos Un objeto del tipo {@link Concursos}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $concursoID )
	{
		$sql = "SELECT * FROM Concursos WHERE (concursoID = ? ) LIMIT 1;";
		$params = array(  $concursoID );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
		return new Concursos( $rs );
	}


	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link Concursos}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link Concursos}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Concursos";
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
    		array_push( $allData, new Concursos($foo));
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Concursos} de la base de datos. 
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
	  * @param Concursos [$Concursos] El objeto de tipo Concursos
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Concursos , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Concursos WHERE ("; 
		$val = array();
		if( $Concursos->getConcursoID() != NULL){
			$sql .= " concursoID = ? AND";
			array_push( $val, $Concursos->getConcursoID() );
		}

		if( $Concursos->getTitulo() != NULL){
			$sql .= " titulo = ? AND";
			array_push( $val, $Concursos->getTitulo() );
		}

		if( $Concursos->getDescripcion() != NULL){
			$sql .= " descripcion = ? AND";
			array_push( $val, $Concursos->getDescripcion() );
		}

		if( $Concursos->getEstado() != NULL){
			$sql .= " estado = ? AND";
			array_push( $val, $Concursos->getEstado() );
		}

		if( $Concursos->getInicio() != NULL){
			$sql .= " inicio = ? AND";
			array_push( $val, $Concursos->getInicio() );
		}

		if( $Concursos->getFinal() != NULL){
			$sql .= " final = ? AND";
			array_push( $val, $Concursos->getFinal() );
		}

		if( $Concursos->getEstilo() != NULL){
			$sql .= " estilo = ? AND";
			array_push( $val, $Concursos->getEstilo() );
		}

		if( $Concursos->getCreador() != NULL){
			$sql .= " creador = ? AND";
			array_push( $val, $Concursos->getCreador() );
		}

		$sql = substr($sql, 0, -3) . " )";
		if( $orderBy !== null ){
		    $sql .= " order by " . $orderBy . " " . $orden ;
		
		}
		global $conn;
		$rs = $conn->Execute($sql, $val);
		$ar = array();
		foreach ($rs as $foo) {
    		array_push( $ar, new Concursos($foo));
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
	  * @param Concursos [$Concursos] El objeto de tipo Concursos a actualizar.
	  **/
	private static final function update( $Concursos )
	{
		$sql = "UPDATE Concursos SET  titulo = ?, descripcion = ?, estado = ?, inicio = ?, final = ?, estilo = ?, creador = ? WHERE  concursoID = ?;";
		$params = array( 
			$Concursos->getTitulo(), 
			$Concursos->getDescripcion(), 
			$Concursos->getEstado(), 
			$Concursos->getInicio(), 
			$Concursos->getFinal(), 
			$Concursos->getEstilo(), 
			$Concursos->getCreador(), 
			$Concursos->getConcursoID(), );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		return $conn->Affected_Rows();
	}


	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto Concursos suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto Concursos dentro de la misma transaccion.
	  *	
	  * @internal private information for advanced developers only
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param Concursos [$Concursos] El objeto de tipo Concursos a crear.
	  **/
	private static final function create( &$Concursos )
	{
		$sql = "INSERT INTO Concursos ( concursoID, titulo, descripcion, estado, inicio, final, estilo, creador ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?);";
		$params = array( 
			$Concursos->getConcursoID(), 
			$Concursos->getTitulo(), 
			$Concursos->getDescripcion(), 
			$Concursos->getEstado(), 
			$Concursos->getInicio(), 
			$Concursos->getFinal(), 
			$Concursos->getEstilo(), 
			$Concursos->getCreador(), 
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
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Concursos} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Concursos}.
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
	  * @param Concursos [$Concursos] El objeto de tipo Concursos
	  * @param Concursos [$Concursos] El objeto de tipo Concursos
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $ConcursosA , $ConcursosB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Concursos WHERE ("; 
		$val = array();
		if( (($a = $ConcursosA->getConcursoID()) != NULL) & ( ($b = $ConcursosB->getConcursoID()) != NULL) ){
				$sql .= " concursoID >= ? AND concursoID <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " concursoID = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ConcursosA->getTitulo()) != NULL) & ( ($b = $ConcursosB->getTitulo()) != NULL) ){
				$sql .= " titulo >= ? AND titulo <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " titulo = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ConcursosA->getDescripcion()) != NULL) & ( ($b = $ConcursosB->getDescripcion()) != NULL) ){
				$sql .= " descripcion >= ? AND descripcion <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " descripcion = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ConcursosA->getEstado()) != NULL) & ( ($b = $ConcursosB->getEstado()) != NULL) ){
				$sql .= " estado >= ? AND estado <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " estado = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ConcursosA->getInicio()) != NULL) & ( ($b = $ConcursosB->getInicio()) != NULL) ){
				$sql .= " inicio >= ? AND inicio <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " inicio = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ConcursosA->getFinal()) != NULL) & ( ($b = $ConcursosB->getFinal()) != NULL) ){
				$sql .= " final >= ? AND final <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " final = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ConcursosA->getEstilo()) != NULL) & ( ($b = $ConcursosB->getEstilo()) != NULL) ){
				$sql .= " estilo >= ? AND estilo <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " estilo = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ConcursosA->getCreador()) != NULL) & ( ($b = $ConcursosB->getCreador()) != NULL) ){
				$sql .= " creador >= ? AND creador <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " creador = ? AND"; 
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
    		array_push( $ar, new Concursos($foo));
		}
		return $ar;
	}


	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto Concursos suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param Concursos [$Concursos] El objeto de tipo Concursos a eliminar
	  **/
	public static final function delete( &$Concursos )
	{
		if(self::getByPK($Concursos->getConcursoID()) === NULL) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Concursos WHERE  concursoID = ?;";
		$params = array( $Concursos->getConcursoID() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
