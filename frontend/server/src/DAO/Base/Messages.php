<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** Messages Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Messages}.
 * @access public
 * @abstract
 */
abstract class Messages {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\Messages $Messages El objeto de tipo Messages a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\Messages $Messages
    ): int {
        $sql = '
            UPDATE
                `Messages`
            SET
                `read` = ?,
                `sender_id` = ?,
                `recipient_id` = ?,
                `message` = ?,
                `date` = ?
            WHERE
                (
                    `message_id` = ?
                );';
        $params = [
            intval($Messages->read),
            (
                $Messages->sender_id === null ?
                null :
                intval($Messages->sender_id)
            ),
            (
                $Messages->recipient_id === null ?
                null :
                intval($Messages->recipient_id)
            ),
            $Messages->message,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Messages->date
            ),
            intval($Messages->message_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\Messages} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\Messages}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\Messages Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\Messages} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $message_id
    ): ?\OmegaUp\DAO\VO\Messages {
        $sql = '
            SELECT
                `Messages`.`message_id`,
                `Messages`.`read`,
                `Messages`.`sender_id`,
                `Messages`.`recipient_id`,
                `Messages`.`message`,
                `Messages`.`date`
            FROM
                `Messages`
            WHERE
                (
                    `message_id` = ?
                )
            LIMIT 1;';
        $params = [$message_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Messages($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\Messages} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\Messages}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        int $message_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Messages`
            WHERE
                (
                    `message_id` = ?
                );';
        $params = [$message_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Contar todos los registros en `Messages`.
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
                `Messages`;';
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, []);
        return intval($count);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\Messages} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\Messages $Messages El
     * objeto de tipo \OmegaUp\DAO\VO\Messages a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\Messages $Messages
    ): void {
        $sql = '
            DELETE FROM
                `Messages`
            WHERE
                (
                    `message_id` = ?
                );';
        $params = [
            $Messages->message_id
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
     * {@link \OmegaUp\DAO\VO\Messages}.
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
     * @return list<\OmegaUp\DAO\VO\Messages> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\Messages}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = 'message_id',
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
                `Messages`.`message_id`,
                `Messages`.`read`,
                `Messages`.`sender_id`,
                `Messages`.`recipient_id`,
                `Messages`.`message`,
                `Messages`.`date`
            FROM
                `Messages`
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
            $allData[] = new \OmegaUp\DAO\VO\Messages(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\Messages}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\Messages $Messages El
     * objeto de tipo {@link \OmegaUp\DAO\VO\Messages}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\Messages $Messages
    ): int {
        $sql = '
            INSERT INTO
                `Messages` (
                    `read`,
                    `sender_id`,
                    `recipient_id`,
                    `message`,
                    `date`
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                );';
        $params = [
            intval($Messages->read),
            (
                $Messages->sender_id === null ?
                null :
                intval($Messages->sender_id)
            ),
            (
                $Messages->recipient_id === null ?
                null :
                intval($Messages->recipient_id)
            ),
            $Messages->message,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Messages->date
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Messages->message_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
