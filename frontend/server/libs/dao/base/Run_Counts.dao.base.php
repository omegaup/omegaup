<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** RunCounts Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link RunCounts }.
 * @access public
 * @abstract
 *
 */
abstract class RunCountsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link RunCounts} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param RunCounts [$Run_Counts] El objeto de tipo RunCounts
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(RunCounts $Run_Counts) {
        if (!is_null(self::getByPK($Run_Counts->date))) {
            return RunCountsDAOBase::update($Run_Counts);
        } else {
            return RunCountsDAOBase::create($Run_Counts);
        }
    }

    /**
     * Obtener {@link RunCounts} por llave primaria.
     *
     * Este metodo cargara un objeto {@link RunCounts} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link RunCounts Un objeto del tipo {@link RunCounts}. NULL si no hay tal registro.
     */
    final public static function getByPK($date) {
        if (is_null($date)) {
            return null;
        }
        $sql = 'SELECT `Run_Counts`.`date`, `Run_Counts`.`total`, `Run_Counts`.`ac_count` FROM Run_Counts WHERE (date = ?) LIMIT 1;';
        $params = [$date];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new RunCounts($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link RunCounts}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link RunCounts}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Run_Counts`.`date`, `Run_Counts`.`total`, `Run_Counts`.`ac_count` from Run_Counts';
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
            $allData[] = new RunCounts($row);
        }
        return $allData;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param RunCounts [$Run_Counts] El objeto de tipo RunCounts a actualizar.
      */
    final private static function update(RunCounts $Run_Counts) {
        $sql = 'UPDATE `Run_Counts` SET `total` = ?, `ac_count` = ? WHERE `date` = ?;';
        $params = [
            $Run_Counts->total,
            $Run_Counts->ac_count,
            $Run_Counts->date,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto RunCounts suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto RunCounts dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param RunCounts [$Run_Counts] El objeto de tipo RunCounts a crear.
     */
    final private static function create(RunCounts $Run_Counts) {
        if (is_null($Run_Counts->total)) {
            $Run_Counts->total = '0';
        }
        if (is_null($Run_Counts->ac_count)) {
            $Run_Counts->ac_count = '0';
        }
        $sql = 'INSERT INTO Run_Counts (`date`, `total`, `ac_count`) VALUES (?, ?, ?);';
        $params = [
            $Run_Counts->date,
            $Run_Counts->total,
            $Run_Counts->ac_count,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }

        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto RunCounts suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @param RunCounts [$Run_Counts] El objeto de tipo RunCounts a eliminar
     */
    final public static function delete(RunCounts $Run_Counts) {
        $sql = 'DELETE FROM `Run_Counts` WHERE date = ?;';
        $params = [$Run_Counts->date];
        global $conn;

        $conn->Execute($sql, $params);
        if ($conn->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }
}
