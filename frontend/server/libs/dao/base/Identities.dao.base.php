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
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Identities}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Identities [$Identities] El objeto de tipo Identities
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(Identities $Identities) {
        if (is_null(self::getByPK($Identities->identity_id))) {
            return IdentitiesDAOBase::create($Identities);
        }
        return IdentitiesDAOBase::update($Identities);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param Identities [$Identities] El objeto de tipo Identities a actualizar.
     */
    final public static function update(Identities $Identities) {
        $sql = 'UPDATE `Identities` SET `username` = ?, `password` = ?, `name` = ?, `user_id` = ?, `language_id` = ?, `country_id` = ?, `state_id` = ?, `school_id` = ?, `gender` = ? WHERE `identity_id` = ?;';
        $params = [
            $Identities->username,
            $Identities->password,
            $Identities->name,
            $Identities->user_id,
            $Identities->language_id,
            $Identities->country_id,
            $Identities->state_id,
            $Identities->school_id,
            $Identities->gender,
            $Identities->identity_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link Identities} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Identities} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link Identities Un objeto del tipo {@link Identities}. NULL si no hay tal registro.
     */
    final public static function getByPK($identity_id) {
        if (is_null($identity_id)) {
            return null;
        }
        $sql = 'SELECT `Identities`.`identity_id`, `Identities`.`username`, `Identities`.`password`, `Identities`.`name`, `Identities`.`user_id`, `Identities`.`language_id`, `Identities`.`country_id`, `Identities`.`state_id`, `Identities`.`school_id`, `Identities`.`gender` FROM Identities WHERE (identity_id = ?) LIMIT 1;';
        $params = [$identity_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new Identities($rs);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Identities suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param Identities [$Identities] El objeto de tipo Identities a eliminar
     */
    final public static function delete(Identities $Identities) {
        $sql = 'DELETE FROM `Identities` WHERE identity_id = ?;';
        $params = [$Identities->identity_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link Identities}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link Identities}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Identities`.`identity_id`, `Identities`.`username`, `Identities`.`password`, `Identities`.`name`, `Identities`.`user_id`, `Identities`.`language_id`, `Identities`.`country_id`, `Identities`.`state_id`, `Identities`.`school_id`, `Identities`.`gender` from Identities';
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
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param Identities [$Identities] El objeto de tipo Identities a crear.
     */
    final public static function create(Identities $Identities) {
        $sql = 'INSERT INTO Identities (`identity_id`, `username`, `password`, `name`, `user_id`, `language_id`, `country_id`, `state_id`, `school_id`, `gender`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Identities->identity_id,
            $Identities->username,
            $Identities->password,
            $Identities->name,
            $Identities->user_id,
            $Identities->language_id,
            $Identities->country_id,
            $Identities->state_id,
            $Identities->school_id,
            $Identities->gender,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Identities->identity_id = $conn->Insert_ID();

        return $ar;
    }
}
