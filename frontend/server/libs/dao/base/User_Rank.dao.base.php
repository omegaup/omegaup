<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** UserRank Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link UserRank}.
 * @access public
 * @abstract
 *
 */
abstract class UserRankDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link UserRank}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces replace() creará una nueva fila.
     *
     * @throws Exception si la operacion fallo.
     *
     * @param UserRank $User_Rank El objeto de tipo UserRank
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(UserRank $User_Rank) : int {
        if (is_null($User_Rank->user_id)) {
            throw new NotFoundException('recordNotFound');
        }
        if (is_null($User_Rank->problems_solved_count)) {
            $User_Rank->problems_solved_count = 0;
        }
        if (is_null($User_Rank->score)) {
            $User_Rank->score = 0.00;
        }
        $sql = 'REPLACE INTO User_Rank (`user_id`, `rank`, `problems_solved_count`, `score`, `username`, `name`, `country_id`, `state_id`, `school_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            (int)$User_Rank->user_id,
            (int)$User_Rank->rank,
            (int)$User_Rank->problems_solved_count,
            (float)$User_Rank->score,
            $User_Rank->username,
            $User_Rank->name,
            $User_Rank->country_id,
            $User_Rank->state_id,
            is_null($User_Rank->school_id) ? null : (int)$User_Rank->school_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @param UserRank $User_Rank El objeto de tipo UserRank a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(UserRank $User_Rank) : int {
        $sql = 'UPDATE `User_Rank` SET `rank` = ?, `problems_solved_count` = ?, `score` = ?, `username` = ?, `name` = ?, `country_id` = ?, `state_id` = ?, `school_id` = ? WHERE `user_id` = ?;';
        $params = [
            (int)$User_Rank->rank,
            (int)$User_Rank->problems_solved_count,
            (float)$User_Rank->score,
            $User_Rank->username,
            $User_Rank->name,
            $User_Rank->country_id,
            $User_Rank->state_id,
            is_null($User_Rank->school_id) ? null : (int)$User_Rank->school_id,
            (int)$User_Rank->user_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link UserRank} por llave primaria.
     *
     * Este metodo cargará un objeto {@link UserRank} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?UserRank Un objeto del tipo {@link UserRank}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $user_id) : ?UserRank {
        $sql = 'SELECT `User_Rank`.`user_id`, `User_Rank`.`rank`, `User_Rank`.`problems_solved_count`, `User_Rank`.`score`, `User_Rank`.`username`, `User_Rank`.`name`, `User_Rank`.`country_id`, `User_Rank`.`state_id`, `User_Rank`.`school_id` FROM User_Rank WHERE (user_id = ?) LIMIT 1;';
        $params = [$user_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new UserRank($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto UserRank suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param UserRank $User_Rank El objeto de tipo UserRank a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(UserRank $User_Rank) : void {
        $sql = 'DELETE FROM `User_Rank` WHERE user_id = ?;';
        $params = [$User_Rank->user_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link UserRank}.
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
     * @return array Un arreglo que contiene objetos del tipo {@link UserRank}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `User_Rank`.`user_id`, `User_Rank`.`rank`, `User_Rank`.`problems_solved_count`, `User_Rank`.`score`, `User_Rank`.`username`, `User_Rank`.`name`, `User_Rank`.`country_id`, `User_Rank`.`state_id`, `User_Rank`.`school_id` from User_Rank';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new UserRank($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto UserRank suministrado.
     *
     * @param UserRank $User_Rank El objeto de tipo UserRank a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(UserRank $User_Rank) : int {
        if (is_null($User_Rank->problems_solved_count)) {
            $User_Rank->problems_solved_count = 0;
        }
        if (is_null($User_Rank->score)) {
            $User_Rank->score = 0.00;
        }
        $sql = 'INSERT INTO User_Rank (`user_id`, `rank`, `problems_solved_count`, `score`, `username`, `name`, `country_id`, `state_id`, `school_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            (int)$User_Rank->user_id,
            (int)$User_Rank->rank,
            (int)$User_Rank->problems_solved_count,
            (float)$User_Rank->score,
            $User_Rank->username,
            $User_Rank->name,
            $User_Rank->country_id,
            $User_Rank->state_id,
            is_null($User_Rank->school_id) ? null : (int)$User_Rank->school_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }

        return $affectedRows;
    }
}
