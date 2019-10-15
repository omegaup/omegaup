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
    const FIELD_NAMES = [
        'coder_of_the_month_id' => true,
        'user_id' => true,
        'description' => true,
        'time' => true,
        'interview_url' => true,
        'rank' => true,
        'selected_by' => true,
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
            $this->description = strval(
                $data['description']
            );
        }
        if (isset($data['time'])) {
            $this->time = strval(
                $data['time']
            );
        }
        if (isset($data['interview_url'])) {
            $this->interview_url = strval(
                $data['interview_url']
            );
        }
        if (isset($data['rank'])) {
            $this->rank = intval(
                $data['rank']
            );
        }
        if (isset($data['selected_by'])) {
            $this->selected_by = intval(
                $data['selected_by']
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
    public $rank = null;

    /**
     * Id de la identidad que seleccionó al coder.
     *
     * @var int|null
     */
    public $selected_by = null;
}
