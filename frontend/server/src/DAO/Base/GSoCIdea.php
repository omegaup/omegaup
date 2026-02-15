<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** GSoCIdea Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\GSoCIdea}.
 * @access public
 * @abstract
 */
abstract class GSoCIdea {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\GSoCIdea $GSoC_Idea El objeto de tipo GSoCIdea a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\GSoCIdea $GSoC_Idea
    ): int {
        $sql = '
            UPDATE
                `GSoC_Idea`
            SET
                `title` = ?,
                `brief_description` = ?,
                `expected_results` = ?,
                `preferred_skills` = ?,
                `possible_mentors` = ?,
                `estimated_hours` = ?,
                `skill_level` = ?,
                `blog_link` = ?,
                `contributor_username` = ?,
                `created_at` = ?,
                `updated_at` = ?
            WHERE
                (
                    `idea_id` = ?
                );';
        $params = [
            $GSoC_Idea->title,
            $GSoC_Idea->brief_description,
            $GSoC_Idea->expected_results,
            $GSoC_Idea->preferred_skills,
            $GSoC_Idea->possible_mentors,
            (
                is_null($GSoC_Idea->estimated_hours) ?
                null :
                intval($GSoC_Idea->estimated_hours)
            ),
            $GSoC_Idea->skill_level,
            $GSoC_Idea->blog_link,
            $GSoC_Idea->contributor_username,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $GSoC_Idea->created_at
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $GSoC_Idea->updated_at
            ),
            intval($GSoC_Idea->idea_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\GSoCIdea} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\GSoCIdea}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\GSoCIdea Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\GSoCIdea} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $idea_id
    ): ?\OmegaUp\DAO\VO\GSoCIdea {
        $sql = '
            SELECT
                `GSoC_Idea`.`idea_id`,
                `GSoC_Idea`.`title`,
                `GSoC_Idea`.`brief_description`,
                `GSoC_Idea`.`expected_results`,
                `GSoC_Idea`.`preferred_skills`,
                `GSoC_Idea`.`possible_mentors`,
                `GSoC_Idea`.`estimated_hours`,
                `GSoC_Idea`.`skill_level`,
                `GSoC_Idea`.`blog_link`,
                `GSoC_Idea`.`contributor_username`,
                `GSoC_Idea`.`created_at`,
                `GSoC_Idea`.`updated_at`
            FROM
                `GSoC_Idea`
            WHERE
                (
                    `idea_id` = ?
                )
            LIMIT 1;';
        $params = [$idea_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\GSoCIdea($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\GSoCIdea} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\GSoCIdea}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        int $idea_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `GSoC_Idea`
            WHERE
                (
                    `idea_id` = ?
                );';
        $params = [$idea_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Contar todos los registros en `GSoC_Idea`.
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
                `GSoC_Idea`;';
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, []);
        return intval($count);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\GSoCIdea} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\GSoCIdea $GSoC_Idea El
     * objeto de tipo \OmegaUp\DAO\VO\GSoCIdea a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\GSoCIdea $GSoC_Idea
    ): void {
        $sql = '
            DELETE FROM
                `GSoC_Idea`
            WHERE
                (
                    `idea_id` = ?
                );';
        $params = [
            $GSoC_Idea->idea_id
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
     * {@link \OmegaUp\DAO\VO\GSoCIdea}.
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
     * @return list<\OmegaUp\DAO\VO\GSoCIdea> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\GSoCIdea}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = 'idea_id',
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
                `GSoC_Idea`.`idea_id`,
                `GSoC_Idea`.`title`,
                `GSoC_Idea`.`brief_description`,
                `GSoC_Idea`.`expected_results`,
                `GSoC_Idea`.`preferred_skills`,
                `GSoC_Idea`.`possible_mentors`,
                `GSoC_Idea`.`estimated_hours`,
                `GSoC_Idea`.`skill_level`,
                `GSoC_Idea`.`blog_link`,
                `GSoC_Idea`.`contributor_username`,
                `GSoC_Idea`.`created_at`,
                `GSoC_Idea`.`updated_at`
            FROM
                `GSoC_Idea`
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
            $allData[] = new \OmegaUp\DAO\VO\GSoCIdea(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\GSoCIdea}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\GSoCIdea $GSoC_Idea El
     * objeto de tipo {@link \OmegaUp\DAO\VO\GSoCIdea}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\GSoCIdea $GSoC_Idea
    ): int {
        $sql = '
            INSERT INTO
                `GSoC_Idea` (
                    `title`,
                    `brief_description`,
                    `expected_results`,
                    `preferred_skills`,
                    `possible_mentors`,
                    `estimated_hours`,
                    `skill_level`,
                    `blog_link`,
                    `contributor_username`,
                    `created_at`,
                    `updated_at`
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
                    ?
                );';
        $params = [
            $GSoC_Idea->title,
            $GSoC_Idea->brief_description,
            $GSoC_Idea->expected_results,
            $GSoC_Idea->preferred_skills,
            $GSoC_Idea->possible_mentors,
            (
                is_null($GSoC_Idea->estimated_hours) ?
                null :
                intval($GSoC_Idea->estimated_hours)
            ),
            $GSoC_Idea->skill_level,
            $GSoC_Idea->blog_link,
            $GSoC_Idea->contributor_username,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $GSoC_Idea->created_at
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $GSoC_Idea->updated_at
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $GSoC_Idea->idea_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
