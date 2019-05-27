<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** PrivacyStatements Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link PrivacyStatements}.
 * @access public
 * @abstract
 *
 */
abstract class PrivacyStatementsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link PrivacyStatements}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param PrivacyStatements [$PrivacyStatements] El objeto de tipo PrivacyStatements
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(PrivacyStatements $PrivacyStatements) {
        if (is_null(self::getByPK($PrivacyStatements->privacystatement_id))) {
            return PrivacyStatementsDAOBase::create($PrivacyStatements);
        }
        return PrivacyStatementsDAOBase::update($PrivacyStatements);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param PrivacyStatements [$PrivacyStatements] El objeto de tipo PrivacyStatements a actualizar.
     */
    final public static function update(PrivacyStatements $PrivacyStatements) {
        $sql = 'UPDATE `PrivacyStatements` SET `git_object_id` = ?, `type` = ? WHERE `privacystatement_id` = ?;';
        $params = [
            $PrivacyStatements->git_object_id,
            $PrivacyStatements->type,
            is_null($PrivacyStatements->privacystatement_id) ? null : (int)$PrivacyStatements->privacystatement_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link PrivacyStatements} por llave primaria.
     *
     * Este metodo cargará un objeto {@link PrivacyStatements} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link PrivacyStatements Un objeto del tipo {@link PrivacyStatements}. NULL si no hay tal registro.
     */
    final public static function getByPK($privacystatement_id) {
        if (is_null($privacystatement_id)) {
            return null;
        }
        $sql = 'SELECT `PrivacyStatements`.`privacystatement_id`, `PrivacyStatements`.`git_object_id`, `PrivacyStatements`.`type` FROM PrivacyStatements WHERE (privacystatement_id = ?) LIMIT 1;';
        $params = [$privacystatement_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new PrivacyStatements($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto PrivacyStatements suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param PrivacyStatements [$PrivacyStatements] El objeto de tipo PrivacyStatements a eliminar
     */
    final public static function delete(PrivacyStatements $PrivacyStatements) {
        $sql = 'DELETE FROM `PrivacyStatements` WHERE privacystatement_id = ?;';
        $params = [$PrivacyStatements->privacystatement_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link PrivacyStatements}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link PrivacyStatements}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `PrivacyStatements`.`privacystatement_id`, `PrivacyStatements`.`git_object_id`, `PrivacyStatements`.`type` from PrivacyStatements';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new PrivacyStatements($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto PrivacyStatements suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param PrivacyStatements [$PrivacyStatements] El objeto de tipo PrivacyStatements a crear.
     */
    final public static function create(PrivacyStatements $PrivacyStatements) {
        if (is_null($PrivacyStatements->type)) {
            $PrivacyStatements->type = 'privacy_policy';
        }
        $sql = 'INSERT INTO PrivacyStatements (`git_object_id`, `type`) VALUES (?, ?);';
        $params = [
            $PrivacyStatements->git_object_id,
            $PrivacyStatements->type,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $PrivacyStatements->privacystatement_id = $conn->Insert_ID();

        return $ar;
    }
}
