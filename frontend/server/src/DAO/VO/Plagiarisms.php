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
 * Value Object class for table `Plagiarisms`.
 *
 * @access public
 */
class Plagiarisms extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'plagiarism_id' => true,
        'contest_id' => true,
        'submission_id_1' => true,
        'submission_id_2' => true,
        'score_1' => true,
        'score_2' => true,
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
        if (isset($data['plagiarism_id'])) {
            $this->plagiarism_id = intval(
                $data['plagiarism_id']
            );
        }
        if (isset($data['contest_id'])) {
            $this->contest_id = intval(
                $data['contest_id']
            );
        }
        if (isset($data['submission_id_1'])) {
            $this->submission_id_1 = intval(
                $data['submission_id_1']
            );
        }
        if (isset($data['submission_id_2'])) {
            $this->submission_id_2 = intval(
                $data['submission_id_2']
            );
        }
        if (isset($data['score_1'])) {
            $this->score_1 = intval(
                $data['score_1']
            );
        }
        if (isset($data['score_2'])) {
            $this->score_2 = intval(
                $data['score_2']
            );
        }
        if (isset($data['contents'])) {
            $this->contents = is_scalar(
                $data['contents']
            ) ? strval($data['contents']) : '';
        }
    }

    /**
     * El identificador único para cada potencial caso de plagio
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $plagiarism_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $contest_id = null;

    /**
     * El identificador del envío del primer código plagiado
     *
     * @var int|null
     */
    public $submission_id_1 = null;

    /**
     * El identificador del envío del segundo código plagiado
     *
     * @var int|null
     */
    public $submission_id_2 = null;

    /**
     * porcentaje de plagio encontrado usando copydetect en el envío 1
     *
     * @var int|null
     */
    public $score_1 = null;

    /**
     * porcentaje de plagio encontrado usando copydetect en el envío 2
     *
     * @var int|null
     */
    public $score_2 = null;

    /**
     * Almacena los rangos de números de línea de las similitudes
     *
     * @var string|null
     */
    public $contents = null;
}
