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
 * Value Object class for table `Course_Clone_Log`.
 *
 * @access public
 */
class CourseCloneLog extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'course_clone_log_id' => true,
        'ip' => true,
        'course_id' => true,
        'new_course_id' => true,
        'token_payload' => true,
        'timestamp' => true,
        'user_id' => true,
        'result' => true,
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
        if (isset($data['course_clone_log_id'])) {
            $this->course_clone_log_id = intval(
                $data['course_clone_log_id']
            );
        }
        if (isset($data['ip'])) {
            $this->ip = is_scalar(
                $data['ip']
            ) ? strval($data['ip']) : '';
        }
        if (isset($data['course_id'])) {
            $this->course_id = intval(
                $data['course_id']
            );
        }
        if (isset($data['new_course_id'])) {
            $this->new_course_id = intval(
                $data['new_course_id']
            );
        }
        if (isset($data['token_payload'])) {
            $this->token_payload = is_scalar(
                $data['token_payload']
            ) ? strval($data['token_payload']) : '';
        }
        if (isset($data['timestamp'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['timestamp']
             * @var \OmegaUp\Timestamp $this->timestamp
             */
            $this->timestamp = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['timestamp']
                )
            );
        } else {
            $this->timestamp = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
        if (isset($data['user_id'])) {
            $this->user_id = intval(
                $data['user_id']
            );
        }
        if (isset($data['result'])) {
            $this->result = is_scalar(
                $data['result']
            ) ? strval($data['result']) : '';
        }
    }

    /**
     * Identificador del intento de clonar curso
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $course_clone_log_id = 0;

    /**
     * Dirección IP desde la cual se intentó clonar el curso.
     *
     * @var string|null
     */
    public $ip = null;

    /**
     * ID del curso original
     *
     * @var int|null
     */
    public $course_id = null;

    /**
     * ID del curso nuevo, null si no se pudo colonar el curso
     *
     * @var int|null
     */
    public $new_course_id = null;

    /**
     * Claims del token usado para intentar clonar, independientemente de si fue exitoso o no.
     *
     * @var string|null
     */
    public $token_payload = null;

    /**
     * Fecha y hora en la que el usuario intenta clonar el curso
     *
     * @var \OmegaUp\Timestamp
     */
    public $timestamp;  // CURRENT_TIMESTAMP

    /**
     * ID del usuario que intentó clonar.
     *
     * @var int|null
     */
    public $user_id = null;

    /**
     * Resultado obtenido del intento de clonación de curso
     *
     * @var string
     */
    public $result = 'success';
}
