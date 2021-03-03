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
 * Value Object class for table `Certificates`.
 *
 * @access public
 */
class Certificates extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'certificate_id' => true,
        'identity_id' => true,
        'timestamp' => true,
        'certificate_type' => true,
        'course_id' => true,
        'contest_id' => true,
        'verification_code' => true,
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
        if (isset($data['certificate_id'])) {
            $this->certificate_id = intval(
                $data['certificate_id']
            );
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = intval(
                $data['identity_id']
            );
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
        if (isset($data['certificate_type'])) {
            $this->certificate_type = is_scalar(
                $data['certificate_type']
            ) ? strval($data['certificate_type']) : '';
        }
        if (isset($data['course_id'])) {
            $this->course_id = intval(
                $data['course_id']
            );
        }
        if (isset($data['contest_id'])) {
            $this->contest_id = intval(
                $data['contest_id']
            );
        }
        if (isset($data['verification_code'])) {
            $this->verification_code = is_scalar(
                $data['verification_code']
            ) ? strval($data['verification_code']) : '';
        }
    }

    /**
     * Identificador del diploma
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $certificate_id = 0;

    /**
     * Identificador del usuario acreedor del diploma
     *
     * @var int|null
     */
    public $identity_id = null;

    /**
     * Fecha y hora del otorgamiento del diploma
     *
     * @var \OmegaUp\Timestamp
     */
    public $timestamp;  // CURRENT_TIMESTAMP

    /**
     * Tipo de diploma
     *
     * @var string|null
     */
    public $certificate_type = null;

    /**
     * ID del curso
     *
     * @var int|null
     */
    public $course_id = null;

    /**
     * ID del concurso
     *
     * @var int|null
     */
    public $contest_id = null;

    /**
     * Código de verificación del diploma
     *
     * @var string|null
     */
    public $verification_code = null;
}
