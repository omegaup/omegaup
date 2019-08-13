<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Clarifications Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Clarifications}.
 * @access public
 * @abstract
 *
 */
abstract class ClarificationsDAOBase {
    /**
     * Actualizar registros.
     *
     * @param Clarifications $Clarifications El objeto de tipo Clarifications a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(Clarifications $Clarifications) : int {
        $sql = 'UPDATE `Clarifications` SET `author_id` = ?, `receiver_id` = ?, `message` = ?, `answer` = ?, `time` = ?, `problem_id` = ?, `problemset_id` = ?, `public` = ? WHERE `clarification_id` = ?;';
        $params = [
            (int)$Clarifications->author_id,
            is_null($Clarifications->receiver_id) ? null : (int)$Clarifications->receiver_id,
            $Clarifications->message,
            $Clarifications->answer,
            DAO::toMySQLTimestamp($Clarifications->time),
            is_null($Clarifications->problem_id) ? null : (int)$Clarifications->problem_id,
            (int)$Clarifications->problemset_id,
            (int)$Clarifications->public,
            (int)$Clarifications->clarification_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link Clarifications} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Clarifications} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?Clarifications Un objeto del tipo {@link Clarifications}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $clarification_id) : ?Clarifications {
        $sql = 'SELECT `Clarifications`.`clarification_id`, `Clarifications`.`author_id`, `Clarifications`.`receiver_id`, `Clarifications`.`message`, `Clarifications`.`answer`, `Clarifications`.`time`, `Clarifications`.`problem_id`, `Clarifications`.`problemset_id`, `Clarifications`.`public` FROM Clarifications WHERE (clarification_id = ?) LIMIT 1;';
        $params = [$clarification_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new Clarifications($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Clarifications suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param Clarifications $Clarifications El objeto de tipo Clarifications a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(Clarifications $Clarifications) : void {
        $sql = 'DELETE FROM `Clarifications` WHERE clarification_id = ?;';
        $params = [$Clarifications->clarification_id];
        global $conn;

        $conn->Execute($sql, $params);
        if ($conn->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo {@link Clarifications}.
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
     * @return array Un arreglo que contiene objetos del tipo {@link Clarifications}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Clarifications`.`clarification_id`, `Clarifications`.`author_id`, `Clarifications`.`receiver_id`, `Clarifications`.`message`, `Clarifications`.`answer`, `Clarifications`.`time`, `Clarifications`.`problem_id`, `Clarifications`.`problemset_id`, `Clarifications`.`public` from Clarifications';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new Clarifications($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Clarifications suministrado.
     *
     * @param Clarifications $Clarifications El objeto de tipo Clarifications a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(Clarifications $Clarifications) : int {
        if (is_null($Clarifications->time)) {
            $Clarifications->time = Time::get();
        }
        if (is_null($Clarifications->public)) {
            $Clarifications->public = false;
        }
        $sql = 'INSERT INTO Clarifications (`author_id`, `receiver_id`, `message`, `answer`, `time`, `problem_id`, `problemset_id`, `public`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            (int)$Clarifications->author_id,
            is_null($Clarifications->receiver_id) ? null : (int)$Clarifications->receiver_id,
            $Clarifications->message,
            $Clarifications->answer,
            DAO::toMySQLTimestamp($Clarifications->time),
            is_null($Clarifications->problem_id) ? null : (int)$Clarifications->problem_id,
            (int)$Clarifications->problemset_id,
            (int)$Clarifications->public,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Clarifications->clarification_id = $conn->Insert_ID();

        return $affectedRows;
    }
}
