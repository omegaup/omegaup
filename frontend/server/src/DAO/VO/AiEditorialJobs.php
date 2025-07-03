<?php
// WARNING: This file is auto-generated. Do not modify it directly.

namespace OmegaUp\DAO\VO;

/**
 * Value Object file for table AI_Editorial_Jobs.
 *
 * VO does not have any behaviour except for storage.
 * @access public
 */
class AiEditorialJobs extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
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
            $this->job_id = strval($data['job_id']);
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = intval($data['problem_id']);
        }
        if (isset($data['user_id'])) {
            $this->user_id = intval($data['user_id']);
        }
        if (isset($data['status'])) {
            $this->status = strval($data['status']);
        }
        if (isset($data['error_message'])) {
            $this->error_message = is_null(
                $data['error_message']
            ) ? null : strval(
                $data['error_message']
            );
        }
        if (isset($data['is_retriable'])) {
            $this->is_retriable = boolval($data['is_retriable']);
        }
        if (isset($data['attempts'])) {
            $this->attempts = intval($data['attempts']);
        }
        if (isset($data['created_at'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['created_at']
             * @var \OmegaUp\Timestamp
             */
            $this->created_at = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['created_at']
                )
            );
        } else {
            $this->created_at = new \OmegaUp\Timestamp(\OmegaUp\Time::get());
        }
        if (isset($data['md_en'])) {
            $this->md_en = is_null(
                $data['md_en']
            ) ? null : strval(
                $data['md_en']
            );
        }
        if (isset($data['md_es'])) {
            $this->md_es = is_null(
                $data['md_es']
            ) ? null : strval(
                $data['md_es']
            );
        }
        if (isset($data['md_pt'])) {
            $this->md_pt = is_null(
                $data['md_pt']
            ) ? null : strval(
                $data['md_pt']
            );
        }
        if (isset($data['validation_verdict'])) {
            $this->validation_verdict = is_null(
                $data['validation_verdict']
            ) ? null : strval(
                $data['validation_verdict']
            );
        }
    }

    /**
     * Identificador único del trabajo de editorial IA
     * Llave Primaria
     * @var string|null
     */
    public $job_id = null;

    /**
     * ID del problema para el cual se genera la editorial
     * @var int|null
     */
    public $problem_id = null;

    /**
     * ID del usuario que solicitó la generación
     * @var int|null
     */
    public $user_id = null;

    /**
     * Estado del trabajo (queued, processing, completed, failed, approved, rejected)
     * @var string|null
     */
    public $status = 'queued';

    /**
     * Mensaje de error si el trabajo falló
     * @var string|null
     */
    public $error_message = null;

    /**
     * Indica si el error permite reintentos
     * @var bool|null
     */
    public $is_retriable = true;

    /**
     * Número de intentos de procesamiento
     * @var int|null
     */
    public $attempts = 0;

    /**
     * Timestamp de creación del trabajo
     * @var \OmegaUp\Timestamp
     */
    public $created_at;  // CURRENT_TIMESTAMP

    /**
     * Editorial generada en inglés (formato Markdown)
     * @var string|null
     */
    public $md_en = null;

    /**
     * Editorial generada en español (formato Markdown)
     * @var string|null
     */
    public $md_es = null;

    /**
     * Editorial generada en portugués (formato Markdown)
     * @var string|null
     */
    public $md_pt = null;

    /**
     * Veredicto de validación del contenido generado
     * @var string|null
     */
    public $validation_verdict = null;
}
