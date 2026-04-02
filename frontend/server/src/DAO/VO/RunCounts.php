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
 * Value Object class for table `Run_Counts`.
 *
 * @access public
 */
class RunCounts extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'date' => true,
        'total' => true,
        'ac_count' => true,
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
        if (isset($data['date'])) {
            $this->date = is_scalar(
                $data['date']
            ) ? strval($data['date']) : '';
        }
        if (isset($data['total'])) {
            $this->total = intval(
                $data['total']
            );
        }
        if (isset($data['ac_count'])) {
            $this->ac_count = intval(
                $data['ac_count']
            );
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var string|null
     */
    public $date = null;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $total = 0;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $ac_count = 0;
}
