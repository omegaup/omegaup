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
 * Value Object class for table `GSoC_Idea_Edition`.
 *
 * @access public
 */
class GSoCIdeaEdition extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'idea_edition_id' => true,
        'idea_id' => true,
        'edition_id' => true,
        'status' => true,
        'decision_notes' => true,
        'created_at' => true,
        'updated_at' => true,
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
        if (isset($data['idea_edition_id'])) {
            $this->idea_edition_id = intval(
                $data['idea_edition_id']
            );
        }
        if (isset($data['idea_id'])) {
            $this->idea_id = intval(
                $data['idea_id']
            );
        }
        if (isset($data['edition_id'])) {
            $this->edition_id = intval(
                $data['edition_id']
            );
        }
        if (isset($data['status'])) {
            $this->status = is_scalar(
                $data['status']
            ) ? strval($data['status']) : '';
        }
        if (isset($data['decision_notes'])) {
            $this->decision_notes = is_scalar(
                $data['decision_notes']
            ) ? strval($data['decision_notes']) : '';
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
        if (isset($data['updated_at'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['updated_at']
             * @var \OmegaUp\Timestamp $this->updated_at
             */
            $this->updated_at = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['updated_at']
                )
            );
        } else {
            $this->updated_at = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
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
    public $idea_edition_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $idea_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $edition_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string
     */
    public $status = 'Proposed';

    /**
     * Notas explicando la decisión tomada para este proyecto en esta edición
     *
     * @var string|null
     */
    public $decision_notes = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $created_at;  // CURRENT_TIMESTAMP

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $updated_at;  // CURRENT_TIMESTAMP
}
