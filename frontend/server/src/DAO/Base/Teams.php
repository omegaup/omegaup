<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** Teams Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Teams}.
 * @access public
 * @abstract
 */
abstract class Teams {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\Teams $Teams El objeto de tipo Teams a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\Teams $Teams
    ): int {
        $sql = '
            UPDATE
                `Teams`
            SET
                `team_group_id` = ?,
                `identity_id` = ?
            WHERE
                (
                    `team_id` = ?
                );';
        $params = [
            (
                is_null($Teams->team_group_id) ?
                null :
                intval($Teams->team_group_id)
            ),
            (
                is_null($Teams->identity_id) ?
                null :
                intval($Teams->identity_id)
            ),
            intval($Teams->team_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\Teams} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\Teams}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\Teams Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\Teams} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $team_id
    ): ?\OmegaUp\DAO\VO\Teams {
        $sql = '
            SELECT
                `Teams`.`team_id`,
                `Teams`.`team_group_id`,
                `Teams`.`identity_id`
            FROM
                `Teams`
            WHERE
                (
                    `team_id` = ?
                )
            LIMIT 1;';
        $params = [$team_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Teams($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\Teams} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\Teams}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        int $team_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Teams`
            WHERE
                (
                    `team_id` = ?
                );';
        $params = [$team_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\Teams} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\Teams $Teams El
     * objeto de tipo \OmegaUp\DAO\VO\Teams a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\Teams $Teams
    ): void {
        $sql = '
            DELETE FROM
                `Teams`
            WHERE
                (
                    `team_id` = ?
                );';
        $params = [
            $Teams->team_id
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
     * {@link \OmegaUp\DAO\VO\Teams}.
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
     * @return list<\OmegaUp\DAO\VO\Teams> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\Teams}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ): array {
        $sql = '
            SELECT
                `Teams`.`team_id`,
                `Teams`.`team_group_id`,
                `Teams`.`identity_id`
            FROM
                `Teams`
        ';
        if (!is_null($orden)) {
            $sql .= (
                ' ORDER BY `' .
                \OmegaUp\MySQLConnection::getInstance()->escape($orden) .
                '` ' .
                ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC')
            );
        }
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
            $allData[] = new \OmegaUp\DAO\VO\Teams(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\Teams}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\Teams $Teams El
     * objeto de tipo {@link \OmegaUp\DAO\VO\Teams}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\Teams $Teams
    ): int {
        $sql = '
            INSERT INTO
                `Teams` (
                    `team_group_id`,
                    `identity_id`
                ) VALUES (
                    ?,
                    ?
                );';
        $params = [
            (
                is_null($Teams->team_group_id) ?
                null :
                intval($Teams->team_group_id)
            ),
            (
                is_null($Teams->identity_id) ?
                null :
                intval($Teams->identity_id)
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Teams->team_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }

    final public static function getFields(): string {
        return '
                `t`.`team_id`,
                `t`.`team_group_id`,
                `t`.`identity_id`
               ';
    }
}
