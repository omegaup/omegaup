<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** States Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link States}.
 * @access public
 * @abstract
 *
 */
abstract class StatesDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link States}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces replace() creará una nueva fila.
     *
     * @throws Exception si la operacion fallo.
     *
     * @param States $States El objeto de tipo States
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(States $States) : int {
        if (empty($States->country_id) || empty($States->state_id)) {
            throw new NotFoundException('recordNotFound');
        }
        $sql = 'REPLACE INTO States (`country_id`, `state_id`, `name`) VALUES (?, ?, ?);';
        $params = [
            $States->country_id,
            $States->state_id,
            $States->name,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @param States $States El objeto de tipo States a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(States $States) : int {
        $sql = 'UPDATE `States` SET `name` = ? WHERE `country_id` = ? AND `state_id` = ?;';
        $params = [
            $States->name,
            $States->country_id,
            $States->state_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link States} por llave primaria.
     *
     * Este metodo cargará un objeto {@link States} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?States Un objeto del tipo {@link States}. NULL si no hay tal registro.
     */
    final public static function getByPK(?string $country_id, ?string $state_id) : ?States {
        $sql = 'SELECT `States`.`country_id`, `States`.`state_id`, `States`.`name` FROM States WHERE (country_id = ? AND state_id = ?) LIMIT 1;';
        $params = [$country_id, $state_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new States($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto States suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param States $States El objeto de tipo States a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(States $States) : void {
        $sql = 'DELETE FROM `States` WHERE country_id = ? AND state_id = ?;';
        $params = [$States->country_id, $States->state_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link States}.
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
     * @return States[] Un arreglo que contiene objetos del tipo {@link States}.
     *
     * @psalm-return array<int, States>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `States`.`country_id`, `States`.`state_id`, `States`.`name` from States';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new States($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto States suministrado.
     *
     * @param States $States El objeto de tipo States a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(States $States) : int {
        $sql = 'INSERT INTO States (`country_id`, `state_id`, `name`) VALUES (?, ?, ?);';
        $params = [
            $States->country_id,
            $States->state_id,
            $States->name,
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
