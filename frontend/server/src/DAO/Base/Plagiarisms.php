<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** Plagiarisms Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Plagiarisms}.
 * @access public
 * @abstract
 */
abstract class Plagiarisms {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\Plagiarisms $Plagiarisms El objeto de tipo Plagiarisms a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\Plagiarisms $Plagiarisms
    ): int {
        $sql = '
            UPDATE
                `Plagiarisms`
            SET
                `contest_id` = ?,
                `submission_id_1` = ?,
                `submission_id_2` = ?,
                `score_1` = ?,
                `score_2` = ?,
                `contents` = ?
            WHERE
                (
                    `plagiarism_id` = ?
                );';
        $params = [
            (
                is_null($Plagiarisms->contest_id) ?
                null :
                intval($Plagiarisms->contest_id)
            ),
            (
                is_null($Plagiarisms->submission_id_1) ?
                null :
                intval($Plagiarisms->submission_id_1)
            ),
            (
                is_null($Plagiarisms->submission_id_2) ?
                null :
                intval($Plagiarisms->submission_id_2)
            ),
            (
                is_null($Plagiarisms->score_1) ?
                null :
                intval($Plagiarisms->score_1)
            ),
            (
                is_null($Plagiarisms->score_2) ?
                null :
                intval($Plagiarisms->score_2)
            ),
            $Plagiarisms->contents,
            intval($Plagiarisms->plagiarism_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\Plagiarisms} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\Plagiarisms}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\Plagiarisms Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\Plagiarisms} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $plagiarism_id
    ): ?\OmegaUp\DAO\VO\Plagiarisms {
        $sql = '
            SELECT
                `Plagiarisms`.`plagiarism_id`,
                `Plagiarisms`.`contest_id`,
                `Plagiarisms`.`submission_id_1`,
                `Plagiarisms`.`submission_id_2`,
                `Plagiarisms`.`score_1`,
                `Plagiarisms`.`score_2`,
                `Plagiarisms`.`contents`
            FROM
                `Plagiarisms`
            WHERE
                (
                    `plagiarism_id` = ?
                )
            LIMIT 1;';
        $params = [$plagiarism_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Plagiarisms($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\Plagiarisms} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\Plagiarisms}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        int $plagiarism_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Plagiarisms`
            WHERE
                (
                    `plagiarism_id` = ?
                );';
        $params = [$plagiarism_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\Plagiarisms} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\Plagiarisms $Plagiarisms El
     * objeto de tipo \OmegaUp\DAO\VO\Plagiarisms a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\Plagiarisms $Plagiarisms
    ): void {
        $sql = '
            DELETE FROM
                `Plagiarisms`
            WHERE
                (
                    `plagiarism_id` = ?
                );';
        $params = [
            $Plagiarisms->plagiarism_id
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
     * {@link \OmegaUp\DAO\VO\Plagiarisms}.
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
     * @return list<\OmegaUp\DAO\VO\Plagiarisms> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\Plagiarisms}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = '`Plagiarisms`.`plagiarism_id`',
        string $tipoDeOrden = 'ASC'
    ): array {
        $sql = '
            SELECT
                `Plagiarisms`.`plagiarism_id`,
                `Plagiarisms`.`contest_id`,
                `Plagiarisms`.`submission_id_1`,
                `Plagiarisms`.`submission_id_2`,
                `Plagiarisms`.`score_1`,
                `Plagiarisms`.`score_2`,
                `Plagiarisms`.`contents`
            FROM
                `Plagiarisms`
        ';
        $sql .= (
            ' ORDER BY `' .
            \OmegaUp\MySQLConnection::getInstance()->escape($orden) .
            '` ' .
            ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC')
        );
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
            $allData[] = new \OmegaUp\DAO\VO\Plagiarisms(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\Plagiarisms}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\Plagiarisms $Plagiarisms El
     * objeto de tipo {@link \OmegaUp\DAO\VO\Plagiarisms}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\Plagiarisms $Plagiarisms
    ): int {
        $sql = '
            INSERT INTO
                `Plagiarisms` (
                    `contest_id`,
                    `submission_id_1`,
                    `submission_id_2`,
                    `score_1`,
                    `score_2`,
                    `contents`
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
                is_null($Plagiarisms->contest_id) ?
                null :
                intval($Plagiarisms->contest_id)
            ),
            (
                is_null($Plagiarisms->submission_id_1) ?
                null :
                intval($Plagiarisms->submission_id_1)
            ),
            (
                is_null($Plagiarisms->submission_id_2) ?
                null :
                intval($Plagiarisms->submission_id_2)
            ),
            (
                is_null($Plagiarisms->score_1) ?
                null :
                intval($Plagiarisms->score_1)
            ),
            (
                is_null($Plagiarisms->score_2) ?
                null :
                intval($Plagiarisms->score_2)
            ),
            $Plagiarisms->contents,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Plagiarisms->plagiarism_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
