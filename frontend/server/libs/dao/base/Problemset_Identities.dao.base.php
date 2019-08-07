<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ProblemsetIdentities Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link ProblemsetIdentities}.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsetIdentitiesDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ProblemsetIdentities}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces replace() creará una nueva fila.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ProblemsetIdentities [$Problemset_Identities] El objeto de tipo ProblemsetIdentities
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(ProblemsetIdentities $Problemset_Identities) : int {
        if (is_null($Problemset_Identities->identity_id) || is_null($Problemset_Identities->problemset_id)) {
            throw new NotFoundException('recordNotFound');
        }
        if (is_null($Problemset_Identities->score)) {
            $Problemset_Identities->score = 1;
        }
        if (is_null($Problemset_Identities->time)) {
            $Problemset_Identities->time = 1;
        }
        if (is_null($Problemset_Identities->is_invited)) {
            $Problemset_Identities->is_invited = false;
        }
        $sql = 'REPLACE INTO Problemset_Identities (`identity_id`, `problemset_id`, `access_time`, `score`, `time`, `share_user_information`, `privacystatement_consent_id`, `is_invited`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            (int)$Problemset_Identities->identity_id,
            (int)$Problemset_Identities->problemset_id,
            $Problemset_Identities->access_time,
            (int)$Problemset_Identities->score,
            (int)$Problemset_Identities->time,
            is_null($Problemset_Identities->share_user_information) ? null : (int)$Problemset_Identities->share_user_information,
            is_null($Problemset_Identities->privacystatement_consent_id) ? null : (int)$Problemset_Identities->privacystatement_consent_id,
            (int)$Problemset_Identities->is_invited,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param ProblemsetIdentities [$Problemset_Identities] El objeto de tipo ProblemsetIdentities a actualizar.
     */
    final public static function update(ProblemsetIdentities $Problemset_Identities) : int {
        $sql = 'UPDATE `Problemset_Identities` SET `access_time` = ?, `end_time` = ?, `score` = ?, `time` = ?, `share_user_information` = ?, `privacystatement_consent_id` = ?, `is_invited` = ? WHERE `identity_id` = ? AND `problemset_id` = ?;';
        $params = [
            $Problemset_Identities->access_time,
            $Problemset_Identities->end_time,
            (int)$Problemset_Identities->score,
            (int)$Problemset_Identities->time,
            is_null($Problemset_Identities->share_user_information) ? null : (int)$Problemset_Identities->share_user_information,
            is_null($Problemset_Identities->privacystatement_consent_id) ? null : (int)$Problemset_Identities->privacystatement_consent_id,
            (int)$Problemset_Identities->is_invited,
            (int)$Problemset_Identities->identity_id,
            (int)$Problemset_Identities->problemset_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link ProblemsetIdentities} por llave primaria.
     *
     * Este metodo cargará un objeto {@link ProblemsetIdentities} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link ProblemsetIdentities Un objeto del tipo {@link ProblemsetIdentities}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $identity_id, int $problemset_id) : ?ProblemsetIdentities {
        $sql = 'SELECT `Problemset_Identities`.`identity_id`, `Problemset_Identities`.`problemset_id`, `Problemset_Identities`.`access_time`, `Problemset_Identities`.`end_time`, `Problemset_Identities`.`score`, `Problemset_Identities`.`time`, `Problemset_Identities`.`share_user_information`, `Problemset_Identities`.`privacystatement_consent_id`, `Problemset_Identities`.`is_invited` FROM Problemset_Identities WHERE (identity_id = ? AND problemset_id = ?) LIMIT 1;';
        $params = [$identity_id, $problemset_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new ProblemsetIdentities($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto ProblemsetIdentities suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param ProblemsetIdentities [$Problemset_Identities] El objeto de tipo ProblemsetIdentities a eliminar
     */
    final public static function delete(ProblemsetIdentities $Problemset_Identities) : void {
        $sql = 'DELETE FROM `Problemset_Identities` WHERE identity_id = ? AND problemset_id = ?;';
        $params = [$Problemset_Identities->identity_id, $Problemset_Identities->problemset_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link ProblemsetIdentities}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link ProblemsetIdentities}.
     */
    final public static function getAll(
        ?int $pagina = null,
        ?int $filasPorPagina = null,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Problemset_Identities`.`identity_id`, `Problemset_Identities`.`problemset_id`, `Problemset_Identities`.`access_time`, `Problemset_Identities`.`end_time`, `Problemset_Identities`.`score`, `Problemset_Identities`.`time`, `Problemset_Identities`.`share_user_information`, `Problemset_Identities`.`privacystatement_consent_id`, `Problemset_Identities`.`is_invited` from Problemset_Identities';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new ProblemsetIdentities($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ProblemsetIdentities suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param ProblemsetIdentities [$Problemset_Identities] El objeto de tipo ProblemsetIdentities a crear.
     */
    final public static function create(ProblemsetIdentities $Problemset_Identities) : int {
        if (is_null($Problemset_Identities->score)) {
            $Problemset_Identities->score = 1;
        }
        if (is_null($Problemset_Identities->time)) {
            $Problemset_Identities->time = 1;
        }
        if (is_null($Problemset_Identities->is_invited)) {
            $Problemset_Identities->is_invited = false;
        }
        $sql = 'INSERT INTO Problemset_Identities (`identity_id`, `problemset_id`, `access_time`, `end_time`, `score`, `time`, `share_user_information`, `privacystatement_consent_id`, `is_invited`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            (int)$Problemset_Identities->identity_id,
            (int)$Problemset_Identities->problemset_id,
            $Problemset_Identities->access_time,
            $Problemset_Identities->end_time,
            (int)$Problemset_Identities->score,
            (int)$Problemset_Identities->time,
            is_null($Problemset_Identities->share_user_information) ? null : (int)$Problemset_Identities->share_user_information,
            is_null($Problemset_Identities->privacystatement_consent_id) ? null : (int)$Problemset_Identities->privacystatement_consent_id,
            (int)$Problemset_Identities->is_invited,
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
