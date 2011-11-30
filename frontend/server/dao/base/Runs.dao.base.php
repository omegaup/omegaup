<?php
/** Runs Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link Runs }. 
  * @author alanboy
  * @access private
  * @abstract
  * @package docs
  * 
  */
abstract class RunsDAOBase extends DAO
{

		private static $loadedRecords = array();

		private static function recordExists(  $run_id ){
			$pk = "";
			$pk .= $run_id . "-";
			return array_key_exists ( $pk , self::$loadedRecords );
		}
		private static function pushRecord( $inventario,  $run_id){
			$pk = "";
			$pk .= $run_id . "-";
			self::$loadedRecords [$pk] = $inventario;
		}
		private static function getRecord(  $run_id ){
			$pk = "";
			$pk .= $run_id . "-";
			return self::$loadedRecords[$pk];
		}
	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link Runs} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param Runs [$Runs] El objeto de tipo Runs
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Runs )
	{
		if(  self::getByPK(  $Runs->getRunId() ) !== NULL )
		{
			try{ return RunsDAOBase::update( $Runs) ; } catch(Exception $e){ throw $e; }
		}else{
			try{ return RunsDAOBase::create( $Runs) ; } catch(Exception $e){ throw $e; }
		}
	}


	/**
	  *	Obtener {@link Runs} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link Runs} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link Runs Un objeto del tipo {@link Runs}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $run_id )
	{
		if(self::recordExists(  $run_id)){
			return self::getRecord( $run_id );
		}
		$sql = "SELECT * FROM Runs WHERE (run_id = ? ) LIMIT 1;";
		$params = array(  $run_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
			$foo = new Runs( $rs );
			self::pushRecord( $foo,  $run_id );
			return $foo;
	}


	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link Runs}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link Runs}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Runs";
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
			$bar = new Runs($foo);
    		array_push( $allData, $bar);
			//run_id
    		self::pushRecord( $bar, $foo["run_id"] );
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Runs} de la base de datos. 
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
	  * @param Runs [$Runs] El objeto de tipo Runs
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Runs , $orderBy = null, $orden = 'ASC', $columnas = NULL)
	{
                // Implode array of columns to a coma-separated string               
                $columns_str = is_null($columnas) ? "*" : implode(",", $columnas);
            
		$sql = "SELECT ".$columns_str."  from Runs WHERE ("; 
		$val = array();
		if( $Runs->getRunId() != NULL){
			$sql .= " run_id = ? AND";
			array_push( $val, $Runs->getRunId() );
		}

		if( $Runs->getUserId() != NULL){
			$sql .= " user_id = ? AND";
			array_push( $val, $Runs->getUserId() );
		}

		if( $Runs->getProblemId() != NULL){
			$sql .= " problem_id = ? AND";
			array_push( $val, $Runs->getProblemId() );
		}

		if( $Runs->getContestId() != NULL){
			$sql .= " contest_id = ? AND";
			array_push( $val, $Runs->getContestId() );
		}

		if( $Runs->getGuid() != NULL){
			$sql .= " guid = ? AND";
			array_push( $val, $Runs->getGuid() );
		}

		if( $Runs->getLanguage() != NULL){
			$sql .= " language = ? AND";
			array_push( $val, $Runs->getLanguage() );
		}

		if( $Runs->getStatus() != NULL){
			$sql .= " status = ? AND";
			array_push( $val, $Runs->getStatus() );
		}

		if( $Runs->getVeredict() != NULL){
			$sql .= " veredict = ? AND";
			array_push( $val, $Runs->getVeredict() );
		}

		if( $Runs->getRuntime() != NULL){
			$sql .= " runtime = ? AND";
			array_push( $val, $Runs->getRuntime() );
		}

		if( $Runs->getMemory() != NULL){
			$sql .= " memory = ? AND";
			array_push( $val, $Runs->getMemory() );
		}

		if( $Runs->getScore() != NULL){
			$sql .= " score = ? AND";
			array_push( $val, $Runs->getScore() );
		}

		if( $Runs->getContestScore() != NULL){
			$sql .= " contest_score = ? AND";
			array_push( $val, $Runs->getContestScore() );
		}

		if( $Runs->getIp() != NULL){
			$sql .= " ip = ? AND";
			array_push( $val, $Runs->getIp() );
		}

		if( $Runs->getTime() != NULL){
			$sql .= " time = ? AND";
			array_push( $val, $Runs->getTime() );
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
			$bar =  new Runs($foo);
    		array_push( $ar,$bar);
    		self::pushRecord( $bar, $foo["run_id"] );
		}
		return $ar;
	}
        
        /*
         *  SELECT DISTINCT
         * 
         */        
        public static final function distinct($Runs, $orderBy = null, $orden = 'ASC', $columns = NULL )
        {                        
            
            // Implode array of columns to a coma-separated string               
            $columns_str = is_null($columns) ? "*" : implode(",", $columns);
            
            // Build SQL statement
            $sql = "SELECT DISTINCT ".$columns_str." from Runs WHERE (";
            
            
            // Add WHERE part
            $val = array();
            if( $Runs->getRunId() != NULL){
                    $sql .= " run_id = ? AND";
                    array_push( $val, $Runs->getRunId() );
            }

            if( $Runs->getUserId() != NULL){
                    $sql .= " user_id = ? AND";
                    array_push( $val, $Runs->getUserId() );
            }

            if( $Runs->getProblemId() != NULL){
                    $sql .= " problem_id = ? AND";
                    array_push( $val, $Runs->getProblemId() );
            }

            if( $Runs->getContestId() != NULL){
                    $sql .= " contest_id = ? AND";
                    array_push( $val, $Runs->getContestId() );
            }

            if( $Runs->getGuid() != NULL){
                    $sql .= " guid = ? AND";
                    array_push( $val, $Runs->getGuid() );
            }

            if( $Runs->getLanguage() != NULL){
                    $sql .= " language = ? AND";
                    array_push( $val, $Runs->getLanguage() );
            }

            if( $Runs->getStatus() != NULL){
                    $sql .= " status = ? AND";
                    array_push( $val, $Runs->getStatus() );
            }

            if( $Runs->getVeredict() != NULL){
                    $sql .= " veredict = ? AND";
                    array_push( $val, $Runs->getVeredict() );
            }

            if( $Runs->getRuntime() != NULL){
                    $sql .= " runtime = ? AND";
                    array_push( $val, $Runs->getRuntime() );
            }

            if( $Runs->getMemory() != NULL){
                    $sql .= " memory = ? AND";
                    array_push( $val, $Runs->getMemory() );
            }

            if( $Runs->getScore() != NULL){
                    $sql .= " score = ? AND";
                    array_push( $val, $Runs->getScore() );
            }

            if( $Runs->getContestScore() != NULL){
                    $sql .= " contest_score = ? AND";
                    array_push( $val, $Runs->getContestScore() );
            }

            if( $Runs->getIp() != NULL){
                    $sql .= " ip = ? AND";
                    array_push( $val, $Runs->getIp() );
            }

            if( $Runs->getTime() != NULL){
                    $sql .= " time = ? AND";
                    array_push( $val, $Runs->getTime() );
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
                    $bar =  new Runs($foo);
            array_push( $ar,$bar);
            }
            
            return $ar;
            
        }
        
        /*
         *  GetAllRelevantUsers
         * 
         */
        public static final function GetAllRelevantUsers($contest_id)
        {
            // Build SQL statement
            $sql = "SELECT Users.user_id, username from Users INNER JOIN ( SELECT DISTINCT Runs.user_id from Runs WHERE ( Runs.contest_id = ? AND Runs.status = 'ready'  ) ) RunsContests ON Users.user_id = RunsContests.user_id ";
            $val = array($contest_id);
            
            global $conn;
            $rs = $conn->Execute($sql, $val);
            
            $ar = array();
            foreach ($rs as $foo) {
                    $bar =  new Users($foo);
            array_push( $ar,$bar);
            }
            
            return $ar;
            
            
        }
        
        /*
         * 
         * Get best run of a user
         * 
         */
        public static final function GetBestRun($contest_id, $problem_id, $user_id)
        {
            //Build SQL statement
            $sql = "SELECT contest_score, submit_delay from Runs where user_id = ? and contest_id = ? and problem_id = ? and status = 'ready' ORDER BY contest_score DESC, submit_delay ASC  LIMIT 1";
            $val = array($user_id, $contest_id, $problem_id);
            
            global $conn;
            $rs = $conn->GetRow($sql, $val);            
            
            $bar =  new Runs($rs);
            
            return $bar;
            
        }
        
        /*
         * 
         * Get last run of a user
     * 
         */
        public static final function GetLastRun($contest_id, $problem_id, $user_id)
        {
            //Build SQL statement
            $sql = "SELECT time from Runs where user_id = ? and contest_id = ? and problem_id = ? ORDER BY time DESC LIMIT 1";
            $val = array($user_id, $contest_id, $problem_id);
            
            global $conn;
            $rs = $conn->GetRow($sql, $val);            
            
            $bar =  new Runs($rs);
            
            return $bar;
            
        }
        
        public static final function IsRunInsideSubmissionGap($contest_id, $problem_id, $user_id)
        {
            // SQL Statement
            $sql = "SELECT IF (
                              ( (SELECT COUNT(time) from Runs where user_id = ? and contest_id = ? and problem_id = ?) =
                                0
                              )
                              OR (   
                                (SELECT UNIX_TIMESTAMP()) > 
                                (SELECT UNIX_TIMESTAMP(time) from Runs where user_id = ? and contest_id = ? and problem_id = ? ORDER BY time DESC LIMIT 1) 
                                    + ( SELECT submissions_gap FROM Contests WHERE contest_id = ? )
                              )
                              AND (
                                (SELECT UNIX_TIMESTAMP()) < 
                                (SELECT UNIX_TIMESTAMP(finish_time) from Contests where contest_id = ?)
                              )
                              ,1,0 ) As 'IsValid' ;";
            $val = array($user_id, $contest_id, $problem_id, $user_id, $contest_id, $problem_id, $contest_id, $contest_id);
            
            global $conn;
            $rs = $conn->GetRow($sql, $val); 
            
            if($rs["IsValid"] === '1') return true;
            
            return false;
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
	  * @param Runs [$Runs] El objeto de tipo Runs a actualizar.
	  **/
	private static final function update( $Runs )
	{
		$sql = "UPDATE Runs SET  user_id = ?, problem_id = ?, contest_id = ?, guid = ?, language = ?, status = ?, veredict = ?, runtime = ?, memory = ?, score = ?, contest_score = ?, ip = ?, time = ? WHERE  run_id = ?;";
		$params = array( 
			$Runs->getUserId(), 
			$Runs->getProblemId(), 
			$Runs->getContestId(), 
			$Runs->getGuid(), 
			$Runs->getLanguage(), 
			$Runs->getStatus(), 
			$Runs->getVeredict(), 
			$Runs->getRuntime(), 
			$Runs->getMemory(), 
			$Runs->getScore(), 
			$Runs->getContestScore(), 
			$Runs->getIp(), 
			$Runs->getTime(), 
			$Runs->getRunId(), );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		return $conn->Affected_Rows();
	}


	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto Runs suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto Runs dentro de la misma transaccion.
	  *	
	  * @internal private information for advanced developers only
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param Runs [$Runs] El objeto de tipo Runs a crear.
	  **/
	private static final function create( &$Runs )
	{
		$sql = "INSERT INTO Runs ( run_id, user_id, problem_id, contest_id, guid, language, status, veredict, runtime, memory, score, contest_score, ip, time, submit_delay ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
		$params = array( 
			$Runs->getRunId(), 
			$Runs->getUserId(), 
			$Runs->getProblemId(), 
			$Runs->getContestId(), 
			$Runs->getGuid(), 
			$Runs->getLanguage(), 
			$Runs->getStatus(), 
			$Runs->getVeredict(), 
			$Runs->getRuntime(), 
			$Runs->getMemory(), 
			$Runs->getScore(), 
			$Runs->getContestScore(), 
			$Runs->getIp(), 
			$Runs->getTime(), 
                        $Runs->getSubmitDelay() 
		 );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		$ar = $conn->Affected_Rows();
		if($ar == 0) return 0;
		/* save autoincremented value on obj */  $Runs->setRunId( $conn->Insert_ID() ); /*  */ 
		return $ar;
	}


	/**
	  *	Buscar por rango.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Runs} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Runs}.
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
	  * @param Runs [$Runs] El objeto de tipo Runs
	  * @param Runs [$Runs] El objeto de tipo Runs
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $RunsA , $RunsB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Runs WHERE ("; 
		$val = array();
		if( (($a = $RunsA->getRunId()) != NULL) & ( ($b = $RunsB->getRunId()) != NULL) ){
				$sql .= " run_id >= ? AND run_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " run_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $RunsA->getUserId()) != NULL) & ( ($b = $RunsB->getUserId()) != NULL) ){
				$sql .= " user_id >= ? AND user_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " user_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $RunsA->getProblemId()) != NULL) & ( ($b = $RunsB->getProblemId()) != NULL) ){
				$sql .= " problem_id >= ? AND problem_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " problem_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $RunsA->getContestId()) != NULL) & ( ($b = $RunsB->getContestId()) != NULL) ){
				$sql .= " contest_id >= ? AND contest_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " contest_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $RunsA->getGuid()) != NULL) & ( ($b = $RunsB->getGuid()) != NULL) ){
				$sql .= " guid >= ? AND guid <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " guid = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $RunsA->getLanguage()) != NULL) & ( ($b = $RunsB->getLanguage()) != NULL) ){
				$sql .= " language >= ? AND language <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " language = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $RunsA->getStatus()) != NULL) & ( ($b = $RunsB->getStatus()) != NULL) ){
				$sql .= " status >= ? AND status <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " status = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $RunsA->getVeredict()) != NULL) & ( ($b = $RunsB->getVeredict()) != NULL) ){
				$sql .= " veredict >= ? AND veredict <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " veredict = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $RunsA->getRuntime()) != NULL) & ( ($b = $RunsB->getRuntime()) != NULL) ){
				$sql .= " runtime >= ? AND runtime <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " runtime = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $RunsA->getMemory()) != NULL) & ( ($b = $RunsB->getMemory()) != NULL) ){
				$sql .= " memory >= ? AND memory <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " memory = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $RunsA->getScore()) != NULL) & ( ($b = $RunsB->getScore()) != NULL) ){
				$sql .= " score >= ? AND score <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " score = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $RunsA->getContestScore()) != NULL) & ( ($b = $RunsB->getContestScore()) != NULL) ){
				$sql .= " contest_score >= ? AND contest_score <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " contest_score = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $RunsA->getIp()) != NULL) & ( ($b = $RunsB->getIp()) != NULL) ){
				$sql .= " ip >= ? AND ip <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " ip = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $RunsA->getTime()) != NULL) & ( ($b = $RunsB->getTime()) != NULL) ){
				$sql .= " time >= ? AND time <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " time = ? AND"; 
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
    		array_push( $ar, new Runs($foo));
		}
		return $ar;
	}


	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto Runs suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param Runs [$Runs] El objeto de tipo Runs a eliminar
	  **/
	public static final function delete( &$Runs )
	{
		if(self::getByPK($Runs->getRunId()) === NULL) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Runs WHERE  run_id = ?;";
		$params = array( $Runs->getRunId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
