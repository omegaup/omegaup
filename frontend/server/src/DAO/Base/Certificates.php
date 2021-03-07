<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** Certificates Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Certificates}.
 * @access public
 * @abstract
 */
abstract class Certificates {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\Certificates $Certificates El objeto de tipo Certificates a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\Certificates $Certificates
    ): int {
        $sql = '
            UPDATE
                `Certificates`
            SET
                `identity_id` = ?,
                `timestamp` = ?,
                `certificate_type` = ?,
                `course_id` = ?,
                `contest_id` = ?,
                `verification_code` = ?
            WHERE
                (
                    `certificate_id` = ?
                );';
        $params = [
            (
                is_null($Certificates->identity_id) ?
                null :
                intval($Certificates->identity_id)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Certificates->timestamp
            ),
            $Certificates->certificate_type,
            (
                is_null($Certificates->course_id) ?
                null :
                intval($Certificates->course_id)
            ),
            (
                is_null($Certificates->contest_id) ?
                null :
                intval($Certificates->contest_id)
            ),
            $Certificates->verification_code,
            intval($Certificates->certificate_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\Certificates} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\Certificates}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\Certificates Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\Certificates} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $certificate_id
    ): ?\OmegaUp\DAO\VO\Certificates {
        $sql = '
            SELECT
                `Certificates`.`certificate_id`,
                `Certificates`.`identity_id`,
                `Certificates`.`timestamp`,
                `Certificates`.`certificate_type`,
                `Certificates`.`course_id`,
                `Certificates`.`contest_id`,
                `Certificates`.`verification_code`
            FROM
                `Certificates`
            WHERE
                (
                    `certificate_id` = ?
                )
            LIMIT 1;';
        $params = [$certificate_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Certificates($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\Certificates} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\Certificates $Certificates El
     * objeto de tipo \OmegaUp\DAO\VO\Certificates a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\Certificates $Certificates
    ): void {
        $sql = '
            DELETE FROM
                `Certificates`
            WHERE
                (
                    `certificate_id` = ?
                );';
        $params = [
            $Certificates->certificate_id
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
     * {@link \OmegaUp\DAO\VO\Certificates}.
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
     * @return list<\OmegaUp\DAO\VO\Certificates> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\Certificates}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ): array {
        $sql = '
            SELECT
                `Certificates`.`certificate_id`,
                `Certificates`.`identity_id`,
                `Certificates`.`timestamp`,
                `Certificates`.`certificate_type`,
                `Certificates`.`course_id`,
                `Certificates`.`contest_id`,
                `Certificates`.`verification_code`
            FROM
                `Certificates`
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
            $allData[] = new \OmegaUp\DAO\VO\Certificates(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\Certificates}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\Certificates $Certificates El
     * objeto de tipo {@link \OmegaUp\DAO\VO\Certificates}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\Certificates $Certificates
    ): int {
        $sql = '
            INSERT INTO
                `Certificates` (
                    `identity_id`,
                    `timestamp`,
                    `certificate_type`,
                    `course_id`,
                    `contest_id`,
                    `verification_code`
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
                is_null($Certificates->identity_id) ?
                null :
                intval($Certificates->identity_id)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Certificates->timestamp
            ),
            $Certificates->certificate_type,
            (
                is_null($Certificates->course_id) ?
                null :
                intval($Certificates->course_id)
            ),
            (
                is_null($Certificates->contest_id) ?
                null :
                intval($Certificates->contest_id)
            ),
            $Certificates->verification_code,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Certificates->certificate_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
