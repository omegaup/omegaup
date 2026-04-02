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
 * Value Object class for table `AI_Editorial_Jobs`.
 *
 * @access public
 */
class AIEditorialJobs extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'job_id' => true,
        'problem_id' => true,
        'user_id' => true,
        'status' => true,
        'error_message' => true,
        'is_retriable' => true,
        'attempts' => true,
        'created_at' => true,
        'md_en' => true,
        'md_es' => true,
        'md_pt' => true,
        'validation_verdict' => true,
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
        if (isset($data['job_id'])) {
            $this->job_id = is_scalar(
                $data['job_id']
            ) ? strval($data['job_id']) : '';
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = intval(
                $data['problem_id']
            );
        }
        if (isset($data['user_id'])) {
            $this->user_id = intval(
                $data['user_id']
            );
        }
        if (isset($data['status'])) {
            $this->status = is_scalar(
                $data['status']
            ) ? strval($data['status']) : '';
        }
        if (isset($data['error_message'])) {
            $this->error_message = is_scalar(
                $data['error_message']
            ) ? strval($data['error_message']) : '';
        }
        if (isset($data['is_retriable'])) {
            $this->is_retriable = boolval(
                $data['is_retriable']
            );
        }
        if (isset($data['attempts'])) {
            $this->attempts = intval(
                $data['attempts']
            );
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
        if (isset($data['md_en'])) {
            $this->md_en = is_scalar(
                $data['md_en']
            ) ? strval($data['md_en']) : '';
        }
        if (isset($data['md_es'])) {
            $this->md_es = is_scalar(
                $data['md_es']
            ) ? strval($data['md_es']) : '';
        }
        if (isset($data['md_pt'])) {
            $this->md_pt = is_scalar(
                $data['md_pt']
            ) ? strval($data['md_pt']) : '';
        }
        if (isset($data['validation_verdict'])) {
            $this->validation_verdict = is_scalar(
                $data['validation_verdict']
            ) ? strval($data['validation_verdict']) : '';
        }
    }

    /**
     * UUID identificador único del trabajo
     * Llave Primaria
     *
     * @var string|null
     */
    public $job_id = null;

    /**
     * Identificador del problema
     *
     * @var int|null
     */
    public $problem_id = null;

    /**
     * Usuario que solicitó la generación
     *
     * @var int|null
     */
    public $user_id = null;

    /**
     * Estado actual del trabajo
     *
     * @var string
     */
    public $status = 'queued';

    /**
     * Mensaje de error en caso de fallo
     *
     * @var string|null
     */
    public $error_message = null;

    /**
     * Indica si el error permite reintentos (1 = sí, 0 = no)
     *
     * @var bool
     */
    public $is_retriable = true;

    /**
     * Número de intentos realizados
     *
     * @var int
     */
    public $attempts = 0;

    /**
     * Hora de creación del trabajo
     *
     * @var \OmegaUp\Timestamp
     */
    public $created_at;  // CURRENT_TIMESTAMP

    /**
     * Editorial generado en inglés
     *
     * @var string|null
     */
    public $md_en = null;

    /**
     * Editorial generado en español
     *
     * @var string|null
     */
    public $md_es = null;

    /**
     * Editorial generado en portugués
     *
     * @var string|null
     */
    public $md_pt = null;

    /**
     * Veredicto de validación del código generado
     *
     * @var string|null
     */
    public $validation_verdict = null;
}
