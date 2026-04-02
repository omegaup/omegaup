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
 * Value Object class for table `Problem_Bookmarks`.
 *
 * @access public
 */
class ProblemBookmarks extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'identity_id' => true,
        'problem_id' => true,
        'created_at' => true,
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
        if (isset($data['identity_id'])) {
            $this->identity_id = intval(
                $data['identity_id']
            );
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = intval(
                $data['problem_id']
            );
        }
        if (isset($data['created_at'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['created_at']
             * @var \OmegaUp\Timestamp $this->created_at
             */
            $this->created_at = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['created_at']
                )
            );
        } else {
            $this->created_at = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var int|null
     */
    public $identity_id = null;

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var int|null
     */
    public $problem_id = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $created_at;  // CURRENT_TIMESTAMP
}
