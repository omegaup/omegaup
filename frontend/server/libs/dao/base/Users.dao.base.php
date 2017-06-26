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
abstract class UsersDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Users`.`user_id`, `Users`.`username`, `Users`.`facebook_user_id`, `Users`.`password`, `Users`.`main_email_id`, `Users`.`name`, `Users`.`solved`, `Users`.`submissions`, `Users`.`country_id`, `Users`.`state_id`, `Users`.`school_id`, `Users`.`scholar_degree`, `Users`.`language_id`, `Users`.`graduation_date`, `Users`.`birth_date`, `Users`.`last_access`, `Users`.`verified`, `Users`.`verification_id`, `Users`.`reset_digest`, `Users`.`reset_sent_at`, `Users`.`recruitment_optin`, `Users`.`in_mailing_list`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Users} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Users [$Users] El objeto de tipo Users
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(Users $Users) {
        if (!is_null(self::getByPK($Users->user_id))) {
            return UsersDAOBase::update($Users);
        } else {
            return UsersDAOBase::create($Users);
        }
    }

    /**
     * Obtener {@link Users} por llave primaria.
     *
     * Este metodo cargara un objeto {@link Users} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link Users Un objeto del tipo {@link Users}. NULL si no hay tal registro.
     */
    final public static function getByPK($user_id) {
        if (is_null($user_id)) {
            return null;
        }
        $sql = 'SELECT `Users`.`user_id`, `Users`.`username`, `Users`.`facebook_user_id`, `Users`.`password`, `Users`.`main_email_id`, `Users`.`name`, `Users`.`solved`, `Users`.`submissions`, `Users`.`country_id`, `Users`.`state_id`, `Users`.`school_id`, `Users`.`scholar_degree`, `Users`.`language_id`, `Users`.`graduation_date`, `Users`.`birth_date`, `Users`.`last_access`, `Users`.`verified`, `Users`.`verification_id`, `Users`.`reset_digest`, `Users`.`reset_sent_at`, `Users`.`recruitment_optin`, `Users`.`in_mailing_list` FROM Users WHERE (user_id = ?) LIMIT 1;';
        $params = [$user_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new Users($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link Users}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link Users}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Users`.`user_id`, `Users`.`username`, `Users`.`facebook_user_id`, `Users`.`password`, `Users`.`main_email_id`, `Users`.`name`, `Users`.`solved`, `Users`.`submissions`, `Users`.`country_id`, `Users`.`state_id`, `Users`.`school_id`, `Users`.`scholar_degree`, `Users`.`language_id`, `Users`.`graduation_date`, `Users`.`birth_date`, `Users`.`last_access`, `Users`.`verified`, `Users`.`verification_id`, `Users`.`reset_digest`, `Users`.`reset_sent_at`, `Users`.`recruitment_optin`, `Users`.`in_mailing_list` from Users';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . mysqli_real_escape_string($conn->_connectionID, $orden) . '` ' . ($tipo_de_orden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $columnas_por_pagina) . ', ' . (int)$columnas_por_pagina;
        }
        $rs = $conn->Execute($sql);
        $allData = [];
        foreach ($rs as $row) {
            $allData[] = new Users($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Users} de la base de datos.
      * Consiste en buscar todos los objetos que coinciden con las variables permanentes instanciadas de objeto pasado como argumento.
      * Aquellas variables que tienen valores NULL seran excluidos en busca de criterios.
      *
      * <code>
      *   // Ejemplo de uso - buscar todos los clientes que tengan limite de credito igual a 20000
      *   $cliente = new Cliente();
      *   $cliente->setLimiteCredito('20000');
      *   $resultados = ClienteDAO::search($cliente);
      *
      *   foreach ($resultados as $c){
      *       echo $c->nombre . '<br>';
      *   }
      * </code>
      * @static
      * @param Users [$Users] El objeto de tipo Users
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Users, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Users instanceof Users)) {
            $Users = new Users($Users);
        }

        $clauses = [];
        $params = [];
        if (!is_null($Users->user_id)) {
            $clauses[] = '`user_id` = ?';
            $params[] = $Users->user_id;
        }
        if (!is_null($Users->username)) {
            $clauses[] = '`username` = ?';
            $params[] = $Users->username;
        }
        if (!is_null($Users->facebook_user_id)) {
            $clauses[] = '`facebook_user_id` = ?';
            $params[] = $Users->facebook_user_id;
        }
        if (!is_null($Users->password)) {
            $clauses[] = '`password` = ?';
            $params[] = $Users->password;
        }
        if (!is_null($Users->main_email_id)) {
            $clauses[] = '`main_email_id` = ?';
            $params[] = $Users->main_email_id;
        }
        if (!is_null($Users->name)) {
            $clauses[] = '`name` = ?';
            $params[] = $Users->name;
        }
        if (!is_null($Users->solved)) {
            $clauses[] = '`solved` = ?';
            $params[] = $Users->solved;
        }
        if (!is_null($Users->submissions)) {
            $clauses[] = '`submissions` = ?';
            $params[] = $Users->submissions;
        }
        if (!is_null($Users->country_id)) {
            $clauses[] = '`country_id` = ?';
            $params[] = $Users->country_id;
        }
        if (!is_null($Users->state_id)) {
            $clauses[] = '`state_id` = ?';
            $params[] = $Users->state_id;
        }
        if (!is_null($Users->school_id)) {
            $clauses[] = '`school_id` = ?';
            $params[] = $Users->school_id;
        }
        if (!is_null($Users->scholar_degree)) {
            $clauses[] = '`scholar_degree` = ?';
            $params[] = $Users->scholar_degree;
        }
        if (!is_null($Users->language_id)) {
            $clauses[] = '`language_id` = ?';
            $params[] = $Users->language_id;
        }
        if (!is_null($Users->graduation_date)) {
            $clauses[] = '`graduation_date` = ?';
            $params[] = $Users->graduation_date;
        }
        if (!is_null($Users->birth_date)) {
            $clauses[] = '`birth_date` = ?';
            $params[] = $Users->birth_date;
        }
        if (!is_null($Users->last_access)) {
            $clauses[] = '`last_access` = ?';
            $params[] = $Users->last_access;
        }
        if (!is_null($Users->verified)) {
            $clauses[] = '`verified` = ?';
            $params[] = $Users->verified;
        }
        if (!is_null($Users->verification_id)) {
            $clauses[] = '`verification_id` = ?';
            $params[] = $Users->verification_id;
        }
        if (!is_null($Users->reset_digest)) {
            $clauses[] = '`reset_digest` = ?';
            $params[] = $Users->reset_digest;
        }
        if (!is_null($Users->reset_sent_at)) {
            $clauses[] = '`reset_sent_at` = ?';
            $params[] = $Users->reset_sent_at;
        }
        if (!is_null($Users->recruitment_optin)) {
            $clauses[] = '`recruitment_optin` = ?';
            $params[] = $Users->recruitment_optin;
        }
        if (!is_null($Users->in_mailing_list)) {
            $clauses[] = '`in_mailing_list` = ?';
            $params[] = $Users->in_mailing_list;
        }
        global $conn;
        if (!is_null($likeColumns)) {
            foreach ($likeColumns as $column => $value) {
                $escapedValue = mysqli_real_escape_string($conn->_connectionID, $value);
                $clauses[] = "`{$column}` LIKE '%{$escapedValue}%'";
            }
        }
        if (sizeof($clauses) == 0) {
            return self::getAll();
        }
        $sql = 'SELECT `Users`.`user_id`, `Users`.`username`, `Users`.`facebook_user_id`, `Users`.`password`, `Users`.`main_email_id`, `Users`.`name`, `Users`.`solved`, `Users`.`submissions`, `Users`.`country_id`, `Users`.`state_id`, `Users`.`school_id`, `Users`.`scholar_degree`, `Users`.`language_id`, `Users`.`graduation_date`, `Users`.`birth_date`, `Users`.`last_access`, `Users`.`verified`, `Users`.`verification_id`, `Users`.`reset_digest`, `Users`.`reset_sent_at`, `Users`.`recruitment_optin`, `Users`.`in_mailing_list` FROM `Users`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . mysqli_real_escape_string($conn->_connectionID, $orderBy) . '` ' . ($orden == 'DESC' ? 'DESC' : 'ASC');
        }
        // Add LIMIT offset, rowcount if rowcount is set
        if (!is_null($rowcount)) {
            $sql .= ' LIMIT '. (int)$offset . ', ' . (int)$rowcount;
        }
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new Users($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param Users [$Users] El objeto de tipo Users a actualizar.
      */
    final private static function update(Users $Users) {
        $sql = 'UPDATE `Users` SET `username` = ?, `facebook_user_id` = ?, `password` = ?, `main_email_id` = ?, `name` = ?, `solved` = ?, `submissions` = ?, `country_id` = ?, `state_id` = ?, `school_id` = ?, `scholar_degree` = ?, `language_id` = ?, `graduation_date` = ?, `birth_date` = ?, `last_access` = ?, `verified` = ?, `verification_id` = ?, `reset_digest` = ?, `reset_sent_at` = ?, `recruitment_optin` = ?, `in_mailing_list` = ? WHERE `user_id` = ?;';
        $params = [
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
            $Users->verification_id,
            $Users->reset_digest,
            $Users->reset_sent_at,
            $Users->recruitment_optin,
            $Users->in_mailing_list,
            $Users->user_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Users suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto Users dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param Users [$Users] El objeto de tipo Users a crear.
     */
    final private static function create(Users $Users) {
        if (is_null($Users->solved)) {
            $Users->solved = '0';
        }
        if (is_null($Users->submissions)) {
            $Users->submissions = '0';
        }
        if (is_null($Users->last_access)) {
            $Users->last_access = gmdate('Y-m-d H:i:s');
        }
        if (is_null($Users->verified)) {
            $Users->verified = false;
        }
        if (is_null($Users->in_mailing_list)) {
            $Users->in_mailing_list = false;
        }
        $sql = 'INSERT INTO Users (`user_id`, `username`, `facebook_user_id`, `password`, `main_email_id`, `name`, `solved`, `submissions`, `country_id`, `state_id`, `school_id`, `scholar_degree`, `language_id`, `graduation_date`, `birth_date`, `last_access`, `verified`, `verification_id`, `reset_digest`, `reset_sent_at`, `recruitment_optin`, `in_mailing_list`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
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
            $Users->verification_id,
            $Users->reset_digest,
            $Users->reset_sent_at,
            $Users->recruitment_optin,
            $Users->in_mailing_list,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Users->user_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Buscar por rango.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Users} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Users}.
     *
     * Aquellas variables que tienen valores NULL seran excluidos en la busqueda (los valores 0 y false no son tomados como NULL) .
     * No es necesario ordenar los objetos criterio, asi como tambien es posible mezclar atributos.
     * Si algun atributo solo esta especificado en solo uno de los objetos de criterio se buscara que los resultados conicidan exactamente en ese campo.
     *
     * <code>
     *   // Ejemplo de uso - buscar todos los clientes que tengan limite de credito
     *   // mayor a 2000 y menor a 5000. Y que tengan un descuento del 50%.
     *   $cr1 = new Cliente();
     *   $cr1->limite_credito = "2000";
     *   $cr1->descuento = "50";
     *
     *   $cr2 = new Cliente();
     *   $cr2->limite_credito = "5000";
     *   $resultados = ClienteDAO::byRange($cr1, $cr2);
     *
     *   foreach($resultados as $c ){
     *       echo $c->nombre . "<br>";
     *   }
     * </code>
     * @static
     * @param Users [$Users] El objeto de tipo Users
     * @param Users [$Users] El objeto de tipo Users
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(Users $UsersA, Users $UsersB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $UsersA->user_id;
        $b = $UsersB->user_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`user_id` >= ? AND `user_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`user_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->username;
        $b = $UsersB->username;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`username` >= ? AND `username` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`username` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->facebook_user_id;
        $b = $UsersB->facebook_user_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`facebook_user_id` >= ? AND `facebook_user_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`facebook_user_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->password;
        $b = $UsersB->password;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`password` >= ? AND `password` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`password` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->main_email_id;
        $b = $UsersB->main_email_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`main_email_id` >= ? AND `main_email_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`main_email_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->name;
        $b = $UsersB->name;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`name` >= ? AND `name` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`name` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->solved;
        $b = $UsersB->solved;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`solved` >= ? AND `solved` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`solved` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->submissions;
        $b = $UsersB->submissions;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`submissions` >= ? AND `submissions` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`submissions` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->country_id;
        $b = $UsersB->country_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`country_id` >= ? AND `country_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`country_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->state_id;
        $b = $UsersB->state_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`state_id` >= ? AND `state_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`state_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->school_id;
        $b = $UsersB->school_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`school_id` >= ? AND `school_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`school_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->scholar_degree;
        $b = $UsersB->scholar_degree;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`scholar_degree` >= ? AND `scholar_degree` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`scholar_degree` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->language_id;
        $b = $UsersB->language_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`language_id` >= ? AND `language_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`language_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->graduation_date;
        $b = $UsersB->graduation_date;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`graduation_date` >= ? AND `graduation_date` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`graduation_date` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->birth_date;
        $b = $UsersB->birth_date;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`birth_date` >= ? AND `birth_date` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`birth_date` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->last_access;
        $b = $UsersB->last_access;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`last_access` >= ? AND `last_access` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`last_access` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->verified;
        $b = $UsersB->verified;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`verified` >= ? AND `verified` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`verified` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->verification_id;
        $b = $UsersB->verification_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`verification_id` >= ? AND `verification_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`verification_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->reset_digest;
        $b = $UsersB->reset_digest;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`reset_digest` >= ? AND `reset_digest` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`reset_digest` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->reset_sent_at;
        $b = $UsersB->reset_sent_at;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`reset_sent_at` >= ? AND `reset_sent_at` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`reset_sent_at` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->recruitment_optin;
        $b = $UsersB->recruitment_optin;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`recruitment_optin` >= ? AND `recruitment_optin` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`recruitment_optin` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $UsersA->in_mailing_list;
        $b = $UsersB->in_mailing_list;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`in_mailing_list` >= ? AND `in_mailing_list` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`in_mailing_list` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Users`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new Users($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto Users suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param Users [$Users] El objeto de tipo Users a eliminar
     */
    final public static function delete(Users $Users) {
        if (is_null(self::getByPK($Users->user_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `Users` WHERE user_id = ?;';
        $params = [$Users->user_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
