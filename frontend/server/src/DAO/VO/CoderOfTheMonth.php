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
 * Value Object class for table `Coder_Of_The_Month`.
 *
 * @access public
 */
class CoderOfTheMonth extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'coder_of_the_month_id' => true,
        'user_id' => true,
        'description' => true,
        'time' => true,
        'interview_url' => true,
        'ranking' => true,
        'selected_by' => true,
        'school_id' => true,
        'category' => true,
        'score' => true,
        'problems_solved' => true,
        'certificate_status' => true,
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
        if (isset($data['coder_of_the_month_id'])) {
            $this->coder_of_the_month_id = intval(
                $data['coder_of_the_month_id']
            );
        }
        if (isset($data['user_id'])) {
            $this->user_id = intval(
                $data['user_id']
            );
        }
        if (isset($data['description'])) {
            $this->description = is_scalar(
                $data['description']
            ) ? strval($data['description']) : '';
        }
        if (isset($data['time'])) {
            $this->time = is_scalar(
                $data['time']
            ) ? strval($data['time']) : '';
        }
        if (isset($data['interview_url'])) {
            $this->interview_url = is_scalar(
                $data['interview_url']
            ) ? strval($data['interview_url']) : '';
        }
        if (isset($data['ranking'])) {
            $this->ranking = intval(
                $data['ranking']
            );
        }
        if (isset($data['selected_by'])) {
            $this->selected_by = intval(
                $data['selected_by']
            );
        }
        if (isset($data['school_id'])) {
            $this->school_id = intval(
                $data['school_id']
            );
        }
        if (isset($data['category'])) {
            $this->category = is_scalar(
                $data['category']
            ) ? strval($data['category']) : '';
        }
        if (isset($data['score'])) {
            $this->score = floatval(
                $data['score']
            );
        }
        if (isset($data['problems_solved'])) {
            $this->problems_solved = intval(
                $data['problems_solved']
            );
        }
        if (isset($data['certificate_status'])) {
            $this->certificate_status = is_scalar(
                $data['certificate_status']
            ) ? strval($data['certificate_status']) : '';
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $coder_of_the_month_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $user_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $description = null;

    /**
     * Fecha no es UNIQUE por si hay más de 1 coder de mes.
     *
     * @var string
     */
    public $time = '2000-01-01';

    /**
     * Para linekar a un post del blog con entrevistas.
     *
     * @var string|null
     */
    public $interview_url = null;

    /**
     * El lugar en el que el usuario estuvo durante ese mes
     *
     * @var int|null
     */
    public $ranking = null;

    /**
     * Id de la identidad que seleccionó al coder.
     *
     * @var int|null
     */
    public $selected_by = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $school_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string
     */
    public $category = 'all';

    /**
     * [Campo no documentado]
     *
     * @var float
     */
    public $score = 0.00;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $problems_solved = 0;

    /**
     * Estado de la petición de generar diplomas
     *
     * @var string
     */
    public $certificate_status = 'uninitiated';
}
