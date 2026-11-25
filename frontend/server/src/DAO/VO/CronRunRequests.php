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
 * Value Object class for table `Cron_Run_Requests`.
 *
 * @access public
 */
class CronRunRequests extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'request_id' => true,
        'name' => true,
        'requested_by' => true,
        'status' => true,
        'requested_at' => true,
        'picked_at' => true,
        'finished_at' => true,
        'run_id' => true,
        'error_text' => true,
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
        if (isset($data['request_id'])) {
            $this->request_id = intval(
                $data['request_id']
            );
        }
        if (isset($data['name'])) {
            $this->name = is_scalar(
                $data['name']
            ) ? strval($data['name']) : '';
        }
        if (isset($data['requested_by'])) {
            $this->requested_by = intval(
                $data['requested_by']
            );
        }
        if (isset($data['status'])) {
            $this->status = is_scalar(
                $data['status']
            ) ? strval($data['status']) : '';
        }
        if (isset($data['requested_at'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['requested_at']
             * @var \OmegaUp\Timestamp $this->requested_at
             */
            $this->requested_at = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['requested_at']
                )
            );
        } else {
            $this->requested_at = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
        if (isset($data['picked_at'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['picked_at']
             * @var \OmegaUp\Timestamp $this->picked_at
             */
            $this->picked_at = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['picked_at']
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
        if (isset($data['run_id'])) {
            $this->run_id = intval(
                $data['run_id']
            );
        }
        if (isset($data['error_text'])) {
            $this->error_text = is_scalar(
                $data['error_text']
            ) ? strval($data['error_text']) : '';
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $request_id = 0;

    /**
     * Nombre del script cuya reejecución se solicita
     *
     * @var string|null
     */
    public $name = null;

    /**
     * El administrador que la solicitó, NULL si su cuenta ya no existe
     *
     * @var int|null
     */
    public $requested_by = null;

    /**
     * pending mientras espera al despachador, picked mientras corre
     *
     * @var string
     */
    public $status = 'pending';

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $requested_at;  // CURRENT_TIMESTAMP

    /**
     * Cuando el despachador tomó la solicitud
     *
     * @var \OmegaUp\Timestamp|null
     */
    public $picked_at = null;

    /**
     * Cuando terminó la ejecución, haya salido bien o mal
     *
     * @var \OmegaUp\Timestamp|null
     */
    public $finished_at = null;

    /**
     * La ejecución que produjo, NULL si no llegó a correr o si el historial ya se purgó
     *
     * @var int|null
     */
    public $run_id = null;

    /**
     * El final de stderr cuando la ejecución falló
     *
     * @var string|null
     */
    public $error_text = null;
}
