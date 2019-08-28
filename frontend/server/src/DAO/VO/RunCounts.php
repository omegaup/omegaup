<?php
/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

namespace OmegaUp\DAO\VO;

/**
 * Value Object class for table `Run_Counts`.
 *
 * @access public
 */
class RunCounts extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'date' => true,
        'total' => true,
        'ac_count' => true,
    ];

    function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['date'])) {
            $this->date = strval($data['date']);
        }
        if (isset($data['total'])) {
            $this->total = (int)$data['total'];
        }
        if (isset($data['ac_count'])) {
            $this->ac_count = (int)$data['ac_count'];
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
