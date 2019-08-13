<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** UsersBadges Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link UsersBadges}.
 * @access public
 * @abstract
 *
 */
abstract class UsersBadgesDAOBase {
    /**
     * Actualizar registros.
     *
     * @param UsersBadges $Users_Badges El objeto de tipo UsersBadges a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(UsersBadges $Users_Badges) : int {
        $sql = 'UPDATE `Users_Badges` SET `user_id` = ?, `badge_alias` = ?, `assignation_time` = ? WHERE `user_badge_id` = ?;';
        $params = [
            (int)$Users_Badges->user_id,
            $Users_Badges->badge_alias,
            DAO::toMySQLTimestamp($Users_Badges->assignation_time),
            (int)$Users_Badges->user_badge_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link UsersBadges} por llave primaria.
     *
     * Este metodo cargará un objeto {@link UsersBadges} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?UsersBadges Un objeto del tipo {@link UsersBadges}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $user_badge_id) : ?UsersBadges {
        $sql = 'SELECT `Users_Badges`.`user_badge_id`, `Users_Badges`.`user_id`, `Users_Badges`.`badge_alias`, `Users_Badges`.`assignation_time` FROM Users_Badges WHERE (user_badge_id = ?) LIMIT 1;';
        $params = [$user_badge_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new UsersBadges($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto UsersBadges suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param UsersBadges $Users_Badges El objeto de tipo UsersBadges a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(UsersBadges $Users_Badges) : void {
        $sql = 'DELETE FROM `Users_Badges` WHERE user_badge_id = ?;';
        $params = [$Users_Badges->user_badge_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link UsersBadges}.
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
     * @return array Un arreglo que contiene objetos del tipo {@link UsersBadges}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Users_Badges`.`user_badge_id`, `Users_Badges`.`user_id`, `Users_Badges`.`badge_alias`, `Users_Badges`.`assignation_time` from Users_Badges';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new UsersBadges($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto UsersBadges suministrado.
     *
     * @param UsersBadges $Users_Badges El objeto de tipo UsersBadges a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(UsersBadges $Users_Badges) : int {
        if (is_null($Users_Badges->assignation_time)) {
            $Users_Badges->assignation_time = Time::get();
        }
        $sql = 'INSERT INTO Users_Badges (`user_id`, `badge_alias`, `assignation_time`) VALUES (?, ?, ?);';
        $params = [
            (int)$Users_Badges->user_id,
            $Users_Badges->badge_alias,
            DAO::toMySQLTimestamp($Users_Badges->assignation_time),
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Users_Badges->user_badge_id = $conn->Insert_ID();

        return $affectedRows;
    }
}
