<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Emails Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Emails}.
 * @access public
 * @abstract
 *
 */
abstract class EmailsDAOBase {
    /**
     * Actualizar registros.
     *
     * @param Emails $Emails El objeto de tipo Emails a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(Emails $Emails) : int {
        $sql = 'UPDATE `Emails` SET `email` = ?, `user_id` = ? WHERE `email_id` = ?;';
        $params = [
            $Emails->email,
            is_null($Emails->user_id) ? null : (int)$Emails->user_id,
            (int)$Emails->email_id,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link Emails} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Emails} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?Emails Un objeto del tipo {@link Emails}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $email_id) : ?Emails {
        $sql = 'SELECT `Emails`.`email_id`, `Emails`.`email`, `Emails`.`user_id` FROM Emails WHERE (email_id = ?) LIMIT 1;';
        $params = [$email_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new Emails($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Emails suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param Emails $Emails El objeto de tipo Emails a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(Emails $Emails) : void {
        $sql = 'DELETE FROM `Emails` WHERE email_id = ?;';
        $params = [$Emails->email_id];

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        if (\OmegaUp\MySQLConnection::getInstance()->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo {@link Emails}.
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
     * @return Emails[] Un arreglo que contiene objetos del tipo {@link Emails}.
     *
     * @psalm-return array<int, Emails>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Emails`.`email_id`, `Emails`.`email`, `Emails`.`user_id` from Emails';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . \OmegaUp\MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql) as $row) {
            $allData[] = new Emails($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Emails suministrado.
     *
     * @param Emails $Emails El objeto de tipo Emails a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(Emails $Emails) : int {
        $sql = 'INSERT INTO Emails (`email`, `user_id`) VALUES (?, ?);';
        $params = [
            $Emails->email,
            is_null($Emails->user_id) ? null : (int)$Emails->user_id,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Emails->email_id = \OmegaUp\MySQLConnection::getInstance()->Insert_ID();

        return $affectedRows;
    }
}
