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
 * Value Object class for table `Course_Identity_Request`.
 *
 * @access public
 */
class CourseIdentityRequest extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'identity_id' => true,
        'course_id' => true,
        'request_time' => true,
        'last_update' => true,
        'accepted' => true,
        'extra_note' => true,
        'accept_teacher' => true,
        'share_user_information' => true,
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
        if (isset($data['identity_id'])) {
            $this->identity_id = intval(
                $data['identity_id']
            );
        }
        if (isset($data['course_id'])) {
            $this->course_id = intval(
                $data['course_id']
            );
        }
        if (isset($data['request_time'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['request_time']
             * @var \OmegaUp\Timestamp $this->request_time
             */
            $this->request_time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['request_time']
                )
            );
        } else {
            $this->request_time = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
        if (isset($data['last_update'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['last_update']
             * @var \OmegaUp\Timestamp $this->last_update
             */
            $this->last_update = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['last_update']
                )
            );
        }
        if (isset($data['accepted'])) {
            $this->accepted = boolval(
                $data['accepted']
            );
        }
        if (isset($data['extra_note'])) {
            $this->extra_note = is_scalar(
                $data['extra_note']
            ) ? strval($data['extra_note']) : '';
        }
        if (isset($data['accept_teacher'])) {
            $this->accept_teacher = boolval(
                $data['accept_teacher']
            );
        }
        if (isset($data['share_user_information'])) {
            $this->share_user_information = boolval(
                $data['share_user_information']
            );
        }
    }

    /**
     * Identidad del usuario
     * Llave Primaria
     *
     * @var int|null
     */
    public $identity_id = null;

    /**
     * Curso al cual se necesita un request para ingresar
     * Llave Primaria
     *
     * @var int|null
     */
    public $course_id = null;

    /**
     * Hora en la que se realizó el request
     *
     * @var \OmegaUp\Timestamp
     */
    public $request_time;  // CURRENT_TIMESTAMP

    /**
     * Última fecha de actualización del request
     *
     * @var \OmegaUp\Timestamp|null
     */
    public $last_update = null;

    /**
     * Indica si la respuesta del request fue aceptada
     *
     * @var bool|null
     */
    public $accepted = null;

    /**
     * Indica una descripción con el motivo de aceptar o rechazar un usuario al curso
     *
     * @var string|null
     */
    public $extra_note = null;

    /**
     * Almacena la respuesta del participante de un curso si acepta al organizador como su maestro.
     *
     * @var bool|null
     */
    public $accept_teacher = null;

    /**
     * Almacena la respuesta del participante de un curso si está de acuerdo en divulgar su información.
     *
     * @var bool|null
     */
    public $share_user_information = null;
}
