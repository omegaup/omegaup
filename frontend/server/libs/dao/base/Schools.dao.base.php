<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Schools Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Schools}.
 * @access public
 * @abstract
 *
 */
abstract class SchoolsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Schools}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Schools [$Schools] El objeto de tipo Schools
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(Schools $Schools) : int {
        if (is_null($Schools->school_id) ||
            is_null(self::getByPK($Schools->school_id))
        ) {
            return SchoolsDAOBase::create($Schools);
        }
        return SchoolsDAOBase::update($Schools);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param Schools [$Schools] El objeto de tipo Schools a actualizar.
     */
    final public static function update(Schools $Schools) : int {
        $sql = 'UPDATE `Schools` SET `country_id` = ?, `state_id` = ?, `name` = ? WHERE `school_id` = ?;';
        $params = [
            $Schools->country_id,
            $Schools->state_id,
            $Schools->name,
            (int)$Schools->school_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link Schools} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Schools} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link Schools Un objeto del tipo {@link Schools}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $school_id) : ?Schools {
        $sql = 'SELECT `Schools`.`school_id`, `Schools`.`country_id`, `Schools`.`state_id`, `Schools`.`name` FROM Schools WHERE (school_id = ?) LIMIT 1;';
        $params = [$school_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new Schools($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Schools suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param Schools [$Schools] El objeto de tipo Schools a eliminar
     */
    final public static function delete(Schools $Schools) : void {
        $sql = 'DELETE FROM `Schools` WHERE school_id = ?;';
        $params = [$Schools->school_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link Schools}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link Schools}.
     */
    final public static function getAll(
        ?int $pagina = null,
        ?int $filasPorPagina = null,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Schools`.`school_id`, `Schools`.`country_id`, `Schools`.`state_id`, `Schools`.`name` from Schools';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new Schools($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Schools suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param Schools [$Schools] El objeto de tipo Schools a crear.
     */
    final public static function create(Schools $Schools) : int {
        $sql = 'INSERT INTO Schools (`country_id`, `state_id`, `name`) VALUES (?, ?, ?);';
        $params = [
            $Schools->country_id,
            $Schools->state_id,
            $Schools->name,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Schools->school_id = $conn->Insert_ID();

        return $affectedRows;
    }
}
