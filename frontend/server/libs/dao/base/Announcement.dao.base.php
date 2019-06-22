<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Announcement Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Announcement}.
 * @access public
 * @abstract
 *
 */
abstract class AnnouncementDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Announcement}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Announcement [$Announcement] El objeto de tipo Announcement
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(Announcement $Announcement) {
        if (is_null(self::getByPK($Announcement->announcement_id))) {
            return AnnouncementDAOBase::create($Announcement);
        }
        return AnnouncementDAOBase::update($Announcement);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param Announcement [$Announcement] El objeto de tipo Announcement a actualizar.
     */
    final public static function update(Announcement $Announcement) {
        $sql = 'UPDATE `Announcement` SET `user_id` = ?, `time` = ?, `description` = ? WHERE `announcement_id` = ?;';
        $params = [
            is_null($Announcement->user_id) ? null : (int)$Announcement->user_id,
            $Announcement->time,
            $Announcement->description,
            is_null($Announcement->announcement_id) ? null : (int)$Announcement->announcement_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link Announcement} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Announcement} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link Announcement Un objeto del tipo {@link Announcement}. NULL si no hay tal registro.
     */
    final public static function getByPK($announcement_id) {
        if (is_null($announcement_id)) {
            return null;
        }
        $sql = 'SELECT `Announcement`.`announcement_id`, `Announcement`.`user_id`, `Announcement`.`time`, `Announcement`.`description` FROM Announcement WHERE (announcement_id = ?) LIMIT 1;';
        $params = [$announcement_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new Announcement($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Announcement suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param Announcement [$Announcement] El objeto de tipo Announcement a eliminar
     */
    final public static function delete(Announcement $Announcement) {
        $sql = 'DELETE FROM `Announcement` WHERE announcement_id = ?;';
        $params = [$Announcement->announcement_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link Announcement}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link Announcement}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Announcement`.`announcement_id`, `Announcement`.`user_id`, `Announcement`.`time`, `Announcement`.`description` from Announcement';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new Announcement($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Announcement suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param Announcement [$Announcement] El objeto de tipo Announcement a crear.
     */
    final public static function create(Announcement $Announcement) {
        if (is_null($Announcement->time)) {
            $Announcement->time = gmdate('Y-m-d H:i:s', Time::get());
        }
        $sql = 'INSERT INTO Announcement (`user_id`, `time`, `description`) VALUES (?, ?, ?);';
        $params = [
            is_null($Announcement->user_id) ? null : (int)$Announcement->user_id,
            $Announcement->time,
            $Announcement->description,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Announcement->announcement_id = $conn->Insert_ID();

        return $ar;
    }
}
