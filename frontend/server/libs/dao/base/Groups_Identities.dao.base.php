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
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param GroupsIdentities [$Groups_Identities] El objeto de tipo GroupsIdentities
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(GroupsIdentities $Groups_Identities) {
        if (is_null(self::getByPK($Groups_Identities->group_id, $Groups_Identities->identity_id))) {
            return GroupsIdentitiesDAOBase::create($Groups_Identities);
        }
        return GroupsIdentitiesDAOBase::update($Groups_Identities);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param GroupsIdentities [$Groups_Identities] El objeto de tipo GroupsIdentities a actualizar.
     */
    final public static function update(GroupsIdentities $Groups_Identities) {
        $sql = 'UPDATE `Groups_Identities` SET `share_user_information` = ?, `privacystatement_consent_id` = ?, `accept_teacher` = ? WHERE `group_id` = ? AND `identity_id` = ?;';
        $params = [
            $Groups_Identities->share_user_information,
            $Groups_Identities->privacystatement_consent_id,
            $Groups_Identities->accept_teacher,
            $Groups_Identities->group_id,
            $Groups_Identities->identity_id,
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
     * @static
     * @return @link GroupsIdentities Un objeto del tipo {@link GroupsIdentities}. NULL si no hay tal registro.
     */
    final public static function getByPK($group_id, $identity_id) {
        if (is_null($group_id) || is_null($identity_id)) {
            return null;
        }
        $sql = 'SELECT `Groups_Identities`.`group_id`, `Groups_Identities`.`identity_id`, `Groups_Identities`.`share_user_information`, `Groups_Identities`.`privacystatement_consent_id`, `Groups_Identities`.`accept_teacher` FROM Groups_Identities WHERE (group_id = ? AND identity_id = ?) LIMIT 1;';
        $params = [$group_id, $identity_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return new GroupsIdentities($rs);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto GroupsIdentities suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param GroupsIdentities [$Groups_Identities] El objeto de tipo GroupsIdentities a eliminar
     */
    final public static function delete(GroupsIdentities $Groups_Identities) {
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
     * @static
     * @param $pagina Página a ver.
     * @param $filasPorPagina Filas por página.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link GroupsIdentities}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Groups_Identities`.`group_id`, `Groups_Identities`.`identity_id`, `Groups_Identities`.`share_user_information`, `Groups_Identities`.`privacystatement_consent_id`, `Groups_Identities`.`accept_teacher` from Groups_Identities';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . mysqli_real_escape_string($conn->_connectionID, $orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $rs = $conn->Execute($sql);
        $allData = [];
        foreach ($rs as $row) {
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
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param GroupsIdentities [$Groups_Identities] El objeto de tipo GroupsIdentities a crear.
     */
    final public static function create(GroupsIdentities $Groups_Identities) {
        $sql = 'INSERT INTO Groups_Identities (`group_id`, `identity_id`, `share_user_information`, `privacystatement_consent_id`, `accept_teacher`) VALUES (?, ?, ?, ?, ?);';
        $params = [
            $Groups_Identities->group_id,
            $Groups_Identities->identity_id,
            $Groups_Identities->share_user_information,
            $Groups_Identities->privacystatement_consent_id,
            $Groups_Identities->accept_teacher,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }

        return $ar;
    }
}
