<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Tags Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Tags}.
 * @access public
 * @abstract
 *
 */
abstract class TagsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Tags}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Tags [$Tags] El objeto de tipo Tags
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(Tags $Tags) {
        if (is_null(self::getByPK($Tags->tag_id))) {
            return TagsDAOBase::create($Tags);
        }
        return TagsDAOBase::update($Tags);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param Tags [$Tags] El objeto de tipo Tags a actualizar.
     */
    final public static function update(Tags $Tags) {
        $sql = 'UPDATE `Tags` SET `name` = ? WHERE `tag_id` = ?;';
        $params = [
            $Tags->name,
            is_null($Tags->tag_id) ? null : (int)$Tags->tag_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link Tags} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Tags} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link Tags Un objeto del tipo {@link Tags}. NULL si no hay tal registro.
     */
    final public static function getByPK($tag_id) {
        if (is_null($tag_id)) {
            return null;
        }
        $sql = 'SELECT `Tags`.`tag_id`, `Tags`.`name` FROM Tags WHERE (tag_id = ?) LIMIT 1;';
        $params = [$tag_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new Tags($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Tags suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param Tags [$Tags] El objeto de tipo Tags a eliminar
     */
    final public static function delete(Tags $Tags) {
        $sql = 'DELETE FROM `Tags` WHERE tag_id = ?;';
        $params = [$Tags->tag_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link Tags}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link Tags}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Tags`.`tag_id`, `Tags`.`name` from Tags';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new Tags($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Tags suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param Tags [$Tags] El objeto de tipo Tags a crear.
     */
    final public static function create(Tags $Tags) {
        $sql = 'INSERT INTO Tags (`name`) VALUES (?);';
        $params = [
            $Tags->name,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Tags->tag_id = $conn->Insert_ID();

        return $ar;
    }
}
