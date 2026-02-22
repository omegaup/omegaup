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
 * Value Object class for table `Submission_Feedback`.
 *
 * @access public
 */
class SubmissionFeedback extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'submission_feedback_id' => true,
        'identity_id' => true,
        'submission_id' => true,
        'feedback' => true,
        'date' => true,
        'range_bytes_start' => true,
        'range_bytes_end' => true,
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
        if (isset($data['submission_feedback_id'])) {
            $this->submission_feedback_id = intval(
                $data['submission_feedback_id']
            );
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = intval(
                $data['identity_id']
            );
        }
        if (isset($data['submission_id'])) {
            $this->submission_id = intval(
                $data['submission_id']
            );
        }
        if (isset($data['feedback'])) {
            $this->feedback = is_scalar(
                $data['feedback']
            ) ? strval($data['feedback']) : '';
        }
        if (isset($data['date'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['date']
             * @var \OmegaUp\Timestamp $this->date
             */
            $this->date = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['date']
                )
            );
        } else {
            $this->date = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
        if (isset($data['range_bytes_start'])) {
            $this->range_bytes_start = intval(
                $data['range_bytes_start']
            );
        }
        if (isset($data['range_bytes_end'])) {
            $this->range_bytes_end = intval(
                $data['range_bytes_end']
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
    public $submission_feedback_id = 0;

    /**
     * Identidad de quien envió el feedback
     *
     * @var int|null
     */
    public $identity_id = null;

    /**
     * Identificador del envío asociado
     *
     * @var int|null
     */
    public $submission_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $feedback = null;

    /**
     * Hora en la que se envió el feedback
     *
     * @var \OmegaUp\Timestamp
     */
    public $date;  // CURRENT_TIMESTAMP

    /**
     * Inicio de la subcadena seleccionada (en bytes) para agregarle el comentario
     *
     * @var int|null
     */
    public $range_bytes_start = null;

    /**
     * Fin de la subcadena seleccionada (en bytes) para agregarle el comentario
     *
     * @var int|null
     */
    public $range_bytes_end = null;
}
