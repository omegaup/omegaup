<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** CoderOfTheMonth Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\CoderOfTheMonth}.
 * @access public
 * @abstract
 */
abstract class CoderOfTheMonth {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\CoderOfTheMonth $Coder_Of_The_Month El objeto de tipo CoderOfTheMonth a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\CoderOfTheMonth $Coder_Of_The_Month
    ): int {
        $sql = '
            UPDATE
                `Coder_Of_The_Month`
            SET
                `user_id` = ?,
                `description` = ?,
                `time` = ?,
                `interview_url` = ?,
                `ranking` = ?,
                `selected_by` = ?,
                `school_id` = ?,
                `category` = ?,
                `score` = ?,
                `problems_solved` = ?,
                `certificate_status` = ?
            WHERE
                (
                    `coder_of_the_month_id` = ?
                );';
        $params = [
            (
                is_null($Coder_Of_The_Month->user_id) ?
                null :
                intval($Coder_Of_The_Month->user_id)
            ),
            $Coder_Of_The_Month->description,
            $Coder_Of_The_Month->time,
            $Coder_Of_The_Month->interview_url,
            (
                is_null($Coder_Of_The_Month->ranking) ?
                null :
                intval($Coder_Of_The_Month->ranking)
            ),
            (
                is_null($Coder_Of_The_Month->selected_by) ?
                null :
                intval($Coder_Of_The_Month->selected_by)
            ),
            (
                is_null($Coder_Of_The_Month->school_id) ?
                null :
                intval($Coder_Of_The_Month->school_id)
            ),
            $Coder_Of_The_Month->category,
            floatval($Coder_Of_The_Month->score),
            intval($Coder_Of_The_Month->problems_solved),
            $Coder_Of_The_Month->certificate_status,
            intval($Coder_Of_The_Month->coder_of_the_month_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\CoderOfTheMonth} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\CoderOfTheMonth}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\CoderOfTheMonth Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\CoderOfTheMonth} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $coder_of_the_month_id
    ): ?\OmegaUp\DAO\VO\CoderOfTheMonth {
        $sql = '
            SELECT
                `Coder_Of_The_Month`.`coder_of_the_month_id`,
                `Coder_Of_The_Month`.`user_id`,
                `Coder_Of_The_Month`.`description`,
                `Coder_Of_The_Month`.`time`,
                `Coder_Of_The_Month`.`interview_url`,
                `Coder_Of_The_Month`.`ranking`,
                `Coder_Of_The_Month`.`selected_by`,
                `Coder_Of_The_Month`.`school_id`,
                `Coder_Of_The_Month`.`category`,
                `Coder_Of_The_Month`.`score`,
                `Coder_Of_The_Month`.`problems_solved`,
                `Coder_Of_The_Month`.`certificate_status`
            FROM
                `Coder_Of_The_Month`
            WHERE
                (
                    `coder_of_the_month_id` = ?
                )
            LIMIT 1;';
        $params = [$coder_of_the_month_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\CoderOfTheMonth($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\CoderOfTheMonth} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\CoderOfTheMonth}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        int $coder_of_the_month_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Coder_Of_The_Month`
            WHERE
                (
                    `coder_of_the_month_id` = ?
                );';
        $params = [$coder_of_the_month_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Contar todos los registros en `Coder_Of_The_Month`.
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
                `Coder_Of_The_Month`;';
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, []);
        return intval($count);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\CoderOfTheMonth} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\CoderOfTheMonth $Coder_Of_The_Month El
     * objeto de tipo \OmegaUp\DAO\VO\CoderOfTheMonth a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\CoderOfTheMonth $Coder_Of_The_Month
    ): void {
        $sql = '
            DELETE FROM
                `Coder_Of_The_Month`
            WHERE
                (
                    `coder_of_the_month_id` = ?
                );';
        $params = [
            $Coder_Of_The_Month->coder_of_the_month_id
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
     * {@link \OmegaUp\DAO\VO\CoderOfTheMonth}.
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
     * @return list<\OmegaUp\DAO\VO\CoderOfTheMonth> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\CoderOfTheMonth}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = 'coder_of_the_month_id',
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
                `Coder_Of_The_Month`.`coder_of_the_month_id`,
                `Coder_Of_The_Month`.`user_id`,
                `Coder_Of_The_Month`.`description`,
                `Coder_Of_The_Month`.`time`,
                `Coder_Of_The_Month`.`interview_url`,
                `Coder_Of_The_Month`.`ranking`,
                `Coder_Of_The_Month`.`selected_by`,
                `Coder_Of_The_Month`.`school_id`,
                `Coder_Of_The_Month`.`category`,
                `Coder_Of_The_Month`.`score`,
                `Coder_Of_The_Month`.`problems_solved`,
                `Coder_Of_The_Month`.`certificate_status`
            FROM
                `Coder_Of_The_Month`
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
            $allData[] = new \OmegaUp\DAO\VO\CoderOfTheMonth(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\CoderOfTheMonth}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\CoderOfTheMonth $Coder_Of_The_Month El
     * objeto de tipo {@link \OmegaUp\DAO\VO\CoderOfTheMonth}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\CoderOfTheMonth $Coder_Of_The_Month
    ): int {
        $sql = '
            INSERT INTO
                `Coder_Of_The_Month` (
                    `user_id`,
                    `description`,
                    `time`,
                    `interview_url`,
                    `ranking`,
                    `selected_by`,
                    `school_id`,
                    `category`,
                    `score`,
                    `problems_solved`,
                    `certificate_status`
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
            (
                is_null($Coder_Of_The_Month->user_id) ?
                null :
                intval($Coder_Of_The_Month->user_id)
            ),
            $Coder_Of_The_Month->description,
            $Coder_Of_The_Month->time,
            $Coder_Of_The_Month->interview_url,
            (
                is_null($Coder_Of_The_Month->ranking) ?
                null :
                intval($Coder_Of_The_Month->ranking)
            ),
            (
                is_null($Coder_Of_The_Month->selected_by) ?
                null :
                intval($Coder_Of_The_Month->selected_by)
            ),
            (
                is_null($Coder_Of_The_Month->school_id) ?
                null :
                intval($Coder_Of_The_Month->school_id)
            ),
            $Coder_Of_The_Month->category,
            floatval($Coder_Of_The_Month->score),
            intval($Coder_Of_The_Month->problems_solved),
            $Coder_Of_The_Month->certificate_status,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Coder_Of_The_Month->coder_of_the_month_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
