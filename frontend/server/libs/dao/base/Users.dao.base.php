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
abstract class UsersDAOBase {
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
        $sql = 'SELECT `Users`.`user_id`, `Users`.`username`, `Users`.`facebook_user_id`, `Users`.`password`, `Users`.`main_email_id`, `Users`.`main_identity_id`, `Users`.`name`, `Users`.`country_id`, `Users`.`state_id`, `Users`.`school_id`, `Users`.`scholar_degree`, `Users`.`language_id`, `Users`.`graduation_date`, `Users`.`birth_date`, `Users`.`gender`, `Users`.`verified`, `Users`.`verification_id`, `Users`.`reset_digest`, `Users`.`reset_sent_at`, `Users`.`hide_problem_tags`, `Users`.`in_mailing_list`, `Users`.`is_private`, `Users`.`preferred_language` FROM Users WHERE (user_id = ?) LIMIT 1;';
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
        $sql = 'SELECT `Users`.`user_id`, `Users`.`username`, `Users`.`facebook_user_id`, `Users`.`password`, `Users`.`main_email_id`, `Users`.`main_identity_id`, `Users`.`name`, `Users`.`country_id`, `Users`.`state_id`, `Users`.`school_id`, `Users`.`scholar_degree`, `Users`.`language_id`, `Users`.`graduation_date`, `Users`.`birth_date`, `Users`.`gender`, `Users`.`verified`, `Users`.`verification_id`, `Users`.`reset_digest`, `Users`.`reset_sent_at`, `Users`.`hide_problem_tags`, `Users`.`in_mailing_list`, `Users`.`is_private`, `Users`.`preferred_language` from Users';
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
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param Users [$Users] El objeto de tipo Users a actualizar.
      */
    final private static function update(Users $Users) {
        $sql = 'UPDATE `Users` SET `username` = ?, `facebook_user_id` = ?, `password` = ?, `main_email_id` = ?, `main_identity_id` = ?, `name` = ?, `country_id` = ?, `state_id` = ?, `school_id` = ?, `scholar_degree` = ?, `language_id` = ?, `graduation_date` = ?, `birth_date` = ?, `gender` = ?, `verified` = ?, `verification_id` = ?, `reset_digest` = ?, `reset_sent_at` = ?, `hide_problem_tags` = ?, `in_mailing_list` = ?, `is_private` = ?, `preferred_language` = ? WHERE `user_id` = ?;';
        $params = [
            $Users->username,
            $Users->facebook_user_id,
            $Users->password,
            $Users->main_email_id,
            $Users->main_identity_id,
            $Users->name,
            $Users->country_id,
            $Users->state_id,
            $Users->school_id,
            $Users->scholar_degree,
            $Users->language_id,
            $Users->graduation_date,
            $Users->birth_date,
            $Users->gender,
            $Users->verified,
            $Users->verification_id,
            $Users->reset_digest,
            $Users->reset_sent_at,
            $Users->hide_problem_tags,
            $Users->in_mailing_list,
            $Users->is_private,
            $Users->preferred_language,
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
        if (is_null($Users->verified)) {
            $Users->verified = '0';
        }
        if (is_null($Users->in_mailing_list)) {
            $Users->in_mailing_list = '0';
        }
        if (is_null($Users->is_private)) {
            $Users->is_private = '0';
        }
        $sql = 'INSERT INTO Users (`user_id`, `username`, `facebook_user_id`, `password`, `main_email_id`, `main_identity_id`, `name`, `country_id`, `state_id`, `school_id`, `scholar_degree`, `language_id`, `graduation_date`, `birth_date`, `gender`, `verified`, `verification_id`, `reset_digest`, `reset_sent_at`, `hide_problem_tags`, `in_mailing_list`, `is_private`, `preferred_language`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Users->user_id,
            $Users->username,
            $Users->facebook_user_id,
            $Users->password,
            $Users->main_email_id,
            $Users->main_identity_id,
            $Users->name,
            $Users->country_id,
            $Users->state_id,
            $Users->school_id,
            $Users->scholar_degree,
            $Users->language_id,
            $Users->graduation_date,
            $Users->birth_date,
            $Users->gender,
            $Users->verified,
            $Users->verification_id,
            $Users->reset_digest,
            $Users->reset_sent_at,
            $Users->hide_problem_tags,
            $Users->in_mailing_list,
            $Users->is_private,
            $Users->preferred_language,
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
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto Users suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @param Users [$Users] El objeto de tipo Users a eliminar
     */
    final public static function delete(Users $Users) {
        $sql = 'DELETE FROM `Users` WHERE user_id = ?;';
        $params = [$Users->user_id];
        global $conn;

        $conn->Execute($sql, $params);
        if ($conn->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }
}
