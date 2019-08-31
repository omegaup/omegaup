<?php
/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

namespace OmegaUp\DAO\Base;

/** Identities Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Identities}.
 * @access public
 * @abstract
 */
abstract class Identities {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\Identities $Identities El objeto de tipo Identities a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(\OmegaUp\DAO\VO\Identities $Identities) : int {
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
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\Identities} por llave primaria.
     *
     * Este metodo cargará un objeto {@link \OmegaUp\DAO\VO\Identities}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\Identities Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\Identities} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(int $identity_id) : ?\OmegaUp\DAO\VO\Identities {
        $sql = 'SELECT `Identities`.`identity_id`, `Identities`.`username`, `Identities`.`password`, `Identities`.`name`, `Identities`.`user_id`, `Identities`.`language_id`, `Identities`.`country_id`, `Identities`.`state_id`, `Identities`.`school_id`, `Identities`.`gender` FROM Identities WHERE (identity_id = ?) LIMIT 1;';
        $params = [$identity_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Identities($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\Identities} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\Identities $Identities El
     * objeto de tipo \OmegaUp\DAO\VO\Identities a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(\OmegaUp\DAO\VO\Identities $Identities) : void {
        $sql = 'DELETE FROM `Identities` WHERE identity_id = ?;';
        $params = [$Identities->identity_id];

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
     * {@link \OmegaUp\DAO\VO\Identities}.
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
     * @return \OmegaUp\DAO\VO\Identities[] Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\Identities}.
     *
     * @psalm-return array<int, \OmegaUp\DAO\VO\Identities>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Identities`.`identity_id`, `Identities`.`username`, `Identities`.`password`, `Identities`.`name`, `Identities`.`user_id`, `Identities`.`language_id`, `Identities`.`country_id`, `Identities`.`state_id`, `Identities`.`school_id`, `Identities`.`gender` from Identities';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . \OmegaUp\MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql) as $row) {
            $allData[] = new \OmegaUp\DAO\VO\Identities($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\Identities}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\Identities $Identities El
     * objeto de tipo {@link \OmegaUp\DAO\VO\Identities} a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(\OmegaUp\DAO\VO\Identities $Identities) : int {
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
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Identities->identity_id = \OmegaUp\MySQLConnection::getInstance()->Insert_ID();

        return $affectedRows;
    }
}
