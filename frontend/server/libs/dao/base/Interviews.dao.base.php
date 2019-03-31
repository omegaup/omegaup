<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Interviews Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link Interviews }.
 * @access public
 * @abstract
 *
 */
abstract class InterviewsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Interviews} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Interviews [$Interviews] El objeto de tipo Interviews
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(Interviews $Interviews) {
        if (!is_null(self::getByPK($Interviews->interview_id))) {
            return InterviewsDAOBase::update($Interviews);
        } else {
            return InterviewsDAOBase::create($Interviews);
        }
    }

    /**
     * Obtener {@link Interviews} por llave primaria.
     *
     * Este metodo cargara un objeto {@link Interviews} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link Interviews Un objeto del tipo {@link Interviews}. NULL si no hay tal registro.
     */
    final public static function getByPK($interview_id) {
        if (is_null($interview_id)) {
            return null;
        }
        $sql = 'SELECT `Interviews`.`interview_id`, `Interviews`.`problemset_id`, `Interviews`.`acl_id`, `Interviews`.`alias`, `Interviews`.`title`, `Interviews`.`description`, `Interviews`.`window_length` FROM Interviews WHERE (interview_id = ?) LIMIT 1;';
        $params = [$interview_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new Interviews($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link Interviews}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link Interviews}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Interviews`.`interview_id`, `Interviews`.`problemset_id`, `Interviews`.`acl_id`, `Interviews`.`alias`, `Interviews`.`title`, `Interviews`.`description`, `Interviews`.`window_length` from Interviews';
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
            $allData[] = new Interviews($row);
        }
        return $allData;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param Interviews [$Interviews] El objeto de tipo Interviews a actualizar.
      */
    final private static function update(Interviews $Interviews) {
        $sql = 'UPDATE `Interviews` SET `problemset_id` = ?, `acl_id` = ?, `alias` = ?, `title` = ?, `description` = ?, `window_length` = ? WHERE `interview_id` = ?;';
        $params = [
            $Interviews->problemset_id,
            $Interviews->acl_id,
            $Interviews->alias,
            $Interviews->title,
            $Interviews->description,
            $Interviews->window_length,
            $Interviews->interview_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Interviews suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto Interviews dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param Interviews [$Interviews] El objeto de tipo Interviews a crear.
     */
    final private static function create(Interviews $Interviews) {
        $sql = 'INSERT INTO Interviews (`interview_id`, `problemset_id`, `acl_id`, `alias`, `title`, `description`, `window_length`) VALUES (?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Interviews->interview_id,
            $Interviews->problemset_id,
            $Interviews->acl_id,
            $Interviews->alias,
            $Interviews->title,
            $Interviews->description,
            $Interviews->window_length,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Interviews->interview_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto Interviews suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @param Interviews [$Interviews] El objeto de tipo Interviews a eliminar
     */
    final public static function delete(Interviews $Interviews) {
        $sql = 'DELETE FROM `Interviews` WHERE interview_id = ?;';
        $params = [$Interviews->interview_id];
        global $conn;

        $conn->Execute($sql, $params);
        if ($conn->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }
}
