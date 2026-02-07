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
 * Value Object class for table `User_Readme_Report_Log`.
 *
 * @access public
 */
class UserReadmeReportLog extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'readme_id' => true,
        'reporter_user_id' => true,
        'report_time' => true,
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
        if (isset($data['readme_id'])) {
            $this->readme_id = intval(
                $data['readme_id']
            );
        }
        if (isset($data['reporter_user_id'])) {
            $this->reporter_user_id = intval(
                $data['reporter_user_id']
            );
        }
        if (isset($data['report_time'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['report_time']
             * @var \OmegaUp\Timestamp $this->report_time
             */
            $this->report_time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['report_time']
                )
            );
        } else {
            $this->report_time = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
    }

    /**
     * README reportado
     * Llave Primaria
     *
     * @var int|null
     */
    public $readme_id = null;

    /**
     * Usuario que hizo el reporte
     * Llave Primaria
     *
     * @var int|null
     */
    public $reporter_user_id = null;

    /**
     * Fecha y hora del reporte
     *
     * @var \OmegaUp\Timestamp
     */
    public $report_time;  // CURRENT_TIMESTAMP
}
