<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Users Data Access Object (DAO) Base.
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Users }.
  * @access public
  * @abstract
  *
  */
abstract class UsersDAOBase extends DAO
{
	/**
	  *	Guardar registros.
	  *
	  *	Este metodo guarda el estado actual del objeto {@link Users} pasado en la base de datos. La llave
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param Users [$Users] El objeto de tipo Users
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( $Users )
	{
		if (!is_null(self::getByPK( $Users->getUserId() )))
		{
			return UsersDAOBase::update( $Users);
		} else {
			return UsersDAOBase::create( $Users);
		}
	}

	/**
	  *	Obtener {@link Users} por llave primaria.
	  *
	  * Este metodo cargara un objeto {@link Users} de la base de datos
	  * usando sus llaves primarias.
	  *
	  *	@static
	  * @return @link Users Un objeto del tipo {@link Users}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $user_id )
	{
		if(  is_null( $user_id )  ){ return NULL; }
		$sql = "SELECT * FROM Users WHERE (user_id = ? ) LIMIT 1;";
		$params = array(  $user_id );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0) return NULL;
		$foo = new Users( $rs );
		return $foo;
	}

	/**
	  *	Obtener todas las filas.
	  *
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link Users}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link Users}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Users";
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
			$bar = new Users($foo);
    		array_push( $allData, $bar);
		}
		return $allData;
	}

	/**
	  *	Buscar registros.
	  *
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Users} de la base de datos.
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
	  * @param Users [$Users] El objeto de tipo Users
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Users , $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = NULL, $likeColumns = NULL)
	{
		if (!($Users instanceof Users)) {
			return self::search(new Users($Users));
		}

		$sql = "SELECT * from Users WHERE (";
		$val = array();
		if (!is_null( $Users->getUserId())) {
			$sql .= " `user_id` = ? AND";
			array_push( $val, $Users->getUserId() );
		}
		if (!is_null( $Users->getUsername())) {
			$sql .= " `username` = ? AND";
			array_push( $val, $Users->getUsername() );
		}
		if (!is_null( $Users->getFacebookUserId())) {
			$sql .= " `facebook_user_id` = ? AND";
			array_push( $val, $Users->getFacebookUserId() );
		}
		if (!is_null( $Users->getPassword())) {
			$sql .= " `password` = ? AND";
			array_push( $val, $Users->getPassword() );
		}
		if (!is_null( $Users->getMainEmailId())) {
			$sql .= " `main_email_id` = ? AND";
			array_push( $val, $Users->getMainEmailId() );
		}
		if (!is_null( $Users->getName())) {
			$sql .= " `name` = ? AND";
			array_push( $val, $Users->getName() );
		}
		if (!is_null( $Users->getSolved())) {
			$sql .= " `solved` = ? AND";
			array_push( $val, $Users->getSolved() );
		}
		if (!is_null( $Users->getSubmissions())) {
			$sql .= " `submissions` = ? AND";
			array_push( $val, $Users->getSubmissions() );
		}
		if (!is_null( $Users->getCountryId())) {
			$sql .= " `country_id` = ? AND";
			array_push( $val, $Users->getCountryId() );
		}
		if (!is_null( $Users->getStateId())) {
			$sql .= " `state_id` = ? AND";
			array_push( $val, $Users->getStateId() );
		}
		if (!is_null( $Users->getSchoolId())) {
			$sql .= " `school_id` = ? AND";
			array_push( $val, $Users->getSchoolId() );
		}
		if (!is_null( $Users->getScholarDegree())) {
			$sql .= " `scholar_degree` = ? AND";
			array_push( $val, $Users->getScholarDegree() );
		}
		if (!is_null( $Users->getLanguageId())) {
			$sql .= " `language_id` = ? AND";
			array_push( $val, $Users->getLanguageId() );
		}
		if (!is_null( $Users->getGraduationDate())) {
			$sql .= " `graduation_date` = ? AND";
			array_push( $val, $Users->getGraduationDate() );
		}
		if (!is_null( $Users->getBirthDate())) {
			$sql .= " `birth_date` = ? AND";
			array_push( $val, $Users->getBirthDate() );
		}
		if (!is_null( $Users->getLastAccess())) {
			$sql .= " `last_access` = ? AND";
			array_push( $val, $Users->getLastAccess() );
		}
		if (!is_null( $Users->getVerified())) {
			$sql .= " `verified` = ? AND";
			array_push( $val, $Users->getVerified() );
		}
		if (!is_null( $Users->getInterviewer())) {
			$sql .= " `interviewer` = ? AND";
			array_push( $val, $Users->getInterviewer() );
		}
		if (!is_null( $Users->getVerificationId())) {
			$sql .= " `verification_id` = ? AND";
			array_push( $val, $Users->getVerificationId() );
		}
		if (!is_null( $Users->getResetDigest())) {
			$sql .= " `reset_digest` = ? AND";
			array_push( $val, $Users->getResetDigest() );
		}
		if (!is_null( $Users->getResetSentAt())) {
			$sql .= " `reset_sent_at` = ? AND";
			array_push( $val, $Users->getResetSentAt() );
		}
		if (!is_null( $Users->getRecruitmentOptin())) {
			$sql .= " `recruitment_optin` = ? AND";
			array_push( $val, $Users->getRecruitmentOptin() );
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
			$bar =  new Users($foo);
			array_push( $ar,$bar);
		}
		return $ar;
	}

	/**
	  *	Actualizar registros.
	  *
	  * @return Filas afectadas
	  * @param Users [$Users] El objeto de tipo Users a actualizar.
	  **/
	private static final function update($Users)
	{
		$sql = "UPDATE Users SET  `username` = ?, `facebook_user_id` = ?, `password` = ?, `main_email_id` = ?, `name` = ?, `solved` = ?, `submissions` = ?, `country_id` = ?, `state_id` = ?, `school_id` = ?, `scholar_degree` = ?, `language_id` = ?, `graduation_date` = ?, `birth_date` = ?, `last_access` = ?, `verified` = ?, `verification_id` = ?, `reset_digest` = ?, `reset_sent_at` = ?, `recruitment_optin` = ? WHERE  `user_id` = ?;";
		$params = array(
			$Users->getUsername(),
			$Users->getFacebookUserId(),
			$Users->getPassword(),
			$Users->getMainEmailId(),
			$Users->getName(),
			$Users->getSolved(),
			$Users->getSubmissions(),
			$Users->getCountryId(),
			$Users->getStateId(),
			$Users->getSchoolId(),
			$Users->getScholarDegree(),
			$Users->getLanguageId(),
			$Users->getGraduationDate(),
			$Users->getBirthDate(),
			$Users->getLastAccess(),
			$Users->getVerified(),
			$Users->getInterviewer(),
			$Users->getVerificationId(),
			$Users->getResetDigest(),
			$Users->getResetSentAt(),
			$Users->getRecruitmentOptin(),
			$Users->getUserId(), );
		global $conn;
		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}

