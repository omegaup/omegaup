<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Problems Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Problems}.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Problems}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Problems [$Problems] El objeto de tipo Problems
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(Problems $Problems) {
        if (is_null(self::getByPK($Problems->problem_id))) {
            return ProblemsDAOBase::create($Problems);
        }
        return ProblemsDAOBase::update($Problems);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param Problems [$Problems] El objeto de tipo Problems a actualizar.
     */
    final public static function update(Problems $Problems) {
        $sql = 'UPDATE `Problems` SET `acl_id` = ?, `visibility` = ?, `title` = ?, `alias` = ?, `commit` = ?, `current_version` = ?, `languages` = ?, `input_limit` = ?, `visits` = ?, `submissions` = ?, `accepted` = ?, `difficulty` = ?, `creation_date` = ?, `source` = ?, `order` = ?, `deprecated` = ?, `email_clarifications` = ?, `quality` = ?, `quality_histogram` = ?, `difficulty_histogram` = ? WHERE `problem_id` = ?;';
        $params = [
            is_null($Problems->acl_id) ? null : (int)$Problems->acl_id,
            is_null($Problems->visibility) ? null : (int)$Problems->visibility,
            $Problems->title,
            $Problems->alias,
            $Problems->commit,
            $Problems->current_version,
            $Problems->languages,
            is_null($Problems->input_limit) ? null : (int)$Problems->input_limit,
            is_null($Problems->visits) ? null : (int)$Problems->visits,
            is_null($Problems->submissions) ? null : (int)$Problems->submissions,
            is_null($Problems->accepted) ? null : (int)$Problems->accepted,
            is_null($Problems->difficulty) ? null : (float)$Problems->difficulty,
            $Problems->creation_date,
            $Problems->source,
            $Problems->order,
            is_null($Problems->deprecated) ? null : (int)$Problems->deprecated,
            is_null($Problems->email_clarifications) ? null : (int)$Problems->email_clarifications,
            is_null($Problems->quality) ? null : (float)$Problems->quality,
            $Problems->quality_histogram,
            $Problems->difficulty_histogram,
            is_null($Problems->problem_id) ? null : (int)$Problems->problem_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link Problems} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Problems} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link Problems Un objeto del tipo {@link Problems}. NULL si no hay tal registro.
     */
    final public static function getByPK($problem_id) {
        if (is_null($problem_id)) {
            return null;
        }
        $sql = 'SELECT `Problems`.`problem_id`, `Problems`.`acl_id`, `Problems`.`visibility`, `Problems`.`title`, `Problems`.`alias`, `Problems`.`commit`, `Problems`.`current_version`, `Problems`.`languages`, `Problems`.`input_limit`, `Problems`.`visits`, `Problems`.`submissions`, `Problems`.`accepted`, `Problems`.`difficulty`, `Problems`.`creation_date`, `Problems`.`source`, `Problems`.`order`, `Problems`.`deprecated`, `Problems`.`email_clarifications`, `Problems`.`quality`, `Problems`.`quality_histogram`, `Problems`.`difficulty_histogram` FROM Problems WHERE (problem_id = ?) LIMIT 1;';
        $params = [$problem_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new Problems($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Problems suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param Problems [$Problems] El objeto de tipo Problems a eliminar
     */
    final public static function delete(Problems $Problems) {
        $sql = 'DELETE FROM `Problems` WHERE problem_id = ?;';
        $params = [$Problems->problem_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link Problems}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link Problems}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Problems`.`problem_id`, `Problems`.`acl_id`, `Problems`.`visibility`, `Problems`.`title`, `Problems`.`alias`, `Problems`.`commit`, `Problems`.`current_version`, `Problems`.`languages`, `Problems`.`input_limit`, `Problems`.`visits`, `Problems`.`submissions`, `Problems`.`accepted`, `Problems`.`difficulty`, `Problems`.`creation_date`, `Problems`.`source`, `Problems`.`order`, `Problems`.`deprecated`, `Problems`.`email_clarifications`, `Problems`.`quality`, `Problems`.`quality_histogram`, `Problems`.`difficulty_histogram` from Problems';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new Problems($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Problems suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param Problems [$Problems] El objeto de tipo Problems a crear.
     */
    final public static function create(Problems $Problems) {
        if (is_null($Problems->visibility)) {
            $Problems->visibility = 1;
        }
        if (is_null($Problems->commit)) {
            $Problems->commit = 'published';
        }
        if (is_null($Problems->languages)) {
            $Problems->languages = 'c,cpp,java,py,rb,pl,cs,pas,hs,cpp11,lua';
        }
        if (is_null($Problems->input_limit)) {
            $Problems->input_limit = 10240;
        }
        if (is_null($Problems->visits)) {
            $Problems->visits = 0;
        }
        if (is_null($Problems->submissions)) {
            $Problems->submissions = 0;
        }
        if (is_null($Problems->accepted)) {
            $Problems->accepted = 0;
        }
        if (is_null($Problems->creation_date)) {
            $Problems->creation_date = gmdate('Y-m-d H:i:s', Time::get());
        }
        if (is_null($Problems->order)) {
            $Problems->order = 'normal';
        }
        if (is_null($Problems->deprecated)) {
            $Problems->deprecated = false;
        }
        if (is_null($Problems->email_clarifications)) {
            $Problems->email_clarifications = false;
        }
        $sql = 'INSERT INTO Problems (`acl_id`, `visibility`, `title`, `alias`, `commit`, `current_version`, `languages`, `input_limit`, `visits`, `submissions`, `accepted`, `difficulty`, `creation_date`, `source`, `order`, `deprecated`, `email_clarifications`, `quality`, `quality_histogram`, `difficulty_histogram`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            is_null($Problems->acl_id) ? null : (int)$Problems->acl_id,
            is_null($Problems->visibility) ? null : (int)$Problems->visibility,
            $Problems->title,
            $Problems->alias,
            $Problems->commit,
            $Problems->current_version,
            $Problems->languages,
            is_null($Problems->input_limit) ? null : (int)$Problems->input_limit,
            is_null($Problems->visits) ? null : (int)$Problems->visits,
            is_null($Problems->submissions) ? null : (int)$Problems->submissions,
            is_null($Problems->accepted) ? null : (int)$Problems->accepted,
            is_null($Problems->difficulty) ? null : (float)$Problems->difficulty,
            $Problems->creation_date,
            $Problems->source,
            $Problems->order,
            is_null($Problems->deprecated) ? null : (int)$Problems->deprecated,
            is_null($Problems->email_clarifications) ? null : (int)$Problems->email_clarifications,
            is_null($Problems->quality) ? null : (float)$Problems->quality,
            $Problems->quality_histogram,
            $Problems->difficulty_histogram,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Problems->problem_id = $conn->Insert_ID();

        return $ar;
    }
}
