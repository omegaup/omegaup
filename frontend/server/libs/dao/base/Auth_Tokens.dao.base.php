<?php
/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** AuthTokens Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\AuthTokens}.
 * @access public
 * @abstract
 */
abstract class AuthTokensDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link \OmegaUp\DAO\VO\AuthTokens}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces replace() creará una nueva fila.
     *
     * @throws Exception si la operacion falló.
     *
     * @param \OmegaUp\DAO\VO\AuthTokens $Auth_Tokens El
     * objeto de tipo {@link \OmegaUp\DAO\VO\AuthTokens}.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(\OmegaUp\DAO\VO\AuthTokens $Auth_Tokens) : int {
        if (empty($Auth_Tokens->token)) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }
        $sql = 'REPLACE INTO Auth_Tokens (`user_id`, `identity_id`, `token`, `create_time`) VALUES (?, ?, ?, ?);';
        $params = [
            !is_null($Auth_Tokens->user_id) ? intval($Auth_Tokens->user_id) : null,
            !is_null($Auth_Tokens->identity_id) ? intval($Auth_Tokens->identity_id) : null,
            $Auth_Tokens->token,
            \OmegaUp\DAO\DAO::toMySQLTimestamp($Auth_Tokens->create_time),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\AuthTokens $Auth_Tokens El objeto de tipo AuthTokens a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(\OmegaUp\DAO\VO\AuthTokens $Auth_Tokens) : int {
        $sql = 'UPDATE `Auth_Tokens` SET `user_id` = ?, `identity_id` = ?, `create_time` = ? WHERE `token` = ?;';
        $params = [
            is_null($Auth_Tokens->user_id) ? null : (int)$Auth_Tokens->user_id,
            is_null($Auth_Tokens->identity_id) ? null : (int)$Auth_Tokens->identity_id,
            \OmegaUp\DAO\DAO::toMySQLTimestamp($Auth_Tokens->create_time),
            $Auth_Tokens->token,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\AuthTokens} por llave primaria.
     *
     * Este metodo cargará un objeto {@link \OmegaUp\DAO\VO\AuthTokens}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\AuthTokens Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\AuthTokens} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(?string $token) : ?\OmegaUp\DAO\VO\AuthTokens {
        $sql = 'SELECT `Auth_Tokens`.`user_id`, `Auth_Tokens`.`identity_id`, `Auth_Tokens`.`token`, `Auth_Tokens`.`create_time` FROM Auth_Tokens WHERE (token = ?) LIMIT 1;';
        $params = [$token];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\AuthTokens($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\AuthTokens} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\AuthTokens $Auth_Tokens El
     * objeto de tipo \OmegaUp\DAO\VO\AuthTokens a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(\OmegaUp\DAO\VO\AuthTokens $Auth_Tokens) : void {
        $sql = 'DELETE FROM `Auth_Tokens` WHERE token = ?;';
        $params = [$Auth_Tokens->token];

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
     * {@link \OmegaUp\DAO\VO\AuthTokens}.
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
     * @return \OmegaUp\DAO\VO\AuthTokens[] Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\AuthTokens}.
     *
     * @psalm-return array<int, \OmegaUp\DAO\VO\AuthTokens>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Auth_Tokens`.`user_id`, `Auth_Tokens`.`identity_id`, `Auth_Tokens`.`token`, `Auth_Tokens`.`create_time` from Auth_Tokens';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . \OmegaUp\MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql) as $row) {
            $allData[] = new \OmegaUp\DAO\VO\AuthTokens($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\AuthTokens}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\AuthTokens $Auth_Tokens El
     * objeto de tipo {@link \OmegaUp\DAO\VO\AuthTokens} a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(\OmegaUp\DAO\VO\AuthTokens $Auth_Tokens) : int {
        $sql = 'INSERT INTO Auth_Tokens (`user_id`, `identity_id`, `token`, `create_time`) VALUES (?, ?, ?, ?);';
        $params = [
            is_null($Auth_Tokens->user_id) ? null : (int)$Auth_Tokens->user_id,
            is_null($Auth_Tokens->identity_id) ? null : (int)$Auth_Tokens->identity_id,
            $Auth_Tokens->token,
            \OmegaUp\DAO\DAO::toMySQLTimestamp($Auth_Tokens->create_time),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }

        return $affectedRows;
    }
}
