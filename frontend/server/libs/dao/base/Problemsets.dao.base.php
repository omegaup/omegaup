<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Problemsets Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Problemsets}.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsetsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Problemsets}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Problemsets [$Problemsets] El objeto de tipo Problemsets
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(Problemsets $Problemsets) : int {
        if (is_null($Problemsets->problemset_id) ||
            is_null(self::getByPK($Problemsets->problemset_id))
        ) {
            return ProblemsetsDAOBase::create($Problemsets);
        }
        return ProblemsetsDAOBase::update($Problemsets);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param Problemsets [$Problemsets] El objeto de tipo Problemsets a actualizar.
     */
    final public static function update(Problemsets $Problemsets) : int {
        $sql = 'UPDATE `Problemsets` SET `acl_id` = ?, `access_mode` = ?, `languages` = ?, `needs_basic_information` = ?, `requests_user_information` = ?, `scoreboard_url` = ?, `scoreboard_url_admin` = ?, `type` = ?, `contest_id` = ?, `assignment_id` = ?, `interview_id` = ? WHERE `problemset_id` = ?;';
        $params = [
            (int)$Problemsets->acl_id,
            $Problemsets->access_mode,
            $Problemsets->languages,
            (int)$Problemsets->needs_basic_information,
            $Problemsets->requests_user_information,
            $Problemsets->scoreboard_url,
            $Problemsets->scoreboard_url_admin,
            $Problemsets->type,
            is_null($Problemsets->contest_id) ? null : (int)$Problemsets->contest_id,
            is_null($Problemsets->assignment_id) ? null : (int)$Problemsets->assignment_id,
            is_null($Problemsets->interview_id) ? null : (int)$Problemsets->interview_id,
            (int)$Problemsets->problemset_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link Problemsets} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Problemsets} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link Problemsets Un objeto del tipo {@link Problemsets}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $problemset_id) : ?Problemsets {
        $sql = 'SELECT `Problemsets`.`problemset_id`, `Problemsets`.`acl_id`, `Problemsets`.`access_mode`, `Problemsets`.`languages`, `Problemsets`.`needs_basic_information`, `Problemsets`.`requests_user_information`, `Problemsets`.`scoreboard_url`, `Problemsets`.`scoreboard_url_admin`, `Problemsets`.`type`, `Problemsets`.`contest_id`, `Problemsets`.`assignment_id`, `Problemsets`.`interview_id` FROM Problemsets WHERE (problemset_id = ?) LIMIT 1;';
        $params = [$problemset_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new Problemsets($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Problemsets suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param Problemsets [$Problemsets] El objeto de tipo Problemsets a eliminar
     */
    final public static function delete(Problemsets $Problemsets) : void {
        $sql = 'DELETE FROM `Problemsets` WHERE problemset_id = ?;';
        $params = [$Problemsets->problemset_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link Problemsets}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link Problemsets}.
     */
    final public static function getAll(
        ?int $pagina = null,
        ?int $filasPorPagina = null,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Problemsets`.`problemset_id`, `Problemsets`.`acl_id`, `Problemsets`.`access_mode`, `Problemsets`.`languages`, `Problemsets`.`needs_basic_information`, `Problemsets`.`requests_user_information`, `Problemsets`.`scoreboard_url`, `Problemsets`.`scoreboard_url_admin`, `Problemsets`.`type`, `Problemsets`.`contest_id`, `Problemsets`.`assignment_id`, `Problemsets`.`interview_id` from Problemsets';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new Problemsets($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Problemsets suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param Problemsets [$Problemsets] El objeto de tipo Problemsets a crear.
     */
    final public static function create(Problemsets $Problemsets) : int {
        if (is_null($Problemsets->access_mode)) {
            $Problemsets->access_mode = 'public';
        }
        if (is_null($Problemsets->needs_basic_information)) {
            $Problemsets->needs_basic_information = false;
        }
        if (is_null($Problemsets->requests_user_information)) {
            $Problemsets->requests_user_information = 'no';
        }
        if (is_null($Problemsets->type)) {
            $Problemsets->type = 'Contest';
        }
        $sql = 'INSERT INTO Problemsets (`acl_id`, `access_mode`, `languages`, `needs_basic_information`, `requests_user_information`, `scoreboard_url`, `scoreboard_url_admin`, `type`, `contest_id`, `assignment_id`, `interview_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            (int)$Problemsets->acl_id,
            $Problemsets->access_mode,
            $Problemsets->languages,
            (int)$Problemsets->needs_basic_information,
            $Problemsets->requests_user_information,
            $Problemsets->scoreboard_url,
            $Problemsets->scoreboard_url_admin,
            $Problemsets->type,
            is_null($Problemsets->contest_id) ? null : (int)$Problemsets->contest_id,
            is_null($Problemsets->assignment_id) ? null : (int)$Problemsets->assignment_id,
            is_null($Problemsets->interview_id) ? null : (int)$Problemsets->interview_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Problemsets->problemset_id = $conn->Insert_ID();

        return $affectedRows;
    }
}
