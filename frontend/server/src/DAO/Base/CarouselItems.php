<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** CarouselItems Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\CarouselItems}.
 * @access public
 * @abstract
 */
abstract class CarouselItems {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\CarouselItems $Carousel_Items El objeto de tipo CarouselItems a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\CarouselItems $Carousel_Items
    ): int {
        $sql = '
            UPDATE
                `Carousel_Items`
            SET
                `title` = ?,
                `excerpt` = ?,
                `image_url` = ?,
                `link` = ?,
                `button_title` = ?,
                `expiration_date` = ?,
                `status` = ?,
                `user_id` = ?,
                `created_at` = ?,
                `updated_at` = ?
            WHERE
                (
                    `carousel_item_id` = ?
                );';
        $params = [
            $Carousel_Items->title,
            $Carousel_Items->excerpt,
            $Carousel_Items->image_url,
            $Carousel_Items->link,
            $Carousel_Items->button_title,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Carousel_Items->expiration_date
            ),
            $Carousel_Items->status,
            (
                is_null($Carousel_Items->user_id) ?
                null :
                intval($Carousel_Items->user_id)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Carousel_Items->created_at
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Carousel_Items->updated_at
            ),
            intval($Carousel_Items->carousel_item_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\CarouselItems} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\CarouselItems}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\CarouselItems Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\CarouselItems} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $carousel_item_id
    ): ?\OmegaUp\DAO\VO\CarouselItems {
        $sql = '
            SELECT
                `Carousel_Items`.`carousel_item_id`,
                `Carousel_Items`.`title`,
                `Carousel_Items`.`excerpt`,
                `Carousel_Items`.`image_url`,
                `Carousel_Items`.`link`,
                `Carousel_Items`.`button_title`,
                `Carousel_Items`.`expiration_date`,
                `Carousel_Items`.`status`,
                `Carousel_Items`.`user_id`,
                `Carousel_Items`.`created_at`,
                `Carousel_Items`.`updated_at`
            FROM
                `Carousel_Items`
            WHERE
                (
                    `carousel_item_id` = ?
                )
            LIMIT 1;';
        $params = [$carousel_item_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\CarouselItems($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\CarouselItems} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\CarouselItems}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        int $carousel_item_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Carousel_Items`
            WHERE
                (
                    `carousel_item_id` = ?
                );';
        $params = [$carousel_item_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\CarouselItems} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\CarouselItems $Carousel_Items El
     * objeto de tipo \OmegaUp\DAO\VO\CarouselItems a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\CarouselItems $Carousel_Items
    ): void {
        $sql = '
            DELETE FROM
                `Carousel_Items`
            WHERE
                (
                    `carousel_item_id` = ?
                );';
        $params = [
            $Carousel_Items->carousel_item_id
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
     * {@link \OmegaUp\DAO\VO\CarouselItems}.
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
     * @return list<\OmegaUp\DAO\VO\CarouselItems> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\CarouselItems}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ): array {
        $sql = '
            SELECT
                `Carousel_Items`.`carousel_item_id`,
                `Carousel_Items`.`title`,
                `Carousel_Items`.`excerpt`,
                `Carousel_Items`.`image_url`,
                `Carousel_Items`.`link`,
                `Carousel_Items`.`button_title`,
                `Carousel_Items`.`expiration_date`,
                `Carousel_Items`.`status`,
                `Carousel_Items`.`user_id`,
                `Carousel_Items`.`created_at`,
                `Carousel_Items`.`updated_at`
            FROM
                `Carousel_Items`
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
            $allData[] = new \OmegaUp\DAO\VO\CarouselItems(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\CarouselItems}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\CarouselItems $Carousel_Items El
     * objeto de tipo {@link \OmegaUp\DAO\VO\CarouselItems}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\CarouselItems $Carousel_Items
    ): int {
        $sql = '
            INSERT INTO
                `Carousel_Items` (
                    `title`,
                    `excerpt`,
                    `image_url`,
                    `link`,
                    `button_title`,
                    `expiration_date`,
                    `status`,
                    `user_id`,
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
                    ?
                );';
        $params = [
            $Carousel_Items->title,
            $Carousel_Items->excerpt,
            $Carousel_Items->image_url,
            $Carousel_Items->link,
            $Carousel_Items->button_title,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Carousel_Items->expiration_date
            ),
            $Carousel_Items->status,
            (
                is_null($Carousel_Items->user_id) ?
                null :
                intval($Carousel_Items->user_id)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Carousel_Items->created_at
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Carousel_Items->updated_at
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Carousel_Items->carousel_item_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
