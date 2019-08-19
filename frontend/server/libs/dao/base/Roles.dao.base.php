<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Roles Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Roles}.
 * @access public
 * @abstract
 *
 */
abstract class RolesDAOBase {
    /**
     * Actualizar registros.
     *
     * @param Roles $Roles El objeto de tipo Roles a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(Roles $Roles) : int {
        $sql = 'UPDATE `Roles` SET `name` = ?, `description` = ? WHERE `role_id` = ?;';
        $params = [
            $Roles->name,
            $Roles->description,
            (int)$Roles->role_id,
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        return MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link Roles} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Roles} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?Roles Un objeto del tipo {@link Roles}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $role_id) : ?Roles {
        $sql = 'SELECT `Roles`.`role_id`, `Roles`.`name`, `Roles`.`description` FROM Roles WHERE (role_id = ?) LIMIT 1;';
        $params = [$role_id];
        $row = MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new Roles($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Roles suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param Roles $Roles El objeto de tipo Roles a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(Roles $Roles) : void {
        $sql = 'DELETE FROM `Roles` WHERE role_id = ?;';
        $params = [$Roles->role_id];

        MySQLConnection::getInstance()->Execute($sql, $params);
        if (MySQLConnection::getInstance()->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo {@link Roles}.
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
     * @return Roles[] Un arreglo que contiene objetos del tipo {@link Roles}.
     *
     * @psalm-return array<int, Roles>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Roles`.`role_id`, `Roles`.`name`, `Roles`.`description` from Roles';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (MySQLConnection::getInstance()->GetAll($sql) as $row) {
            $allData[] = new Roles($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Roles suministrado.
     *
     * @param Roles $Roles El objeto de tipo Roles a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(Roles $Roles) : int {
        $sql = 'INSERT INTO Roles (`name`, `description`) VALUES (?, ?);';
        $params = [
            $Roles->name,
            $Roles->description,
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Roles->role_id = MySQLConnection::getInstance()->Insert_ID();

        return $affectedRows;
    }
}
