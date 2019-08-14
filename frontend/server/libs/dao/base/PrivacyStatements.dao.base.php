<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** PrivacyStatements Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link PrivacyStatements}.
 * @access public
 * @abstract
 *
 */
abstract class PrivacyStatementsDAOBase {
    /**
     * Actualizar registros.
     *
     * @param PrivacyStatements $PrivacyStatements El objeto de tipo PrivacyStatements a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(PrivacyStatements $PrivacyStatements) : int {
        $sql = 'UPDATE `PrivacyStatements` SET `git_object_id` = ?, `type` = ? WHERE `privacystatement_id` = ?;';
        $params = [
            $PrivacyStatements->git_object_id,
            $PrivacyStatements->type,
            (int)$PrivacyStatements->privacystatement_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link PrivacyStatements} por llave primaria.
     *
     * Este metodo cargará un objeto {@link PrivacyStatements} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?PrivacyStatements Un objeto del tipo {@link PrivacyStatements}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $privacystatement_id) : ?PrivacyStatements {
        $sql = 'SELECT `PrivacyStatements`.`privacystatement_id`, `PrivacyStatements`.`git_object_id`, `PrivacyStatements`.`type` FROM PrivacyStatements WHERE (privacystatement_id = ?) LIMIT 1;';
        $params = [$privacystatement_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new PrivacyStatements($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto PrivacyStatements suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param PrivacyStatements $PrivacyStatements El objeto de tipo PrivacyStatements a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(PrivacyStatements $PrivacyStatements) : void {
        $sql = 'DELETE FROM `PrivacyStatements` WHERE privacystatement_id = ?;';
        $params = [$PrivacyStatements->privacystatement_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link PrivacyStatements}.
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
     * @return PrivacyStatements[] Un arreglo que contiene objetos del tipo {@link PrivacyStatements}.
     *
     * @psalm-return array<int, PrivacyStatements>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `PrivacyStatements`.`privacystatement_id`, `PrivacyStatements`.`git_object_id`, `PrivacyStatements`.`type` from PrivacyStatements';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new PrivacyStatements($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto PrivacyStatements suministrado.
     *
     * @param PrivacyStatements $PrivacyStatements El objeto de tipo PrivacyStatements a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(PrivacyStatements $PrivacyStatements) : int {
        $sql = 'INSERT INTO PrivacyStatements (`git_object_id`, `type`) VALUES (?, ?);';
        $params = [
            $PrivacyStatements->git_object_id,
            $PrivacyStatements->type,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $PrivacyStatements->privacystatement_id = $conn->Insert_ID();

        return $affectedRows;
    }
}
