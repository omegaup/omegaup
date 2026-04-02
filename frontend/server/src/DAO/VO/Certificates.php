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
    public const FIELD_NAMES = [
        'certificate_id' => true,
        'identity_id' => true,
        'timestamp' => true,
        'certificate_type' => true,
        'course_id' => true,
        'contest_id' => true,
        'coder_of_the_month_id' => true,
        'verification_code' => true,
        'contest_place' => true,
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
        if (isset($data['coder_of_the_month_id'])) {
            $this->coder_of_the_month_id = intval(
                $data['coder_of_the_month_id']
            );
        }
        if (isset($data['verification_code'])) {
            $this->verification_code = is_scalar(
                $data['verification_code']
            ) ? strval($data['verification_code']) : '';
        }
        if (isset($data['contest_place'])) {
            $this->contest_place = intval(
                $data['contest_place']
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
    public $certificate_id = 0;

    /**
     * [Campo no documentado]
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
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $course_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $contest_id = null;

    /**
     * Id del Coder del mes que obtuvo el certificado
     *
     * @var int|null
     */
    public $coder_of_the_month_id = null;

    /**
     * Código de verificación del diploma
     *
     * @var string|null
     */
    public $verification_code = null;

    /**
     * Se guarda el lugar en el que quedo un estudiante si es menor o igual a certificate_cutoff
     *
     * @var int|null
     */
    public $contest_place = null;
}
