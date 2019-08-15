<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** GroupsIdentities Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link GroupsIdentities}.
 * @access public
 * @abstract
 *
 */
abstract class GroupsIdentitiesDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link GroupsIdentities}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces replace() creará una nueva fila.
     *
     * @throws Exception si la operacion fallo.
     *
     * @param GroupsIdentities $Groups_Identities El objeto de tipo GroupsIdentities
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(GroupsIdentities $Groups_Identities) : int {
        if (empty($Groups_Identities->group_id) || empty($Groups_Identities->identity_id)) {
            throw new NotFoundException('recordNotFound');
        }
        $sql = 'REPLACE INTO Groups_Identities (`group_id`, `identity_id`, `share_user_information`, `privacystatement_consent_id`, `accept_teacher`) VALUES (?, ?, ?, ?, ?);';
        /**
         * For some reason, psalm is not able to correctly assess the types in
         * the ternary expressions below.
         *
         * @psalm-suppress DocblockTypeContradiction
         * @psalm-suppress RedundantConditionGivenDocblockType
         */
        $params = [
            !is_null($Groups_Identities->group_id) ? intval($Groups_Identities->group_id) : null,
            !is_null($Groups_Identities->identity_id) ? intval($Groups_Identities->identity_id) : null,
            !is_null($Groups_Identities->share_user_information) ? intval($Groups_Identities->share_user_information) : null,
            !is_null($Groups_Identities->privacystatement_consent_id) ? intval($Groups_Identities->privacystatement_consent_id) : null,
            $Groups_Identities->accept_teacher,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @param GroupsIdentities $Groups_Identities El objeto de tipo GroupsIdentities a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(GroupsIdentities $Groups_Identities) : int {
        $sql = 'UPDATE `Groups_Identities` SET `share_user_information` = ?, `privacystatement_consent_id` = ?, `accept_teacher` = ? WHERE `group_id` = ? AND `identity_id` = ?;';
        $params = [
            is_null($Groups_Identities->share_user_information) ? null : (int)$Groups_Identities->share_user_information,
            is_null($Groups_Identities->privacystatement_consent_id) ? null : (int)$Groups_Identities->privacystatement_consent_id,
            $Groups_Identities->accept_teacher,
            is_null($Groups_Identities->group_id) ? null : (int)$Groups_Identities->group_id,
            is_null($Groups_Identities->identity_id) ? null : (int)$Groups_Identities->identity_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link GroupsIdentities} por llave primaria.
     *
     * Este metodo cargará un objeto {@link GroupsIdentities} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?GroupsIdentities Un objeto del tipo {@link GroupsIdentities}. NULL si no hay tal registro.
     */
    final public static function getByPK(?int $group_id, ?int $identity_id) : ?GroupsIdentities {
        $sql = 'SELECT `Groups_Identities`.`group_id`, `Groups_Identities`.`identity_id`, `Groups_Identities`.`share_user_information`, `Groups_Identities`.`privacystatement_consent_id`, `Groups_Identities`.`accept_teacher` FROM Groups_Identities WHERE (group_id = ? AND identity_id = ?) LIMIT 1;';
        $params = [$group_id, $identity_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new GroupsIdentities($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto GroupsIdentities suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param GroupsIdentities $Groups_Identities El objeto de tipo GroupsIdentities a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(GroupsIdentities $Groups_Identities) : void {
        $sql = 'DELETE FROM `Groups_Identities` WHERE group_id = ? AND identity_id = ?;';
        $params = [$Groups_Identities->group_id, $Groups_Identities->identity_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link GroupsIdentities}.
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
     * @return GroupsIdentities[] Un arreglo que contiene objetos del tipo {@link GroupsIdentities}.
     *
     * @psalm-return array<int, GroupsIdentities>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Groups_Identities`.`group_id`, `Groups_Identities`.`identity_id`, `Groups_Identities`.`share_user_information`, `Groups_Identities`.`privacystatement_consent_id`, `Groups_Identities`.`accept_teacher` from Groups_Identities';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new GroupsIdentities($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto GroupsIdentities suministrado.
     *
     * @param GroupsIdentities $Groups_Identities El objeto de tipo GroupsIdentities a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(GroupsIdentities $Groups_Identities) : int {
        $sql = 'INSERT INTO Groups_Identities (`group_id`, `identity_id`, `share_user_information`, `privacystatement_consent_id`, `accept_teacher`) VALUES (?, ?, ?, ?, ?);';
        $params = [
            is_null($Groups_Identities->group_id) ? null : (int)$Groups_Identities->group_id,
            is_null($Groups_Identities->identity_id) ? null : (int)$Groups_Identities->identity_id,
            is_null($Groups_Identities->share_user_information) ? null : (int)$Groups_Identities->share_user_information,
            is_null($Groups_Identities->privacystatement_consent_id) ? null : (int)$Groups_Identities->privacystatement_consent_id,
            $Groups_Identities->accept_teacher,
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
