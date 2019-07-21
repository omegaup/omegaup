<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Notifications Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Notifications}.
 * @access public
 * @abstract
 *
 */
abstract class NotificationsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Notifications}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Notifications [$Notifications] El objeto de tipo Notifications
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(Notifications $Notifications) {
        if (is_null(self::getByPK($Notifications->notification_id))) {
            return NotificationsDAOBase::create($Notifications);
        }
        return NotificationsDAOBase::update($Notifications);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param Notifications [$Notifications] El objeto de tipo Notifications a actualizar.
     */
    final public static function update(Notifications $Notifications) {
        $sql = 'UPDATE `Notifications` SET `user_id` = ?, `timestamp` = ?, `read` = ?, `contents` = ? WHERE `notification_id` = ?;';
        $params = [
            is_null($Notifications->user_id) ? null : (int)$Notifications->user_id,
            $Notifications->timestamp,
            is_null($Notifications->read) ? null : (int)$Notifications->read,
            $Notifications->contents,
            is_null($Notifications->notification_id) ? null : (int)$Notifications->notification_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link Notifications} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Notifications} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link Notifications Un objeto del tipo {@link Notifications}. NULL si no hay tal registro.
     */
    final public static function getByPK($notification_id) {
        if (is_null($notification_id)) {
            return null;
        }
        $sql = 'SELECT `Notifications`.`notification_id`, `Notifications`.`user_id`, `Notifications`.`timestamp`, `Notifications`.`read`, `Notifications`.`contents` FROM Notifications WHERE (notification_id = ?) LIMIT 1;';
        $params = [$notification_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new Notifications($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Notifications suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param Notifications [$Notifications] El objeto de tipo Notifications a eliminar
     */
    final public static function delete(Notifications $Notifications) {
        $sql = 'DELETE FROM `Notifications` WHERE notification_id = ?;';
        $params = [$Notifications->notification_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link Notifications}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link Notifications}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Notifications`.`notification_id`, `Notifications`.`user_id`, `Notifications`.`timestamp`, `Notifications`.`read`, `Notifications`.`contents` from Notifications';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new Notifications($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Notifications suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param Notifications [$Notifications] El objeto de tipo Notifications a crear.
     */
    final public static function create(Notifications $Notifications) {
        if (is_null($Notifications->timestamp)) {
            $Notifications->timestamp = gmdate('Y-m-d H:i:s', Time::get());
        }
        if (is_null($Notifications->read)) {
            $Notifications->read = false;
        }
        $sql = 'INSERT INTO Notifications (`user_id`, `timestamp`, `read`, `contents`) VALUES (?, ?, ?, ?);';
        $params = [
            is_null($Notifications->user_id) ? null : (int)$Notifications->user_id,
            $Notifications->timestamp,
            is_null($Notifications->read) ? null : (int)$Notifications->read,
            $Notifications->contents,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Notifications->notification_id = $conn->Insert_ID();

        return $ar;
    }
}
