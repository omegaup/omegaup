<?php
/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

namespace OmegaUp\DAO\Base;

/** Problems Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Problems}.
 * @access public
 * @abstract
 */
abstract class Problems {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\Problems $Problems El objeto de tipo Problems a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(\OmegaUp\DAO\VO\Problems $Problems) : int {
        $sql = 'UPDATE `Problems` SET `acl_id` = ?, `visibility` = ?, `title` = ?, `alias` = ?, `commit` = ?, `current_version` = ?, `languages` = ?, `input_limit` = ?, `visits` = ?, `submissions` = ?, `accepted` = ?, `difficulty` = ?, `creation_date` = ?, `source` = ?, `order` = ?, `deprecated` = ?, `email_clarifications` = ?, `quality` = ?, `quality_histogram` = ?, `difficulty_histogram` = ? WHERE `problem_id` = ?;';
        $params = [
            is_null($Problems->acl_id) ? null : (int)$Problems->acl_id,
            (int)$Problems->visibility,
            $Problems->title,
            $Problems->alias,
            $Problems->commit,
            $Problems->current_version,
            $Problems->languages,
            (int)$Problems->input_limit,
            (int)$Problems->visits,
            (int)$Problems->submissions,
            (int)$Problems->accepted,
            is_null($Problems->difficulty) ? null : (float)$Problems->difficulty,
            \OmegaUp\DAO\DAO::toMySQLTimestamp($Problems->creation_date),
            $Problems->source,
            $Problems->order,
            (int)$Problems->deprecated,
            (int)$Problems->email_clarifications,
            is_null($Problems->quality) ? null : (float)$Problems->quality,
            $Problems->quality_histogram,
            $Problems->difficulty_histogram,
            (int)$Problems->problem_id,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\Problems} por llave primaria.
     *
     * Este metodo cargará un objeto {@link \OmegaUp\DAO\VO\Problems}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\Problems Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\Problems} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(int $problem_id) : ?\OmegaUp\DAO\VO\Problems {
        $sql = 'SELECT `Problems`.`problem_id`, `Problems`.`acl_id`, `Problems`.`visibility`, `Problems`.`title`, `Problems`.`alias`, `Problems`.`commit`, `Problems`.`current_version`, `Problems`.`languages`, `Problems`.`input_limit`, `Problems`.`visits`, `Problems`.`submissions`, `Problems`.`accepted`, `Problems`.`difficulty`, `Problems`.`creation_date`, `Problems`.`source`, `Problems`.`order`, `Problems`.`deprecated`, `Problems`.`email_clarifications`, `Problems`.`quality`, `Problems`.`quality_histogram`, `Problems`.`difficulty_histogram` FROM Problems WHERE (problem_id = ?) LIMIT 1;';
        $params = [$problem_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Problems($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\Problems} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\Problems $Problems El
     * objeto de tipo \OmegaUp\DAO\VO\Problems a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(\OmegaUp\DAO\VO\Problems $Problems) : void {
        $sql = 'DELETE FROM `Problems` WHERE problem_id = ?;';
        $params = [$Problems->problem_id];

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        if (\OmegaUp\MySQLConnection::getInstance()->Affected_Rows() == 0) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo
     * {@link \OmegaUp\DAO\VO\Problems}.
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
     * @return \OmegaUp\DAO\VO\Problems[] Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\Problems}.
     *
     * @psalm-return array<int, \OmegaUp\DAO\VO\Problems>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Problems`.`problem_id`, `Problems`.`acl_id`, `Problems`.`visibility`, `Problems`.`title`, `Problems`.`alias`, `Problems`.`commit`, `Problems`.`current_version`, `Problems`.`languages`, `Problems`.`input_limit`, `Problems`.`visits`, `Problems`.`submissions`, `Problems`.`accepted`, `Problems`.`difficulty`, `Problems`.`creation_date`, `Problems`.`source`, `Problems`.`order`, `Problems`.`deprecated`, `Problems`.`email_clarifications`, `Problems`.`quality`, `Problems`.`quality_histogram`, `Problems`.`difficulty_histogram` from Problems';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . \OmegaUp\MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql) as $row) {
            $allData[] = new \OmegaUp\DAO\VO\Problems($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\Problems}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\Problems $Problems El
     * objeto de tipo {@link \OmegaUp\DAO\VO\Problems} a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(\OmegaUp\DAO\VO\Problems $Problems) : int {
        $sql = 'INSERT INTO Problems (`acl_id`, `visibility`, `title`, `alias`, `commit`, `current_version`, `languages`, `input_limit`, `visits`, `submissions`, `accepted`, `difficulty`, `creation_date`, `source`, `order`, `deprecated`, `email_clarifications`, `quality`, `quality_histogram`, `difficulty_histogram`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            is_null($Problems->acl_id) ? null : (int)$Problems->acl_id,
            (int)$Problems->visibility,
            $Problems->title,
            $Problems->alias,
            $Problems->commit,
            $Problems->current_version,
            $Problems->languages,
            (int)$Problems->input_limit,
            (int)$Problems->visits,
            (int)$Problems->submissions,
            (int)$Problems->accepted,
            is_null($Problems->difficulty) ? null : (float)$Problems->difficulty,
            \OmegaUp\DAO\DAO::toMySQLTimestamp($Problems->creation_date),
            $Problems->source,
            $Problems->order,
            (int)$Problems->deprecated,
            (int)$Problems->email_clarifications,
            is_null($Problems->quality) ? null : (float)$Problems->quality,
            $Problems->quality_histogram,
            $Problems->difficulty_histogram,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Problems->problem_id = \OmegaUp\MySQLConnection::getInstance()->Insert_ID();

        return $affectedRows;
    }
}
