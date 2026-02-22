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
 * Value Object class for table `Submission_Feedback_Thread`.
 *
 * @access public
 */
class SubmissionFeedbackThread extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'submission_feedback_thread_id' => true,
        'submission_feedback_id' => true,
        'identity_id' => true,
        'date' => true,
        'contents' => true,
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
        if (isset($data['submission_feedback_thread_id'])) {
            $this->submission_feedback_thread_id = intval(
                $data['submission_feedback_thread_id']
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
        if (isset($data['contents'])) {
            $this->contents = is_scalar(
                $data['contents']
            ) ? strval($data['contents']) : '';
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $submission_feedback_thread_id = 0;

    /**
     * Identificador del comentario asociado
     *
     * @var int|null
     */
    public $submission_feedback_id = null;

    /**
     * Identidad de quien envió el feedback
     *
     * @var int|null
     */
    public $identity_id = null;

    /**
     * Hora en la que se envió el feedback
     *
     * @var \OmegaUp\Timestamp
     */
    public $date;  // CURRENT_TIMESTAMP

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $contents = null;
}
