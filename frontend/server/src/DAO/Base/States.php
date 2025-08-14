<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** States Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\States}.
 * @access public
 * @abstract
 */
abstract class States {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link \OmegaUp\DAO\VO\States}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces replace() creará una nueva fila.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException si las columnas de la
     * llave primaria están vacías.
     *
     * @param \OmegaUp\DAO\VO\States $States El
     * objeto de tipo {@link \OmegaUp\DAO\VO\States}.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(
        \OmegaUp\DAO\VO\States $States
    ): int {
        if (
            empty($States->country_id) ||
            empty($States->state_id)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }
        $sql = '
            REPLACE INTO
                States (
                    `country_id`,
                    `state_id`,
                    `name`
                ) VALUES (
                    ?,
                    ?,
                    ?
                );';
        $params = [
            $States->country_id,
            $States->state_id,
            $States->name,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\States $States El objeto de tipo States a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\States $States
    ): int {
        $sql = '
            UPDATE
                `States`
            SET
                `name` = ?
            WHERE
                (
                    `country_id` = ? AND
                    `state_id` = ?
                );';
        $params = [
            $States->name,
            $States->country_id,
            $States->state_id,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\States} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\States}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\States Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\States} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        ?string $country_id,
        ?string $state_id
    ): ?\OmegaUp\DAO\VO\States {
        $sql = '
            SELECT
                `States`.`country_id`,
                `States`.`state_id`,
                `States`.`name`
            FROM
                `States`
            WHERE
                (
                    `country_id` = ? AND
                    `state_id` = ?
                )
            LIMIT 1;';
        $params = [$country_id, $state_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\States($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\States} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\States}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        ?string $country_id,
        ?string $state_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `States`
            WHERE
                (
                    `country_id` = ? AND
                    `state_id` = ?
                );';
        $params = [$country_id, $state_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\States} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\States $States El
     * objeto de tipo \OmegaUp\DAO\VO\States a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\States $States
    ): void {
        $sql = '
            DELETE FROM
                `States`
            WHERE
                (
                    `country_id` = ? AND
                    `state_id` = ?
                );';
        $params = [
            $States->country_id,
            $States->state_id
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
     * {@link \OmegaUp\DAO\VO\States}.
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
     * @return list<\OmegaUp\DAO\VO\States> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\States}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = '`States`.`country_id`',
        string $tipoDeOrden = 'ASC'
    ): array {
        $sql = '
            SELECT
                `States`.`country_id`,
                `States`.`state_id`,
                `States`.`name`
            FROM
                `States`
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
            $allData[] = new \OmegaUp\DAO\VO\States(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\States}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\States $States El
     * objeto de tipo {@link \OmegaUp\DAO\VO\States}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\States $States
    ): int {
        $sql = '
            INSERT INTO
                `States` (
                    `country_id`,
                    `state_id`,
                    `name`
                ) VALUES (
                    ?,
                    ?,
                    ?
                );';
        $params = [
            $States->country_id,
            $States->state_id,
            $States->name,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }

        return $affectedRows;
    }
}
