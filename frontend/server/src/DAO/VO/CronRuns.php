<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\VO;

/**
 * Value Object class for table `Cron_Runs`.
 *
 * @access public
 */
class CronRuns extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'run_id' => true,
        'name' => true,
        'hostname' => true,
        'status' => true,
        'started_at' => true,
        'finished_at' => true,
        'duration_seconds' => true,
        'rows_affected' => true,
        'phases' => true,
        'error_text' => true,
        'created_at' => true,
    ];

    public function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception(
                'Unknown columns: ' . join(', ', array_keys($unknownColumns))
            );
        }
        if (isset($data['run_id'])) {
            $this->run_id = intval(
                $data['run_id']
            );
        }
        if (isset($data['name'])) {
            $this->name = is_scalar(
                $data['name']
            ) ? strval($data['name']) : '';
        }
        if (isset($data['hostname'])) {
            $this->hostname = is_scalar(
                $data['hostname']
            ) ? strval($data['hostname']) : '';
        }
        if (isset($data['status'])) {
            $this->status = is_scalar(
                $data['status']
            ) ? strval($data['status']) : '';
        }
        if (isset($data['started_at'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['started_at']
             * @var \OmegaUp\Timestamp $this->started_at
             */
            $this->started_at = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['started_at']
                )
            );
        }
        if (isset($data['finished_at'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['finished_at']
             * @var \OmegaUp\Timestamp $this->finished_at
             */
            $this->finished_at = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['finished_at']
                )
            );
        }
        if (isset($data['duration_seconds'])) {
            $this->duration_seconds = floatval(
                $data['duration_seconds']
            );
        }
        if (isset($data['rows_affected'])) {
            $this->rows_affected = intval(
                $data['rows_affected']
            );
        }
        if (isset($data['phases'])) {
            $this->phases = is_scalar(
                $data['phases']
            ) ? strval($data['phases']) : '';
        }
        if (isset($data['error_text'])) {
            $this->error_text = is_scalar(
                $data['error_text']
            ) ? strval($data['error_text']) : '';
        }
        if (isset($data['created_at'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['created_at']
             * @var \OmegaUp\Timestamp $this->created_at
             */
            $this->created_at = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['created_at']
                )
            );
        } else {
            $this->created_at = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $run_id = 0;

    /**
     * Nombre del script (parser.prog). Denormalizado a propósito: el historial se registra aunque el trabajo no esté en Cron_Jobs y sobrevive a renombres o borrados del registro
     *
     * @var string|null
     */
    public $name = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $hostname = null;

    /**
     * [Campo no documentado]
     *
     * @var string
     */
    public $status = 'running';

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp|null
     */
    public $started_at = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp|null
     */
    public $finished_at = null;

    /**
     * [Campo no documentado]
     *
     * @var float|null
     */
    public $duration_seconds = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $rows_affected = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $phases = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $error_text = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $created_at;  // CURRENT_TIMESTAMP
}
