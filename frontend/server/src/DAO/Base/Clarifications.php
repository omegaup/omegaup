<?php
/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

namespace OmegaUp\DAO\Base;

/** Clarifications Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Clarifications}.
 * @access public
 * @abstract
 */
abstract class Clarifications {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\Clarifications $Clarifications El objeto de tipo Clarifications a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(\OmegaUp\DAO\VO\Clarifications $Clarifications) : int {
        $sql = 'UPDATE `Clarifications` SET `author_id` = ?, `receiver_id` = ?, `message` = ?, `answer` = ?, `time` = ?, `problem_id` = ?, `problemset_id` = ?, `public` = ? WHERE `clarification_id` = ?;';
        $params = [
            is_null($Clarifications->author_id) ? null : intval($Clarifications->author_id),
            is_null($Clarifications->receiver_id) ? null : intval($Clarifications->receiver_id),
            $Clarifications->message,
            $Clarifications->answer,
            \OmegaUp\DAO\DAO::toMySQLTimestamp($Clarifications->time),
            is_null($Clarifications->problem_id) ? null : intval($Clarifications->problem_id),
            is_null($Clarifications->problemset_id) ? null : intval($Clarifications->problemset_id),
            intval($Clarifications->public),
            intval($Clarifications->clarification_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\Clarifications} por llave primaria.
     *
     * Este metodo cargará un objeto {@link \OmegaUp\DAO\VO\Clarifications}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\Clarifications Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\Clarifications} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(int $clarification_id) : ?\OmegaUp\DAO\VO\Clarifications {
        $sql = 'SELECT `Clarifications`.`clarification_id`, `Clarifications`.`author_id`, `Clarifications`.`receiver_id`, `Clarifications`.`message`, `Clarifications`.`answer`, `Clarifications`.`time`, `Clarifications`.`problem_id`, `Clarifications`.`problemset_id`, `Clarifications`.`public` FROM Clarifications WHERE (clarification_id = ?) LIMIT 1;';
        $params = [$clarification_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Clarifications($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\Clarifications} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\Clarifications $Clarifications El
     * objeto de tipo \OmegaUp\DAO\VO\Clarifications a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(\OmegaUp\DAO\VO\Clarifications $Clarifications) : void {
        $sql = 'DELETE FROM `Clarifications` WHERE clarification_id = ?;';
        $params = [$Clarifications->clarification_id];

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
     * {@link \OmegaUp\DAO\VO\Clarifications}.
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
     * @return \OmegaUp\DAO\VO\Clarifications[] Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\Clarifications}.
     *
     * @psalm-return array<int, \OmegaUp\DAO\VO\Clarifications>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Clarifications`.`clarification_id`, `Clarifications`.`author_id`, `Clarifications`.`receiver_id`, `Clarifications`.`message`, `Clarifications`.`answer`, `Clarifications`.`time`, `Clarifications`.`problem_id`, `Clarifications`.`problemset_id`, `Clarifications`.`public` from Clarifications';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . \OmegaUp\MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . intval($filasPorPagina);
        }
        $allData = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql) as $row) {
            $allData[] = new \OmegaUp\DAO\VO\Clarifications($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\Clarifications}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\Clarifications $Clarifications El
     * objeto de tipo {@link \OmegaUp\DAO\VO\Clarifications} a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(\OmegaUp\DAO\VO\Clarifications $Clarifications) : int {
        $sql = 'INSERT INTO Clarifications (`author_id`, `receiver_id`, `message`, `answer`, `time`, `problem_id`, `problemset_id`, `public`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            is_null($Clarifications->author_id) ? null : intval($Clarifications->author_id),
            is_null($Clarifications->receiver_id) ? null : intval($Clarifications->receiver_id),
            $Clarifications->message,
            $Clarifications->answer,
            \OmegaUp\DAO\DAO::toMySQLTimestamp($Clarifications->time),
            is_null($Clarifications->problem_id) ? null : intval($Clarifications->problem_id),
            is_null($Clarifications->problemset_id) ? null : intval($Clarifications->problemset_id),
            intval($Clarifications->public),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Clarifications->clarification_id = \OmegaUp\MySQLConnection::getInstance()->Insert_ID();

        return $affectedRows;
    }
}
