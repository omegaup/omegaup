<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Messages Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Messages}.
 * @access public
 * @abstract
 *
 */
abstract class MessagesDAOBase {
    /**
     * Actualizar registros.
     *
     * @param Messages $Messages El objeto de tipo Messages a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(Messages $Messages) : int {
        $sql = 'UPDATE `Messages` SET `read` = ?, `sender_id` = ?, `recipient_id` = ?, `message` = ?, `date` = ? WHERE `message_id` = ?;';
        $params = [
            (int)$Messages->read,
            (int)$Messages->sender_id,
            (int)$Messages->recipient_id,
            $Messages->message,
            DAO::toMySQLTimestamp($Messages->date),
            (int)$Messages->message_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link Messages} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Messages} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?Messages Un objeto del tipo {@link Messages}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $message_id) : ?Messages {
        $sql = 'SELECT `Messages`.`message_id`, `Messages`.`read`, `Messages`.`sender_id`, `Messages`.`recipient_id`, `Messages`.`message`, `Messages`.`date` FROM Messages WHERE (message_id = ?) LIMIT 1;';
        $params = [$message_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new Messages($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Messages suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param Messages $Messages El objeto de tipo Messages a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(Messages $Messages) : void {
        $sql = 'DELETE FROM `Messages` WHERE message_id = ?;';
        $params = [$Messages->message_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link Messages}.
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
     * @return array Un arreglo que contiene objetos del tipo {@link Messages}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Messages`.`message_id`, `Messages`.`read`, `Messages`.`sender_id`, `Messages`.`recipient_id`, `Messages`.`message`, `Messages`.`date` from Messages';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new Messages($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Messages suministrado.
     *
     * @param Messages $Messages El objeto de tipo Messages a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(Messages $Messages) : int {
        if (is_null($Messages->read)) {
            $Messages->read = false;
        }
        if (is_null($Messages->date)) {
            $Messages->date = Time::get();
        }
        $sql = 'INSERT INTO Messages (`read`, `sender_id`, `recipient_id`, `message`, `date`) VALUES (?, ?, ?, ?, ?);';
        $params = [
            (int)$Messages->read,
            (int)$Messages->sender_id,
            (int)$Messages->recipient_id,
            $Messages->message,
            DAO::toMySQLTimestamp($Messages->date),
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Messages->message_id = $conn->Insert_ID();

        return $affectedRows;
    }
}
