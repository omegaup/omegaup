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
 * Value Object class for table `Schools`.
 *
 * @access public
 */
class Schools extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'school_id' => true,
        'country_id' => true,
        'state_id' => true,
        'name' => true,
        'rank' => true,
        'score' => true,
        'rank_in_the_month' => true,
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
        if (isset($data['school_id'])) {
            $this->school_id = intval(
                $data['school_id']
            );
        }
        if (isset($data['country_id'])) {
            $this->country_id = strval(
                $data['country_id']
            );
        }
        if (isset($data['state_id'])) {
            $this->state_id = strval(
                $data['state_id']
            );
        }
        if (isset($data['name'])) {
            $this->name = strval(
                $data['name']
            );
        }
        if (isset($data['rank'])) {
            $this->rank = intval(
                $data['rank']
            );
        }
        if (isset($data['score'])) {
            $this->score = floatval(
                $data['score']
            );
        }
        if (isset($data['rank_in_the_month'])) {
            $this->rank_in_the_month = intval(
                $data['rank_in_the_month']
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
    public $school_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $country_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $state_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $name = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $rank = null;

    /**
     * [Campo no documentado]
     *
     * @var float
     */
    public $score = 0.00;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $rank_in_the_month = null;
}
