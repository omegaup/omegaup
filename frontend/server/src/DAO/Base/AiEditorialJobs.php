<?php
// WARNING: This file is auto-generated. Do not modify it directly.

namespace OmegaUp\DAO\Base;

/** AiEditorialJobs Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\AiEditorialJobs}.
 * @access public
 * @abstract
 */
abstract class AiEditorialJobs {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link \OmegaUp\DAO\VO\AiEditorialJobs}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @param \OmegaUp\DAO\VO\AiEditorialJobs $AI_Editorial_Jobs El
     * objeto de tipo {@link \OmegaUp\DAO\VO\AiEditorialJobs}.
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(\OmegaUp\DAO\VO\AiEditorialJobs $AI_Editorial_Jobs): int {
        if (
            is_null(
                $AI_Editorial_Jobs->job_id
            ) || is_null(
                self::getByPK(
                    $AI_Editorial_Jobs->job_id
                )
            )
        ) {
            return AiEditorialJobs::create($AI_Editorial_Jobs);
        }
        return AiEditorialJobs::update($AI_Editorial_Jobs);
    }

    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\AiEditorialJobs $AI_Editorial_Jobs El objeto de tipo AiEditorialJobs a actualizar.
     * @return int Número de filas afectadas
     */
    final public static function update(\OmegaUp\DAO\VO\AiEditorialJobs $AI_Editorial_Jobs): int {
        $sql = '
            UPDATE
                `AI_Editorial_Jobs`
            SET
                `problem_id` = ?,
                `user_id` = ?,
                `status` = ?,
                `error_message` = ?,
                `is_retriable` = ?,
                `attempts` = ?,
                `md_en` = ?,
                `md_es` = ?,
                `md_pt` = ?,
                `validation_verdict` = ?
            WHERE
                `job_id` = ?;';
        $params = [
            $AI_Editorial_Jobs->problem_id,
            $AI_Editorial_Jobs->user_id,
            $AI_Editorial_Jobs->status,
            $AI_Editorial_Jobs->error_message,
            $AI_Editorial_Jobs->is_retriable ? 1 : 0,
            $AI_Editorial_Jobs->attempts,
            $AI_Editorial_Jobs->md_en,
            $AI_Editorial_Jobs->md_es,
            $AI_Editorial_Jobs->md_pt,
            $AI_Editorial_Jobs->validation_verdict,
            $AI_Editorial_Jobs->job_id,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\AiEditorialJobs} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\AiEditorialJobs}
     * de la base de datos usando sus llaves primarias.
     *
     * @return \OmegaUp\DAO\VO\AiEditorialJobs|null Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\AiEditorialJobs} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(string $job_id): ?\OmegaUp\DAO\VO\AiEditorialJobs {
        $sql = '
            SELECT
                `AI_Editorial_Jobs`.`job_id`,
                `AI_Editorial_Jobs`.`problem_id`,
                `AI_Editorial_Jobs`.`user_id`,
                `AI_Editorial_Jobs`.`status`,
                `AI_Editorial_Jobs`.`error_message`,
                `AI_Editorial_Jobs`.`is_retriable`,
                `AI_Editorial_Jobs`.`attempts`,
                `AI_Editorial_Jobs`.`created_at`,
                `AI_Editorial_Jobs`.`md_en`,
                `AI_Editorial_Jobs`.`md_es`,
                `AI_Editorial_Jobs`.`md_pt`,
                `AI_Editorial_Jobs`.`validation_verdict`
            FROM
                `AI_Editorial_Jobs`
            WHERE
                `AI_Editorial_Jobs`.`job_id` = ?
            LIMIT 1;';
        $params = [$job_id];
        /** @var array{attempts: int, created_at: \OmegaUp\Timestamp, error_message: null|string, is_retriable: int, job_id: string, md_en: null|string, md_es: null|string, md_pt: null|string, problem_id: int, status: string, user_id: int, validation_verdict: null|string}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\AiEditorialJobs($row);
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\AiEditorialJobs} suministrado.
     *
     * @param \OmegaUp\DAO\VO\AiEditorialJobs $AI_Editorial_Jobs El
     * objeto de tipo {@link \OmegaUp\DAO\VO\AiEditorialJobs}
     * a crear.
     * @return int Un entero mayor o igual a cero identificando el número de
     * filas afectadas.
     */
    final public static function create(\OmegaUp\DAO\VO\AiEditorialJobs $AI_Editorial_Jobs): int {
        $sql = '
            INSERT INTO `AI_Editorial_Jobs` (
                `job_id`,
                `problem_id`,
                `user_id`,
                `status`,
                `error_message`,
                `is_retriable`,
                `attempts`,
                `created_at`,
                `md_en`,
                `md_es`,
                `md_pt`,
                `validation_verdict`
            ) VALUES (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            );';
        $params = [
            $AI_Editorial_Jobs->job_id,
            $AI_Editorial_Jobs->problem_id,
            $AI_Editorial_Jobs->user_id,
            $AI_Editorial_Jobs->status,
            $AI_Editorial_Jobs->error_message,
            $AI_Editorial_Jobs->is_retriable ? 1 : 0,
            $AI_Editorial_Jobs->attempts,
            \OmegaUp\DAO\DAO::toMySQLTimestamp($AI_Editorial_Jobs->created_at),
            $AI_Editorial_Jobs->md_en,
            $AI_Editorial_Jobs->md_es,
            $AI_Editorial_Jobs->md_pt,
            $AI_Editorial_Jobs->validation_verdict,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Buscar registros.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de
     * objetos {@link \OmegaUp\DAO\VO\AiEditorialJobs} de la base de datos.
     * Consiste en buscar todos los registros que coinciden con las variables
     * del objeto {@link \OmegaUp\DAO\VO\AiEditorialJobs} pasado como argumento.
     * Aquellas variables que tienen valores NULL seran excluidos en busqueda.
     *
     * <code>
     *   // Example usage - get a set of all objects where column A = 1
     *   $jobVO = new \OmegaUp\DAO\VO\AiEditorialJobs();
     *   $jobVO->job_id = 1;
     *   $resultObjects = \OmegaUp\DAO\AiEditorialJobs::search($jobVO);
     *
     *   foreach($resultObjects as $job){
     *        echo $job->user_id . " ";
     *   }
     * </code>
     *
     * @param \OmegaUp\DAO\VO\AiEditorialJobs $AI_Editorial_Jobs
     * @return list<\OmegaUp\DAO\VO\AiEditorialJobs> Un arreglo de objetos de tipo {@link \OmegaUp\DAO\VO\AiEditorialJobs}
     */
    final public static function search(\OmegaUp\DAO\VO\AiEditorialJobs $AI_Editorial_Jobs): array {
        $clauses = [];
        $params = [];
        if (!is_null($AI_Editorial_Jobs->job_id)) {
            $clauses[] = '`AI_Editorial_Jobs`.`job_id` = ?';
            $params[] = $AI_Editorial_Jobs->job_id;
        }
        if (!is_null($AI_Editorial_Jobs->problem_id)) {
            $clauses[] = '`AI_Editorial_Jobs`.`problem_id` = ?';
            $params[] = $AI_Editorial_Jobs->problem_id;
        }
        if (!is_null($AI_Editorial_Jobs->user_id)) {
            $clauses[] = '`AI_Editorial_Jobs`.`user_id` = ?';
            $params[] = $AI_Editorial_Jobs->user_id;
        }
        if (!is_null($AI_Editorial_Jobs->status)) {
            $clauses[] = '`AI_Editorial_Jobs`.`status` = ?';
            $params[] = $AI_Editorial_Jobs->status;
        }
        if (!is_null($AI_Editorial_Jobs->error_message)) {
            $clauses[] = '`AI_Editorial_Jobs`.`error_message` = ?';
            $params[] = $AI_Editorial_Jobs->error_message;
        }
        if (!is_null($AI_Editorial_Jobs->is_retriable)) {
            $clauses[] = '`AI_Editorial_Jobs`.`is_retriable` = ?';
            $params[] = $AI_Editorial_Jobs->is_retriable ? 1 : 0;
        }
        if (!is_null($AI_Editorial_Jobs->attempts)) {
            $clauses[] = '`AI_Editorial_Jobs`.`attempts` = ?';
            $params[] = $AI_Editorial_Jobs->attempts;
        }
        $clauses[] = '`AI_Editorial_Jobs`.`created_at` = ?';
        $params[] = \OmegaUp\DAO\DAO::toMySQLTimestamp(
            $AI_Editorial_Jobs->created_at
        );
        if (!is_null($AI_Editorial_Jobs->md_en)) {
            $clauses[] = '`AI_Editorial_Jobs`.`md_en` = ?';
            $params[] = $AI_Editorial_Jobs->md_en;
        }
        if (!is_null($AI_Editorial_Jobs->md_es)) {
            $clauses[] = '`AI_Editorial_Jobs`.`md_es` = ?';
            $params[] = $AI_Editorial_Jobs->md_es;
        }
        if (!is_null($AI_Editorial_Jobs->md_pt)) {
            $clauses[] = '`AI_Editorial_Jobs`.`md_pt` = ?';
            $params[] = $AI_Editorial_Jobs->md_pt;
        }
        if (!is_null($AI_Editorial_Jobs->validation_verdict)) {
            $clauses[] = '`AI_Editorial_Jobs`.`validation_verdict` = ?';
            $params[] = $AI_Editorial_Jobs->validation_verdict;
        }
        $whereClause = 'WHERE ' . implode(' AND ', $clauses);
        $sql = "
            SELECT
                `AI_Editorial_Jobs`.`job_id`,
                `AI_Editorial_Jobs`.`problem_id`,
                `AI_Editorial_Jobs`.`user_id`,
                `AI_Editorial_Jobs`.`status`,
                `AI_Editorial_Jobs`.`error_message`,
                `AI_Editorial_Jobs`.`is_retriable`,
                `AI_Editorial_Jobs`.`attempts`,
                `AI_Editorial_Jobs`.`created_at`,
                `AI_Editorial_Jobs`.`md_en`,
                `AI_Editorial_Jobs`.`md_es`,
                `AI_Editorial_Jobs`.`md_pt`,
                `AI_Editorial_Jobs`.`validation_verdict`
            FROM
                `AI_Editorial_Jobs`
            {$whereClause};";
        /** @var list<array{attempts: int, created_at: \OmegaUp\Timestamp, error_message: null|string, is_retriable: int, job_id: string, md_en: null|string, md_es: null|string, md_pt: null|string, problem_id: int, status: string, user_id: int, validation_verdict: null|string}> */
        $allData = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            $params
        );
        $allObjects = [];
        foreach ($allData as $row) {
            $allObjects[] = new \OmegaUp\DAO\VO\AiEditorialJobs($row);
        }
        return $allObjects;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará la información de base de datos identificados por
     * la clave primaria en el objeto {@link \OmegaUp\DAO\VO\AiEditorialJobs} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que no hay llaves primarias.
     *
     * @param \OmegaUp\DAO\VO\AiEditorialJobs $AI_Editorial_Jobs El
     * objeto de tipo \OmegaUp\DAO\VO\AiEditorialJobs a eliminar
     * @return int El número de filas afectadas.
     */
    final public static function delete(\OmegaUp\DAO\VO\AiEditorialJobs $AI_Editorial_Jobs): int {
        $sql = '
            DELETE FROM
                `AI_Editorial_Jobs`
            WHERE
                `job_id` = ?;';
        $params = [$AI_Editorial_Jobs->job_id];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo
     * {@link \OmegaUp\DAO\VO\AiEditorialJobs}.
     * Este método consume una cantidad considerable de memoria porque
     * almacenará en memoria todos los objetos de la tabla.
     *
     * @return list<\OmegaUp\DAO\VO\AiEditorialJobs> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\AiEditorialJobs}.
     */
    final public static function getAll(
        ?int $pagina = null,
        ?int $filasPorPagina = null,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ): array {
        $sql = '
            SELECT
                `AI_Editorial_Jobs`.`job_id`,
                `AI_Editorial_Jobs`.`problem_id`,
                `AI_Editorial_Jobs`.`user_id`,
                `AI_Editorial_Jobs`.`status`,
                `AI_Editorial_Jobs`.`error_message`,
                `AI_Editorial_Jobs`.`is_retriable`,
                `AI_Editorial_Jobs`.`attempts`,
                `AI_Editorial_Jobs`.`created_at`,
                `AI_Editorial_Jobs`.`md_en`,
                `AI_Editorial_Jobs`.`md_es`,
                `AI_Editorial_Jobs`.`md_pt`,
                `AI_Editorial_Jobs`.`validation_verdict`
            FROM
                `AI_Editorial_Jobs`
        ';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . \OmegaUp\MySQLConnection::getInstance()->escape(
                $orden
            ) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * intval(
                $filasPorPagina
            )) . ', ' . intval(
                $filasPorPagina
            );
        }
        /** @var list<array{attempts: int, created_at: \OmegaUp\Timestamp, error_message: null|string, is_retriable: int, job_id: string, md_en: null|string, md_es: null|string, md_pt: null|string, problem_id: int, status: string, user_id: int, validation_verdict: null|string}> */
        $allData = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql);
        $allObjects = [];
        foreach ($allData as $row) {
            $allObjects[] = new \OmegaUp\DAO\VO\AiEditorialJobs($row);
        }
        return $allObjects;
    }
}
