<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** CoderOfTheMonth Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link CoderOfTheMonth}.
 * @access public
 * @abstract
 *
 */
abstract class CoderOfTheMonthDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link CoderOfTheMonth}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param CoderOfTheMonth [$Coder_Of_The_Month] El objeto de tipo CoderOfTheMonth
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(CoderOfTheMonth $Coder_Of_The_Month) : int {
        if (is_null($Coder_Of_The_Month->coder_of_the_month_id) ||
            is_null(self::getByPK($Coder_Of_The_Month->coder_of_the_month_id))
        ) {
            return CoderOfTheMonthDAOBase::create($Coder_Of_The_Month);
        }
        return CoderOfTheMonthDAOBase::update($Coder_Of_The_Month);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param CoderOfTheMonth [$Coder_Of_The_Month] El objeto de tipo CoderOfTheMonth a actualizar.
     */
    final public static function update(CoderOfTheMonth $Coder_Of_The_Month) : int {
        $sql = 'UPDATE `Coder_Of_The_Month` SET `user_id` = ?, `description` = ?, `time` = ?, `interview_url` = ?, `rank` = ?, `selected_by` = ? WHERE `coder_of_the_month_id` = ?;';
        $params = [
            (int)$Coder_Of_The_Month->user_id,
            $Coder_Of_The_Month->description,
            $Coder_Of_The_Month->time,
            $Coder_Of_The_Month->interview_url,
            (int)$Coder_Of_The_Month->rank,
            is_null($Coder_Of_The_Month->selected_by) ? null : (int)$Coder_Of_The_Month->selected_by,
            (int)$Coder_Of_The_Month->coder_of_the_month_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link CoderOfTheMonth} por llave primaria.
     *
     * Este metodo cargará un objeto {@link CoderOfTheMonth} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link CoderOfTheMonth Un objeto del tipo {@link CoderOfTheMonth}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $coder_of_the_month_id) : ?CoderOfTheMonth {
        $sql = 'SELECT `Coder_Of_The_Month`.`coder_of_the_month_id`, `Coder_Of_The_Month`.`user_id`, `Coder_Of_The_Month`.`description`, `Coder_Of_The_Month`.`time`, `Coder_Of_The_Month`.`interview_url`, `Coder_Of_The_Month`.`rank`, `Coder_Of_The_Month`.`selected_by` FROM Coder_Of_The_Month WHERE (coder_of_the_month_id = ?) LIMIT 1;';
        $params = [$coder_of_the_month_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new CoderOfTheMonth($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto CoderOfTheMonth suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param CoderOfTheMonth [$Coder_Of_The_Month] El objeto de tipo CoderOfTheMonth a eliminar
     */
    final public static function delete(CoderOfTheMonth $Coder_Of_The_Month) : void {
        $sql = 'DELETE FROM `Coder_Of_The_Month` WHERE coder_of_the_month_id = ?;';
        $params = [$Coder_Of_The_Month->coder_of_the_month_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link CoderOfTheMonth}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link CoderOfTheMonth}.
     */
    final public static function getAll(
        ?int $pagina = null,
        ?int $filasPorPagina = null,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Coder_Of_The_Month`.`coder_of_the_month_id`, `Coder_Of_The_Month`.`user_id`, `Coder_Of_The_Month`.`description`, `Coder_Of_The_Month`.`time`, `Coder_Of_The_Month`.`interview_url`, `Coder_Of_The_Month`.`rank`, `Coder_Of_The_Month`.`selected_by` from Coder_Of_The_Month';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new CoderOfTheMonth($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto CoderOfTheMonth suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param CoderOfTheMonth [$Coder_Of_The_Month] El objeto de tipo CoderOfTheMonth a crear.
     */
    final public static function create(CoderOfTheMonth $Coder_Of_The_Month) : int {
        if (is_null($Coder_Of_The_Month->time)) {
            $Coder_Of_The_Month->time = '2000-01-01';
        }
        $sql = 'INSERT INTO Coder_Of_The_Month (`user_id`, `description`, `time`, `interview_url`, `rank`, `selected_by`) VALUES (?, ?, ?, ?, ?, ?);';
        $params = [
            (int)$Coder_Of_The_Month->user_id,
            $Coder_Of_The_Month->description,
            $Coder_Of_The_Month->time,
            $Coder_Of_The_Month->interview_url,
            (int)$Coder_Of_The_Month->rank,
            is_null($Coder_Of_The_Month->selected_by) ? null : (int)$Coder_Of_The_Month->selected_by,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Coder_Of_The_Month->coder_of_the_month_id = $conn->Insert_ID();

        return $affectedRows;
    }
}
