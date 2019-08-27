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
 * {@link \OmegaUp\DAO\VO\Users}.
 * @access public
 * @abstract
 */
abstract class UsersDAOBase {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\Users $Users El objeto de tipo Users a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(\OmegaUp\DAO\VO\Users $Users) : int {
        $sql = 'UPDATE `Users` SET `username` = ?, `facebook_user_id` = ?, `password` = ?, `git_token` = ?, `main_email_id` = ?, `main_identity_id` = ?, `scholar_degree` = ?, `graduation_date` = ?, `birth_date` = ?, `verified` = ?, `verification_id` = ?, `reset_digest` = ?, `reset_sent_at` = ?, `hide_problem_tags` = ?, `in_mailing_list` = ?, `is_private` = ?, `preferred_language` = ? WHERE `user_id` = ?;';
        $params = [
            $Users->username,
            $Users->facebook_user_id,
            $Users->password,
            $Users->git_token,
            is_null($Users->main_email_id) ? null : (int)$Users->main_email_id,
            is_null($Users->main_identity_id) ? null : (int)$Users->main_identity_id,
            $Users->scholar_degree,
            $Users->graduation_date,
            $Users->birth_date,
            (int)$Users->verified,
            $Users->verification_id,
            $Users->reset_digest,
            \OmegaUp\DAO\DAO::toMySQLTimestamp($Users->reset_sent_at),
            is_null($Users->hide_problem_tags) ? null : (int)$Users->hide_problem_tags,
            (int)$Users->in_mailing_list,
            (int)$Users->is_private,
            $Users->preferred_language,
            (int)$Users->user_id,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\Users} por llave primaria.
     *
     * Este metodo cargará un objeto {@link \OmegaUp\DAO\VO\Users}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\Users Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\Users} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(int $user_id) : ?\OmegaUp\DAO\VO\Users {
        $sql = 'SELECT `Users`.`user_id`, `Users`.`username`, `Users`.`facebook_user_id`, `Users`.`password`, `Users`.`git_token`, `Users`.`main_email_id`, `Users`.`main_identity_id`, `Users`.`scholar_degree`, `Users`.`graduation_date`, `Users`.`birth_date`, `Users`.`verified`, `Users`.`verification_id`, `Users`.`reset_digest`, `Users`.`reset_sent_at`, `Users`.`hide_problem_tags`, `Users`.`in_mailing_list`, `Users`.`is_private`, `Users`.`preferred_language` FROM Users WHERE (user_id = ?) LIMIT 1;';
        $params = [$user_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Users($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\Users} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\Users $Users El
     * objeto de tipo \OmegaUp\DAO\VO\Users a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(\OmegaUp\DAO\VO\Users $Users) : void {
        $sql = 'DELETE FROM `Users` WHERE user_id = ?;';
        $params = [$Users->user_id];

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        if (\OmegaUp\MySQLConnection::getInstance()->Affected_Rows() == 0) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo
     * {@link \OmegaUp\DAO\VO\Users}.
     * Este método consume una cantidad de memoria proporcional al número de
     * registros regresados, así que sólo debe usarse cuando la tabla en
     * cuestión es pequeña o se proporcionan parámetros para obtener un menor
     * número de filas.
     *
     * @param ?int $pagina Página a ver.
     * @param int $filasPorPagina Filas por página.
     * @param ?string $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param string $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     *
     * @return \OmegaUp\DAO\VO\Users[] Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\Users}.
     *
     * @psalm-return array<int, \OmegaUp\DAO\VO\Users>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Users`.`user_id`, `Users`.`username`, `Users`.`facebook_user_id`, `Users`.`password`, `Users`.`git_token`, `Users`.`main_email_id`, `Users`.`main_identity_id`, `Users`.`scholar_degree`, `Users`.`graduation_date`, `Users`.`birth_date`, `Users`.`verified`, `Users`.`verification_id`, `Users`.`reset_digest`, `Users`.`reset_sent_at`, `Users`.`hide_problem_tags`, `Users`.`in_mailing_list`, `Users`.`is_private`, `Users`.`preferred_language` from Users';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . \OmegaUp\MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql) as $row) {
            $allData[] = new \OmegaUp\DAO\VO\Users($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\Users}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\Users $Users El
     * objeto de tipo {@link \OmegaUp\DAO\VO\Users} a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(\OmegaUp\DAO\VO\Users $Users) : int {
        $sql = 'INSERT INTO Users (`username`, `facebook_user_id`, `password`, `git_token`, `main_email_id`, `main_identity_id`, `scholar_degree`, `graduation_date`, `birth_date`, `verified`, `verification_id`, `reset_digest`, `reset_sent_at`, `hide_problem_tags`, `in_mailing_list`, `is_private`, `preferred_language`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Users->username,
            $Users->facebook_user_id,
            $Users->password,
            $Users->git_token,
            is_null($Users->main_email_id) ? null : (int)$Users->main_email_id,
            is_null($Users->main_identity_id) ? null : (int)$Users->main_identity_id,
            $Users->scholar_degree,
            $Users->graduation_date,
            $Users->birth_date,
            (int)$Users->verified,
            $Users->verification_id,
            $Users->reset_digest,
            \OmegaUp\DAO\DAO::toMySQLTimestamp($Users->reset_sent_at),
            is_null($Users->hide_problem_tags) ? null : (int)$Users->hide_problem_tags,
            (int)$Users->in_mailing_list,
            (int)$Users->is_private,
            $Users->preferred_language,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Users->user_id = \OmegaUp\MySQLConnection::getInstance()->Insert_ID();

        return $affectedRows;
    }
}
