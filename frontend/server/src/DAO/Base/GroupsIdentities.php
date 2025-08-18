<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** GroupsIdentities Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\GroupsIdentities}.
 * @access public
 * @abstract
 */
abstract class GroupsIdentities {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link \OmegaUp\DAO\VO\GroupsIdentities}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces replace() creará una nueva fila.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException si las columnas de la
     * llave primaria están vacías.
     *
     * @param \OmegaUp\DAO\VO\GroupsIdentities $Groups_Identities El
     * objeto de tipo {@link \OmegaUp\DAO\VO\GroupsIdentities}.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(
        \OmegaUp\DAO\VO\GroupsIdentities $Groups_Identities
    ): int {
        if (
            empty($Groups_Identities->group_id) ||
            empty($Groups_Identities->identity_id)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }
        $sql = '
            REPLACE INTO
                Groups_Identities (
                    `group_id`,
                    `identity_id`,
                    `share_user_information`,
                    `privacystatement_consent_id`,
                    `accept_teacher`,
                    `is_invited`
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                );';
        $params = [
            $Groups_Identities->group_id,
            $Groups_Identities->identity_id,
            (
                !is_null($Groups_Identities->share_user_information) ?
                intval($Groups_Identities->share_user_information) :
                null
            ),
            (
                !is_null($Groups_Identities->privacystatement_consent_id) ?
                intval($Groups_Identities->privacystatement_consent_id) :
                null
            ),
            (
                !is_null($Groups_Identities->accept_teacher) ?
                intval($Groups_Identities->accept_teacher) :
                null
            ),
            intval($Groups_Identities->is_invited),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\GroupsIdentities $Groups_Identities El objeto de tipo GroupsIdentities a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\GroupsIdentities $Groups_Identities
    ): int {
        $sql = '
            UPDATE
                `Groups_Identities`
            SET
                `share_user_information` = ?,
                `privacystatement_consent_id` = ?,
                `accept_teacher` = ?,
                `is_invited` = ?
            WHERE
                (
                    `group_id` = ? AND
                    `identity_id` = ?
                );';
        $params = [
            (
                is_null($Groups_Identities->share_user_information) ?
                null :
                intval($Groups_Identities->share_user_information)
            ),
            (
                is_null($Groups_Identities->privacystatement_consent_id) ?
                null :
                intval($Groups_Identities->privacystatement_consent_id)
            ),
            (
                is_null($Groups_Identities->accept_teacher) ?
                null :
                intval($Groups_Identities->accept_teacher)
            ),
            intval($Groups_Identities->is_invited),
            (
                is_null($Groups_Identities->group_id) ?
                null :
                intval($Groups_Identities->group_id)
            ),
            (
                is_null($Groups_Identities->identity_id) ?
                null :
                intval($Groups_Identities->identity_id)
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\GroupsIdentities} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\GroupsIdentities}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\GroupsIdentities Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\GroupsIdentities} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        ?int $group_id,
        ?int $identity_id
    ): ?\OmegaUp\DAO\VO\GroupsIdentities {
        $sql = '
            SELECT
                `Groups_Identities`.`group_id`,
                `Groups_Identities`.`identity_id`,
                `Groups_Identities`.`share_user_information`,
                `Groups_Identities`.`privacystatement_consent_id`,
                `Groups_Identities`.`accept_teacher`,
                `Groups_Identities`.`is_invited`
            FROM
                `Groups_Identities`
            WHERE
                (
                    `group_id` = ? AND
                    `identity_id` = ?
                )
            LIMIT 1;';
        $params = [$group_id, $identity_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\GroupsIdentities($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\GroupsIdentities} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\GroupsIdentities}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        ?int $group_id,
        ?int $identity_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Groups_Identities`
            WHERE
                (
                    `group_id` = ? AND
                    `identity_id` = ?
                );';
        $params = [$group_id, $identity_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\GroupsIdentities} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\GroupsIdentities $Groups_Identities El
     * objeto de tipo \OmegaUp\DAO\VO\GroupsIdentities a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\GroupsIdentities $Groups_Identities
    ): void {
        $sql = '
            DELETE FROM
                `Groups_Identities`
            WHERE
                (
                    `group_id` = ? AND
                    `identity_id` = ?
                );';
        $params = [
            $Groups_Identities->group_id,
            $Groups_Identities->identity_id
        ];

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
     * {@link \OmegaUp\DAO\VO\GroupsIdentities}.
     * Este método consume una cantidad de memoria proporcional al número de
     * registros regresados, así que sólo debe usarse cuando la tabla en
     * cuestión es pequeña o se proporcionan parámetros para obtener un menor
     * número de filas.
     *
     * @param ?int $pagina Página a ver.
     * @param int $filasPorPagina Filas por página.
     * @param string $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param string $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     *
     * @return list<\OmegaUp\DAO\VO\GroupsIdentities> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\GroupsIdentities}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = 'group_id',
        string $tipoDeOrden = 'ASC'
    ): array {
        $sanitizedOrder = \OmegaUp\MySQLConnection::getInstance()->escape(
            $orden
        );
        \OmegaUp\Validators::validateInEnum(
            $tipoDeOrden,
            'order_type',
            [
                'ASC',
                'DESC',
            ]
        );
        $sql = "
            SELECT
                `Groups_Identities`.`group_id`,
                `Groups_Identities`.`identity_id`,
                `Groups_Identities`.`share_user_information`,
                `Groups_Identities`.`privacystatement_consent_id`,
                `Groups_Identities`.`accept_teacher`,
                `Groups_Identities`.`is_invited`
            FROM
                `Groups_Identities`
            ORDER BY
                `{$sanitizedOrder}` {$tipoDeOrden}
        ";
        if (!is_null($pagina)) {
            $sql .= (
                ' LIMIT ' .
                (($pagina - 1) * $filasPorPagina) .
                ', ' .
                intval($filasPorPagina)
            );
        }
        $allData = [];
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll($sql) as $row
        ) {
            $allData[] = new \OmegaUp\DAO\VO\GroupsIdentities(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\GroupsIdentities}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\GroupsIdentities $Groups_Identities El
     * objeto de tipo {@link \OmegaUp\DAO\VO\GroupsIdentities}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\GroupsIdentities $Groups_Identities
    ): int {
        $sql = '
            INSERT INTO
                `Groups_Identities` (
                    `group_id`,
                    `identity_id`,
                    `share_user_information`,
                    `privacystatement_consent_id`,
                    `accept_teacher`,
                    `is_invited`
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                );';
        $params = [
            (
                is_null($Groups_Identities->group_id) ?
                null :
                intval($Groups_Identities->group_id)
            ),
            (
                is_null($Groups_Identities->identity_id) ?
                null :
                intval($Groups_Identities->identity_id)
            ),
            (
                is_null($Groups_Identities->share_user_information) ?
                null :
                intval($Groups_Identities->share_user_information)
            ),
            (
                is_null($Groups_Identities->privacystatement_consent_id) ?
                null :
                intval($Groups_Identities->privacystatement_consent_id)
            ),
            (
                is_null($Groups_Identities->accept_teacher) ?
                null :
                intval($Groups_Identities->accept_teacher)
            ),
            intval($Groups_Identities->is_invited),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }

        return $affectedRows;
    }
}
