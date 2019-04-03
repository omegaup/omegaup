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
 * {@link AuthTokens}.
 * @access public
 * @abstract
 *
 */
abstract class AuthTokensDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link AuthTokens}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param AuthTokens [$Auth_Tokens] El objeto de tipo AuthTokens
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(AuthTokens $Auth_Tokens) {
        if (is_null(self::getByPK($Auth_Tokens->token))) {
            return AuthTokensDAOBase::create($Auth_Tokens);
        }
        return AuthTokensDAOBase::update($Auth_Tokens);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param AuthTokens [$Auth_Tokens] El objeto de tipo AuthTokens a actualizar.
     */
    final public static function update(AuthTokens $Auth_Tokens) {
        $sql = 'UPDATE `Auth_Tokens` SET `user_id` = ?, `identity_id` = ?, `create_time` = ? WHERE `token` = ?;';
        $params = [
            $Auth_Tokens->user_id,
            $Auth_Tokens->identity_id,
            $Auth_Tokens->create_time,
            $Auth_Tokens->token,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link AuthTokens} por llave primaria.
     *
     * Este metodo cargará un objeto {@link AuthTokens} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link AuthTokens Un objeto del tipo {@link AuthTokens}. NULL si no hay tal registro.
     */
    final public static function getByPK($token) {
        if (is_null($token)) {
            return null;
        }
        $sql = 'SELECT `Auth_Tokens`.`user_id`, `Auth_Tokens`.`identity_id`, `Auth_Tokens`.`token`, `Auth_Tokens`.`create_time` FROM Auth_Tokens WHERE (token = ?) LIMIT 1;';
        $params = [$token];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new AuthTokens($rs);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto AuthTokens suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param AuthTokens [$Auth_Tokens] El objeto de tipo AuthTokens a eliminar
     */
    final public static function delete(AuthTokens $Auth_Tokens) {
        $sql = 'DELETE FROM `Auth_Tokens` WHERE token = ?;';
        $params = [$Auth_Tokens->token];
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
     * y construirá un arreglo que contiene objetos de tipo {@link AuthTokens}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link AuthTokens}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Auth_Tokens`.`user_id`, `Auth_Tokens`.`identity_id`, `Auth_Tokens`.`token`, `Auth_Tokens`.`create_time` from Auth_Tokens';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . mysqli_real_escape_string($conn->_connectionID, $orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $rs = $conn->Execute($sql);
        $allData = [];
        foreach ($rs as $row) {
            $allData[] = new AuthTokens($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto AuthTokens suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param AuthTokens [$Auth_Tokens] El objeto de tipo AuthTokens a crear.
     */
    final public static function create(AuthTokens $Auth_Tokens) {
        if (is_null($Auth_Tokens->create_time)) {
            $Auth_Tokens->create_time = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO Auth_Tokens (`user_id`, `identity_id`, `token`, `create_time`) VALUES (?, ?, ?, ?);';
        $params = [
            $Auth_Tokens->user_id,
            $Auth_Tokens->identity_id,
            $Auth_Tokens->token,
            $Auth_Tokens->create_time,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }

        return $ar;
    }
}
