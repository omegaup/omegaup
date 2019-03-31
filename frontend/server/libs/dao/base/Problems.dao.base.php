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
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link Problems }.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Problems} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Problems [$Problems] El objeto de tipo Problems
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(Problems $Problems) {
        if (!is_null(self::getByPK($Problems->problem_id))) {
            return ProblemsDAOBase::update($Problems);
        } else {
            return ProblemsDAOBase::create($Problems);
        }
    }

    /**
     * Obtener {@link Problems} por llave primaria.
     *
     * Este metodo cargara un objeto {@link Problems} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link Problems Un objeto del tipo {@link Problems}. NULL si no hay tal registro.
     */
    final public static function getByPK($problem_id) {
        if (is_null($problem_id)) {
            return null;
        }
        $sql = 'SELECT `Problems`.`problem_id`, `Problems`.`acl_id`, `Problems`.`visibility`, `Problems`.`title`, `Problems`.`alias`, `Problems`.`validator`, `Problems`.`languages`, `Problems`.`server`, `Problems`.`remote_id`, `Problems`.`time_limit`, `Problems`.`validator_time_limit`, `Problems`.`overall_wall_time_limit`, `Problems`.`extra_wall_time`, `Problems`.`memory_limit`, `Problems`.`output_limit`, `Problems`.`input_limit`, `Problems`.`visits`, `Problems`.`submissions`, `Problems`.`accepted`, `Problems`.`difficulty`, `Problems`.`creation_date`, `Problems`.`source`, `Problems`.`order`, `Problems`.`tolerance`, `Problems`.`slow`, `Problems`.`deprecated`, `Problems`.`email_clarifications`, `Problems`.`quality`, `Problems`.`quality_histogram`, `Problems`.`difficulty_histogram` FROM Problems WHERE (problem_id = ?) LIMIT 1;';
        $params = [$problem_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new Problems($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link Problems}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link Problems}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Problems`.`problem_id`, `Problems`.`acl_id`, `Problems`.`visibility`, `Problems`.`title`, `Problems`.`alias`, `Problems`.`validator`, `Problems`.`languages`, `Problems`.`server`, `Problems`.`remote_id`, `Problems`.`time_limit`, `Problems`.`validator_time_limit`, `Problems`.`overall_wall_time_limit`, `Problems`.`extra_wall_time`, `Problems`.`memory_limit`, `Problems`.`output_limit`, `Problems`.`input_limit`, `Problems`.`visits`, `Problems`.`submissions`, `Problems`.`accepted`, `Problems`.`difficulty`, `Problems`.`creation_date`, `Problems`.`source`, `Problems`.`order`, `Problems`.`tolerance`, `Problems`.`slow`, `Problems`.`deprecated`, `Problems`.`email_clarifications`, `Problems`.`quality`, `Problems`.`quality_histogram`, `Problems`.`difficulty_histogram` from Problems';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . mysqli_real_escape_string($conn->_connectionID, $orden) . '` ' . ($tipo_de_orden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $columnas_por_pagina) . ', ' . (int)$columnas_por_pagina;
        }
        $rs = $conn->Execute($sql);
        $allData = [];
        foreach ($rs as $row) {
            $allData[] = new Problems($row);
        }
        return $allData;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param Problems [$Problems] El objeto de tipo Problems a actualizar.
      */
    final private static function update(Problems $Problems) {
        $sql = 'UPDATE `Problems` SET `acl_id` = ?, `visibility` = ?, `title` = ?, `alias` = ?, `validator` = ?, `languages` = ?, `server` = ?, `remote_id` = ?, `time_limit` = ?, `validator_time_limit` = ?, `overall_wall_time_limit` = ?, `extra_wall_time` = ?, `memory_limit` = ?, `output_limit` = ?, `input_limit` = ?, `visits` = ?, `submissions` = ?, `accepted` = ?, `difficulty` = ?, `creation_date` = ?, `source` = ?, `order` = ?, `tolerance` = ?, `slow` = ?, `deprecated` = ?, `email_clarifications` = ?, `quality` = ?, `quality_histogram` = ?, `difficulty_histogram` = ? WHERE `problem_id` = ?;';
        $params = [
            $Problems->acl_id,
            $Problems->visibility,
            $Problems->title,
            $Problems->alias,
            $Problems->validator,
            $Problems->languages,
            $Problems->server,
            $Problems->remote_id,
            $Problems->time_limit,
            $Problems->validator_time_limit,
            $Problems->overall_wall_time_limit,
            $Problems->extra_wall_time,
            $Problems->memory_limit,
            $Problems->output_limit,
            $Problems->input_limit,
            $Problems->visits,
            $Problems->submissions,
            $Problems->accepted,
            $Problems->difficulty,
            $Problems->creation_date,
            $Problems->source,
            $Problems->order,
            $Problems->tolerance,
            $Problems->slow,
            $Problems->deprecated,
            $Problems->email_clarifications,
            $Problems->quality,
            $Problems->quality_histogram,
            $Problems->difficulty_histogram,
            $Problems->problem_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Problems suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto Problems dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param Problems [$Problems] El objeto de tipo Problems a crear.
     */
    final private static function create(Problems $Problems) {
        if (is_null($Problems->visibility)) {
            $Problems->visibility = '1';
        }
        if (is_null($Problems->validator)) {
            $Problems->validator = 'token-numeric';
        }
        if (is_null($Problems->languages)) {
            $Problems->languages = 'c,cpp,java,py,rb,pl,cs,pas,hs,cpp11,lua';
        }
        if (is_null($Problems->time_limit)) {
            $Problems->time_limit = '3000';
        }
        if (is_null($Problems->validator_time_limit)) {
            $Problems->validator_time_limit = '3000';
        }
        if (is_null($Problems->overall_wall_time_limit)) {
            $Problems->overall_wall_time_limit = '60000';
        }
        if (is_null($Problems->extra_wall_time)) {
            $Problems->extra_wall_time = '0';
        }
        if (is_null($Problems->memory_limit)) {
            $Problems->memory_limit = '64';
        }
        if (is_null($Problems->output_limit)) {
            $Problems->output_limit = '10240';
        }
        if (is_null($Problems->input_limit)) {
            $Problems->input_limit = '10240';
        }
        if (is_null($Problems->visits)) {
            $Problems->visits = '0';
        }
        if (is_null($Problems->submissions)) {
            $Problems->submissions = '0';
        }
        if (is_null($Problems->accepted)) {
            $Problems->accepted = '0';
        }
        if (is_null($Problems->creation_date)) {
            $Problems->creation_date = gmdate('Y-m-d H:i:s');
        }
        if (is_null($Problems->order)) {
            $Problems->order = 'normal';
        }
        if (is_null($Problems->tolerance)) {
            $Problems->tolerance = '0.000000001';
        }
        if (is_null($Problems->slow)) {
            $Problems->slow = '0';
        }
        if (is_null($Problems->deprecated)) {
            $Problems->deprecated = '0';
        }
        if (is_null($Problems->email_clarifications)) {
            $Problems->email_clarifications = '0';
        }
        $sql = 'INSERT INTO Problems (`problem_id`, `acl_id`, `visibility`, `title`, `alias`, `validator`, `languages`, `server`, `remote_id`, `time_limit`, `validator_time_limit`, `overall_wall_time_limit`, `extra_wall_time`, `memory_limit`, `output_limit`, `input_limit`, `visits`, `submissions`, `accepted`, `difficulty`, `creation_date`, `source`, `order`, `tolerance`, `slow`, `deprecated`, `email_clarifications`, `quality`, `quality_histogram`, `difficulty_histogram`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Problems->problem_id,
            $Problems->acl_id,
            $Problems->visibility,
            $Problems->title,
            $Problems->alias,
            $Problems->validator,
            $Problems->languages,
            $Problems->server,
            $Problems->remote_id,
            $Problems->time_limit,
            $Problems->validator_time_limit,
            $Problems->overall_wall_time_limit,
            $Problems->extra_wall_time,
            $Problems->memory_limit,
            $Problems->output_limit,
            $Problems->input_limit,
            $Problems->visits,
            $Problems->submissions,
            $Problems->accepted,
            $Problems->difficulty,
            $Problems->creation_date,
            $Problems->source,
            $Problems->order,
            $Problems->tolerance,
            $Problems->slow,
            $Problems->deprecated,
            $Problems->email_clarifications,
            $Problems->quality,
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

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto Problems suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
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
}
