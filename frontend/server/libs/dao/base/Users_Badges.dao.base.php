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
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link UsersBadges}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param UsersBadges [$Users_Badges] El objeto de tipo UsersBadges
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(UsersBadges $Users_Badges) {
        if (is_null(self::getByPK($Users_Badges->user_badge_id))) {
            return UsersBadgesDAOBase::create($Users_Badges);
        }
        return UsersBadgesDAOBase::update($Users_Badges);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param UsersBadges [$Users_Badges] El objeto de tipo UsersBadges a actualizar.
     */
    final public static function update(UsersBadges $Users_Badges) {
        $sql = 'UPDATE `Users_Badges` SET `user_id` = ?, `badge_alias` = ?, `assignation_time` = ? WHERE `user_badge_id` = ?;';
        $params = [
            is_null($Users_Badges->user_id) ? null : (int)$Users_Badges->user_id,
            $Users_Badges->badge_alias,
            $Users_Badges->assignation_time,
            is_null($Users_Badges->user_badge_id) ? null : (int)$Users_Badges->user_badge_id,
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
     * @static
     * @return @link UsersBadges Un objeto del tipo {@link UsersBadges}. NULL si no hay tal registro.
     */
    final public static function getByPK($user_badge_id) {
        if (is_null($user_badge_id)) {
            return null;
        }
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
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param UsersBadges [$Users_Badges] El objeto de tipo UsersBadges a eliminar
     */
    final public static function delete(UsersBadges $Users_Badges) {
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
     * @static
     * @param $pagina Página a ver.
     * @param $filasPorPagina Filas por página.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link UsersBadges}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
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
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param UsersBadges [$Users_Badges] El objeto de tipo UsersBadges a crear.
     */
    final public static function create(UsersBadges $Users_Badges) {
        if (is_null($Users_Badges->assignation_time)) {
            $Users_Badges->assignation_time = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO Users_Badges (`user_id`, `badge_alias`, `assignation_time`) VALUES (?, ?, ?);';
        $params = [
            is_null($Users_Badges->user_id) ? null : (int)$Users_Badges->user_id,
            $Users_Badges->badge_alias,
            $Users_Badges->assignation_time,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Users_Badges->user_badge_id = $conn->Insert_ID();

        return $ar;
    }
}
