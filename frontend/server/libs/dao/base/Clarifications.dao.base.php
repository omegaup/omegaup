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
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Clarifications}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Clarifications [$Clarifications] El objeto de tipo Clarifications
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(Clarifications $Clarifications) {
        if (is_null(self::getByPK($Clarifications->clarification_id))) {
            return ClarificationsDAOBase::create($Clarifications);
        }
        return ClarificationsDAOBase::update($Clarifications);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param Clarifications [$Clarifications] El objeto de tipo Clarifications a actualizar.
     */
    final public static function update(Clarifications $Clarifications) {
        $sql = 'UPDATE `Clarifications` SET `author_id` = ?, `receiver_id` = ?, `message` = ?, `answer` = ?, `time` = ?, `problem_id` = ?, `problemset_id` = ?, `public` = ? WHERE `clarification_id` = ?;';
        $params = [
            is_null($Clarifications->author_id) ? null : (int)$Clarifications->author_id,
            is_null($Clarifications->receiver_id) ? null : (int)$Clarifications->receiver_id,
            $Clarifications->message,
            $Clarifications->answer,
            $Clarifications->time,
            is_null($Clarifications->problem_id) ? null : (int)$Clarifications->problem_id,
            is_null($Clarifications->problemset_id) ? null : (int)$Clarifications->problemset_id,
            is_null($Clarifications->public) ? null : (int)$Clarifications->public,
            is_null($Clarifications->clarification_id) ? null : (int)$Clarifications->clarification_id,
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
     * @static
     * @return @link Clarifications Un objeto del tipo {@link Clarifications}. NULL si no hay tal registro.
     */
    final public static function getByPK($clarification_id) {
        if (is_null($clarification_id)) {
            return null;
        }
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
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param Clarifications [$Clarifications] El objeto de tipo Clarifications a eliminar
     */
    final public static function delete(Clarifications $Clarifications) {
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
     * @static
     * @param $pagina Página a ver.
     * @param $filasPorPagina Filas por página.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link Clarifications}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
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
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param Clarifications [$Clarifications] El objeto de tipo Clarifications a crear.
     */
    final public static function create(Clarifications $Clarifications) {
        if (is_null($Clarifications->time)) {
            $Clarifications->time = gmdate('Y-m-d H:i:s', Time::get());
        }
        if (is_null($Clarifications->public)) {
            $Clarifications->public = false;
        }
        $sql = 'INSERT INTO Clarifications (`author_id`, `receiver_id`, `message`, `answer`, `time`, `problem_id`, `problemset_id`, `public`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            is_null($Clarifications->author_id) ? null : (int)$Clarifications->author_id,
            is_null($Clarifications->receiver_id) ? null : (int)$Clarifications->receiver_id,
            $Clarifications->message,
            $Clarifications->answer,
            $Clarifications->time,
            is_null($Clarifications->problem_id) ? null : (int)$Clarifications->problem_id,
            is_null($Clarifications->problemset_id) ? null : (int)$Clarifications->problemset_id,
            is_null($Clarifications->public) ? null : (int)$Clarifications->public,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Clarifications->clarification_id = $conn->Insert_ID();

        return $ar;
    }
}
