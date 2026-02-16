<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** Users Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Users}.
 * @access public
 * @abstract
 */
abstract class Users {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\Users $Users El objeto de tipo Users a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\Users $Users
    ): int {
        $sql = '
            UPDATE
                `Users`
            SET
                `facebook_user_id` = ?,
                `git_token` = ?,
                `main_email_id` = ?,
                `main_identity_id` = ?,
                `has_learning_objective` = ?,
                `has_teaching_objective` = ?,
                `has_scholar_objective` = ?,
                `has_competitive_objective` = ?,
                `scholar_degree` = ?,
                `birth_date` = ?,
                `verified` = ?,
                `verification_id` = ?,
                `deletion_token` = ?,
                `reset_digest` = ?,
                `reset_sent_at` = ?,
                `hide_problem_tags` = ?,
                `in_mailing_list` = ?,
                `is_private` = ?,
                `preferred_language` = ?,
                `parent_verified` = ?,
                `creation_timestamp` = ?,
                `parental_verification_token` = ?,
                `parent_email_verification_initial` = ?,
                `parent_email_verification_deadline` = ?,
                `parent_email_id` = ?,
                `x_url` = ?,
                `linkedin_url` = ?,
                `github_url` = ?
            WHERE
                (
                    `user_id` = ?
                );';
        $params = [
            $Users->facebook_user_id,
            $Users->git_token,
            (
                is_null($Users->main_email_id) ?
                null :
                intval($Users->main_email_id)
            ),
            (
                is_null($Users->main_identity_id) ?
                null :
                intval($Users->main_identity_id)
            ),
            (
                is_null($Users->has_learning_objective) ?
                null :
                intval($Users->has_learning_objective)
            ),
            (
                is_null($Users->has_teaching_objective) ?
                null :
                intval($Users->has_teaching_objective)
            ),
            (
                is_null($Users->has_scholar_objective) ?
                null :
                intval($Users->has_scholar_objective)
            ),
            (
                is_null($Users->has_competitive_objective) ?
                null :
                intval($Users->has_competitive_objective)
            ),
            $Users->scholar_degree,
            $Users->birth_date,
            intval($Users->verified),
            $Users->verification_id,
            $Users->deletion_token,
            $Users->reset_digest,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Users->reset_sent_at
            ),
            (
                is_null($Users->hide_problem_tags) ?
                null :
                intval($Users->hide_problem_tags)
            ),
            intval($Users->in_mailing_list),
            intval($Users->is_private),
            $Users->preferred_language,
            (
                is_null($Users->parent_verified) ?
                null :
                intval($Users->parent_verified)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Users->creation_timestamp
            ),
            $Users->parental_verification_token,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Users->parent_email_verification_initial
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Users->parent_email_verification_deadline
            ),
            (
                is_null($Users->parent_email_id) ?
                null :
                intval($Users->parent_email_id)
            ),
            $Users->x_url,
            $Users->linkedin_url,
            $Users->github_url,
            intval($Users->user_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\Users} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\Users}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\Users Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\Users} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $user_id
    ): ?\OmegaUp\DAO\VO\Users {
        $sql = '
            SELECT
                `Users`.`user_id`,
                `Users`.`facebook_user_id`,
                `Users`.`git_token`,
                `Users`.`main_email_id`,
                `Users`.`main_identity_id`,
                `Users`.`has_learning_objective`,
                `Users`.`has_teaching_objective`,
                `Users`.`has_scholar_objective`,
                `Users`.`has_competitive_objective`,
                `Users`.`scholar_degree`,
                `Users`.`birth_date`,
                `Users`.`verified`,
                `Users`.`verification_id`,
                `Users`.`deletion_token`,
                `Users`.`reset_digest`,
                `Users`.`reset_sent_at`,
                `Users`.`hide_problem_tags`,
                `Users`.`in_mailing_list`,
                `Users`.`is_private`,
                `Users`.`preferred_language`,
                `Users`.`parent_verified`,
                `Users`.`creation_timestamp`,
                `Users`.`parental_verification_token`,
                `Users`.`parent_email_verification_initial`,
                `Users`.`parent_email_verification_deadline`,
                `Users`.`parent_email_id`,
                `Users`.`x_url`,
                `Users`.`linkedin_url`,
                `Users`.`github_url`
            FROM
                `Users`
            WHERE
                (
                    `user_id` = ?
                )
            LIMIT 1;';
        $params = [$user_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Users($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\Users} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\Users}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        int $user_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Users`
            WHERE
                (
                    `user_id` = ?
                );';
        $params = [$user_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Contar todos los registros en `Users`.
     *
     * Este método obtiene el número total de filas de la tabla **sin cargar campos**,
     * útil para pruebas donde sólo se valida el conteo.
     *
     * @return int Número total de registros.
     */
    final public static function countAll(): int {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Users`;';
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, []);
        return intval($count);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\Users} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\Users $Users El
     * objeto de tipo \OmegaUp\DAO\VO\Users a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\Users $Users
    ): void {
        $sql = '
            DELETE FROM
                `Users`
            WHERE
                (
                    `user_id` = ?
                );';
        $params = [
            $Users->user_id
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
     * {@link \OmegaUp\DAO\VO\Users}.
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
     * @return list<\OmegaUp\DAO\VO\Users> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\Users}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = 'user_id',
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
                `Users`.`user_id`,
                `Users`.`facebook_user_id`,
                `Users`.`git_token`,
                `Users`.`main_email_id`,
                `Users`.`main_identity_id`,
                `Users`.`has_learning_objective`,
                `Users`.`has_teaching_objective`,
                `Users`.`has_scholar_objective`,
                `Users`.`has_competitive_objective`,
                `Users`.`scholar_degree`,
                `Users`.`birth_date`,
                `Users`.`verified`,
                `Users`.`verification_id`,
                `Users`.`deletion_token`,
                `Users`.`reset_digest`,
                `Users`.`reset_sent_at`,
                `Users`.`hide_problem_tags`,
                `Users`.`in_mailing_list`,
                `Users`.`is_private`,
                `Users`.`preferred_language`,
                `Users`.`parent_verified`,
                `Users`.`creation_timestamp`,
                `Users`.`parental_verification_token`,
                `Users`.`parent_email_verification_initial`,
                `Users`.`parent_email_verification_deadline`,
                `Users`.`parent_email_id`,
                `Users`.`x_url`,
                `Users`.`linkedin_url`,
                `Users`.`github_url`
            FROM
                `Users`
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
            $allData[] = new \OmegaUp\DAO\VO\Users(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\Users}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\Users $Users El
     * objeto de tipo {@link \OmegaUp\DAO\VO\Users}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\Users $Users
    ): int {
        $sql = '
            INSERT INTO
                `Users` (
                    `facebook_user_id`,
                    `git_token`,
                    `main_email_id`,
                    `main_identity_id`,
                    `has_learning_objective`,
                    `has_teaching_objective`,
                    `has_scholar_objective`,
                    `has_competitive_objective`,
                    `scholar_degree`,
                    `birth_date`,
                    `verified`,
                    `verification_id`,
                    `deletion_token`,
                    `reset_digest`,
                    `reset_sent_at`,
                    `hide_problem_tags`,
                    `in_mailing_list`,
                    `is_private`,
                    `preferred_language`,
                    `parent_verified`,
                    `creation_timestamp`,
                    `parental_verification_token`,
                    `parent_email_verification_initial`,
                    `parent_email_verification_deadline`,
                    `parent_email_id`,
                    `x_url`,
                    `linkedin_url`,
                    `github_url`
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
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
            $Users->facebook_user_id,
            $Users->git_token,
            (
                is_null($Users->main_email_id) ?
                null :
                intval($Users->main_email_id)
            ),
            (
                is_null($Users->main_identity_id) ?
                null :
                intval($Users->main_identity_id)
            ),
            (
                is_null($Users->has_learning_objective) ?
                null :
                intval($Users->has_learning_objective)
            ),
            (
                is_null($Users->has_teaching_objective) ?
                null :
                intval($Users->has_teaching_objective)
            ),
            (
                is_null($Users->has_scholar_objective) ?
                null :
                intval($Users->has_scholar_objective)
            ),
            (
                is_null($Users->has_competitive_objective) ?
                null :
                intval($Users->has_competitive_objective)
            ),
            $Users->scholar_degree,
            $Users->birth_date,
            intval($Users->verified),
            $Users->verification_id,
            $Users->deletion_token,
            $Users->reset_digest,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Users->reset_sent_at
            ),
            (
                is_null($Users->hide_problem_tags) ?
                null :
                intval($Users->hide_problem_tags)
            ),
            intval($Users->in_mailing_list),
            intval($Users->is_private),
            $Users->preferred_language,
            (
                is_null($Users->parent_verified) ?
                null :
                intval($Users->parent_verified)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Users->creation_timestamp
            ),
            $Users->parental_verification_token,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Users->parent_email_verification_initial
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Users->parent_email_verification_deadline
            ),
            (
                is_null($Users->parent_email_id) ?
                null :
                intval($Users->parent_email_id)
            ),
            $Users->x_url,
            $Users->linkedin_url,
            $Users->github_url,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Users->user_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
