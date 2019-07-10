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
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Users}.
 * @access public
 * @abstract
 *
 */
abstract class UsersDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Users}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Users [$Users] El objeto de tipo Users
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(Users $Users) {
        if (is_null(self::getByPK($Users->user_id))) {
            return UsersDAOBase::create($Users);
        }
        return UsersDAOBase::update($Users);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param Users [$Users] El objeto de tipo Users a actualizar.
     */
    final public static function update(Users $Users) {
        $sql = 'UPDATE `Users` SET `username` = ?, `facebook_user_id` = ?, `password` = ?, `git_token` = ?, `main_email_id` = ?, `main_identity_id` = ?, `country_id` = ?, `state_id` = ?, `scholar_degree` = ?, `graduation_date` = ?, `birth_date` = ?, `verified` = ?, `verification_id` = ?, `reset_digest` = ?, `reset_sent_at` = ?, `hide_problem_tags` = ?, `in_mailing_list` = ?, `is_private` = ?, `preferred_language` = ? WHERE `user_id` = ?;';
        $params = [
            $Users->username,
            $Users->facebook_user_id,
            $Users->password,
            $Users->git_token,
            is_null($Users->main_email_id) ? null : (int)$Users->main_email_id,
            is_null($Users->main_identity_id) ? null : (int)$Users->main_identity_id,
            $Users->country_id,
            $Users->state_id,
            $Users->scholar_degree,
            $Users->graduation_date,
            $Users->birth_date,
            is_null($Users->verified) ? null : (int)$Users->verified,
            $Users->verification_id,
            $Users->reset_digest,
            $Users->reset_sent_at,
            is_null($Users->hide_problem_tags) ? null : (int)$Users->hide_problem_tags,
            is_null($Users->in_mailing_list) ? null : (int)$Users->in_mailing_list,
            is_null($Users->is_private) ? null : (int)$Users->is_private,
            $Users->preferred_language,
            is_null($Users->user_id) ? null : (int)$Users->user_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link Users} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Users} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link Users Un objeto del tipo {@link Users}. NULL si no hay tal registro.
     */
    final public static function getByPK($user_id) {
        if (is_null($user_id)) {
            return null;
        }
        $sql = 'SELECT `Users`.`user_id`, `Users`.`username`, `Users`.`facebook_user_id`, `Users`.`password`, `Users`.`git_token`, `Users`.`main_email_id`, `Users`.`main_identity_id`, `Users`.`country_id`, `Users`.`state_id`, `Users`.`scholar_degree`, `Users`.`graduation_date`, `Users`.`birth_date`, `Users`.`verified`, `Users`.`verification_id`, `Users`.`reset_digest`, `Users`.`reset_sent_at`, `Users`.`hide_problem_tags`, `Users`.`in_mailing_list`, `Users`.`is_private`, `Users`.`preferred_language` FROM Users WHERE (user_id = ?) LIMIT 1;';
        $params = [$user_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new Users($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Users suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
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

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo {@link Users}.
     * Este método consume una cantidad de memoria proporcional al número de
     * registros regresados, así que sólo debe usarse cuando la tabla en
     * cuestión es pequeña o se proporcionan parámetros para obtener un menor
     * número de filas.
     *
     * @static
     * @param $pagina Página a ver.
     * @param $filasPorPagina Filas por página.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link Users}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Users`.`user_id`, `Users`.`username`, `Users`.`facebook_user_id`, `Users`.`password`, `Users`.`git_token`, `Users`.`main_email_id`, `Users`.`main_identity_id`, `Users`.`country_id`, `Users`.`state_id`, `Users`.`scholar_degree`, `Users`.`graduation_date`, `Users`.`birth_date`, `Users`.`verified`, `Users`.`verification_id`, `Users`.`reset_digest`, `Users`.`reset_sent_at`, `Users`.`hide_problem_tags`, `Users`.`in_mailing_list`, `Users`.`is_private`, `Users`.`preferred_language` from Users';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new Users($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Users suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param Users [$Users] El objeto de tipo Users a crear.
     */
    final public static function create(Users $Users) {
        if (is_null($Users->verified)) {
            $Users->verified = false;
        }
        if (is_null($Users->in_mailing_list)) {
            $Users->in_mailing_list = false;
        }
        if (is_null($Users->is_private)) {
            $Users->is_private = false;
        }
        $sql = 'INSERT INTO Users (`username`, `facebook_user_id`, `password`, `git_token`, `main_email_id`, `main_identity_id`, `country_id`, `state_id`, `scholar_degree`, `graduation_date`, `birth_date`, `verified`, `verification_id`, `reset_digest`, `reset_sent_at`, `hide_problem_tags`, `in_mailing_list`, `is_private`, `preferred_language`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Users->username,
            $Users->facebook_user_id,
            $Users->password,
            $Users->git_token,
            is_null($Users->main_email_id) ? null : (int)$Users->main_email_id,
            is_null($Users->main_identity_id) ? null : (int)$Users->main_identity_id,
            $Users->country_id,
            $Users->state_id,
            $Users->scholar_degree,
            $Users->graduation_date,
            $Users->birth_date,
            is_null($Users->verified) ? null : (int)$Users->verified,
            $Users->verification_id,
            $Users->reset_digest,
            $Users->reset_sent_at,
            is_null($Users->hide_problem_tags) ? null : (int)$Users->hide_problem_tags,
            is_null($Users->in_mailing_list) ? null : (int)$Users->in_mailing_list,
            is_null($Users->is_private) ? null : (int)$Users->is_private,
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
}
