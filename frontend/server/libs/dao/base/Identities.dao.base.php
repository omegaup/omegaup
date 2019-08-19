<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Identities Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Identities}.
 * @access public
 * @abstract
 *
 */
abstract class IdentitiesDAOBase {
    /**
     * Actualizar registros.
     *
     * @param Identities $Identities El objeto de tipo Identities a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(Identities $Identities) : int {
        $sql = 'UPDATE `Identities` SET `username` = ?, `password` = ?, `name` = ?, `user_id` = ?, `language_id` = ?, `country_id` = ?, `state_id` = ?, `school_id` = ?, `gender` = ? WHERE `identity_id` = ?;';
        $params = [
            $Identities->username,
            $Identities->password,
            $Identities->name,
            is_null($Identities->user_id) ? null : (int)$Identities->user_id,
            is_null($Identities->language_id) ? null : (int)$Identities->language_id,
            $Identities->country_id,
            $Identities->state_id,
            is_null($Identities->school_id) ? null : (int)$Identities->school_id,
            $Identities->gender,
            (int)$Identities->identity_id,
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        return MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link Identities} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Identities} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?Identities Un objeto del tipo {@link Identities}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $identity_id) : ?Identities {
        $sql = 'SELECT `Identities`.`identity_id`, `Identities`.`username`, `Identities`.`password`, `Identities`.`name`, `Identities`.`user_id`, `Identities`.`language_id`, `Identities`.`country_id`, `Identities`.`state_id`, `Identities`.`school_id`, `Identities`.`gender` FROM Identities WHERE (identity_id = ?) LIMIT 1;';
        $params = [$identity_id];
        $row = MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new Identities($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Identities suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param Identities $Identities El objeto de tipo Identities a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(Identities $Identities) : void {
        $sql = 'DELETE FROM `Identities` WHERE identity_id = ?;';
        $params = [$Identities->identity_id];

        MySQLConnection::getInstance()->Execute($sql, $params);
        if (MySQLConnection::getInstance()->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo {@link Identities}.
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
     * @return Identities[] Un arreglo que contiene objetos del tipo {@link Identities}.
     *
     * @psalm-return array<int, Identities>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Identities`.`identity_id`, `Identities`.`username`, `Identities`.`password`, `Identities`.`name`, `Identities`.`user_id`, `Identities`.`language_id`, `Identities`.`country_id`, `Identities`.`state_id`, `Identities`.`school_id`, `Identities`.`gender` from Identities';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (MySQLConnection::getInstance()->GetAll($sql) as $row) {
            $allData[] = new Identities($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Identities suministrado.
     *
     * @param Identities $Identities El objeto de tipo Identities a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(Identities $Identities) : int {
        $sql = 'INSERT INTO Identities (`username`, `password`, `name`, `user_id`, `language_id`, `country_id`, `state_id`, `school_id`, `gender`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Identities->username,
            $Identities->password,
            $Identities->name,
            is_null($Identities->user_id) ? null : (int)$Identities->user_id,
            is_null($Identities->language_id) ? null : (int)$Identities->language_id,
            $Identities->country_id,
            $Identities->state_id,
            is_null($Identities->school_id) ? null : (int)$Identities->school_id,
            $Identities->gender,
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Identities->identity_id = MySQLConnection::getInstance()->Insert_ID();

        return $affectedRows;
    }
}
