<?php
/** Usuarios Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link Usuarios }. 
  * @author Alan Gonzalez <alan@caffeina.mx> 
  * @abstract
  * @package openjudge
  * 
  */
abstract class UsuariosDAOBase extends DAO
{

	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link Usuarios} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param Usuarios [$Usuarios] El objeto de tipo Usuarios
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Usuarios )
	{
		if( self::getByPK(  $Usuarios->getUserID() ) === NULL )
		{
			try{ return UsuariosDAOBase::create( $Usuarios) ; } catch(Exception $e){ throw $e; }
		}else{
			try{ return UsuariosDAOBase::update( $Usuarios) ; } catch(Exception $e){ throw $e; }
		}
	}


	/**
	  *	Obtener {@link Usuarios} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link Usuarios} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link Usuarios Un objeto del tipo {@link Usuarios}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $userID )
	{
		$sql = "SELECT * FROM Usuarios WHERE (userID = ? ) LIMIT 1;";
		$params = array(  $userID );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
		return new Usuarios( $rs );
	}


	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link Usuarios}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link Usuarios}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Usuarios";
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
    		array_push( $allData, new Usuarios($foo));
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Usuarios} de la base de datos. 
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
	  * @param Usuarios [$Usuarios] El objeto de tipo Usuarios
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Usuarios , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Usuarios WHERE ("; 
		$val = array();
		if( $Usuarios->getUserID() != NULL){
			$sql .= " userID = ? AND";
			array_push( $val, $Usuarios->getUserID() );
		}

		if( $Usuarios->getUsername() != NULL){
			$sql .= " username = ? AND";
			array_push( $val, $Usuarios->getUsername() );
		}

		if( $Usuarios->getPassword() != NULL){
			$sql .= " password = ? AND";
			array_push( $val, $Usuarios->getPassword() );
		}

		if( $Usuarios->getEmail() != NULL){
			$sql .= " email = ? AND";
			array_push( $val, $Usuarios->getEmail() );
		}

		if( $Usuarios->getNombre() != NULL){
			$sql .= " nombre = ? AND";
			array_push( $val, $Usuarios->getNombre() );
		}

		if( $Usuarios->getResueltos() != NULL){
			$sql .= " resueltos = ? AND";
			array_push( $val, $Usuarios->getResueltos() );
		}

		if( $Usuarios->getIntentados() != NULL){
			$sql .= " intentados = ? AND";
			array_push( $val, $Usuarios->getIntentados() );
		}

		if( $Usuarios->getPais() != NULL){
			$sql .= " pais = ? AND";
			array_push( $val, $Usuarios->getPais() );
		}

		if( $Usuarios->getEstado() != NULL){
			$sql .= " estado = ? AND";
			array_push( $val, $Usuarios->getEstado() );
		}

		if( $Usuarios->getEscuela() != NULL){
			$sql .= " escuela = ? AND";
			array_push( $val, $Usuarios->getEscuela() );
		}

		if( $Usuarios->getGradoestudios() != NULL){
			$sql .= " gradoestudios = ? AND";
			array_push( $val, $Usuarios->getGradoestudios() );
		}

		if( $Usuarios->getGraduacion() != NULL){
			$sql .= " graduacion = ? AND";
			array_push( $val, $Usuarios->getGraduacion() );
		}

		if( $Usuarios->getFechaNacimiento() != NULL){
			$sql .= " fechaNacimiento = ? AND";
			array_push( $val, $Usuarios->getFechaNacimiento() );
		}

		if( $Usuarios->getUltimoAcceso() != NULL){
			$sql .= " ultimoAcceso = ? AND";
			array_push( $val, $Usuarios->getUltimoAcceso() );
		}

		$sql = substr($sql, 0, -3) . " )";
		if( $orderBy !== null ){
		    $sql .= " order by " . $orderBy . " " . $orden ;
		
		}
		global $conn;
		$rs = $conn->Execute($sql, $val);
		$ar = array();
		foreach ($rs as $foo) {
    		array_push( $ar, new Usuarios($foo));
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
	  * @param Usuarios [$Usuarios] El objeto de tipo Usuarios a actualizar.
	  **/
	private static final function update( $Usuarios )
	{
		$sql = "UPDATE Usuarios SET  username = ?, password = ?, email = ?, nombre = ?, resueltos = ?, intentados = ?, pais = ?, estado = ?, escuela = ?, gradoestudios = ?, graduacion = ?, fechaNacimiento = ?, ultimoAcceso = ? WHERE  userID = ?;";
		$params = array( 
			$Usuarios->getUsername(), 
			$Usuarios->getPassword(), 
			$Usuarios->getEmail(), 
			$Usuarios->getNombre(), 
			$Usuarios->getResueltos(), 
			$Usuarios->getIntentados(), 
			$Usuarios->getPais(), 
			$Usuarios->getEstado(), 
			$Usuarios->getEscuela(), 
			$Usuarios->getGradoestudios(), 
			$Usuarios->getGraduacion(), 
			$Usuarios->getFechaNacimiento(), 
			$Usuarios->getUltimoAcceso(), 
			$Usuarios->getUserID(), );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		return $conn->Affected_Rows();
	}


	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto Usuarios suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto Usuarios dentro de la misma transaccion.
	  *	
	  * @internal private information for advanced developers only
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param Usuarios [$Usuarios] El objeto de tipo Usuarios a crear.
	  **/
	private static final function create( &$Usuarios )
	{
		$sql = "INSERT INTO Usuarios ( userID, username, password, email, nombre, resueltos, intentados, pais, estado, escuela, gradoestudios, graduacion, fechaNacimiento, ultimoAcceso ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
		$params = array( 
			$Usuarios->getUserID(), 
			$Usuarios->getUsername(), 
			$Usuarios->getPassword(), 
			$Usuarios->getEmail(), 
			$Usuarios->getNombre(), 
			$Usuarios->getResueltos(), 
			$Usuarios->getIntentados(), 
			$Usuarios->getPais(), 
			$Usuarios->getEstado(), 
			$Usuarios->getEscuela(), 
			$Usuarios->getGradoestudios(), 
			$Usuarios->getGraduacion(), 
			$Usuarios->getFechaNacimiento(), 
			$Usuarios->getUltimoAcceso(), 
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
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Usuarios} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Usuarios}.
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
	  * @param Usuarios [$Usuarios] El objeto de tipo Usuarios
	  * @param Usuarios [$Usuarios] El objeto de tipo Usuarios
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $UsuariosA , $UsuariosB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Usuarios WHERE ("; 
		$val = array();
		if( (($a = $UsuariosA->getUserID()) != NULL) & ( ($b = $UsuariosB->getUserID()) != NULL) ){
				$sql .= " userID >= ? AND userID <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " userID = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsuariosA->getUsername()) != NULL) & ( ($b = $UsuariosB->getUsername()) != NULL) ){
				$sql .= " username >= ? AND username <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " username = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsuariosA->getPassword()) != NULL) & ( ($b = $UsuariosB->getPassword()) != NULL) ){
				$sql .= " password >= ? AND password <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " password = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsuariosA->getEmail()) != NULL) & ( ($b = $UsuariosB->getEmail()) != NULL) ){
				$sql .= " email >= ? AND email <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " email = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsuariosA->getNombre()) != NULL) & ( ($b = $UsuariosB->getNombre()) != NULL) ){
				$sql .= " nombre >= ? AND nombre <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " nombre = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsuariosA->getResueltos()) != NULL) & ( ($b = $UsuariosB->getResueltos()) != NULL) ){
				$sql .= " resueltos >= ? AND resueltos <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " resueltos = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsuariosA->getIntentados()) != NULL) & ( ($b = $UsuariosB->getIntentados()) != NULL) ){
				$sql .= " intentados >= ? AND intentados <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " intentados = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsuariosA->getPais()) != NULL) & ( ($b = $UsuariosB->getPais()) != NULL) ){
				$sql .= " pais >= ? AND pais <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " pais = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsuariosA->getEstado()) != NULL) & ( ($b = $UsuariosB->getEstado()) != NULL) ){
				$sql .= " estado >= ? AND estado <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " estado = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsuariosA->getEscuela()) != NULL) & ( ($b = $UsuariosB->getEscuela()) != NULL) ){
				$sql .= " escuela >= ? AND escuela <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " escuela = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsuariosA->getGradoestudios()) != NULL) & ( ($b = $UsuariosB->getGradoestudios()) != NULL) ){
				$sql .= " gradoestudios >= ? AND gradoestudios <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " gradoestudios = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsuariosA->getGraduacion()) != NULL) & ( ($b = $UsuariosB->getGraduacion()) != NULL) ){
				$sql .= " graduacion >= ? AND graduacion <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " graduacion = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsuariosA->getFechaNacimiento()) != NULL) & ( ($b = $UsuariosB->getFechaNacimiento()) != NULL) ){
				$sql .= " fechaNacimiento >= ? AND fechaNacimiento <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " fechaNacimiento = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $UsuariosA->getUltimoAcceso()) != NULL) & ( ($b = $UsuariosB->getUltimoAcceso()) != NULL) ){
				$sql .= " ultimoAcceso >= ? AND ultimoAcceso <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " ultimoAcceso = ? AND"; 
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
    		array_push( $ar, new Usuarios($foo));
		}
		return $ar;
	}


	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto Usuarios suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param Usuarios [$Usuarios] El objeto de tipo Usuarios a eliminar
	  **/
	public static final function delete( &$Usuarios )
	{
		if(self::getByPK($Usuarios->getUserID()) === NULL) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Usuarios WHERE  userID = ?;";
		$params = array( $Usuarios->getUserID() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
