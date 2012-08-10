<?php
/** Contests Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link Contests }. 
  * @author alanboy
  * @access private
  * @abstract
  * @package docs
  * 
  */
abstract class ContestsDAOBase extends DAO
{

        public static $useDAOCache = true;
        
		private static $loadedRecords = array();

		private static function recordExists(  $contest_id ){
			$pk = "";
			$pk .= $contest_id . "-";
			return array_key_exists ( $pk , self::$loadedRecords );
		}
		private static function pushRecord( $inventario,  $contest_id){
			$pk = "";
			$pk .= $contest_id . "-";
			self::$loadedRecords [$pk] = $inventario;
		}
		private static function getRecord(  $contest_id ){
			$pk = "";
			$pk .= $contest_id . "-";
			return self::$loadedRecords[$pk];
		}
	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link Contests} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param Contests [$Contests] El objeto de tipo Contests
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Contests )
	{
		if(  self::getByPK(  $Contests->getContestId() ) !== NULL )
		{
			try{ return ContestsDAOBase::update( $Contests) ; } catch(Exception $e){ throw $e; }
		}else{
			try{ return ContestsDAOBase::create( $Contests) ; } catch(Exception $e){ throw $e; }
		}
	}


	/**
	  *	Obtener {@link Contests} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link Contests} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link Contests Un objeto del tipo {@link Contests}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $contest_id )
	{
		if(self::recordExists(  $contest_id) && self::$useDAOCache == true){
			return self::getRecord( $contest_id );
		}
		$sql = "SELECT * FROM Contests WHERE (contest_id = ? ) LIMIT 1;";
		$params = array(  $contest_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
			$foo = new Contests( $rs );
			self::pushRecord( $foo,  $contest_id );
			return $foo;
	}
        



	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link Contests}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link Contests}.
	  **/
        
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC', $columnas = NULL )
	{
                // Implode array of columns to a coma-separated string               
                $columns_str = is_null($columnas) ? "*" : implode(",", $columnas);
                
                $sql = "SELECT ".$columns_str." from Contests";
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
                    $bar = new Contests($foo);
                    array_push( $allData, $bar);
                    
                    //contest_id
                    self::pushRecord( $bar, $foo["contest_id"] );
                }
                return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Contests} de la base de datos. 
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
	  * @param Contests [$Contests] El objeto de tipo Contests
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Contests , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Contests WHERE ("; 
		$val = array();
		if( $Contests->getContestId() != NULL){
			$sql .= " contest_id = ? AND";
			array_push( $val, $Contests->getContestId() );
		}

		if( $Contests->getTitle() != NULL){
			$sql .= " title = ? AND";
			array_push( $val, $Contests->getTitle() );
		}

		if( $Contests->getDescription() != NULL){
			$sql .= " description = ? AND";
			array_push( $val, $Contests->getDescription() );
		}

		if( $Contests->getStartTime() != NULL){
			$sql .= " start_time = ? AND";
			array_push( $val, $Contests->getStartTime() );
		}

		if( $Contests->getFinishTime() != NULL){
			$sql .= " finish_time = ? AND";
			array_push( $val, $Contests->getFinishTime() );
		}

		if( $Contests->getWindowLength() != NULL){
			$sql .= " window_length = ? AND";
			array_push( $val, $Contests->getWindowLength() );
		}

		if( $Contests->getDirectorId() != NULL){
			$sql .= " director_id = ? AND";
			array_push( $val, $Contests->getDirectorId() );
		}

		if( $Contests->getRerunId() != NULL){
			$sql .= " rerun_id = ? AND";
			array_push( $val, $Contests->getRerunId() );
		}

		if( $Contests->getPublic() != NULL){
			$sql .= " public = ? AND";
			array_push( $val, $Contests->getPublic() );
		}

		if( $Contests->getAlias() != NULL){
			$sql .= " alias = ? AND";
			array_push( $val, $Contests->getAlias() );
		}

		if( $Contests->getScoreboard() != NULL){
			$sql .= " scoreboard = ? AND";
			array_push( $val, $Contests->getScoreboard() );
		}

		if( $Contests->getPartialScore() != NULL){
			$sql .= " partial_score = ? AND";
			array_push( $val, $Contests->getPartialScore() );
		}

		if( $Contests->getSubmissionsGap() != NULL){
			$sql .= " submissions_gap = ? AND";
			array_push( $val, $Contests->getSubmissionsGap() );
		}

		if( $Contests->getFeedback() != NULL){
			$sql .= " feedback = ? AND";
			array_push( $val, $Contests->getFeedback() );
		}

		if( $Contests->getPenalty() != NULL){
			$sql .= " penalty = ? AND";
			array_push( $val, $Contests->getPenalty() );
		}

		if( $Contests->getPenaltyTimeStart () != NULL){
			$sql .= " penalty_time_start = ? AND";
			array_push( $val, $Contests->getPenaltyTimeStart() );
		}
                
                if( $Contests->getPenaltyCalcPolicy() != NULL){
			$sql .= " penalty_calc_policy = ? AND";
			array_push( $val, $Contests->getPenaltyCalcPolicy() );
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
			$bar =  new Contests($foo);
    		array_push( $ar,$bar);
    		self::pushRecord( $bar, $foo["contest_id"] );
		}
		return $ar;
	}


	/**
	  *	Actualizar registros.
	  *	
	  * Este metodo es un metodo de ayuda para uso interno. Se ejecutara todas las manipulaciones
	  * en la base de datos que estan dadas en el objeto pasado.No se haran consultas SELECT 
	  * aqui, sin embargo. El valor de retorno indica cu�ntas filas se vieron afectadas.
	  *	
	  * @internal private information for advanced developers only
	  * @return Filas afectadas o un string con la descripcion del error
	  * @param Contests [$Contests] El objeto de tipo Contests a actualizar.
	  **/
	private static final function update( $Contests )
	{
		$sql = "UPDATE Contests SET  title = ?, description = ?, start_time = ?, finish_time = ?, window_length = ?, director_id = ?, rerun_id = ?, public = ?, alias = ?, scoreboard = ?, partial_score = ?, submissions_gap = ?, feedback = ?, penalty = ?, penalty_time_start = ?, points_decay_factor = ?, penalty_calc_policy = ? WHERE  contest_id = ?;";
		$params = array( 
			$Contests->getTitle(), 
			$Contests->getDescription(), 
			$Contests->getStartTime(), 
			$Contests->getFinishTime(), 
			$Contests->getWindowLength(), 
			$Contests->getDirectorId(), 
			$Contests->getRerunId(), 
			$Contests->getPublic(), 
			$Contests->getAlias(), 
			$Contests->getScoreboard(), 
			$Contests->getPartialScore(), 
			$Contests->getSubmissionsGap(), 
			$Contests->getFeedback(), 
			$Contests->getPenalty(), 
			$Contests->getPenaltyTimeStart(),                         
                        $Contests->getPointsDecayFactor(),
                        $Contests->getPenaltyCalcPolicy(),
			$Contests->getContestId()
                        
                        );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		return $conn->Affected_Rows();
	}


	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto Contests suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto Contests dentro de la misma transaccion.
	  *	
	  * @internal private information for advanced developers only
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param Contests [$Contests] El objeto de tipo Contests a crear.
	  **/
	private static final function create( &$Contests )
	{
		$sql = "INSERT INTO Contests ( contest_id, title, description, start_time, finish_time, window_length, director_id, rerun_id, public, alias, scoreboard, partial_score, submissions_gap, feedback, penalty, penalty_time_start, points_decay_factor, penalty_calc_policy) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
		$params = array( 
			$Contests->getContestId(), 
			$Contests->getTitle(), 
			$Contests->getDescription(), 
			$Contests->getStartTime(), 
			$Contests->getFinishTime(), 
			$Contests->getWindowLength(), 
			$Contests->getDirectorId(), 
			$Contests->getRerunId(), 
			$Contests->getPublic(), 
			$Contests->getAlias(), 
			$Contests->getScoreboard(), 
			$Contests->getPartialScore(), 
			$Contests->getSubmissionsGap(), 
			$Contests->getFeedback(), 
			$Contests->getPenalty(), 
			$Contests->getPenaltyTimeStart(),                        
                        $Contests->getPointsDecayFactor(),
                        $Contests->getPenaltyCalcPolicy()                    
		 );                
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		$ar = $conn->Affected_Rows();
		if($ar == 0) return 0;
		/* save autoincremented value on obj */  $Contests->setContestId( $conn->Insert_ID() ); /*  */ 
		return $ar;
	}


	/**
	  *	Buscar por rango.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Contests} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Contests}.
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
	  * @param Contests [$Contests] El objeto de tipo Contests
	  * @param Contests [$Contests] El objeto de tipo Contests
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $ContestsA , $ContestsB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Contests WHERE ("; 
		$val = array();
		if( (($a = $ContestsA->getContestId()) != NULL) & ( ($b = $ContestsB->getContestId()) != NULL) ){
				$sql .= " contest_id >= ? AND contest_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " contest_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ContestsA->getTitle()) != NULL) & ( ($b = $ContestsB->getTitle()) != NULL) ){
				$sql .= " title >= ? AND title <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " title = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ContestsA->getDescription()) != NULL) & ( ($b = $ContestsB->getDescription()) != NULL) ){
				$sql .= " description >= ? AND description <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " description = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ContestsA->getStartTime()) != NULL) & ( ($b = $ContestsB->getStartTime()) != NULL) ){
				$sql .= " start_time >= ? AND start_time <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " start_time = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ContestsA->getFinishTime()) != NULL) & ( ($b = $ContestsB->getFinishTime()) != NULL) ){
				$sql .= " finish_time >= ? AND finish_time <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " finish_time = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ContestsA->getWindowLength()) != NULL) & ( ($b = $ContestsB->getWindowLength()) != NULL) ){
				$sql .= " window_length >= ? AND window_length <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " window_length = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ContestsA->getDirectorId()) != NULL) & ( ($b = $ContestsB->getDirectorId()) != NULL) ){
				$sql .= " director_id >= ? AND director_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " director_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ContestsA->getRerunId()) != NULL) & ( ($b = $ContestsB->getRerunId()) != NULL) ){
				$sql .= " rerun_id >= ? AND rerun_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " rerun_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ContestsA->getPublic()) != NULL) & ( ($b = $ContestsB->getPublic()) != NULL) ){
				$sql .= " public >= ? AND public <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " public = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ContestsA->getAlias()) != NULL) & ( ($b = $ContestsB->getAlias()) != NULL) ){
				$sql .= " alias >= ? AND alias <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " alias = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ContestsA->getScoreboard()) != NULL) & ( ($b = $ContestsB->getScoreboard()) != NULL) ){
				$sql .= " scoreboard >= ? AND scoreboard <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " scoreboard = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ContestsA->getPartialScore()) != NULL) & ( ($b = $ContestsB->getPartialScore()) != NULL) ){
				$sql .= " partial_score >= ? AND partial_score <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " partial_score = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ContestsA->getSubmissionsGap()) != NULL) & ( ($b = $ContestsB->getSubmissionsGap()) != NULL) ){
				$sql .= " submissions_gap >= ? AND submissions_gap <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " submissions_gap = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ContestsA->getFeedback()) != NULL) & ( ($b = $ContestsB->getFeedback()) != NULL) ){
				$sql .= " feedback >= ? AND feedback <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " feedback = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ContestsA->getPenalty()) != NULL) & ( ($b = $ContestsB->getPenalty()) != NULL) ){
				$sql .= " penalty >= ? AND penalty <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " penalty = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $ContestsA->getPenaltyTimeStart()) != NULL) & ( ($b = $ContestsB->getPenaltyTimeStart()) != NULL) ){
				$sql .= " penalty_time_start >= ? AND penalty_time_start <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " penalty_time_start = ? AND"; 
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
    		array_push( $ar, new Contests($foo));
		}
		return $ar;
	}


	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto Contests suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param Contests [$Contests] El objeto de tipo Contests a eliminar
	  **/
	public static final function delete( &$Contests )
	{
		if(self::getByPK($Contests->getContestId()) === NULL) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Contests WHERE  contest_id = ?;";
		$params = array( $Contests->getContestId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
