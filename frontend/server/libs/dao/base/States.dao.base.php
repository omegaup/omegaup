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
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param States [$States] El objeto de tipo States
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(States $States) : int {
        if (is_null($States->country_id) ||
            is_null($States->state_id) ||
            is_null(self::getByPK($States->country_id, $States->state_id))
        ) {
            return StatesDAOBase::create($States);
        }
        return StatesDAOBase::update($States);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param States [$States] El objeto de tipo States a actualizar.
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
     * @static
     * @return @link States Un objeto del tipo {@link States}. NULL si no hay tal registro.
     */
    final public static function getByPK(string $country_id, string $state_id) : ?States {
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
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param States [$States] El objeto de tipo States a eliminar
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
     * @static
     * @param $pagina Página a ver.
     * @param $filasPorPagina Filas por página.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link States}.
     */
    final public static function getAll(
        ?int $pagina = null,
        ?int $filasPorPagina = null,
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
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param States [$States] El objeto de tipo States a crear.
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
