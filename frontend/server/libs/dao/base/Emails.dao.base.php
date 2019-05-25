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
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Emails}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Emails [$Emails] El objeto de tipo Emails
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(Emails $Emails) {
        if (is_null(self::getByPK($Emails->email_id))) {
            return EmailsDAOBase::create($Emails);
        }
        return EmailsDAOBase::update($Emails);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param Emails [$Emails] El objeto de tipo Emails a actualizar.
     */
    final public static function update(Emails $Emails) {
        $sql = 'UPDATE `Emails` SET `email` = ?, `user_id` = ? WHERE `email_id` = ?;';
        $params = [
            $Emails->email,
            $Emails->user_id,
            $Emails->email_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link Emails} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Emails} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link Emails Un objeto del tipo {@link Emails}. NULL si no hay tal registro.
     */
    final public static function getByPK($email_id) {
        if (is_null($email_id)) {
            return null;
        }
        $sql = 'SELECT `Emails`.`email_id`, `Emails`.`email`, `Emails`.`user_id` FROM Emails WHERE (email_id = ?) LIMIT 1;';
        $params = [$email_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return new Emails($rs);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Emails suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param Emails [$Emails] El objeto de tipo Emails a eliminar
     */
    final public static function delete(Emails $Emails) {
        $sql = 'DELETE FROM `Emails` WHERE email_id = ?;';
        $params = [$Emails->email_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link Emails}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link Emails}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Emails`.`email_id`, `Emails`.`email`, `Emails`.`user_id` from Emails';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . mysqli_real_escape_string($conn->_connectionID, $orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $rs = $conn->Execute($sql);
        $allData = [];
        foreach ($rs as $row) {
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
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param Emails [$Emails] El objeto de tipo Emails a crear.
     */
    final public static function create(Emails $Emails) {
        $sql = 'INSERT INTO Emails (`email`, `user_id`) VALUES (?, ?);';
        $params = [
            $Emails->email,
            $Emails->user_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Emails->email_id = $conn->Insert_ID();

        return $ar;
    }
}
