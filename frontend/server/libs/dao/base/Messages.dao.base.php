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
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Messages}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Messages [$Messages] El objeto de tipo Messages
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(Messages $Messages) {
        if (is_null(self::getByPK($Messages->message_id))) {
            return MessagesDAOBase::create($Messages);
        }
        return MessagesDAOBase::update($Messages);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param Messages [$Messages] El objeto de tipo Messages a actualizar.
     */
    final public static function update(Messages $Messages) {
        $sql = 'UPDATE `Messages` SET `read` = ?, `sender_id` = ?, `recipient_id` = ?, `message` = ?, `date` = ? WHERE `message_id` = ?;';
        $params = [
            is_null($Messages->read) ? null : (int)$Messages->read,
            is_null($Messages->sender_id) ? null : (int)$Messages->sender_id,
            is_null($Messages->recipient_id) ? null : (int)$Messages->recipient_id,
            $Messages->message,
            $Messages->date,
            is_null($Messages->message_id) ? null : (int)$Messages->message_id,
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
     * @static
     * @return @link Messages Un objeto del tipo {@link Messages}. NULL si no hay tal registro.
     */
    final public static function getByPK($message_id) {
        if (is_null($message_id)) {
            return null;
        }
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
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param Messages [$Messages] El objeto de tipo Messages a eliminar
     */
    final public static function delete(Messages $Messages) {
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
     * @static
     * @param $pagina Página a ver.
     * @param $filasPorPagina Filas por página.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link Messages}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
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
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param Messages [$Messages] El objeto de tipo Messages a crear.
     */
    final public static function create(Messages $Messages) {
        if (is_null($Messages->read)) {
            $Messages->read = false;
        }
        if (is_null($Messages->date)) {
            $Messages->date = gmdate('Y-m-d H:i:s', Time::get());
        }
        $sql = 'INSERT INTO Messages (`read`, `sender_id`, `recipient_id`, `message`, `date`) VALUES (?, ?, ?, ?, ?);';
        $params = [
            is_null($Messages->read) ? null : (int)$Messages->read,
            is_null($Messages->sender_id) ? null : (int)$Messages->sender_id,
            is_null($Messages->recipient_id) ? null : (int)$Messages->recipient_id,
            $Messages->message,
            $Messages->date,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Messages->message_id = $conn->Insert_ID();

        return $ar;
    }
}
