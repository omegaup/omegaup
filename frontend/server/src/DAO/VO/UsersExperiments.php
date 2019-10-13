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
 * Value Object class for table `Users_Experiments`.
 *
 * @access public
 */
class UsersExperiments extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'user_id' => true,
        'experiment' => true,
    ];

    public function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['user_id'])) {
            $this->user_id = intval($data['user_id']);
        }
        if (isset($data['experiment'])) {
            $this->experiment = strval($data['experiment']);
        }
    }

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
    public $experiment = null;
}
