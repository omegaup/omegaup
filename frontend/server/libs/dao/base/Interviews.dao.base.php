<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Interviews Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Interviews}.
 * @access public
 * @abstract
 *
 */
abstract class InterviewsDAOBase {
    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param Interviews [$Interviews] El objeto de tipo Interviews a actualizar.
     */
    final public static function update(Interviews $Interviews) : int {
        $sql = 'UPDATE `Interviews` SET `problemset_id` = ?, `acl_id` = ?, `alias` = ?, `title` = ?, `description` = ?, `window_length` = ? WHERE `interview_id` = ?;';
        $params = [
            (int)$Interviews->problemset_id,
            (int)$Interviews->acl_id,
            $Interviews->alias,
            $Interviews->title,
            $Interviews->description,
            (int)$Interviews->window_length,
            (int)$Interviews->interview_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link Interviews} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Interviews} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link Interviews Un objeto del tipo {@link Interviews}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $interview_id) : ?Interviews {
        $sql = 'SELECT `Interviews`.`interview_id`, `Interviews`.`problemset_id`, `Interviews`.`acl_id`, `Interviews`.`alias`, `Interviews`.`title`, `Interviews`.`description`, `Interviews`.`window_length` FROM Interviews WHERE (interview_id = ?) LIMIT 1;';
        $params = [$interview_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new Interviews($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Interviews suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param Interviews [$Interviews] El objeto de tipo Interviews a eliminar
     */
    final public static function delete(Interviews $Interviews) : void {
        $sql = 'DELETE FROM `Interviews` WHERE interview_id = ?;';
        $params = [$Interviews->interview_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link Interviews}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link Interviews}.
     */
    final public static function getAll(
        ?int $pagina = null,
        ?int $filasPorPagina = null,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Interviews`.`interview_id`, `Interviews`.`problemset_id`, `Interviews`.`acl_id`, `Interviews`.`alias`, `Interviews`.`title`, `Interviews`.`description`, `Interviews`.`window_length` from Interviews';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new Interviews($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Interviews suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param Interviews [$Interviews] El objeto de tipo Interviews a crear.
     */
    final public static function create(Interviews $Interviews) : int {
        $sql = 'INSERT INTO Interviews (`problemset_id`, `acl_id`, `alias`, `title`, `description`, `window_length`) VALUES (?, ?, ?, ?, ?, ?);';
        $params = [
            (int)$Interviews->problemset_id,
            (int)$Interviews->acl_id,
            $Interviews->alias,
            $Interviews->title,
            $Interviews->description,
            (int)$Interviews->window_length,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Interviews->interview_id = $conn->Insert_ID();

        return $affectedRows;
    }
}