	/**
	  *	Crear registros.
	  *
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los
	  * contenidos del objeto Users suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave
	  * primaria generada en el objeto Users dentro de la misma transaccion.
	  *
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param Users [$Users] El objeto de tipo Users a crear.
	  **/
	private static final function create( $Users )
	{
		if (is_null($Users->solved)) $Users->solved = '0';
		if (is_null($Users->submissions)) $Users->submissions = '0';
		if (is_null($Users->last_access)) $Users->last_access = gmdate('Y-m-d H:i:s');
		if (is_null($Users->verified)) $Users->verified = FALSE;
<<<<<<< HEAD
		if (is_null($Users->interviewer)) $Users->interviewer = FALSE;
		$sql = "INSERT INTO Users ( `user_id`, `username`, `facebook_user_id`, `password`, `main_email_id`, `name`, `solved`, `submissions`, `country_id`, `state_id`, `school_id`, `scholar_degree`, `language_id`, `graduation_date`, `birth_date`, `last_access`, `verified`, `interviewer`, `verification_id`, `reset_digest`, `reset_sent_at` ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
=======
		$sql = "INSERT INTO Users ( `user_id`, `username`, `facebook_user_id`, `password`, `main_email_id`, `name`, `solved`, `submissions`, `country_id`, `state_id`, `school_id`, `scholar_degree`, `language_id`, `graduation_date`, `birth_date`, `last_access`, `verified`, `verification_id`, `reset_digest`, `reset_sent_at`, `recruitment_optin` ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
>>>>>>> master
		$params = array(
			$Users->user_id,
			$Users->username,
			$Users->facebook_user_id,
			$Users->password,
			$Users->main_email_id,
			$Users->name,
			$Users->solved,
			$Users->submissions,
			$Users->country_id,
			$Users->state_id,
			$Users->school_id,
			$Users->scholar_degree,
			$Users->language_id,
			$Users->graduation_date,
			$Users->birth_date,
			$Users->last_access,
			$Users->verified,
			$Users->interviewer,
			$Users->verification_id,
			$Users->reset_digest,
			$Users->reset_sent_at,
			$Users->recruitment_optin,
		 );
		global $conn;
		$conn->Execute($sql, $params);
		$ar = $conn->Affected_Rows();
		if($ar == 0) return 0;
 		$Users->user_id = $conn->Insert_ID();

		return $ar;
	}

