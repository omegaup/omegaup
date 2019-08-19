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
     * Actualizar registros.
     *
     * @param Tags $Tags El objeto de tipo Tags a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(Tags $Tags) : int {
        $sql = 'UPDATE `Tags` SET `name` = ? WHERE `tag_id` = ?;';
        $params = [
            $Tags->name,
            (int)$Tags->tag_id,
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        return MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link Tags} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Tags} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?Tags Un objeto del tipo {@link Tags}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $tag_id) : ?Tags {
        $sql = 'SELECT `Tags`.`tag_id`, `Tags`.`name` FROM Tags WHERE (tag_id = ?) LIMIT 1;';
        $params = [$tag_id];
        $row = MySQLConnection::getInstance()->GetRow($sql, $params);
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
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param Tags $Tags El objeto de tipo Tags a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(Tags $Tags) : void {
        $sql = 'DELETE FROM `Tags` WHERE tag_id = ?;';
        $params = [$Tags->tag_id];

        MySQLConnection::getInstance()->Execute($sql, $params);
        if (MySQLConnection::getInstance()->Affected_Rows() == 0) {
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
     * @param ?int $pagina Página a ver.
     * @param int $filasPorPagina Filas por página.
     * @param ?string $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param string $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     *
     * @return Tags[] Un arreglo que contiene objetos del tipo {@link Tags}.
     *
     * @psalm-return array<int, Tags>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Tags`.`tag_id`, `Tags`.`name` from Tags';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (MySQLConnection::getInstance()->GetAll($sql) as $row) {
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
     * @param Tags $Tags El objeto de tipo Tags a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(Tags $Tags) : int {
        $sql = 'INSERT INTO Tags (`name`) VALUES (?);';
        $params = [
            $Tags->name,
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Tags->tag_id = MySQLConnection::getInstance()->Insert_ID();

        return $affectedRows;
    }
}
