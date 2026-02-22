<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** ProblemsetProblems Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\ProblemsetProblems}.
 * @access public
 * @abstract
 */
abstract class ProblemsetProblems {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link \OmegaUp\DAO\VO\ProblemsetProblems}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces replace() creará una nueva fila.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException si las columnas de la
     * llave primaria están vacías.
     *
     * @param \OmegaUp\DAO\VO\ProblemsetProblems $Problemset_Problems El
     * objeto de tipo {@link \OmegaUp\DAO\VO\ProblemsetProblems}.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(
        \OmegaUp\DAO\VO\ProblemsetProblems $Problemset_Problems
    ): int {
        if (
            empty($Problemset_Problems->problemset_id) ||
            empty($Problemset_Problems->problem_id)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }
        $sql = '
            REPLACE INTO
                Problemset_Problems (
                    `problemset_id`,
                    `problem_id`,
                    `commit`,
                    `version`,
                    `points`,
                    `order`,
                    `is_extra_problem`
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                );';
        $params = [
            $Problemset_Problems->problemset_id,
            $Problemset_Problems->problem_id,
            $Problemset_Problems->commit,
            $Problemset_Problems->version,
            floatval($Problemset_Problems->points),
            intval($Problemset_Problems->order),
            intval($Problemset_Problems->is_extra_problem),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\ProblemsetProblems $Problemset_Problems El objeto de tipo ProblemsetProblems a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\ProblemsetProblems $Problemset_Problems
    ): int {
        $sql = '
            UPDATE
                `Problemset_Problems`
            SET
                `commit` = ?,
                `version` = ?,
                `points` = ?,
                `order` = ?,
                `is_extra_problem` = ?
            WHERE
                (
                    `problemset_id` = ? AND
                    `problem_id` = ?
                );';
        $params = [
            $Problemset_Problems->commit,
            $Problemset_Problems->version,
            floatval($Problemset_Problems->points),
            intval($Problemset_Problems->order),
            intval($Problemset_Problems->is_extra_problem),
            (
                $Problemset_Problems->problemset_id === null ?
                null :
                intval($Problemset_Problems->problemset_id)
            ),
            (
                $Problemset_Problems->problem_id === null ?
                null :
                intval($Problemset_Problems->problem_id)
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\ProblemsetProblems} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\ProblemsetProblems}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\ProblemsetProblems Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\ProblemsetProblems} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        ?int $problemset_id,
        ?int $problem_id
    ): ?\OmegaUp\DAO\VO\ProblemsetProblems {
        $sql = '
            SELECT
                `Problemset_Problems`.`problemset_id`,
                `Problemset_Problems`.`problem_id`,
                `Problemset_Problems`.`commit`,
                `Problemset_Problems`.`version`,
                `Problemset_Problems`.`points`,
                `Problemset_Problems`.`order`,
                `Problemset_Problems`.`is_extra_problem`
            FROM
                `Problemset_Problems`
            WHERE
                (
                    `problemset_id` = ? AND
                    `problem_id` = ?
                )
            LIMIT 1;';
        $params = [$problemset_id, $problem_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\ProblemsetProblems($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\ProblemsetProblems} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\ProblemsetProblems}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        ?int $problemset_id,
        ?int $problem_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Problemset_Problems`
            WHERE
                (
                    `problemset_id` = ? AND
                    `problem_id` = ?
                );';
        $params = [$problemset_id, $problem_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Contar todos los registros en `Problemset_Problems`.
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
                `Problemset_Problems`;';
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, []);
        return intval($count);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\ProblemsetProblems} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\ProblemsetProblems $Problemset_Problems El
     * objeto de tipo \OmegaUp\DAO\VO\ProblemsetProblems a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\ProblemsetProblems $Problemset_Problems
    ): void {
        $sql = '
            DELETE FROM
                `Problemset_Problems`
            WHERE
                (
                    `problemset_id` = ? AND
                    `problem_id` = ?
                );';
        $params = [
            $Problemset_Problems->problemset_id,
            $Problemset_Problems->problem_id
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
     * {@link \OmegaUp\DAO\VO\ProblemsetProblems}.
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
     * @return list<\OmegaUp\DAO\VO\ProblemsetProblems> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\ProblemsetProblems}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = 'problemset_id',
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
                `Problemset_Problems`.`problemset_id`,
                `Problemset_Problems`.`problem_id`,
                `Problemset_Problems`.`commit`,
                `Problemset_Problems`.`version`,
                `Problemset_Problems`.`points`,
                `Problemset_Problems`.`order`,
                `Problemset_Problems`.`is_extra_problem`
            FROM
                `Problemset_Problems`
            ORDER BY
                `{$sanitizedOrder}` {$tipoDeOrden}
        ";
        if ($pagina !== null) {
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
            $allData[] = new \OmegaUp\DAO\VO\ProblemsetProblems(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\ProblemsetProblems}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\ProblemsetProblems $Problemset_Problems El
     * objeto de tipo {@link \OmegaUp\DAO\VO\ProblemsetProblems}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\ProblemsetProblems $Problemset_Problems
    ): int {
        $sql = '
            INSERT INTO
                `Problemset_Problems` (
                    `problemset_id`,
                    `problem_id`,
                    `commit`,
                    `version`,
                    `points`,
                    `order`,
                    `is_extra_problem`
                ) VALUES (
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
                $Problemset_Problems->problemset_id === null ?
                null :
                intval($Problemset_Problems->problemset_id)
            ),
            (
                $Problemset_Problems->problem_id === null ?
                null :
                intval($Problemset_Problems->problem_id)
            ),
            $Problemset_Problems->commit,
            $Problemset_Problems->version,
            floatval($Problemset_Problems->points),
            intval($Problemset_Problems->order),
            intval($Problemset_Problems->is_extra_problem),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }

        return $affectedRows;
    }
}