	/**
	  *	Buscar por rango.
	  *
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Users} de la base de datos siempre y cuando
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Users}.
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
	  * @param Users [$Users] El objeto de tipo Users
	  * @param Users [$Users] El objeto de tipo Users
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $UsersA , $UsersB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Users WHERE (";
		$val = array();
		if( ( !is_null (($a = $UsersA->getUserId()) ) ) & ( ! is_null ( ($b = $UsersB->getUserId()) ) ) ){
				$sql .= " `user_id` >= ? AND `user_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `user_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getUsername()) ) ) & ( ! is_null ( ($b = $UsersB->getUsername()) ) ) ){
				$sql .= " `username` >= ? AND `username` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `username` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getFacebookUserId()) ) ) & ( ! is_null ( ($b = $UsersB->getFacebookUserId()) ) ) ){
				$sql .= " `facebook_user_id` >= ? AND `facebook_user_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `facebook_user_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getPassword()) ) ) & ( ! is_null ( ($b = $UsersB->getPassword()) ) ) ){
				$sql .= " `password` >= ? AND `password` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `password` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getMainEmailId()) ) ) & ( ! is_null ( ($b = $UsersB->getMainEmailId()) ) ) ){
				$sql .= " `main_email_id` >= ? AND `main_email_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `main_email_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getName()) ) ) & ( ! is_null ( ($b = $UsersB->getName()) ) ) ){
				$sql .= " `name` >= ? AND `name` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `name` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getSolved()) ) ) & ( ! is_null ( ($b = $UsersB->getSolved()) ) ) ){
				$sql .= " `solved` >= ? AND `solved` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `solved` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getSubmissions()) ) ) & ( ! is_null ( ($b = $UsersB->getSubmissions()) ) ) ){
				$sql .= " `submissions` >= ? AND `submissions` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `submissions` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getCountryId()) ) ) & ( ! is_null ( ($b = $UsersB->getCountryId()) ) ) ){
				$sql .= " `country_id` >= ? AND `country_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `country_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getStateId()) ) ) & ( ! is_null ( ($b = $UsersB->getStateId()) ) ) ){
				$sql .= " `state_id` >= ? AND `state_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `state_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getSchoolId()) ) ) & ( ! is_null ( ($b = $UsersB->getSchoolId()) ) ) ){
				$sql .= " `school_id` >= ? AND `school_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `school_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getScholarDegree()) ) ) & ( ! is_null ( ($b = $UsersB->getScholarDegree()) ) ) ){
				$sql .= " `scholar_degree` >= ? AND `scholar_degree` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `scholar_degree` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getLanguageId()) ) ) & ( ! is_null ( ($b = $UsersB->getLanguageId()) ) ) ){
				$sql .= " `language_id` >= ? AND `language_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `language_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getGraduationDate()) ) ) & ( ! is_null ( ($b = $UsersB->getGraduationDate()) ) ) ){
				$sql .= " `graduation_date` >= ? AND `graduation_date` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `graduation_date` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getBirthDate()) ) ) & ( ! is_null ( ($b = $UsersB->getBirthDate()) ) ) ){
				$sql .= " `birth_date` >= ? AND `birth_date` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `birth_date` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getLastAccess()) ) ) & ( ! is_null ( ($b = $UsersB->getLastAccess()) ) ) ){
				$sql .= " `last_access` >= ? AND `last_access` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `last_access` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getVerified()) ) ) & ( ! is_null ( ($b = $UsersB->getVerified()) ) ) ){
				$sql .= " `verified` >= ? AND `verified` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `verified` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getInterviewer()) ) ) & ( ! is_null ( ($b = $UsersB->getInterviewer()) ) ) ){
				$sql .= " `interviewer` >= ? AND `interviewer` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `interviewer` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getVerificationId()) ) ) & ( ! is_null ( ($b = $UsersB->getVerificationId()) ) ) ){
				$sql .= " `verification_id` >= ? AND `verification_id` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `verification_id` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getResetDigest()) ) ) & ( ! is_null ( ($b = $UsersB->getResetDigest()) ) ) ){
				$sql .= " `reset_digest` >= ? AND `reset_digest` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `reset_digest` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getResetSentAt()) ) ) & ( ! is_null ( ($b = $UsersB->getResetSentAt()) ) ) ){
				$sql .= " `reset_sent_at` >= ? AND `reset_sent_at` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `reset_sent_at` = ? AND";
			$a = is_null ( $a ) ? $b : $a;
			array_push( $val, $a);
		}

		if( ( !is_null (($a = $UsersA->getRecruitmentOptin()) ) ) & ( ! is_null ( ($b = $UsersB->getRecruitmentOptin()) ) ) ){
				$sql .= " `recruitment_optin` >= ? AND `recruitment_optin` <= ? AND";
				array_push( $val, min($a,$b));
				array_push( $val, max($a,$b));
		}elseif( !is_null ( $a ) || !is_null ( $b ) ){
			$sql .= " `recruitment_optin` = ? AND";
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
			array_push( $ar, $bar = new Users($row));
		}
		return $ar;
	}

	/**
	  *	Eliminar registros.
	  *
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto Users suministrado. Una vez que se ha suprimido un objeto, este no
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param Users [$Users] El objeto de tipo Users a eliminar
	  **/
	public static final function delete( $Users )
	{
		if( is_null( self::getByPK($Users->getUserId()) ) ) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Users WHERE  user_id = ?;";
		$params = array( $Users->getUserId() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}
}
