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
 * Value Object class for table `Recommendation_Model_Runs`.
 *
 * @access public
 */
class RecommendationModelRuns extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'model_run_id' => true,
        'cron_run_id' => true,
        'map_score' => true,
        'dataset_size' => true,
        'num_followups' => true,
        'followup_decay' => true,
        'train_fraction' => true,
        'output_path' => true,
        'published' => true,
        'skip_reason' => true,
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
        if (isset($data['model_run_id'])) {
            $this->model_run_id = intval(
                $data['model_run_id']
            );
        }
        if (isset($data['cron_run_id'])) {
            $this->cron_run_id = intval(
                $data['cron_run_id']
            );
        }
        if (isset($data['map_score'])) {
            $this->map_score = floatval(
                $data['map_score']
            );
        }
        if (isset($data['dataset_size'])) {
            $this->dataset_size = intval(
                $data['dataset_size']
            );
        }
        if (isset($data['num_followups'])) {
            $this->num_followups = intval(
                $data['num_followups']
            );
        }
        if (isset($data['followup_decay'])) {
            $this->followup_decay = floatval(
                $data['followup_decay']
            );
        }
        if (isset($data['train_fraction'])) {
            $this->train_fraction = floatval(
                $data['train_fraction']
            );
        }
        if (isset($data['output_path'])) {
            $this->output_path = is_scalar(
                $data['output_path']
            ) ? strval($data['output_path']) : '';
        }
        if (isset($data['published'])) {
            $this->published = boolval(
                $data['published']
            );
        }
        if (isset($data['skip_reason'])) {
            $this->skip_reason = is_scalar(
                $data['skip_reason']
            ) ? strval($data['skip_reason']) : '';
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
     * Auto Incremento
     *
     * @var int|null
     */
    public $model_run_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $cron_run_id = null;

    /**
     * [Campo no documentado]
     *
     * @var float|null
     */
    public $map_score = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $dataset_size = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $num_followups = null;

    /**
     * [Campo no documentado]
     *
     * @var float|null
     */
    public $followup_decay = null;

    /**
     * [Campo no documentado]
     *
     * @var float|null
     */
    public $train_fraction = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $output_path = null;

    /**
     * [Campo no documentado]
     *
     * @var bool
     */
    public $published = false;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $skip_reason = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $created_at;  // CURRENT_TIMESTAMP
}
