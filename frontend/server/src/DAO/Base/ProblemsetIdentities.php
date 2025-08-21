<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** ProblemsetIdentities Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\ProblemsetIdentities}.
 * @access public
 * @abstract
 */
abstract class ProblemsetIdentities {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link \OmegaUp\DAO\VO\ProblemsetIdentities}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces replace() creará una nueva fila.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException si las columnas de la
     * llave primaria están vacías.
     *
     * @param \OmegaUp\DAO\VO\ProblemsetIdentities $Problemset_Identities El
     * objeto de tipo {@link \OmegaUp\DAO\VO\ProblemsetIdentities}.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(
        \OmegaUp\DAO\VO\ProblemsetIdentities $Problemset_Identities
    ): int {
        if (
            empty($Problemset_Identities->identity_id) ||
            empty($Problemset_Identities->problemset_id)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }
        $sql = '
            REPLACE INTO
                Problemset_Identities (
                    `identity_id`,
                    `problemset_id`,
                    `access_time`,
                    `end_time`,
                    `score`,
                    `time`,
                    `share_user_information`,
                    `privacystatement_consent_id`,
                    `is_invited`
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                );';
        $params = [
            $Problemset_Identities->identity_id,
            $Problemset_Identities->problemset_id,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Problemset_Identities->access_time
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Problemset_Identities->end_time
            ),
            intval($Problemset_Identities->score),
            intval($Problemset_Identities->time),
            (
                !is_null($Problemset_Identities->share_user_information) ?
                intval($Problemset_Identities->share_user_information) :
                null
            ),
            (
                !is_null($Problemset_Identities->privacystatement_consent_id) ?
                intval($Problemset_Identities->privacystatement_consent_id) :
                null
            ),
            intval($Problemset_Identities->is_invited),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\ProblemsetIdentities $Problemset_Identities El objeto de tipo ProblemsetIdentities a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\ProblemsetIdentities $Problemset_Identities
    ): int {
        $sql = '
            UPDATE
                `Problemset_Identities`
            SET
                `access_time` = ?,
                `end_time` = ?,
                `score` = ?,
                `time` = ?,
                `share_user_information` = ?,
                `privacystatement_consent_id` = ?,
                `is_invited` = ?
            WHERE
                (
                    `identity_id` = ? AND
                    `problemset_id` = ?
                );';
        $params = [
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Problemset_Identities->access_time
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Problemset_Identities->end_time
            ),
            intval($Problemset_Identities->score),
            intval($Problemset_Identities->time),
            (
                is_null($Problemset_Identities->share_user_information) ?
                null :
                intval($Problemset_Identities->share_user_information)
            ),
            (
                is_null($Problemset_Identities->privacystatement_consent_id) ?
                null :
                intval($Problemset_Identities->privacystatement_consent_id)
            ),
            intval($Problemset_Identities->is_invited),
            (
                is_null($Problemset_Identities->identity_id) ?
                null :
                intval($Problemset_Identities->identity_id)
            ),
            (
                is_null($Problemset_Identities->problemset_id) ?
                null :
                intval($Problemset_Identities->problemset_id)
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\ProblemsetIdentities} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\ProblemsetIdentities}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\ProblemsetIdentities Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\ProblemsetIdentities} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        ?int $identity_id,
        ?int $problemset_id
    ): ?\OmegaUp\DAO\VO\ProblemsetIdentities {
        $sql = '
            SELECT
                `Problemset_Identities`.`identity_id`,
                `Problemset_Identities`.`problemset_id`,
                `Problemset_Identities`.`access_time`,
                `Problemset_Identities`.`end_time`,
                `Problemset_Identities`.`score`,
                `Problemset_Identities`.`time`,
                `Problemset_Identities`.`share_user_information`,
                `Problemset_Identities`.`privacystatement_consent_id`,
                `Problemset_Identities`.`is_invited`
            FROM
                `Problemset_Identities`
            WHERE
                (
                    `identity_id` = ? AND
                    `problemset_id` = ?
                )
            LIMIT 1;';
        $params = [$identity_id, $problemset_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\ProblemsetIdentities($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\ProblemsetIdentities} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\ProblemsetIdentities}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        ?int $identity_id,
        ?int $problemset_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Problemset_Identities`
            WHERE
                (
                    `identity_id` = ? AND
                    `problemset_id` = ?
                );';
        $params = [$identity_id, $problemset_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\ProblemsetIdentities} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\ProblemsetIdentities $Problemset_Identities El
     * objeto de tipo \OmegaUp\DAO\VO\ProblemsetIdentities a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\ProblemsetIdentities $Problemset_Identities
    ): void {
        $sql = '
            DELETE FROM
                `Problemset_Identities`
            WHERE
                (
                    `identity_id` = ? AND
                    `problemset_id` = ?
                );';
        $params = [
            $Problemset_Identities->identity_id,
            $Problemset_Identities->problemset_id
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
     * {@link \OmegaUp\DAO\VO\ProblemsetIdentities}.
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
     * @return list<\OmegaUp\DAO\VO\ProblemsetIdentities> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\ProblemsetIdentities}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = 'identity_id',
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
                `Problemset_Identities`.`identity_id`,
                `Problemset_Identities`.`problemset_id`,
                `Problemset_Identities`.`access_time`,
                `Problemset_Identities`.`end_time`,
                `Problemset_Identities`.`score`,
                `Problemset_Identities`.`time`,
                `Problemset_Identities`.`share_user_information`,
                `Problemset_Identities`.`privacystatement_consent_id`,
                `Problemset_Identities`.`is_invited`
            FROM
                `Problemset_Identities`
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
            $allData[] = new \OmegaUp\DAO\VO\ProblemsetIdentities(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\ProblemsetIdentities}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\ProblemsetIdentities $Problemset_Identities El
     * objeto de tipo {@link \OmegaUp\DAO\VO\ProblemsetIdentities}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\ProblemsetIdentities $Problemset_Identities
    ): int {
        $sql = '
            INSERT INTO
                `Problemset_Identities` (
                    `identity_id`,
                    `problemset_id`,
                    `access_time`,
                    `end_time`,
                    `score`,
                    `time`,
                    `share_user_information`,
                    `privacystatement_consent_id`,
                    `is_invited`
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                );';
        $params = [
            (
                is_null($Problemset_Identities->identity_id) ?
                null :
                intval($Problemset_Identities->identity_id)
            ),
            (
                is_null($Problemset_Identities->problemset_id) ?
                null :
                intval($Problemset_Identities->problemset_id)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Problemset_Identities->access_time
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Problemset_Identities->end_time
            ),
            intval($Problemset_Identities->score),
            intval($Problemset_Identities->time),
            (
                is_null($Problemset_Identities->share_user_information) ?
                null :
                intval($Problemset_Identities->share_user_information)
            ),
            (
                is_null($Problemset_Identities->privacystatement_consent_id) ?
                null :
                intval($Problemset_Identities->privacystatement_consent_id)
            ),
            intval($Problemset_Identities->is_invited),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }

        return $affectedRows;
    }
}
